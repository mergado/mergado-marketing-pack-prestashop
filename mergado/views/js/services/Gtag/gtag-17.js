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
const mergado_gtag_functions_ps17 = {
  initAddToCartPs17: function (sendTo, withVat, googleBusinessVertical = false) {
    // PS 1.7
    $('body').on('click', '.add-to-cart', function () {
      addEvents($(this))
    });

    function addEvents(target) {
      var $_id, $_name, $_price, $_category, $_quantity, $_currency, $_modal, $_id2;

      if (target.closest('.product-add-to-cart').find('#quantity_wanted').length > 0) {
        $_quantity = target.closest('.product-add-to-cart').find('#quantity_wanted').val();
      } else {
        $_quantity = 1;
      }

      // VAT changes
      let productData = mergado_gua_helper_functions.functions.getProductObject('#mergado-product-informations.mergado-product-data[data-product]', withVat);

      if (!productData) {
        // If someone modified PS 1.7 and added "add to cart" on product list page
        productData = mergado_gua_helper_functions.functions.getProductObjectFromTarget($(target).closest('.js-product-miniature'), '#mergado-product-informations.mergado-product-list-item-data[data-product]');
      }

      if ($('#product-details[data-product]').length > 0) {

        let productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
        $_id = productJSON.id;
        $_name = productJSON.name;
        $_price = productData.price;
        $_category = productJSON.category_name;
        $_currency = prestashop.currency.iso_code;

        if (productJSON.id_product_attribute !== "" && productJSON.id_product_attribute !== "0") {
          $_id = $_id + '-' + productJSON.id_product_attribute;
        }
      } else {
        $_modal = target.closest('[id*="quickview-modal-"]').clone().html();

        //Can't get real attr ID because no hook or data on that place :=/
        $_id = $($_modal).find('#product_page_product_id').val();
        $_id2 = $($_modal).find('[data-product-attribute]:checked').attr('data-product-attribute')

        if ($_id2 !== '' && $_id2 && $_id2 !== '0') {
          $_id = $_id + '-' + $_id2;
        } else if ($(this).closest('form').find('#idCombination').length > 0) {
          $_id = $_id + '-' + target.closest('form').find('#idCombination').val();
        }

        $_name = $('h1[itemprop="name"]').text();
        // $_price = $('.product-price').find('[itemprop="price"]').attr('content');
        $_price = productData.price;
        $_category = '';

        if ($_name === '') {
          $_name = $('.modal-body h1').text();
        }
      }

      mergado_gua_helper_functions.events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency, sendTo, googleBusinessVertical);
    }
  },
  initRemoveFromCartPs17: function (sendTo) {
    //PS 1.7
    $('body').on('click', '.remove-from-cart[data-link-action="delete-from-cart"]', function () {
      var urlParams = new URLSearchParams($(this).attr('href'));

      var $_id = urlParams.get('id_product');
      var $_ipa = urlParams.get('id_product_attribute');

      var $_currency = prestashop.currency.iso_code;

      if ($_ipa != null && $_ipa != 0) {
        $_id += "-" + $_ipa;
      }

      mergado_gua_helper_functions.events.sendRemoveFromCart($_id, $_currency, sendTo);
    });
  },
  initPaymentSetPs17: function (sendTo) {
    //PS 1.7
    $('.payment-options input').on('click', function () {
      // var value = $(this).closest('.payment-option').find('label span').text();
      var value = $(this).attr('id');
      var label = $('label[for="' + value + '"] span').text();

      mergado_gua_helper_functions.events.sendCheckoutOptionSelected('payment method', 4, label, sendTo);
    });
  },
  initCarrierSetPs17: function (sendTo) {
    $('.delivery-option input').on('click', function () {
      // var value = $(this).closest('.delivery-option').find('.carrier-name').text();
      var value = $(this).attr('id');
      var label = $('label[for="' + value + '"]').find('.carrier-name').text();

      mergado_gua_helper_functions.events.sendCheckoutOptionSelected('payment method', 3, label, sendTo);
    });
  },
  initCheckoutStarted17: function (sendTo, withVat) {
    var orderUrl = $('[data-morder-url]').attr('data-morder-url');
    $('a[href="' + orderUrl + '"]').on('click', function () {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = prestashop.currency.iso_code;
      mergado_gua_helper_functions.events.sendBeginCheckout(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    });
  },
  initCheckoutAddressStep17: function (sendTo, withVat) {
    if ($('#checkout-addresses-step').hasClass('-current') || $('#checkout-addresses-step').hasClass('js-currenct-step')) {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var currency = prestashop.currency.iso_code;
      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), currency, sendTo);
    }
  },
  initCheckoutDeliveryStep17: function (sendTo, withVat) {
    if ($('#checkout-delivery-step').hasClass('-current') || $('#checkout-delivery-step').hasClass('js-currenct-step')) {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var currency = prestashop.currency.iso_code;
      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), currency, sendTo);
    }
  },
  initCheckoutPaymentStep17: function (sendTo, withVat) {
    if ($('#checkout-payment-step').hasClass('-current') || $('#checkout-payment-step').hasClass('js-currenct-step')) {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var currency = prestashop.currency.iso_code;
      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), currency, sendTo);
    }
  },
  initCouponChangePs17: function (sendTo, withVat) {
    //One method for add/remove in ps1.7
    if (typeof prestashop !== 'undefined') {
      prestashop.on(
          'updatedCart',
          function () {
            var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
            var currency = prestashop.currency.iso_code;

            mergado_gua_helper_functions.events.sendDiscountRemoved(items, mergado_gtag_functions_shared.getCoupon(), currency, sendTo);
          }
      );
    }
  },
  initViewListPs17: function (sendTo, googleBusinessVertical = false) {
    if(typeof prestashop !== 'undefined') {
      var products = $('.product-miniature[data-id-product]');
      var currency = prestashop.currency.iso_code;

      if (products.length > 0) {
        var items = getProductsData(products, googleBusinessVertical);
        mergado_gua_helper_functions.events.sendViewList(items, currency, sendTo);
      }

      prestashop.on('updateProductList', function (e) {
        var currentProducts = $('.product-miniature[data-id-product]');
        var newProducts = getProductsData(currentProducts, googleBusinessVertical)

        if (items !== newProducts && currentProducts.length > 0) {
          mergado_gua_helper_functions.events.sendViewList(newProducts, currency, sendTo);
        }
      });

      function getProductsData(products, googleBusinessVertical) {
        var items = [];

        if (products.length > 0) {
          products.each(function (key, value) {
            var $_id = $(this).attr('data-id-product');
            var $_name = $(this).find('.product-title a').text().replace(/\t/g, '').trim();
            var id_attr = $(this).attr('data-id-product-attribute');

            if (id_attr && id_attr != 0) {
              $_id = $_id + '-' + id_attr;
            }

            var list = '';

            if ($('body#category').length > 0) {
              list = $('title').text();
            } else if ($('body#search').length > 0) {
              list = 'search';
            }

            var item = {};
            item['id'] = $_id;
            item['name'] = $_name;

            if (list !== '') {
              item['list_name'] = list.trim();
            }

            item['position'] = key;

            if (googleBusinessVertical) {
              item['google_business_vertical'] = googleBusinessVertical;
            }

            items.push(item);
          });
        }

        return items;
      }
    }
  }
};
