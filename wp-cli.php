<?php

use Exploreo\VillaMetaData;

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

class Algolia_Command {
    public function reindex_post($args, $assoc_args) {
        global $algolia;
        $index = $algolia->initIndex('dev_villas');

        $index->clearObjects()->wait();

        $paged = 1;
        $count = 0;

        do {
            $posts = new WP_Query([
                'posts_per_page' => 100,
                'paged' => $paged,
                'post_type' => 'villas'
            ]);

            if (!$posts->have_posts()) {
                break;
            }

            $records = [];

            // TODO remove once we know how to pass in args
            $assoc_args['verbose'] = true;

            foreach ($posts->posts as $post) {
                if (!empty($assoc_args['verbose'])) {
                    WP_CLI::line('Serializing ['.$post->post_title.']');
                }

                $postMeta = get_post_meta($post->ID);
                if (empty($postMeta) || empty($postMeta[VillaMetaData::META_KEY_HOUSE_CODE])) {
                    wp_delete_post($post->ID, true);
                    continue;
                }

                $record = (array) apply_filters('post_to_record', $post);
                if (empty($record)) {
                    continue;
                }

                if (!isset($record['objectID'])) {
                    $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
                }

                $records[] = $record;
                $count++;
            }

            if (!empty($assoc_args['verbose'])) {
                WP_CLI::line('Sending batch');
            }

            $index->saveObjects($records);

            $paged++;

        } while (true);

        WP_CLI::success("$count posts indexed in Algolia");

        $myfile = fopen("/home/clients/f5324ef7a235265c578ecd3a2a09430e/cron-logs/test.txt", "w") or die("Unable to open file!");
        fwrite($myfile, "$count posts indexed in Algolia\n");
        fclose($myfile);
    }
}


WP_CLI::add_command('algolia', 'Algolia_Command');
