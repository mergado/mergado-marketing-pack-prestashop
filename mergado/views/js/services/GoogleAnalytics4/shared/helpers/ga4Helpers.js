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

var mmp_GA4_helpers = {
  events: {
    sendAddToCart: function (value, currency, itemId, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount) {
      const eventObject = {
        "value": value,
        "currency": currency,
        "items": [
          {
            "item_id": itemId,
            "item_name": itemName,
            "item_category": itemCategory,
            "quantity": itemQuantity,
            "price": itemPrice,
            "discount": itemDiscount
          }
        ],
        "send_to": mergado_GA4_settings.sendTo,
      };

      if (itemVariantId) {
        eventObject.items[0]['item_variant'] = itemVariantId;
      }

      gtag('event', 'add_to_cart', eventObject);
    },

    sendViewItem: function (value, currency, itemId, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount) {
      const eventObject = {
        "value": value,
        "currency": currency,
        "items": [
          {
            "item_id": itemId,
            "item_name": itemName,
            "item_category": itemCategory,
            "quantity": itemQuantity,
            "price": itemPrice,
            "discount": itemDiscount
          }
        ],
        "send_to": mergado_GA4_settings.sendTo,
      };

      if (itemVariantId) {
        eventObject.items[0]['item_variant'] = itemVariantId;
      }

      gtag('event', 'view_item', eventObject);
    },

    sendSelectItem: function (itemId, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount, itemCurrency) {
      const eventObject = {
        "items": [
          {
            "item_id": itemId,
            "item_name": itemName,
            "item_category": itemCategory,
            "quantity": itemQuantity,
            "price": itemPrice,
            "discount": itemDiscount,
            "currency": itemCurrency
          }
        ],
        "send_to": mergado_GA4_settings.sendTo
      };

      if (itemVariantId) {
        eventObject.items[0]['item_variant'] = itemVariantId;
      }

      gtag('event', 'select_item', eventObject);
    },
    sendSelectContent: function(itemId) {
      gtag('event', 'select_content', {
        'content_type': 'product',
        'item_id': itemId,
        'send_to': mergado_GA4_settings.sendTo
      });
    },
    sendViewItemList: function(items) {
      gtag('event', 'view_item_list', {
        'items': items,
        'send_to': mergado_GA4_settings.sendTo
      })
    },
    sendSearch: function (search_term) {
      gtag('event', 'search', {
        'search_term': search_term,
        'send_to': mergado_GA4_settings.sendTo
      });
    },
    sendViewCart: function (value, currency, items) {
      const eventObject = {
        'value': Math.round((value + Number.EPSILON) * 100) / 100,
        'currency': currency,
        'items': items,
        'send_to': mergado_GA4_settings.sendTo
      };

      gtag('event', 'view_cart', eventObject)
    },
    sendBeginCheckout: function (value, currency, items, coupon) {
      const eventObject = {
        'value': Math.round((value + Number.EPSILON) * 100) / 100,
        'currency': currency,
        'items': items,
        'send_to': mergado_GA4_settings.sendTo
      };

      if (coupon) {
        eventObject['coupon'] = coupon;
      }

      gtag('event', 'begin_checkout', eventObject);
    },
    sendRemoveFromCart: function (value, currency, items) {
      const eventObject = {
        'value': Math.round((value + Number.EPSILON) * 100) / 100,
        'currency': currency,
        'items': items,
        'send_to': mergado_GA4_settings.sendTo
      };

      gtag('event', 'remove_from_cart', eventObject);
    },
    sendAddShippingInfo: function (value, shippingTier, currency, items, coupon) {
      const eventObject = {
        'value': value,
        'items': items,
        'shipping_tier': shippingTier,
        'currency': currency,
        'coupon': coupon,
        'send_to': mergado_GA4_settings.sendTo
      };

      if (coupon) {
        eventObject['coupon'] = coupon;
      }

      gtag('event', 'add_shipping_info', eventObject)
    },
    sendAddPaymentInfo: function (value, paymentType, currency, items, coupon) {
      const eventObject = {
        'value': value,
        'items': items,
        'payment_type': paymentType,
        'currency': currency,
        'send_to': mergado_GA4_settings.sendTo
      };

      if (coupon) {
        eventObject['coupon'] = coupon;
      }

      gtag('event', 'add_payment_info', eventObject)
    },
    sendPurchase: function (transactionId, value, tax, shipping, currency, items, coupon) {
      const eventObject = {
        'transaction_id': transactionId.toString(),
        'value': value,
        'tax': tax,
        'shipping': shipping,
        'currency': currency,
        'items': items,
        'send_to': mergado_GA4_settings.sendTo
      };

      if (coupon) {
        eventObject['coupon'] = coupon;
      }

      gtag('event', 'purchase', eventObject)
    }
  },
  functions: {
    getProductPrices(productJSON) {
      if (mergado_GA4_settings.withVat) {
        return {
          price: productJSON.price_with_reduction_with_tax,
          discount: productJSON.reduction_with_tax
        }
      } else {
        return {
          price: productJSON.price_with_reduction_without_tax,
          discount: productJSON.reduction_without_tax
        }
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
    getProductObjectFromTarget(target, selector) {
      let productJSON, prices;

      productJSON = JSON.parse($(target).find(selector).attr('data-product'));
      prices = mmp_GA4_helpers.functions.getProductPrices(productJSON);

      return {productJSON, prices};
    },
    getProductObject(selector) {
      let productJSON, prices;

      if ($(selector).length > 0) {
        productJSON = JSON.parse($(selector).attr('data-product'));
        prices = mmp_GA4_helpers.functions.getProductPrices(productJSON);

        return {productJSON, prices};
      }
    },
    getProductObjectAttribute(mergedId) {
      let productJSON, prices;

      let attributesData = $('#mergado-product-informations.mergado-product-data[data-product-attributes]');

      if (attributesData.length > 0) {
        attributesData = JSON.parse(attributesData.attr('data-product-attributes'));
        productJSON = attributesData[mergedId];
        prices = mmp_GA4_helpers.functions.getProductPrices(productJSON);
      }

      return {productJSON, prices};
    },
    getCartItems(products) {
      let totalProducts = 0;
      let outputProducts = [];

      if (products.length > 0) {
        products.forEach(function (product) {
          const prices = mmp_GA4_helpers.functions.getProductPrices(product)

          const {
            itemDiscount,
            itemIdMerged,
            itemName,
            itemPrice,
            itemVariantId,
            itemCategory,
            coupon
          } = mmp_GA4_helpers.functions.getProductData(product, prices);

          totalProducts += itemPrice;

          const itemObject = {
            "item_id": itemIdMerged,
            "item_name": itemName,
            "item_category": itemCategory,
            "quantity": product.quantity,
            "price": itemPrice,
            "discount": itemDiscount
          };

          if (itemVariantId) {
            itemObject['item_variant'] = itemVariantId;
          }

          if (coupon) {
            itemObject['coupon'] = coupon;
          }

          outputProducts.push(
              itemObject
          )
        });
      }

      return {totalProducts, outputProducts};
    },
    getCartData() {
      return $.getJSON(mergado_ajax_cart_get_data, function (data) {
          return data;
      });
    },
    getCartValue(cartData) {
      let cartValue;

      if (mergado_GA4_settings.withVat) {
        cartValue = cartData.order_total_with_taxes;

        if (!mergado_GA4_settings.withShipping) {
          cartValue = cartValue - cartData.order_shipping_with_taxes;
        }

      } else {
        cartValue = cartData.order_total_without_taxes;

        if (!mergado_GA4_settings.withShipping) {
          cartValue = cartValue - cartData.order_shipping_without_taxes;
        }
      }

      return cartValue;
    },
    getCarrier(shippingId) {
      const carriers = window.mergado_shipping_data.carriers;

      return carriers.filter(obj => {
        return obj.id == shippingId
      });
    }
  }
}
