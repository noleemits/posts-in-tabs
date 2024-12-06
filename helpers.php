<?php
// helpers.php

function sp_get_tab_query_args($category_slug, $posts_per_page, $paged) {
    return [
        'category_name'  => sanitize_title($category_slug),
        'posts_per_page' => intval($posts_per_page),
        'paged'          => max(1, intval($paged)),
        'post_status'    => 'publish',
    ];
}
