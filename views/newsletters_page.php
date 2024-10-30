<?php

if ( ! defined('ABSPATH') ) {
  exit;
}

wp_redirect(admin_url('edit.php?post_type=' . Litesub::POST_TYPE_NEWSLETTER));
exit();
