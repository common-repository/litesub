<?php
/*
Plugin Name:  Litesub
Plugin URI:   https://litesub.com
Description:  Do subscriptions & newsletters with Wordpress
Version:      1.0
Author:       Litesub
Author URI:   https://litesub.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  litesub
Domain Path:  /languages
 */

if ( ! defined('ABSPATH') ) {
  exit;
}

require_once(dirname(__FILE__) . '/litesub-config.php');
require_once(dirname(__FILE__) . '/includes/class-litesub.php');
require_once(dirname(__FILE__) . '/includes/class-litesub-api.php');
require_once(dirname(__FILE__) . '/includes/class-litesub-newsletter.php');
require_once(dirname(__FILE__) . '/includes/class-litesub-post.php');
require_once(dirname(__FILE__) . '/includes/class-litesub-shortcode.php');

/**
 * Plugin activation hook - register Litesub account for user, and load
 * some sample data
 */
function litesub_activation() {
  Litesub_API::register_user();

  // create sample newsletter
  $newsletter_query = new WP_Query(array(
    'post_type' => Litesub::POST_TYPE_NEWSLETTER,
  ));
  if ($newsletter_query->post_count == 0) {
    $sample_newsletter_content = file_get_contents(dirname(__FILE__) . '/sample_data/newsletter.txt', 'r');
    wp_insert_post(array(
      'post_type' => Litesub::POST_TYPE_NEWSLETTER,
      'post_title' => '[sample] Litesub Newsletter',
      'post_content' => $sample_newsletter_content,
    ));
  }
}

/**
 * Deactivation hook - disable the popup form.
 * Clean up is done when being uninstalled
 */
function litesub_deactivation() {
  Litesub::toggle_form(false);
}

register_activation_hook(__FILE__, 'litesub_activation');
register_deactivation_hook(__FILE__, 'litesub_deactivation');

/**
 * Hook to add links to litesub dashboard page and newsletter list page
 * to admin menu
 */
function litesub_admin_menu() {
  add_menu_page(
    'Litesub',
    'Litesub',
    'manage_options',
    'litesub_home',
    'litesub_home_page_html',
    plugin_dir_url(__FILE__) . '/assets/images/logo-16x16.jpg'
  );
  add_submenu_page(
    'litesub_home',
    'Newsletters',
    'Newsletters',
    'manage_options',
    'edit.php?post_type=' . Litesub::POST_TYPE_NEWSLETTER
  );
}

/**
 * Callback to load dashboard page
 */
function litesub_home_page_html() {
  if (!current_user_can('manage_options')) {
    return;
  }
  include(dirname(__FILE__) . '/views/dashboard.php');
}

/**
 * Enqueue scripts for Litesub dashboard page and newsletter page
 */
function litesub_enqueue_scripts($hook) {
  if (Litesub::is_dashboard_page($hook)) {
    wp_enqueue_style('litesub-admin-style', plugin_dir_url(__FILE__) . 'assets/css/litesub.css');
    wp_enqueue_script('litesub-home', plugin_dir_url(__FILE__) . 'assets/javascripts/home.js', array('litesub'), '1.0', true);
    $nonce = wp_create_nonce( 'litesub' );
    wp_localize_script( 'litesub-home', 'LitesubAjax', array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'nonce'    => $nonce,
      'post_id'  => get_post() ? get_post()->ID : null
    ));
  } elseif (Litesub::is_newsletter_edit_page($hook)) {
    wp_enqueue_style('litesub-admin-style', plugin_dir_url(__FILE__) . 'assets/css/litesub.css');
    wp_enqueue_script('litesub-newsletter', plugin_dir_url(__FILE__) . 'assets/javascripts/newsletter.js', array('litesub'), '1.0', true);
    $nonce = wp_create_nonce( 'litesub' );
    wp_localize_script( 'litesub-newsletter', 'LitesubAjax', array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'nonce'    => $nonce,
      'post_id'  => get_post() ? get_post()->ID : null
    ));
  }
}

/**
 * Register scripts and custom post types
 */
function litesub_admin_init() {
  wp_register_script('litesub-config', plugins_url('/assets/javascripts/litesub-config.js', __FILE__), null, '1.0', true);
  wp_register_script('litesub', plugins_url('/assets/javascripts/litesub.js', __FILE__),
                     array('litesub-config', 'jquery', 'underscore'), '1.0', true);

  register_post_type(Litesub::POST_TYPE_NEWSLETTER,
    [
      'labels'      => [
        'name'          => __('Newsletters (Litesub)'),
        'singular_name' => __('Newsletter (Litesub)'),
        'add_new_item' => __('Add New Newsletter'),
        'edit_item' => __('Edit Newsletter'),
      ],
      'public'      => true,
      'supports' => array('title', 'editor'),
      'show_in_menu' => 'edit.php?post_type=' . Litesub::POST_TYPE_NEWSLETTER,
    ]
  );
}

add_action('wp_footer', array('Litesub', 'enqueue_popup_form_script'));
add_action('draft_to_publish', array('Litesub_API', 'create_newsletter'));
add_action('wp_trash_post', array('Litesub_API', 'trash_newsletter'));
add_action('admin_init', 'litesub_admin_init');
add_action('admin_menu', 'litesub_admin_menu');
add_action('admin_enqueue_scripts', 'litesub_enqueue_scripts');

/**
 * Add ajax handler to enable/disable the popup form
 */
function litesub_toggle_form () {
  $enabled = $_POST['enabled'] == 'true' ? true : false;
  Litesub::toggle_form($enabled);
  wp_send_json(array(
    'enabled' => Litesub::form_enabled()
  ));
}
add_action( 'wp_ajax_toggle_form', 'litesub_toggle_form' );

/**
 * Ajax handler for send preview newsletter
 */
function litesub_ajax_send_preview_newsletter() {
  if (!isset($_POST['id'])) {
    return;
  }
  $post = get_post($_POST['id']);
  Litesub_API::create_newsletter($post, array('preview' => true));
  wp_send_json(array(
    'msg' => '* Newsletter has been sent.'
  ));
}
add_action( 'wp_ajax_send_preview_newsletter', 'litesub_ajax_send_preview_newsletter' );

/**
 * Ajax handler for candidate posts request
 */
function litesub_ajax_get_candidate_posts() {
  $newsletter_id = $_GET['newsletter_id'];
  $page = $_GET['page'];
  $result = Litesub_Newsletter::candidate_posts($newsletter_id, $page);
  wp_send_json(array(
    'candidate_posts' => $result['posts'],
    'total_pages' => $result['wp_query']->max_num_pages,
    'current_page' => $page,
  ));
}
add_action( 'wp_ajax_litesub_get_candidate_posts', 'litesub_ajax_get_candidate_posts' );

/**
 * Ajax handler for import subscribers request
 */
function litesub_ajax_import_subscribers() {
  $result = Litesub_API::import_subscribers();
  wp_send_json($result);
}
add_action( 'wp_ajax_litesub_import_subscribers', 'litesub_ajax_import_subscribers' );

add_shortcode('litesub_newsletter_posts', array('Litesub_ShortCode', 'newsletter_posts'));

/**
 * Callback for included posts meta box
 */
function litesub_included_posts_meta_box_html($newsletter) {
  include(plugin_dir_path(__FILE__) . '/views/metabox-newsletter-posts.php');
}

/**
 * Add a custom meta box to new/edit newsletter page, after the WP editor.
 * The user can use this meta box to choose which posts will be included in
 * a newsletter
 */
function litesub_add_included_posts_meta_box() {
  add_meta_box('litesub-included-posts-meta-box', 'Choose your posts to be listed in this newsletter', 'litesub_included_posts_meta_box_html',
               Litesub::POST_TYPE_NEWSLETTER, 'normal', 'high', null);
}
add_action('add_meta_boxes', 'litesub_add_included_posts_meta_box');

/**
 * Save included post ids to post meta on saving the newsletter
 */
function litesub_save_included_posts($newsletter_id) {
  if(Litesub::is_newsletter() && isset($_POST['included-posts'])) {
    $included_post_ids = $_POST['included-posts'];
    update_post_meta($newsletter_id, 'included-posts', $included_post_ids);
  }
}
add_action('save_post', 'litesub_save_included_posts');

/**
 * Add send preview button to publish meta box
 */
function litesub_submitbox_misc_actions(){
  if (!Litesub::is_newsletter()) {
    return;
  }
?>
<div class="misc-pub-section">
  <a class='button' id='litesub-send-preview-newsletter'>
    Send a preview newsletter to me
  </a>
  <p id='litesub-send-preview-newsletter-msg'></p>
</div>
<?php
}
add_action( 'post_submitbox_misc_actions', 'litesub_submitbox_misc_actions' );

/**
 * Add text messages for newsletter post type
 */
function litesub_updated_messages( $messages ) {
  global $post, $post_ID;
  $messages[Litesub::POST_TYPE_NEWSLETTER] = array(
    0 => '',
    1 => sprintf( __('Newsletter updated.')),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Newsletter updated.'),
    5 => isset($_GET['revision']) ? sprintf( __('Newsletter restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Newsletter published.')),
    7 => __('Newsletter saved.'),
    8 => sprintf( __('Newsletter submitted.')),
    9 => sprintf( __('Newsletter scheduled for: <strong>%1$s</strong>.'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
    10 => sprintf( __('Newsletter draft updated.') )
  );
  return $messages;
}
add_filter( 'post_updated_messages', 'litesub_updated_messages' );

/**
 * Show a general error notice
 */
function litesub_error_notice() {
?>
  <div class="notice notice-error is-dismissible">
    <p>Error occured when connecting to Litesub. Litesub may be under maintenance, please try again later.</p>
  </div>
<?php
}
