<?php

function villas_rewrite_rule() {
    add_rewrite_rule(
        '^holidayhomes/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?$',
        'index.php?villas=$matches[4]&post_type=villas',
        'top'
    );
}
add_action('init', 'villas_rewrite_rule');
//var_dump(get_option( 'rewrite_rules' ));

