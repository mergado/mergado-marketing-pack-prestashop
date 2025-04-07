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

/***********************************************************************************************************************
 * GTAG FUNCTIONS
 **********************************************************************************************************************/

var mergado_gua_helper_functions = {
  events: {
    sendAddToCart: function (id, name, category, price, quantity, currency, sendTo, googleBusinessVertical = false) {
      const result = {
        "currency" : currency,
        "items": [
          {
            "id": id,
            "name": name,
            "category": category,
            "quantity": quantity,
            "price": price
          }
        ],
        "send_to": sendTo,
      };

      if (googleBusinessVertical) {
        result.items[0].google_business_vertical = googleBusinessVertical;
      }

      gtag('event', 'add_to_cart', result);
    },
    sendRemoveFromCart: function(id, currency, sendTo) {
      gtag('event', 'remove_from_cart', {
        "currency" : currency,
        "items": [
          {
            "id": id,
          }
        ],
        "send_to": sendTo
      });
    },
    sendViewItem: function(id, name, currency, sendTo, googleBusinessVertical = false) {
      const result = {
        "currency" : currency,
        "items": [
          {
            "id": id,
            "name": name,
          }
        ],
        "send_to": sendTo,
      };

      if (googleBusinessVertical) {
        result.items[0].google_business_vertical = googleBusinessVertical;
      }

      gtag('event', 'view_item', result);
    },
    sendCheckoutOptionSelected: function (option, step, value, sendTo) {
      gtag('event', 'set_checkout_option', {
        "checkout_step": step,
        "checkout_option": option,
        "value": value,
        "send_to": sendTo
      });
    },
    sendBeginCheckout: function(items, coupon, currency, sendTo) {
      gtag('event', 'begin_checkout', {
        "currency" : currency,
        "items": items,
        "coupon": coupon,
        "send_to": sendTo
      });
    },
    sendCheckoutProgress: function(items, coupon, currency, sendTo) {
      gtag('event', 'checkout_progress', {
        "currency" : currency,
        "items": items,
        "coupon": coupon,
        "send_to": sendTo
      });
    },
    sendDiscountRemoved: function (items, coupon, currency, sendTo) {
      gtag('event', 'checkout_progress', {
        "currency" : currency,
        "items": items,
        "coupon": coupon,
        "send_to": sendTo
      });
    },
    sendViewList: function (items, currency, sendTo) {
      gtag('event', 'view_item_list', {
        "currency" : currency,
        "items": items,
        "send_to": sendTo
      });
    }
  },
  functions: {
    getProductPrice(productJSON, withVat) {
      if (withVat) {
        return productJSON.price_with_reduction_with_tax;
      } else {
        return productJSON.price_with_reduction_without_tax;
      }
    },
    getProductData(productJSON, prices) {
      let currency, itemId, itemIdMerged, itemVariantId, itemName, itemCategory, itemPrice, itemDiscount, coupon;

      itemId = productJSON.id;
      itemIdMerged = productJSON.id_merged;
      itemVariantId = productJSON.id_product_attribute;
      itemName = productJSON.name;
      itemPrice = prices['price'];
      itemDiscount = prices['discount'];
      itemCategory = productJSON.category_name;
      coupon = productJSON.coupon;
      currency = productJSON.currency;

      const productObject = {
        'itemId': itemId,
        'itemIdMerged': itemIdMerged,
        'itemVariantId': itemVariantId,
        'itemName': itemName,
        'itemPrice': itemPrice,
        'itemDiscount': itemDiscount,
        'itemCategory': itemCategory,
        'currency': currency,
      };

      if (coupon) {
        productObject['coupon'] = coupon;
      }

      return productObject;
    },
    getProductObject(selector, withVat) {
      let productJSON, price;

      if ($(selector).length > 0) {
        productJSON = JSON.parse($(selector).attr('data-product'));
        price = mergado_gua_helper_functions.functions.getProductPrice(productJSON, withVat);

        return {productJSON, price};
      }
    },
    getProductObjectFromTarget(target, selector, withVat) {
      let productJSON, prices;

      productJSON = JSON.parse($(target).find(selector).attr('data-product'));
      prices = mergado_gua_helper_functions.functions.getProductPrice(productJSON, withVat);

      return {productJSON, prices};
    },
    getMscdData(withVat)
    {
      if (withVat) {
        return JSON.parse($('[data-mscd]').attr('data-products-with-vat'));
      } else {
        return JSON.parse($('[data-mscd]').attr('data-products-without-vat'));
      }
    },
    getProductObjectAttribute(mergedId, withVat) {
      let productJSON, price;

      let attributesData = $('#mergado-product-informations.mergado-product-data[data-product-attributes]');

      if (attributesData.length > 0) {
        attributesData = JSON.parse(attributesData.attr('data-product-attributes'));
        productJSON = attributesData[mergedId];
        let price = mergado_gua_helper_functions.functions.getProductPrice(productJSON, withVat);
      }

      return {productJSON, price};
    },
  }
}
