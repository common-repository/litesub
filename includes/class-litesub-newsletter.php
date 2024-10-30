<?php

if ( ! defined('ABSPATH') ) {
  exit;
}

class Litesub_Newsletter {

  /**
   * Get the posts included in a newsletter
   */
  public static function included_posts($newsletter_id) {
    $post_meta = get_post_meta($newsletter_id, 'included-posts', true);
    $post_ids = explode(',', $post_meta);
    $post_query = new WP_Query(array(
      'post__in' => $post_ids
    ));
    // sort posts, so posts in same order as in post ids
    $posts = array();
    foreach ($post_ids as $pid) {
      $next_post = null;
      foreach ($post_query->posts as $p) {
        if ($pid == $p->ID) {
          $next_post = $p;
          break;
        }
      }
      if ($next_post) {
        $posts[] = $next_post;
      }
    }

    return $posts;
  }

  /**
   * helper method to get the posts included in the specified newsletter
   * @param integer $newsletter_id The specified newsletter ID
   * @param boolean $in_array If true, the returned post IDs will be in an array.
   *        When false, in comma seperated string.
   * @return string|array Post IDs
   */
  public static function included_post_ids($newsletter_id, $in_array = false) {
    $ids = get_post_meta($newsletter_id, 'included-posts', true);
    if ($in_array) {
      return explode(',', $ids);
    } else {
      return empty($ids) ? '' : $ids;
    }
  }

  /**
   * Get a list of existing posts that can be added into a newsletter
   */
  public static function candidate_posts($newsletter_id = null, $page = 1) {
    $args = array(
      'post_type' => 'post',
      'orderby' => 'ID',
      'order' => 'desc',
      'ignore_sticky_posts' => true,
      'posts_per_page' => 10,
      'paged' => $page,
    );
    //if ($newsletter_id) {
    //  $args['post__not_in'] = Litesub_Newsletter::included_post_ids($newsletter_id, true);
    //}
    $query = new WP_Query($args);
    $posts = array_map(function ($p) {
      return Litesub_Post::pluck($p, array('ID', 'post_title', 'post_excerpt', 'permalink'));
    }, $query->posts);
    return array(
      'posts' => $posts,
      'wp_query' => $query
    );
  }

  /**
   * Get the newsletter content composed with WP editor, and render it
   * with a simple newsletter template
   * @param interge $id Newsletter id
   * @return string Rendered newsletter in html format
   */
  public static function render_newsletter($id) {
    ob_start();
    $args = array('p' => $id, 'post_type' => 'litesub_newsletter', 'posts_per_page' => 1);
    $query = new WP_Query( $args );
    while ($query->have_posts()) : $query->the_post();
    ?>
      <!DOCTYPE html>
      <html>
        <head>
        <meta name=viewport content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
        <?php wp_head(); ?>
        <style>
        .ls-newsletter {
          padding: 0 20px;
        }
        .ls-posts {
          padding: 30px 0;
        }
        .ls-post {
          margin-bottom: 30px;
        }
        .ls-thumbnail-wrapper img {
          max-height: 200px;
        }
        </style>
        </head>
        <body class='ls-newsletter'>
          <div class='entry-content'>
            <?php the_content(); ?>
          </div>
        </body>
      </html>
    <?php
    endwhile;

    $html = Litesub_Newsletter::clean_html(ob_get_contents());
    ob_end_clean();
    wp_reset_query();
    return do_shortcode($html);
  }

  /**
   * remove script tags from the given html snippet
   */
  private static function clean_html($snippet) {
    $regex = '/<\s*script.*?>.+?<\/\s*script\s*>/si';
    return preg_replace($regex, '', $snippet);
  }
}
