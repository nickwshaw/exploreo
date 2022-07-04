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
     * @var array
     */
    private $villaCodesInDb;

    private function __construct()
    {

        global $wpdb;

        $this->wpdb = $wpdb;

        add_action('init', array($this, 'createVillaPostType'));

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

        add_action('admin_init', function() {

            if (!isset($_GET['insert_villa_posts'])) {
                return;
            }
            $count = 0;


            $this->villaCodesInDb = $this->wpdb->get_col(
            'SELECT meta_value from wp_postmeta WHERE meta_key = "' . VillaMetaData::META_KEY_HOUSE_CODE . '"'
            );

            foreach ($this->getListOfVillas() as $villa)
            {
                if (in_array($villa['HouseCode'], $this->villaCodesInDb) || $villa['RealOrTest'] == 'Test') {
                    // Villa has already been imported or a test villa
                    continue;
                }
                // Limit number of imports for now
                if ($count > 1) {
                    exit;
                }
                if ($this->insertVillaPost($this->getVillaDetails($villa['HouseCode']))) {
                    $count++;
                }

            }
            echo 'DONE!';
            //var_dump($this->getVillaDetails('AT.5360.01'));
        });

    }

    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function insertVillaPost(array $villaDetails): ?int
    {
        $houseCode = $villaDetails['HouseCode'];
        $basicInfo = $villaDetails[VillaMetaData::API_METHOD_BASIC_INFORMATION];
        $description = $villaDetails[VillaMetaData::API_METHOD_DESCRIPTION];
        $media = $villaDetails[VillaMetaData::API_METHOD_MEDIA][0];

        $englishDescription = [];

        foreach ($description as $language) {
            if ($language['Language'] == 'EN') {
                $englishDescription = $language;
            }
        }

        $postData = [
            "post_title" => $basicInfo['Name'],
            "post_content" => $englishDescription['Description'],
            "post_type" => self::VILLA_POST_TYPE,
            "post_status" => "publish"
        ];

        $id = wp_insert_post($postData);

        // Insert custom fields

        update_post_meta($id, VillaMetaData::META_KEY_HOUSE_CODE, $houseCode);

        foreach (VillaMetaData::getBasicInformationMetaDataKeys() as $apiKey => $metaDataKey) {
            update_post_meta($id, $metaDataKey, $basicInfo[$apiKey]);
        }

        update_post_meta(
            $id,
            VillaMetaData::META_KEY_MEDIA_PHOTOS,
            $media[VillaMetaData::API_KEY_MEDIA_PHOTOS]
        );

        return $id;
    }

    private function getApiResponse(string $url, array $body): array
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
            'sslverify'   => false, //TODO cahnge
            'data_format' => 'body',
        ]);

        try {
            $json = json_decode( $response['body'], true );
        } catch ( Exception $ex ) {
            $json = null;
        }

        return $json['result'];
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

        return $this->getApiResponse($url, $data)[0];
    }

    private function getListOfVillas(): array
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'ListOfHousesV1',
            'params' => [
                'PartnerCode' => $this->apiUsername,
                'PartnerPassword' => $this->apiPassword
            ],
            'id' => '38114532'
        ];

        $url = 'https://listofhousesv1.villaforyou.biz/cgi/jsonrpc-partner/listofhousesv1';

        return $this->getApiResponse($url, $data);
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
