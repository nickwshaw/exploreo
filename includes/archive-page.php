<?php

use Exploreo\VillaMetaData;

function exploreo_archive_query(WP_Query $query) {
    if ($query->is_main_query() && is_post_type_archive('villas')) {
        if (isset($query->query['country'])) {
            $query->set('meta_key', VillaMetaData::META_KEY_COUNTRY_SLUG);
            $query->set('meta_value', $query->query['country']);
        }

        if (isset($query->query['stars'])) {
            $query->set('meta_key', VillaMetaData::META_KEY_NUMBER_OF_STARS);
            $query->set('meta_value', $query->query['stars']);
        }

        if (isset($query->query['bedrooms'])) {
            $query->set('meta_key', VillaMetaData::META_KEY_NUMBER_OF_BEDROOMS);
            $query->set('meta_value', $query->query['bedrooms']);
        }
    }
}

function exploreo_register_query_params() {
    global $wp;
    $wp->add_query_var('country');
    $wp->add_query_var('stars');
    $wp->add_query_var('bedrooms');
}

add_action( 'init', 'exploreo_register_query_params' );
add_action( 'pre_get_posts', 'exploreo_archive_query' );