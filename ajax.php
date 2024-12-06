<?php
require_once get_stylesheet_directory() . '/post-tabs/helpers.php';

// Register AJAX actions
add_action('wp_ajax_sp_load_tab', 'sp_load_tab_content'); // For logged-in users
add_action('wp_ajax_nopriv_sp_load_tab', 'sp_load_tab_content'); // For non-logged-in users

function sp_load_tab_content() {
    error_log('AJAX handler started.');

    // Verify nonce
    if (!check_ajax_referer('sp_nonce', 'security', false)) {
        error_log('Nonce verification failed.');
        wp_send_json_error(['message' => 'Nonce verification failed.']);
        wp_die();
    }

    $category_slug = sanitize_text_field($_POST['category_slug'] ?? '');
    $posts_per_page = intval($_POST['posts_per_page'] ?? 3);
    $paged = max(1, intval($_POST['paged'] ?? 1));

    if (empty($category_slug)) {
        error_log('Category slug is missing.');
        wp_send_json_error(['message' => 'Category slug is missing.']);
        wp_die();
    }

    $query_args = sp_get_tab_query_args($category_slug, $posts_per_page, $paged);
    $query = new WP_Query($query_args);

    error_log('Total posts found: ' . $query->found_posts);

    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post(); // THIS MUST BE CALLED BEFORE THE TEMPLATE
            error_log('In AJAX: Rendering post: ' . get_the_title()); // Debug
            include get_stylesheet_directory() . '/post-tabs/templates/tab-content.php';
        }

        $posts_markup = ob_get_clean();
        wp_send_json_success([
            'content'       => $posts_markup,
            'has_next_page' => $paged < $query->max_num_pages,
            'has_prev_page' => $paged > 1,
        ]);
    } else {
        error_log('No posts found for category: ' . $category_slug);
        wp_send_json_error(['message' => 'No posts found in this category.']);
    }



    wp_die();
}
