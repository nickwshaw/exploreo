<?php

use Exploreo\VillaMetaData;

function villas_rewrite_rule() {
    add_rewrite_rule(
        '^villas/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?$',
        'index.php?villas=$matches[4]&post_type=villas',
        'top'
    );
}

function exploreo_holiday_homes_rule() {
    add_rewrite_tag('%country%', '([^&|/]+)', 'country=');
    add_rewrite_tag('%province%', '([^&|/]+)', 'province=');
    add_rewrite_tag('%city%', '([^&|/]+)', 'city=');

    add_rewrite_rule(
        '^holiday-homes/([^/]*)/([^/]*)/([^/]*)/?$',
        'index.php?' .
        'name=$matches[4]' .
        '&post_type=villas'
    );


    add_rewrite_rule('^holiday-homes(/)?$', 'index.php?post_type=villas', 'top');
}

function exploreo_holiday_homes_post_link($permalink, $post) {

    if (false === strpos($permalink, '%country%/%province%/%city%')) {
        return $permalink;
    }

    $tags = ['%country%', '%province%', '%city%'];

    $slugs = [
        get_post_meta($post->ID, VillaMetaData::META_KEY_COUNTRY_SLUG, true),
        get_post_meta($post->ID, VillaMetaData::META_KEY_PROVINCE_SLUG, true),
        get_post_meta($post->ID, VillaMetaData::META_KEY_CITY_SLUG, true),
    ];

    return str_replace($tags, $slugs, $permalink);
}

add_filter('post_type_link', 'exploreo_holiday_homes_post_link', 10, 2);
add_action('init', 'exploreo_holiday_homes_rule');


