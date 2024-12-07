<?php
// helpers.php

function sp_get_tab_query_args($slug, $posts_per_page = 3, $paged = 1) {
    $query_args = [
        'category_name'  => sanitize_text_field($slug),
        'posts_per_page' => intval($posts_per_page),
        'paged'          => max(1, intval($paged)),
    ];

    // Print query args for debugging
    echo '<pre>';
    var_dump($query_args);
    echo '</pre>';

    return $query_args;
}
