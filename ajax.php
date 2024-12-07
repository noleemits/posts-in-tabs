<?php

add_action('wp_ajax_sp_load_tab', 'sp_load_tab_content');
add_action('wp_ajax_nopriv_sp_load_tab', 'sp_load_tab_content');

function sp_load_tab_content() {
    check_ajax_referer('sp_nonce', 'security');

    $category_slug = sanitize_text_field($_POST['category_slug'] ?? '');
    $posts_per_page = intval($_POST['posts_per_page'] ?? 3);
    $paged = max(1, intval($_POST['paged'] ?? 1));

    if (empty($category_slug)) {
        wp_send_json_error(['message' => 'Category slug is missing.']);
        wp_die();
    }

    $query_args = [
        'category_name'  => $category_slug,
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'post_status'    => 'publish',
    ];

    $query = new WP_Query($query_args);

    if ($query->have_posts()) {
        ob_start();

        while ($query->have_posts()) {
            $query->the_post(); // This sets the $post global
            include get_stylesheet_directory() . '/post-tabs/templates/tab-content.php';
        }

        wp_reset_postdata();

        $posts_markup = ob_get_clean();

        wp_send_json_success([
            'content'       => $posts_markup,
            'has_next_page' => $paged < $query->max_num_pages,
            'has_prev_page' => $paged > 1,
        ]);
    } else {
        wp_send_json_error(['message' => 'No posts found in this category.']);
    }

    wp_die();
}
