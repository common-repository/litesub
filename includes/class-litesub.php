<?php

if ( ! defined('ABSPATH') ) {
  exit;
}

/**
 * Litesub plugin constants and helper methods
 * @since 1.0
 */
class Litesub {

  const VERSION = '1.0';
  const PREFIX = 'litesub';
  const DEBUG = LITESUB_DEBUG;

  // wp_option key for litesub user data
  const USER_DATA_OPTION_NAME = 'litesub_user_data';

  const API_ENDPOINT = LITESUB_API_ENDPOINT;

  // custom post type for newsletter
  const POST_TYPE_NEWSLETTER = 'litesub_newsletter';
  // litesub dashboard page id
  const TOPLEVEL_PAGE_LITESUB_HOME = 'toplevel_page_litesub_home';

  /**
   * Load the user data from database
   */
  public static function user_data() {
    $user_data = get_option(Litesub::USER_DATA_OPTION_NAME);
    if (!$user_data) {
      return null;
    }
    return json_decode($user_data, true);
  }

  /**
   * Save the user data to wp_options
   */
  public static function update_user_data($data) {
    $value = gettype($data) == 'string' ? $data : json_encode($data);
    $key = Litesub::USER_DATA_OPTION_NAME;
    if (get_option($key)) {
      update_option($key, $value);
    } else {
      add_option($key, $value);
    }
  }

  /**
   * Build the URL to the Litesub form script, then this URL and be
   * add to target blog pages, to load and show the subscription popup
   */
  public static function form_url() {
    $user_data = Litesub::user_data();
    $url = Litesub::API_ENDPOINT . '/lib/form.min.js?id=' . $user_data['form_id'];
    if (Litesub::DEBUG) {
      $url .= '&test=y';
    }
    return $url;
  }

  /**
   * Enable/disable the popup form. Once disabled, the popup will
   * not be added to blog pages.
   * @param boolean $enabled True to enable, false to disable
   */
  public static function toggle_form($enabled) {
    $user_data = Litesub::user_data();
    $user_data['form_enabled'] = $enabled;
    //update_option(Litesub::USER_DATA_OPTION_NAME, json_encode($user_data));
    Litesub::update_user_data($user_data);
  }

  /**
   * Check if popup form is enabled
   */
  public static function form_enabled() {
    $user_data = Litesub::user_data();
    return array_key_exists('form_enabled', $user_data) && $user_data['form_enabled'];
  }

  public static function enqueue_popup_form_script() {
    if (Litesub::form_enabled() && is_single()) {
      wp_enqueue_script('litesub_form',  Litesub::form_url(), array(), Litesub::VERSION, true);
    }
  }

  /**
   * Test if a post is of Litesub newsletter custom type
   * @param WP_Post $post The post to test
   * return boolean Test result
   */
  public static function is_newsletter($post = null) {
    return ($post ? $post->post_type : get_post_type()) == Litesub::POST_TYPE_NEWSLETTER;
  }

  public static function is_dashboard_page($hook) {
    return Litesub::TOPLEVEL_PAGE_LITESUB_HOME == $hook;
  }

  public static function is_newsletter_edit_page($hook) {
    return ($hook == 'post.php' || $hook == 'post-new.php') && get_post_type() == Litesub::POST_TYPE_NEWSLETTER;
  }
}
