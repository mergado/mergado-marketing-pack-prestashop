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
const mergado_gtag_functions_ps16 = {
  initAddToCartPs16: function (sendTo, withVat, googleBusinessVertical = false) {
    //PS 1.6 AJAX
    $('.ajax_add_to_cart_button').on('click', function () {
      var $_id = $(this).attr('data-id-product');
      var $_name = $(this).closest('li').find('.product-name').text().replace(/\t/g, '').trim();
      var $_quantity = 1;
      var $_category = '';
      var $_price = '';
      var $_currency = currency.iso_code;

      const dataItem = $(this).closest('li').find('.mergado-product-list-item-data[data-product]');
      if (dataItem.length > 0) {
        const productData = JSON.parse(dataItem.attr('data-product'));
        $_price = mergado_gua_helper_functions.functions.getProductPrice(productData, withVat);
      }

      if ($(this).attr('data-id-product-attribute') && $(this).attr('data-id-product-attribute') !== '' && $(this).attr('data-id-product-attribute') !== '0') {
        $_id = $_id + '-' + $(this).attr('data-id-product-attribute');
      }

      mergado_gua_helper_functions.events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency, sendTo, googleBusinessVertical);
    });

    //PS 1.6
    $('#add_to_cart button').on('click', function () {
      var buyBlock = $(this).closest('form');

      var $_id = buyBlock.find('#product_page_product_id').val();
      var $_name = $('h1[itemprop="name"]').text();
      var $_quantity = buyBlock.find('#quantity_wanted').val();
      var $_category = '';
      var $_price = buyBlock.find('[itemprop="price"]').attr('content');
      var $_currency = currency.iso_code;


      if (buyBlock.find('#idCombination').length > 0 && buyBlock.find('#idCombination').val() !== '' && buyBlock.find('#idCombination').val() !== '0') {
        $_id = $_id + '-' + buyBlock.find('#idCombination').val();
      }

      const productData = mergado_gua_helper_functions.functions.getProductObjectAttribute($_id, withVat);

      if (productData) {
        $_price = productData.price;
      }

      mergado_gua_helper_functions.events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency, sendTo, googleBusinessVertical);
    });
  },
  initRemoveFromCartPs16: function (sendTo) {
    //PS 1.6
    $('.ajax_cart_block_remove_link, .cart_quantity_delete').on('click', function () {
      var urlParams = new URLSearchParams($(this).attr('href'));

      var $_id = urlParams.get('id_product');
      var $_ipa = urlParams.get('ipa');
      var $_currency = currency.iso_code;

      if ($_ipa != null && $_ipa != 0) {
        $_id += "-" + $_ipa;
      }

      mergado_gua_helper_functions.events.sendRemoveFromCart($_id, $_currency, sendTo);
    });
  },
  initPaymentSetPs16: function (sendTo, withVat) {
    //PS 1.6
    //Multistep
    if ($('body#order').length > 0) {
      $('.payment_module a').on('click', function () {
        var value = $(this).attr('title');

        mergado_gua_helper_functions.events.sendCheckoutOptionSelected('payment method', 4, value, sendTo);
      });
    }

    //One page checkout
    if ($('body#order-opc').length > 0) {
      $('.payment_module a').on('click', function () {
        var value = $(this).attr('title');
        var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
        var $_currency = currency.iso_code;

        mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
        mergado_gua_helper_functions.events.sendCheckoutOptionSelected('payment method', 3, value, sendTo);
      });
    }
  },
  initCarrierSetPs16: function (sendTo, withVat) {
    //Mutlipage checkout

    if ($('body#order')) {
      var lock = false;

      $('body').on('click', '.delivery_option_radio', function () {
        if (!lock) {
          lock = true;

          var value = $(this).val().replace(',', '');

          mergado_gua_helper_functions.events.sendCheckoutOptionSelected('shipping method', 3, value, sendTo);

          setTimeout(function () {
            lock = false;
          }, 500)
        }
      });
    }

    //One page checkout
    if ($('body#order-opc')) {
      var lock = false;

      $('body').on('click', '.delivery_option_radio', function () {
        if (!lock) {
          lock = true;
          var $_currency = currency.iso_code;
          var value = $(this).val().replace(',', '');
          var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
          mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
          mergado_gua_helper_functions.events.sendCheckoutOptionSelected('shipping method', 2, value, sendTo);

          setTimeout(function () {
            lock = false;
          }, 500)
        }
      });
    }
  },
  initCheckoutStarted16: function (sendTo, withVat) {
    //Multistep
    $('body#order .cart_navigation .standard-checkout').on('click', function () {
      if (typeof $(this).attr('name') == 'undefined' || $(this).attr('name') === '') {
        var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
        var $_currency = currency.iso_code;

        mergado_gua_helper_functions.events.sendBeginCheckout(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
      }
    });

    //OnePageCheckout
    if ($('body#order-opc').length > 0) {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = currency.iso_code;

      mergado_gua_helper_functions.events.sendBeginCheckout(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    }
  },
  initCheckoutLoginStepPs16: function (sendTo, withVat) {
    if ($('body#authentication').length > 0) {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = currency.iso_code;
      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    }
  },
  initCheckoutAddressStepPs16: function (sendTo, withVat) {
    if ($('body#order [name="processAddress"]').length > 0) {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = currency.iso_code;

      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    }
  },
  initCheckoutDeliveryStepPs16: function (sendTo, withVat) {
    if ($('body#order [name="processCarrier"]').length > 0) {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = currency.iso_code;

      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    }
  },
  initCheckoutPaymentStepPs16: function (sendTo, withVat) {
    if ($('body#order .payment_module a').length > 0) {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = currency.iso_code;

      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    }
  },
  initCheckoutReviewStepPs16: function (sendTo, withVat) {
    //Multistep
    $('body#order .payment_module a').on('click', function () {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = currency.iso_code;

      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    });

    //One page checkout payment/review page before confirm order
    $('body#order-opc #opc_payment_methods a').on('click', function () {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = currency.iso_code;

      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    });
  },
  initViewListPs16: function (sendTo, googleBusinessVertical = false) {
    var products = $('.ajax_block_product');
    var $_currency = currency.iso_code;

    if (products.length > 0) {
      var items = getProductsData(products, googleBusinessVertical);
      mergado_gua_helper_functions.events.sendViewList(items, $_currency, sendTo);
    }

    $(document).ajaxComplete(function () {
      var currentProducts = $('.ajax_block_product');
      var newProducts = getProductsData(currentProducts, googleBusinessVertical)
      var $_currency = currency.iso_code;

      if (items !== newProducts && currentProducts.length > 0) {
        mergado_gua_helper_functions.events.sendViewList(newProducts, $_currency, sendTo);
      }
    });

    function getProductsData(products, googleBusinessVertical = false) {
      var items = [];

      if (products.length > 0) {
        products.each(function (key, value) {
          var $_id = $(this).find('[data-id-product]').attr('data-id-product');
          var $_name = $(this).find('.product-name').text().replace(/\t/g, '').trim();

          var id_attr = $(this).find('[data-id-product-attribute]');

          if (id_attr.length > 0 && id_attr.attr('data-id-product-attribute') !== '' && id_attr.attr('data-id-product-attribute') !== '0') {
            $_id = $_id + '-' + id_attr.attr('data-id-product-attribute');
          }

          var list = '';

          if ($('body#category').length > 0) {
            list = $('.category-name').text();
          } else if ($('body#search').length > 0) {
            list = 'search';
          }

          var item = {};
          item['id'] = $_id;
          item['name'] = $_name;

          if (list !== '') {
            item['list_name'] = list.trim();
          }

          if (googleBusinessVertical) {
            item['google_business_vertical'] = googleBusinessVertical;
          }

          item['position'] = key;

          items.push(item);
        });
      }

      return items;
    }
  },
  initCouponAdded: function (sendTo, withVat) {
    //Only for ps 1.6

    //Checkout coupon
    var urlParams = new URLSearchParams(window.location.search);
    var couponAdded = urlParams.get('addingCartRule');
    var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
    var $_currency = currency.iso_code;

    if (couponAdded) {
      mergado_gua_helper_functions.events.sendCheckoutProgress(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    }
  },
  initCouponRemoved: function (sendTo, withVat) {
    //Only for ps 1.6

    $('.price_discount_delete').on('click', function () {
      var items = mergado_gua_helper_functions.functions.getMscdData(withVat);
      var $_currency = currency.iso_code;

      //Send new discount change
      mergado_gua_helper_functions.events.sendDiscountRemoved(items, mergado_gtag_functions_shared.getCoupon(), $_currency, sendTo);
    });
  },
};
