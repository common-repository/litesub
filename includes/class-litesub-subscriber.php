<?php

if ( ! defined('ABSPATH') ) {
  exit;
}

/*
 * for future use
 */
class Litesub_Subscriber {
  /**
   * Create a subscriber in local WP instance
   */
  public static function create($options) {
    $email = $options['email'];
    $username = $options['user_name'] || explode('@', $options['email'])[0];
    $_username = $username;
    while (username_exists($_username) != false) {
      $_username = $username . rand();
    }
    $username = $_username;
    if ( email_exists($user_email) == false ) {
      $random_password = wp_generate_password( $length=12, $include_standard_special_chars = false );
      $user_id = wp_insert_user(array(
        'user_login' => $username,
        'user_email' => $email,
        'user_pass' => $random_password,
        'role' => 'subscriber'
      ));
      add_user_meta($user_id, 'litesub_role', 'subscriber');
    }
  }
}
