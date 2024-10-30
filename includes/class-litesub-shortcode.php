<?php

if ( ! defined('ABSPATH') ) {
  exit;
}

/**
 * Shortcodes used in this plugin
 */
class Litesub_Shortcode {

  const NEWSLETTER_POSTS = 'litesub_newsletter_posts';

  /**
   * Callback for litesub_newsletter_posts shorcode
   */
  public static function newsletter_posts($attrs = [], $content = null) {
    $posts = Litesub_Newsletter::included_posts(get_post()->ID);
    ob_start();
    ?>
    <div class='ls-posts'>
    <?php foreach($posts as $post) : ?>
      <div class='ls-post'>
        <?php
        $thumbnail_url = Litesub_Post::thumbnail_image_url($post);
        if (!empty($thumbnail_url)) :
        ?>
          <div class='ls-thumbnail-wrapper'>
            <img src='<?php echo Litesub_Post::thumbnail_image_url($post); ?>' class='ls-post-thumbnail' />
          </div>
        <?php endif; ?>
        <h2><a href='<?php echo get_permalink($post); ?>'><?php echo $post->post_title; ?></a></h2>
        <div class='ls-post-excerpt'>
          <?php echo Litesub_Post::excerpt($post); ?>
        </div>
        <div class='ls-post-footer'>
          <small>author: <?php echo get_the_author_meta('display_name', $post->post_author); ?></small>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return do_shortcode($content);
  }
}
