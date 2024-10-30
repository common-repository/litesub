<div class='litesub-page litesub-landing-page'>
  <div class='litesub-box'>
    <div class='topbar'>
      <a href='<?php echo Litesub_API::litesub_page_url(); ?>' target='_blank' class='litesub-home-link'>
        <img class='litesub-logo' src='https://litesub.com/images/logo-32x32.jpg'> Litesub
      </a>
      <span class='litesub-nav-links'>
        <a class='btn-toggle btn-toggle-form' title='When disabled, the popup form will not be added to your site pages'>
          <span class='toggle-icon'></span>
          <span class='text'>Use Litesub Popup</span>
        </a>
        <a href='<?php echo Litesub_API::litesub_page_url(); ?>' target='_blank'>
          My Litesub
        </a>
        <a href='https://litesub.com/wordpress/help' target='_blank'>Help</a>
      </span>
    </div>
  </div>

  <div class='litesub-box'>
    <h2>
      Subscribers <small>total: <b id='total-subscribers'></b></small>
      <a href="#TB_inline?width=600&height=180&inlineId=import-modal" class='link-import-subscribers thickbox'>import existing subscribers</a>
    </h2>
    <table class="wp-list-table widefat striped" id='sub-list'>
      <thead>
        <tr>
          <th>Email</th><th>Subscribed at</th><th>From</th><th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <div id='litesub-subscriber-paginator'></div>
  </div>

  <div class='litesub-box'>
    <h2> Newsletters </h2>
    <table class="wp-list-table widefat striped" id='newsletter-list'>
      <thead>
        <tr>
          <th>Subject</th><th>Created at</th><th>Opens</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <div id='litesub-newsletter-paginator'></div>
  </div>

</div>

<script type='text/x-ls-template' id='sub-row-tmpl'>
<tr class='subscriber' data-id='<%= id %>'>
  <td><a href='mailto:<%= email %>' title='mail to <%= email %>'><%= email %></a></td>
  <td><%= new Date(created_at).toLocaleString() %></td>
  <td><%= (geo_data ? geo_data : '') + (ip ? ',' + ip : '') %></td>
  <td><a class='btn-del-sub'>delete</a></td>
</tr>
</script>

<script type='text/x-ls-template' id='newsletter-row-tmpl'>
  <tr class='newsletter' data-nl-id='<%= id %>'>
    <td><a href='/wp-admin/post.php?action=edit&post=<%= external_id %>'><b><%= name %></b></a></td>
    <td><%= new Date(created_at).toLocaleString() %></td>
    <td><%= stats_total_opens %></td>
    <td></td>
  </tr>
</script>

<script>
  var litesubFormSettings = {
    enabled: <?php echo Litesub::form_enabled() ? 'true' : 'false' ?>
  };
  var LitesubSetup = {};
  LitesubSetup.accessTokenURL = '<?php echo Litesub_API::access_token_url(); ?>';
</script>

<?php add_thickbox() ?>
<div id="import-modal" style="display:none;">
  <div class='litesub-center'>
    <h2>Import Subscribers to Litesub</h2>
    <label>Import users with this role to Litesub</label>
    <select id='import-selected-role'>
      <?php wp_dropdown_roles(); ?>
    </select>
    <button id='btn-import-subscribers' class='button'>Import</button>
    <p id='import-msg'></p>
    <p>
      If any trouble, contact <a href='mailto:support@litesub.com'>support@litesub.com</a>
    </p>
  </div>
</div>
