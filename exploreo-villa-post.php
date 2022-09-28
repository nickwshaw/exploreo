<?php

/*
  Plugin Name: Exploreo Villa
  Plugin URI: http://wordpress.org/plugins/exploreo_villa_post/
  Description: Creates a Villa post type
  Author: Nick Shaw
  Version: 0.1.0
  Author URI:
*/

use Exploreo\VillaMetaData;
use Exploreo\Exception\VillaNotFoundException;

require_once('src/VillaMetaData.php');
require_once('config.php');

class ExploreoVilla {

    public const VILLA_POST_TYPE = 'villas';

    /**
     * @var ExploreoVilla
     */
    private static $instance;


    private $wpdb;

    /**
     * @var string
     */
    private $apiPassword = VILLA_API_PASSWORD;

    /**
     * @var string
     */
    private $apiUsername = VILLA_API_USERNAME;

    /**
     * @var string
     */
    private $tablePrefix = TABLE_PREFIX;

    /**
     * @var array
     */
    private $villaCodesInDb;

    private $latestUpdateVersion;

    /**
     * @var array
     */
    private $houseTypes;

    private function __construct()
    {

        global $wpdb;

        $this->wpdb = $wpdb;

        // Create the villa post type on install
        add_action('init', array($this, 'createVillaPostType'));

        // Create a hook for import cron job
        add_action('exploreo_villas_import', array($this, 'importVillas'));

        // Create a hook for update cron job
        add_action('exploreo_villas_update', array($this, 'updateVillasCron'));

        /**
         * Show 'insert posts' button on backend
         */
        add_action( "admin_notices", function() {
            echo "<div class='updated'>";
            echo "<p>";
            echo "To insert the posts into the database, click the button to the right.";
            echo "<a class='button button-primary' style='margin:0.25em 1em' href='{$_SERVER["REQUEST_URI"]}&insert_villa_posts'>Import Villas</a>";
            echo "</p>";
            echo "</div>";
        });

        add_filter('cron_schedules', array($this, 'villaImportInterval'));
    }

    public function villaImportInterval(array $schedules)
    {
        $schedules['5 minutes'] = array(
            'interval' => 60 * 5,
            'display'  => esc_html__('Every Five Minutes'),);
        return $schedules;
    }

    public function getLatestUpdateVersion(): int
    {
        if (is_null($this->latestUpdateVersion)) {
            $result = $this->wpdb->get_row('
                select * from ' . $this->tablePrefix . 'postmeta
                where meta_key = "' . VillaMetaData::META_KEY_UPDATE_VERSION . '"
                order by cast(meta_value as unsigned) desc limit 1
            ');
            $this->latestUpdateVersion = ($result) ? (int) $result->meta_value : 0;
        }

        return $this->latestUpdateVersion;
    }

    public function updateVillasCron()
    {
        $start = microtime(true);
        // Get villas with lowest update version
        $results = $this->wpdb->get_results('
            select * from ' . $this->tablePrefix . 'postmeta
            where meta_key = "' . VillaMetaData::META_KEY_UPDATE_VERSION . '"
            order by cast(meta_value as unsigned) asc limit 100
        ');

        error_log('Got ' . count($results));
        error_log('first post: ' . $results[0]->post_id);

        $villasUpdatedCount = 0;

        foreach ($results as $row) {
            $checksum = $this->wpdb->get_col('
                SELECT meta_value
                from ' . $this->tablePrefix . 'postmeta
                where post_id = ' . $row->post_id . '
                and meta_key = "villa_checksum"'
            );

            $houseCode = $this->wpdb->get_col('
                SELECT meta_value
                from ' . $this->tablePrefix . 'postmeta
                where post_id = ' . $row->post_id . '
                and meta_key = "' . VillaMetaData::META_KEY_HOUSE_CODE . '"'
            );

            if (empty($houseCode)) {
                wp_delete_post($row->post_id, true);
                error_log(sprintf('No house code meta_data found for post id: %s', $row->post_id));
                continue;
            }

            $postsWithHouseCode = $this->wpdb->get_col('
                SELECT meta_value
                from ' . $this->tablePrefix . 'postmeta
                where meta_value = "' . $houseCode[0] . '"
                and meta_key = "' . VillaMetaData::META_KEY_HOUSE_CODE . '"'
            );

            // Delete duplicates
            if (count($postsWithHouseCode) > 1) {
                wp_delete_post($row->post_id, true);
                error_log(sprintf(
                    'Deleted villa %s with post id %s as it is a duplicate',
                    $houseCode[0],
                    $row->post_id
                ));
                continue;
            }

            try {
                // Get villa from API and do checksum check.
                $villaPostData = $this->prepareVillaPostData($this->getVillaDetails($houseCode[0]));
            } catch (VillaNotFoundException $exception) {
                // No longer in API. Delete post.
                wp_delete_post($row->post_id, true);
                error_log(sprintf(
                    'Deleted villa %s with post id %s as it no longer exists in the VFY API',
                    $houseCode[0],
                    $row->post_id
                ));
                continue;
            }

            if (serialize($villaPostData) !== $checksum[0]) {
                error_log(sprintf('Villa data changed. Update required for post id: %s', $row->post_id));
                $this->updateVilla($row->post_id, $villaPostData);
                $villasUpdatedCount++;
            }

            // Increment version
            $version = (int) $row->meta_value;
            $version++;
            update_post_meta(
                $row->post_id,
                VillaMetaData::META_KEY_UPDATE_VERSION,
                (string) $version
            );
        }

        $end = microtime(true);
        $elapsed = round($end-$start, 2);

        error_log('Updated ' . $villasUpdatedCount . ' villas');
        error_log('Update time: ' . $elapsed);
    }



    public function importVillas()
    {
        $start = microtime(true);
//        if (!isset($_GET['insert_villa_posts'])) {
//            return;
//        }
        $count = 0;

        $this->villaCodesInDb = $this->wpdb->get_col('
            SELECT meta_value 
            FROM ' . $this->tablePrefix . 'postmeta 
            WHERE meta_key = "' . VillaMetaData::META_KEY_HOUSE_CODE . '"    
        ');

        $villaCodesFromApi = [];

        foreach ($this->getListOfVillas() as $villa)
        {

            if ($villa['RealOrTest'] != 'Test') {
                $villaCodesFromApi[] = $villa['HouseCode'];
            } else {
                // Not interested in test villas
                continue;
            }
            // Check if villa already exits in DB
            if (in_array($villa['HouseCode'], $this->villaCodesInDb)) {
                continue;
            }
            // Limit number of imports for now
            if ($count > 100) {
                continue;
            }
            try {
                if ($this->insertVillaPost($this->getVillaDetails($villa['HouseCode']))) {
                    $count++;
                }
            } catch (RuntimeException $exception) {
                error_log(sprintf('Error importing villas. Reason: %s', $exception->getMessage()));
            }

        }
        error_log('Number of villas imported: ' . $count);
        error_log('Villas from API: ' . count($villaCodesFromApi));
        error_log('Number of villas in db: ' . count($this->villaCodesInDb));
        $end = microtime(true);
        $elapsed = round($end-$start, 2);
        error_log("Import time: $elapsed");

    }

    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function prepareVillaPostData(array $villaDetails): array
    {
        if (null === $this->houseTypes) {
            $this->houseTypes = $this->getVillaTypes();
        }

        $villaData = [];
        $villaData['houseCode'] = $villaDetails['HouseCode'];
        $villaData['basicInfo'] = $villaDetails[VillaMetaData::API_METHOD_BASIC_INFORMATION];
        $villaData['media'] = $villaDetails[VillaMetaData::API_METHOD_MEDIA][0];

        foreach ($villaDetails[VillaMetaData::API_METHOD_DESCRIPTION] as $language) {
            if ($language['Language'] == 'EN') {
                $villaData['description'] = $language['Description'];
            }
        }

        return $villaData;
    }

    public function updateVilla(int $postId, array $villaData)
    {
        $postData = [
            "ID" => $postId,
            "post_title" => $villaData['basicInfo']['Name'],
            "post_content" => $villaData['description'],
        ];
        wp_update_post($postData);

        $this->insertUpdateMetaData($postId, $villaData);
    }


    private function insertVillaPost(array $villaDetails): ?int
    {
        $villaData = $this->prepareVillaPostData($villaDetails);

        $postData = [
            "post_title" => $villaData['basicInfo']['Name'],
            "post_content" => $villaData['description'],
            "post_type" => self::VILLA_POST_TYPE,
            "post_status" => "publish"
        ];

        $id = wp_insert_post($postData);

        update_post_meta($id, VillaMetaData::META_KEY_HOUSE_CODE, $villaData['houseCode']);

        // Get the latest update version and use that for new posts
        update_post_meta(
            $id,
            VillaMetaData::META_KEY_UPDATE_VERSION,
            (string) $this->getLatestUpdateVersion()
        );

        $this->insertUpdateMetaData($id, $villaData);

        return $id;
    }

    /**
     * Metadata that needs an updating for both insert and update
     * @param int $id
     * @param array $villaData
     */
    private function insertUpdateMetaData(int $id, array $villaData) {
        update_post_meta($id, VillaMetaData::META_KEY_CHECKSUM, $villaData);

        foreach (VillaMetaData::getBasicInformationMetaDataKeys() as $apiKey => $metaDataKey) {
            update_post_meta($id, $metaDataKey, $villaData['basicInfo'][$apiKey]);
        }

        update_post_meta(
            $id,
            VillaMetaData::META_KEY_MEDIA_PHOTOS,
            $villaData['media'][VillaMetaData::API_KEY_MEDIA_PHOTOS]
        );

        update_post_meta(
            $id,
            VillaMetaData::META_KEY_HOUSE_TYPE,
            $this->houseTypes[$villaData['basicInfo'][VillaMetaData::API_KEY_HOUSE_TYPE]]
        );

    }

    private function getApiResponse(string $url, array $body = []): array
    {
        $response = wp_remote_post($url, [
            'body' => wp_json_encode($body),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->apiUsername . ':' . $this->apiPassword),
            ),
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => false, //TODO change
            'data_format' => 'body',
        ]);

        if ($response instanceof WP_Error) {
            foreach ($response->get_error_messages() as $error) {
                error_log($error);
            }
            throw new RuntimeException('Error making request to VFY with payload: ' . wp_json_encode($body));
        }

        try {
            $json = json_decode($response['body'], true);
        } catch ( Exception $ex ) {
            $json = null;
        }

        return $json['result'];
    }

    private function getVillaTypes(): array
    {
        $result =  $this->getApiResponse(
            $this->getApiUrl('referencehousetypesv1'),
            $this->getBasePayLoad('ReferenceHouseTypesV1')
        );

        foreach ($result as $record) {
            $translations = [];
            foreach ($record['Description'] as $description) {
                $translations[$description['Language']] = $description['Description'];
            }
            $houseTypes[$record['Id']] = $translations;
        }

        return $houseTypes;
    }

    private function getApiUrl(string $action): string
    {
        return 'https://listofhousesv1.villaforyou.biz/cgi/jsonrpc-partner/' . $action;
    }

    private function getVillaDetails(string $villaId): array
    {
        $url = 'https://dataofhousesv1.villaforyou.biz/cgi/jsonrpc-partner/dataofhousesv1';

        $data = [
            'jsonrpc' => '2.0',
            'method' => 'DataOfHousesV1',
            'params' => [
                'PartnerCode' => $this->apiUsername,
                'PartnerPassword' => $this->apiPassword,
                'HouseCodes' => [$villaId],
                'Items' => [
                    VillaMetaData::API_METHOD_BASIC_INFORMATION,
                    VillaMetaData::API_METHOD_DESCRIPTION,
                    VillaMetaData::API_METHOD_MEDIA
                ]
            ],
        ];

        $response = $this->getApiResponse($url, $data);

        if (isset($response[0]['error'])) {

            if ($response[0]['error'] === sprintf('HouseCode %s is unknown', strtolower($villaId))) {
                throw new VillaNotFoundException($response[0]['error']);
            }

            throw new RuntimeException(
                sprintf(
                    'Error response from VFY when getting villa details with code %s. Error message: %s',
                    $villaId,
                    $response[0]['error']
                )
            );
        }

        return $this->getApiResponse($url, $data)[0];
    }

    private function getListOfVillas(): array
    {
        $url = 'https://listofhousesv1.villaforyou.biz/cgi/jsonrpc-partner/listofhousesv1';

        return $this->getApiResponse($url, $this->getBasePayLoad('ListOfHousesV1'));
    }

    private function getBasePayLoad(string $method): array
    {
        return  [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => [
                'PartnerCode' => $this->apiUsername,
                'PartnerPassword' => $this->apiPassword
            ],
        ];

    }

    public function createVillaPostType()
    {
        // Set UI labels for Custom Post Type
        $labels = array(
            'name'                => _x( 'Villas', 'Post Type General Name', 'exploreo-villa-post' ),
            'singular_name'       => _x( 'Villa', 'Post Type Singular Name', 'exploreo-villa-post' ),
            'menu_name'           => __( 'Villas', 'exploreo-villa-post' ),
            'parent_item_colon'   => __( 'Parent Villa', 'exploreo-villa-post' ),
            'all_items'           => __( 'All Villas', 'exploreo-villa-post' ),
            'view_item'           => __( 'View Villa', 'exploreo-villa-post' ),
            'add_new_item'        => __( 'Add New Villa', 'exploreo-villa-post' ),
            'add_new'             => __( 'Add New', 'exploreo-villa-post' ),
            'edit_item'           => __( 'Edit Villa', 'exploreo-villa-post' ),
            'update_item'         => __( 'Update Villa', 'exploreo-villa-post' ),
            'search_items'        => __( 'Search Villa', 'exploreo-villa-post' ),
            'not_found'           => __( 'Not Found', 'exploreo-villa-post' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'exploreo-villa-post' ),
        );

        // Set other options for Custom Post Type
        $args = array(
            'label'               => __( 'villas', 'exploreo-villa-post' ),
            'description'         => __( 'Villa news and reviews', 'exploreo-villa-post' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'taxonomies'          => array( 'genres' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'show_in_rest' => true,
        );
        // Registering your Custom Post Type
        register_post_type( 'villas', $args );
    }
}

ExploreoVilla::get_instance();
