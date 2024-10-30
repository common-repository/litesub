<?php

if ( ! defined('ABSPATH') ) {
  exit;
}

/**
 * Helper methods for WP posts
 */
class Litesub_Post {

  /**
   * Get thumbnail url for a post. First try featured image, if not
   * exist, try the first image in attachments
   */
  public static function thumbnail_image_url($wp_post) {
    $featured_image = get_the_post_thumbnail_url($wp_post);
    if (!$featured_image) {
      $images = get_children( array (
        'post_parent' => $wp_post->ID,
        'post_type' => 'attachment',
        'post_mime_type' => 'image'
      ));

      if ( !empty($images) ) {
        $featured_image = $images[0]['src'];
      }
    }
    return $featured_image;
  }

  /**
   * Get the excerpt of a post
   */
  public static function excerpt($wp_post) {
    $excerpt_len = 100;
    $excerpt = $wp_post->excerpt;
    if (empty($excerpt)) {
      $excerpt = wp_strip_all_tags($wp_post->post_content);
    }
    return substr($excerpt, 0, $excerpt_len) . (strlen($excerpt) > $excerpt_len ? '...' : '');
  }

  /**
   * Get the specified attributes from a post
   */
  public static function pluck($wp_post, $attrs) {
    $data = array();
    foreach ($attrs as $attr) {
      switch ($attr) {
      case 'post_excerpt':
        $data[$attr] = Litesub_Post::excerpt($wp_post);
        break;
      case 'permalink':
        $data[$attr] = get_permalink($wp_post);
        break;
      default:
        $data[$attr] = $wp_post->$attr;
      }
    }
    return $data;
  }
}
