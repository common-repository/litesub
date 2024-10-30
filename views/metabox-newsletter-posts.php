<?php
  $included_posts = Litesub_Newsletter::included_posts($newsletter->ID);
  wp_nonce_field(basename(__FILE__), "meta-box-nonce");
?>
  <input type='hidden' id='included-posts' name='included-posts' value='<?php echo Litesub_Newsletter::included_post_ids($newsletter->ID); ?>'>
  <p>
    You can select a few posts here, and add a shortcode <b>[litesub_newsletter_posts]</b> in the editor above, as a placeholder for the list of selected posts.
  </p>
  <div>Selected Posts</div>
  <div id='selected-post-list'>
    <?php foreach ($included_posts as $post) { ?>
    <div class='post-entry' style='padding: 10px;' data-id='<?php echo $post->ID; ?>'>
      <div>
        <a href='<?php echo get_permalink($post) ?>' class='post-title' target='_blank'>
          <b><?php echo $post->post_title; ?></b>
        </a>
        <span class='post-actions'>
          <a class='post-action post-action-select' title='Select'>
            <b class='dashicons dashicons-yes' style='font-size: 1em;'></b>
          </a>
          <a class='post-action post-action-unselect' title='Unselect'>
            <b class='dashicons dashicons-no' style='font-size: 1em;'></b>
          </a>
          <a class='post-action post-action-move-up' title='Move up'>
            <b class='dashicons dashicons-arrow-up-alt'></b>
          </a>
          <a class='post-action post-action-move-down' title='Move down'>
            <b class='dashicons dashicons-arrow-down-alt' style='font-size: 1em;'></b>
          </a>
        </span>
      </div>
      <div>
        <?php echo Litesub_Post::excerpt($post); ?>
      </div>
    </div>
    <?php } ?>
  </div>
  <br>
  <div>Candidate Posts</div>
  <div id='candidate-post-list'></div>
  <div id='candidate-posts-paginator'></div>

  <script type='text/x-ls-template' id='litesub-post-entry-tmpl'>
    <div class='post-entry' style='padding: 10px;' data-id='<%= ID %>'>
      <div>
        <a href='<%= permalink %>' class='post-title' target='_blank'>
          <b><%= post_title %></b>
        </a>
        <span class='post-actions'>
          <a class='post-action post-action-select' title='Select'>
            <b class='dashicons dashicons-yes' style='font-size: 1em;'></b>
          </a>
          <a class='post-action post-action-unselect' title='Unselect'>
            <b class='dashicons dashicons-no' style='font-size: 1em;'></b>
          </a>
          <a class='post-action post-action-move-up' title='Move up'>
            <b class='dashicons dashicons-arrow-up-alt'></b>
          </a>
          <a class='post-action post-action-move-down' title='Move down'>
            <b class='dashicons dashicons-arrow-down-alt' style='font-size: 1em;'></b>
          </a>
        </span>
      </div>
      <div class='excerpt'>
        <%= post_excerpt %>
      </div>
    </div>
  </script>
