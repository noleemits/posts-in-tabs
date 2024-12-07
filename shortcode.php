<?php

function sp_post_tabs_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts([
        'titles'        => 'Más recientes,Destacados,Préstamos Personales',
        'slugs'         => 'recent,featured,personal-loans',
        'posts_per_tab' => 3, // Default to 3 posts per tab
    ], $atts, 'sp-post-tab');

    $posts_per_tab = intval($atts['posts_per_tab']); // Ensure it's an integer

    // Parse titles and slugs into arrays
    $titles = explode(',', $atts['titles']);
    $slugs = explode(',', $atts['slugs']);

    // Ensure exactly three tabs
    $titles = array_slice(array_pad($titles, 3, 'Tab'), 0, 3);
    $slugs = array_slice(array_pad($slugs, 3, ''), 0, 3);

    // Start generating output
    $output = '<div class="tabs">';
    $output .= '<div class="tab-titles" role="tablist">';
    foreach ($titles as $index => $title) {
        $active_class = $index === 0 ? 'active' : '';
        $aria_selected = $index === 0 ? 'true' : 'false';
        $output .= "<button 
            class='tab-title $active_class' 
            data-tab='tab-$index' 
            data-slug='" . esc_attr($slugs[$index]) . "' 
            data-posts-per-page='$posts_per_tab' 
            role='tab' 
            aria-selected='$aria_selected'>" . esc_html($title) . "</button>";
    }
    $output .= '</div>'; // Close tab-titles

    $output .= '<div class="tab-contents">';
    foreach ($slugs as $index => $slug) {
        $active_class = $index === 0 ? 'active' : '';
        $output .= "<div class='tab-content $active_class' id='tab-$index' data-paged='1'>";

        // Render posts server-side for the first tab only
        if ($index === 0) {
            $query_args = [
                'posts_per_page' => $posts_per_tab,
                'paged'          => 1,
                'post_status'    => 'publish',
            ];

            if ($slug !== 'all') {
                $query_args['category_name'] = sanitize_text_field($slug);
            }

            $query = new WP_Query($query_args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    // Include the post template for rendering
                    ob_start();
                    include get_stylesheet_directory() . '/post-tabs/templates/tab-content.php';
                    $output .= ob_get_clean();
                }

                // Add pagination for the first tab
                $output .= '<div class="pagination">';
                if ($query->max_num_pages > 1) {
                    $output .= "<button class='load-older' data-slug='" . esc_attr($slug) . "' data-posts-per-page='$posts_per_tab'>« Entradas más antiguas</button>";
                }
                $output .= "<button class='load-newer' style='display:none;' data-slug='" . esc_attr($slug) . "' data-posts-per-page='$posts_per_tab'>Entradas más recientes »</button>";
                $output .= '</div>';
            } else {
                $output .= '<p>No posts found in this category.</p>';
            }

            wp_reset_postdata();
        }

        $output .= '</div>'; // Close tab-content
    }
    $output .= '</div>'; // Close tab-contents
    $output .= '</div>'; // Close tabs

    return $output;
}
add_shortcode('sp-post-tab', 'sp_post_tabs_shortcode');
