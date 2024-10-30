(function ($, _, LitesubAjax) {
  var litesub = window.litesub;
  var litesubFormSettings = window.litesubFormSettings;

  function updateToggleFormBtn() {
    if (litesubFormSettings.enabled) {
      $('.btn-toggle-form').addClass('on').removeClass('off');
    } else {
      $('.btn-toggle-form').removeClass('on').addClass('off');
    }
  };

  $('.btn-toggle-form').click(function() {
    var data = {
      _ajax_nonce: LitesubAjax.nonce,
      action: 'toggle_form',
      enabled: !litesubFormSettings.enabled
    };
    $.post(LitesubAjax.ajax_url, data, function (res) {
      litesubFormSettings.enabled = res.enabled;
      updateToggleFormBtn();
    });
  });

  $('#btn-import-subscribers').click(function () {
    $('#import-msg').empty();
    var data = {
      _ajax_nonce: LitesubAjax.nonce,
      action: 'litesub_import_subscribers'
    };
    $.post(LitesubAjax.ajax_url, data, function () {
      $('#import-msg').text('Subscribers submitted successfully, please refresh this page in a while to check it');
    });
  });

  $('#sub-list').on('click', '.btn-del-sub', function() {
    if (!confirm('Delete this subscriber?')) {
      return;
    }
    var subRow = $(this).parents('.subscriber');
    var subID = subRow.data('id');
    litesub.restRequest('delete', '/subscribers/' + subID, null, function (res) {
      if (res.id === subID) {
        subRow.fadeOut();
      }
    });
  });

  function loadSubscribers(page) {
    page = page || 1;
    litesub.restGet('/subscribers.json?page=' + page, function (res) {
      $('#sub-list tbody').empty();
      res.subscribers.forEach(function (sub) {
        var row = litesub.template($('#sub-row-tmpl').html())(sub);
        $('#sub-list tbody').append($(row));
      });
      $('#total-subscribers').text(res.total_count);
      litesub.paginator('#litesub-subscriber-paginator', res.current_page, res.total_pages, loadSubscribers);
    });
  }

  function renderNewsletterPage(res) {
    var newsletterList = $('#newsletter-list tbody');
    newsletterList.empty();
    res.newsletters.forEach(function (newsletter) {
      var row = litesub.template($('#newsletter-row-tmpl').html())(newsletter);
      newsletterList.append($(row));
    });
    litesub.paginator('#litesub-newsletter-paginator', res.current_page, res.total_pages, loadNewsletters);
  }

  function loadNewsletters(page) {
    page = page || 1;
    litesub.restGet('/newsletters.json?page=' + page, function (res) {
      renderNewsletterPage(res);
    });
  }

  updateToggleFormBtn();

  loadSubscribers();
  loadNewsletters();
})(jQuery, _, LitesubAjax);
