<?php
// Enqueue CSS and JavaScript
function sp_post_tabs_enqueue_assets() {
    wp_enqueue_style(
        'sp-post-tabs-css',
        get_stylesheet_directory_uri() . '/post-tabs/assets/css/post-tabs.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'post-tabs-js',
        get_stylesheet_directory_uri() . '/post-tabs/assets/js/post-tabs.js',
        ['jquery'],
        '1.0.0',
        true
    );

    wp_localize_script('post-tabs-js', 'sp_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('sp_nonce'),
    ]);

    // Debug: Print sp_ajax in the console
    wp_add_inline_script('post-tabs-js', 'console.log("sp_ajax:", sp_ajax);');
}
add_action('wp_enqueue_scripts', 'sp_post_tabs_enqueue_assets');

// Include other functionality
require_once get_stylesheet_directory() . '/post-tabs/shortcode.php';
require_once get_stylesheet_directory() . '/post-tabs/ajax.php';
