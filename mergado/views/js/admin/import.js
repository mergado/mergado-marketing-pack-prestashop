/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */

$(document).ready(function () {
  $('.saveAndImportRecursive').on('click', function (e) {
    e.preventDefault();
    var feed = $(this).attr('data-feed');
    var importUrl = $('#import_product_prices_url').val();
    saveImportUrlAndGenerate(feed, importUrl);
  });
});

function saveImportUrlAndGenerate(feed, importUrl)
{
  $.ajax({
    type: "POST",
    url: admin_mergado_ajax_url,
    data: {
      action: 'ajax_save_import_url',
      dataType: 'json',
      url: importUrl,
      ajax: true,
      controller: 'AdminMergado',
    },
    beforeSend: function() {
      $('.mmp-popup').addClass('active');
      $('.mmp-popup__button').addClass('disabled');
      $('.mmp-popup__loader').show();
    },
    success: function () {
      importRecursive(feed);
    },
    error: function(jqXHR) {
      var responseText;
      if (jqXHR.responseText.length > 0) {
        responseText = JSON.parse(jqXHR.responseText).error;
      } else {
        responseText = 'Error during import.';
      }

      $('.mmp-popup__loader').hide();
      $('.mmp-popup__output').html(responseText);
      $('.mmp-popup__button').removeClass('disabled');
    },
  });
}

function importRecursive(feed)
{
  $.ajax({
    type: "POST",
    url: admin_mergado_ajax_url,
    data: {
      action: 'ajax_import_prices',
      dataType: 'json',
      ajax: true,
      controller: 'AdminMergado',
    },
    success: function (data) {
      $('.mmp-popup__loader').hide();

      if (data) {
        var output = JSON.parse(data);

        if (output['feedStatus'] === 'finished') {
          $('.mmp-popup__loader').hide();
          $('.mmp-popup__output').html(output['success']);
          $('.mmp-popup__button').removeClass('disabled');
        }
      } else {
        importRecursive(feed);
      }
    },
    error: function(jqXHR) {
      if (jqXHR.status === 424) {
        var responseText;
        if (jqXHR.responseText.length > 0) {
          responseText = JSON.parse(jqXHR.responseText).error;
        } else {
          responseText = 'Error during import.';
        }

        $('.mmp-popup__loader').hide();
        $('.mmp-popup__output').html(responseText);
        $('.mmp-popup__button').removeClass('disabled');
      } else {
        mmpLowerImportProductFeedPerStepAndCallNextRun(feed);
      }
    },
  });
}

function mmpLowerImportProductFeedPerStepAndCallNextRun(feed)
{
  $.ajax({
    type: "POST",
    url: admin_mergado_ajax_url,
    data: {
      action: 'ajax_lower_cron_product_step',
      feed: 'import',
      dataType: 'json',
      ajax: true,
      controller: 'AdminMergado',
    },
    success: function () {
      importRecursive(feed);
    },
    error: function (jqXHR) {
      var responseText;
      if (jqXHR.responseText.length > 0) {
        responseText = JSON.parse(jqXHR.responseText).error;
      } else {
        responseText = 'Error during import.';
      }

      $('.mmp-popup__loader').hide();
      $('.mmp-popup__output').html(responseText);
      $('.mmp-popup__button').removeClass('disabled');
    }
  });
}
