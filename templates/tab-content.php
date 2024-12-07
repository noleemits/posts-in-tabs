<?php

// Debugging: Ensure $post is available
if (!isset($GLOBALS['post'])) {
    error_log('The $post global is not set in the tab-content template.');
}

?>
<div class="tab-post">
    <a href="<?php echo esc_url(get_permalink()); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <img
                src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>"
                alt="<?php echo esc_attr(get_the_title()); ?>"
                class="tab-post-image">
        <?php endif; ?>

    </a>
    <div class="tab-post-details">
        <p class="tab-post-title">
            <a href="<?php echo esc_url(get_permalink()); ?>">
                <?php echo esc_html(get_the_title()); ?>
            </a>
        </p>
        <p class="tab-post-meta">
            por <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                <?php echo esc_html(get_the_author()); ?>
            </a>
        </p>
    </div>
</div>