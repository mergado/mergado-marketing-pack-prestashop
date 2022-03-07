(function ($) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var $ = jQuery;

    $('[data-mmp-hide-alert]').on('click', function () {
      var alert = $(this).closest('.mmp_alert__wrapper');
      var alertData = JSON.parse(alert.attr('data-mmp-alert'));
      var feed = alertData.feed;

      alert.hide();

      $.ajax({
        type: "POST",
        url: admin_mergado_ajax_url,
        data: {
          controller: 'AdminMergado',
          ajax: true,
          action: 'ajax_disable_alert',
          name: alertData.name,
          feed: feed,
          dataType: 'json'
        },
        success: function () {
        },
      });
    });

    $('[data-mmp-disable-all-notifications]').on('click', function () {
      var alert = $(this).closest('.mmp_alert__wrapper');
      var alertData = JSON.parse(alert.attr('data-mmp-alert'));

      $('.mmp_alert__wrapper').hide();

      $.ajax({
        type: "POST",
        url: admin_mergado_ajax_url,
        data: {
          controller: 'AdminMergado',
          ajax: true,
          action: 'ajax_disable_section',
          section: alertData.section,
          dataType: 'json'
        },
        success: function () {
        },
      });
    });
  });
})(jQuery);