<?php

if ( ! defined('ABSPATH') ) {
  exit;
}

require_once(dirname(__FILE__) . '/class-litesub.php');

/**
 * Class to access the Litesub APIs
 * @since 1.0
 */
class Litesub_API {
  /**
   * Build the Litesub API URL given a path. The query parameters will
   * be signed by default.
   * @param $path string The path to a Litesub API service
   * @param $options array Set 'params' to pass in the query parameters,
   *        and set 'signed' to false if the URL doesn't need to be signed
   * @return string URL to an API service
   */
  public static function url($path, $options = array('signed' => true)) {
    if (empty($options['signed']) && empty($options['params'])) {
      return Litesub::API_ENDPOINT . $path;
    } else  {
      $params = isset($options['params']) ? $options['params'] : array();
      return Litesub::API_ENDPOINT . $path . '?' . http_build_query(Litesub_API::sign($params));
    }
  }

  /**
   * Get the URL to Litesub home page, which is now the subscribers page
   * @return string url to Litesub subscribers page
   */
  public static function litesub_page_url() {
    return Litesub_API::url('/subscribers');
  }

  /**
   * Send a newsletter to Litesub. Litesub will then send the newsletter
   * to subscribers or schedule it.
   * @param WP_Post $post WP post of newsletter type
   * @param array $options If 'preview' is set in $options, tell Litesub
   *        to only send a preview mail to admin.
   * @return array Whether the request is success, and the response body
   */
  public static function create_newsletter($post, $options = array()) {
    if (!Litesub::is_newsletter($post)) {
      return;
    }
    $content = Litesub_Newsletter::render_newsletter($post->ID);
    $url = Litesub::API_ENDPOINT . '/newsletter-drafts';
    $params = array(
      'id' => $post->ID,
      'subject' => $post->post_title,
      'body' => $content,
      'source' => 'litesub-wp',
      'post_date_gmt' => $post->post_date_gmt,
    );
    if (isset($options['preview'])) {
      $params['send_for_preview'] = 'y';
    }
    $signed_params = Litesub_API::sign($params);

    $response = wp_remote_post($url, array(
      'body' => json_encode($signed_params),
      'headers' => array(
        'Content-Type' => 'application/json'
      )
    ));
    $http_code = wp_remote_retrieve_response_code($response);

    if (is_wp_error($response) || empty($http_code) || $http_code >= 400) {
      return array(
        'success' => false
      );
    }

    $body = wp_remote_retrieve_body($response);

    return array(
      'success' => true,
      'body' => $body
    );
  }

  /**
   * Register a Litesub account with the site admininster's email, and
   * user data retrieved from Litesub, like API key & secret, will be
   * saved into wp_options table with Litesub::update_user_data.
   * @return boolean True if user set up successfully, false for failure
   */
  public static function register_user() {
    $info = get_bloginfo();
    $admin_email = get_bloginfo('admin_email');
    $url = get_bloginfo('url');
    $body = array(
      'site' => get_bloginfo(),
      'url' => get_bloginfo('url'),
      'admin_email' => get_bloginfo('admin_email')
    );
    $args = array(
      'body' => $body
    );

    $response = wp_remote_post(Litesub::API_ENDPOINT . '/wordpress/register', $args);
    $http_code = wp_remote_retrieve_response_code($response);
    if (is_wp_error($response) || $http_code >= 400) {
      Litesub::update_user_data(array('error' => 'ERR_USER', 'message' => 'Failed to get user data'));
      return false;
    }

    $body = wp_remote_retrieve_body($response);
    Litesub::update_user_data($body);
    return true;
  }

  /**
   * Import subscribers into Litesub. The administrator can specify which
   * wordpress users should be imported by selecting the desired role.
   * This method only send the subscriber emails and names to Litesub, the
   * import will be done asynchronously.
   */
  public static function import_subscribers() {
    $users = get_users(array('role' => $_POST['role']));
    $csv = "email,name\n";
    foreach ($users as $user) {
      $csv .= sprintf("%s,%s\n", $user->user_email, $user->display_name);
    }

    $url = Litesub::API_ENDPOINT . '/subscribers/import';
    $params = array(
      'subscribers_in_csv' => $csv
    );
    $signed_params = Litesub_API::sign($params);

    $response = wp_remote_post($url, array(
      'body' => json_encode($signed_params),
      'headers' => array(
        'Content-Type' => 'application/json'
      )
    ));
    $http_code = wp_remote_retrieve_response_code($response);

    if (is_wp_error($response) || empty($http_code) || $http_code >= 400) {
      return;
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body);
  }

  /**
   * Send uninstallation notification to Litesub
   */
  public static function plugin_uninstalled() {
    wp_remote_post(Litesub::API_ENDPOINT . '/wordpress/uninstall', array(
      'body' => Litesub_API::sign()
    ));
  }

  /**
   * The URL to request an access token from Litesub API
   * @return string
   */
  public static function access_token_url() {
    return Litesub_API::url('/wordpress/access-token');
  }

  /**
   * Sign API request parameters/data with the user's api_secret for user authentication
   * at Litesub API services.
   * @param $data array Query parameters for GET request, or data for POST/PUT/DELETE request
   * @return array Signed parameters/data, the original data plus api_key, timestamp, nonce,
   *               and a signature
   */
  private static function sign($data = array()) {
    $user_data = Litesub::user_data();
    $data['api_key'] = $user_data['api_key'];
    $data['timestamp'] = time();
    $data['nonce'] = rtrim(strtr(base64_encode(random_bytes(8)), '+/', '-_'), '=');
    ksort($data);
    $ar = array();
    foreach ($data as $key => $val) {
      array_push($ar, $key . '=' . urlencode($val));
    }
    $query_str = join('&', $ar);
    $sig = hash_hmac('sha256', $query_str, $user_data['api_secret']);
    $data['sig'] = $sig;
    return $data;
  }
}
