<?php

add_action('wp_ajax_sp_load_tab', 'sp_load_tab_content');
add_action('wp_ajax_nopriv_sp_load_tab', 'sp_load_tab_content');

function sp_load_tab_content() {
    check_ajax_referer('sp_nonce', 'security');

    $category_slug = sanitize_text_field($_POST['category_slug'] ?? '');
    $posts_per_page = intval($_POST['posts_per_page'] ?? 3);
    $paged = max(1, intval($_POST['paged'] ?? 1));

    // Build query arguments
    $query_args = [
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'post_status'    => 'publish',
    ];

    if (!empty($category_slug) && $category_slug !== 'all') {
        $query_args['category_name'] = $category_slug;
    }

    $query = new WP_Query($query_args);

    if ($query->have_posts()) {

        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            include get_stylesheet_directory() . '/post-tabs/templates/tab-content.php';
        }
        $posts_markup = ob_get_clean();

        // Construct pagination buttons
        $pagination = '<div class="pagination">';
        if ($paged < $query->max_num_pages) {
            $pagination .= "<button class='load-older' data-slug='" . esc_attr($category_slug) . "' data-posts-per-page='" . esc_attr($posts_per_page) . "'>« Entradas más antiguas</button>";
        } else {
            $pagination .= "<button class='load-older disabled' disabled data-slug='" . esc_attr($category_slug) . "' data-posts-per-page='" . esc_attr($posts_per_page) . "'>« Entradas más antiguas</button>";
        }

        if ($paged > 1) {
            $pagination .= "<button class='load-newer' data-slug='" . esc_attr($category_slug) . "' data-posts-per-page='" . esc_attr($posts_per_page) . "'>Entradas más recientes »</button>";
        }


        $pagination .= '</div>';

        wp_send_json_success([
            'content'       => $posts_markup,
            'has_next_page' => $paged < $query->max_num_pages,
            'has_prev_page' => $paged > 1,
            'pagination'    => $pagination,
        ]);
    } else {
        wp_send_json_error(['message' => 'No posts found on this page.']);
    }

    wp_die();
}
