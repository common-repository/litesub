(function ($, _) {
  var LitesubSetup = window.LitesubSetup || {};
  var litesub = window.litesub;
  litesub._accessToken = null;

  litesub.getAccessToken = function () {
    return $.get(LitesubSetup.accessTokenURL, function (res) {
      litesub._accessToken = res.access_token;
    });
  };

  litesub.url = function (path) {
    return litesub.config.apiEndpoint + path;
  };

  function onRequestError() {
    alert('Error occured when sending request to Litesub. Please try again later.');
  }

  function doRequest(type, url, data, success) {
    return $.ajax({
      type: type,
      url: litesub.url(url),
      data: data,
      success: success,
      error: onRequestError,
      headers: {
        'Accept': 'application/json',
        'Authorization': litesub._accessToken
      }
    });
  }

  litesub.restRequest = function (type, url, data, success) {
    if (litesub._accessToken) {
      doRequest(type, url, data, success);
    } else {
      litesub.getAccessToken().done(function () {
        doRequest(type, url, data, success);
      }).fail(onRequestError);
    }
  };

  litesub.restGet = function (url, success) {
    litesub.restRequest('GET', url, null, success);
  };

  // wrapper of _.template, in case default underscore template settings
  // were changed by other plugins
  litesub.template = function (templateString) {
    var defaultSettings = {
      evaluate: /<%([\s\S]+?)%>/g,
      interpolate: /<%=([\s\S]+?)%>/g,
      escape: /<%-([\s\S]+?)%>/g
    };
    return _.template(templateString, defaultSettings);
  };

  litesub.paginator = function (selector, currentPage, totalPages, fnNavigate) {
    !_.isNumber(currentPage) && (currentPage = parseInt(currentPage, 10));
    !_.isNumber(totalPages) && (totalpages = parseInt(totalPages, 10));

    var tmpl = '<div class="tablenav"><div class="tablenav-pages"><span class="pagination-links"><a class="first-page">&laquo;</a><a class="prev-page">&lsaquo;</a>&nbsp;<span class="tablenav-paging-text"><b class="current-page"></b>/<b class="total-pages"></b></span>&nbsp;<a class="next-page">&rsaquo;</a><a class="last-page">&raquo;</a></span></div></div>';
    var elPaginator = $(tmpl);
    elPaginator.find('.current-page').text(currentPage);
    elPaginator.find('.total-pages').text(Math.max(totalPages, 1));
    elPaginator.find('.first-page').click(function () {
      currentPage > 1 && fnNavigate(1);
    });
    elPaginator.find('.prev-page').click(function () {
      currentPage > 1 && fnNavigate(currentPage - 1);
    });
    elPaginator.find('.next-page').click(function () {
      currentPage < totalPages && fnNavigate(currentPage + 1);
    });
    elPaginator.find('.last-page').click(function () {
      currentPage < totalPages && fnNavigate(totalPages);
    });
    $(selector).empty().append(elPaginator);
  };

  window.litesub = litesub;
})(jQuery, _);
