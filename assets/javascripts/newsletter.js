(function ($, _, $ls, LitesubAjax) {

  function updateIncludedPosts() {
    var posts = [];
    $('#selected-post-list .post-entry').each(function (i, entry) {
      posts.push($(entry).data('id'));
    });
    $('#included-posts').val(posts.join(','));
  }

  function updatePostLists() {
    var selectedPostIds = _.map($('#selected-post-list .post-entry'), function (el) {
      return $(el).data('id');
    });

    $('#candidate-post-list .post-entry').each(function (i, el) {
      var $el = $(el);
      if (_.contains(selectedPostIds, $el.data('id'))) {
        $el.addClass('selected');
      } else {
        $el.removeClass('selected');
      }
    });
  }

  $('#candidate-post-list').on('click', '.post-action-select', function () {
    var elPost = $(this).parents('.post-entry');
    if (elPost.hasClass('selected')) {
      elPost.removeClass('selected');
      $('#selected-post-list .post-entry').each(function (i, el) {
        var $el = $(el);
        if ($el.data('id') === elPost.data('id')) {
          $el.remove();
        }
      });
    } else {
      elPost.clone().appendTo('#selected-post-list');
    }
    updatePostLists();
    updateIncludedPosts();
  });

  $('#selected-post-list').on('click', '.post-action-unselect', function () {
    $(this).parents('.post-entry').remove();
    updatePostLists();
    updateIncludedPosts();
  });

  $('#selected-post-list').on('click', '.post-action-move-up', function () {
    var postEntry = $(this).parents('.post-entry');
    var prev = postEntry.prev();
    if (prev.length === 1) {
      postEntry.remove().insertBefore(prev);
      updateIncludedPosts();
    }
  });
  $('#selected-post-list').on('click', '.post-action-move-down', function () {
    var postEntry = $(this).parents('.post-entry');
    var next = postEntry.next();
    if (next.length === 1) {
      postEntry.remove().insertAfter(next);
      updateIncludedPosts();
    }
  });

  function loadCandidatePosts(page) {
    page = page || 1;
    var data = {
      _ajax_nonce: LitesubAjax.nonce,
      action: 'litesub_get_candidate_posts',
      newsletter_id: LitesubAjax.post_id,
      page: page
    };
    $.get(LitesubAjax.ajax_url, data, function (res) {
      var postList = $('#candidate-post-list').empty();
      res.candidate_posts.forEach(function (post) {
        $(_.template($('#litesub-post-entry-tmpl').html())(post)).appendTo(postList);
      });
      $ls.paginator('#candidate-posts-paginator', res.current_page, res.total_pages, loadCandidatePosts);
      updatePostLists();
    });
  }

  $(function () {
    $('#litesub-send-preview-newsletter').click(function() {
      $('#litesub-send-preview-newsletter').attr('disabled', 'disabled');
      $('#litesub-send-preview-newsletter-msg').text('');
      var data = {
        _ajax_nonce: LitesubAjax.nonce,
        action: 'send_preview_newsletter',
        id: LitesubAjax.post_id
      };
      $.post(LitesubAjax.ajax_url, data, function (res) {
        $('#litesub-send-preview-newsletter-msg').text(res.msg);
        $('#litesub-send-preview-newsletter').removeAttr('disabled');
      });
    });

    $('#preview-action').hide();
    $('.misc-pub-visibility').hide();

    loadCandidatePosts();
  });
})(jQuery, _, litesub, LitesubAjax);
