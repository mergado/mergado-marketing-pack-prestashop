/**
 * NOTICE OF LICENSE
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
const mergado_gtag_functions_shared = {
  initDetailViewed: function (sendTo, googleBusinessVertical = false) {
    var $_id, $_name, $_currency;

    if ($('#product-details[data-product]').length > 0) {
      var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
      $_id = productJSON.id;
      $_name = productJSON.name;
      $_currency = prestashop.currency.iso_code;

      if (productJSON.id_product_attribute !== "" && productJSON.id_product_attribute !== "0") {
        $_id = $_id + '-' + productJSON.id_product_attribute;
      }
    } else {
      var baseBlock = $('#add_to_cart').closest('form');

      $_id = baseBlock.find('#product_page_product_id').val();
      $_name = $('h1[itemprop="name"]').text();
      $_currency = currency.iso_code;

      if ($_name === '') {
        $_name = $('.modal-body h1').text();
      }

      if (baseBlock.find('#idCombination').length > 0 && baseBlock.find('#idCombination').val() !== '' && baseBlock.find('#idCombination').val() !== '0') {
        $_id = $_id + '-' + baseBlock.find('#idCombination').val();
      }
    }

    mergado_gua_helper_functions.events.sendViewItem($_id, $_name, $_currency, sendTo, googleBusinessVertical);
  },
  getCoupon: function () {
    return $('[data-mcoupons]').attr('data-mcoupons');
  },
};
