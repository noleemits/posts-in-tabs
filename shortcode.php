<?php

require_once get_stylesheet_directory() . '/post-tabs/helpers.php';


function sp_post_tabs_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts([
        'titles'        => 'Más recientes,Destacados,Préstamos Personales',
        'slugs'         => 'recent,featured,personal-loans',
        'posts_per_tab' => 3,
    ], $atts, 'sp-post-tab');

    // Parse titles and slugs into arrays
    $titles = explode(',', $atts['titles']);
    $slugs = explode(',', $atts['slugs']);

    // Ensure exactly three tabs
    $titles = array_slice(array_pad($titles, 3, 'Tab'), 0, 3);
    $slugs = array_slice(array_pad($slugs, 3, ''), 0, 3);

    $output = '<div class="tabs">';
    $output .= '<div class="tab-titles" role="tablist">';
    foreach ($titles as $index => $title) {
        $active_class = $index === 0 ? 'active' : '';
        $aria_selected = $index === 0 ? 'true' : 'false';
        $output .= "<button class='tab-title $active_class' data-tab='tab-$index' data-slug='" . esc_attr($slugs[$index]) . "' data-posts-per-page='" . intval($atts['posts_per_tab']) . "' role='tab' aria-selected='$aria_selected'>" . esc_html($title) . "</button>";
    }
    $output .= '</div>';

    $output .= '<div class="tab-contents">';
    foreach ($slugs as $index => $slug) {
        $active_class = $index === 0 ? 'active' : '';
        $output .= "<div class='tab-content $active_class' id='tab-$index' data-paged='1'>";

        if ($index === 0 && !empty($slug)) {
            $query_args = sp_get_tab_query_args($slug, $atts['posts_per_tab'], 1);
            $query = new WP_Query($query_args);

            if ($query->have_posts()) {
                ob_start();
                while ($query->have_posts()) {
                    $query->the_post(); // IMPORTANT: Sets the $post global
                    include get_stylesheet_directory() . '/post-tabs/templates/tab-content.php';
                }
                $output .= ob_get_clean();
            } else {
                $output .= '<p>No posts found in this category.</p>';
            }
            wp_reset_postdata();
        }


        $output .= '</div>';
    }
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

add_shortcode('sp-post-tab', 'sp_post_tabs_shortcode');
