<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  exit;
}

require_once(dirname(__FILE__) . '/includes/class-litesub.php');
require_once(dirname(__FILE__) . '/includes/class-litesub-api.php');

// send uninstall notification to litesub
Litesub_API::plugin_uninstalled();

// delete litesub user data
delete_option(Litesub::USER_DATA_OPTION_NAME);

// delete all newsletters
$query = new WP_Query(array('post_type' => Litesub::POST_TYPE_NEWSLETTER));
foreach ($query->posts as $newsletter) {
  wp_delete_post($newsletter->ID);
}
