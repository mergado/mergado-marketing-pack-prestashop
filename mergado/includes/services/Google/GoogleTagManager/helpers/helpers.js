/***********************************************************************************************************************
 * GTAG FUNCTIONS
 **********************************************************************************************************************/

var mmp_GTM_helpers = {
  events: {
    sendAddToCart: function (id, name, category, price, currency, quantity) {
      dataLayer.push({
        'event': 'addToCart',
        'ecommerce': {
          'currencyCode': currency,
          'add': {
            'products': [{
              'name': name,
              'id': id,
              'price': price,
              'category': category,
              'quantity': quantity
            }]
          }
        }
      });
    },
    sendRemoveFromCart: function(id, currency) {
      dataLayer.push({
        'event': 'removeFromCart',
        'ecommerce': {
          'currencyCode': currency,
          'remove': {
            'products': [{
              'id': id,
            }]
          }
        }
      });
    },
    sendViewItem: function(id, name, category, currency) {
      dataLayer.push({
        'event': 'viewItem',
        'ecommerce': {
          'currencyCode': currency,
          'detail': {
            'products': [{
              'name': name,
              'id': id,
              'category': category
            }]
          }
        }
      });
    },
    sendCheckoutOptionSelected: function (step, value) {
      dataLayer.push({
        'event': 'checkoutOption',
        'ecommerce': {
          'checkout_option': {
            'actionField': {'step': step, 'option': value}
          }
        }
      });
    },
    sendCheckoutProgress: function(step, items, currency) {
      dataLayer.push({
        'event': 'checkout',
        'ecommerce': {
          'currencyCode': currency,
          'checkout': {
            'actionField': {'step': step},
            'products': items
          }
        }
      });
    },
    sendViewList: function (currency, items) {
      dataLayer.push({
        'event': 'view_item_list',
        'ecommerce': {
          'currencyCode': currency,
          'impressions': items
        }
      });
    }
  },
  functions: {
    getProductPrices(productJSON) {
      if (mergado_GTM_settings.withVat) {
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
    getProductObject(selector) {
      let productJSON, prices;

      if ($(selector).length > 0) {
        productJSON = JSON.parse($(selector).attr('data-product'));
        prices = mmp_GTM_helpers.functions.getProductPrices(productJSON);

        return {productJSON, prices};
      }
    },
    getProductObjectAttribute(mergedId) {
      let productJSON, prices;

      let attributesData = $('#mergado-product-informations.mergado-product-data[data-product-attributes]');

      if (attributesData.length > 0) {
        attributesData = JSON.parse(attributesData.attr('data-product-attributes'));
        productJSON = attributesData[mergedId];
        prices = mmp_GTM_helpers.functions.getProductPrices(productJSON);
      }

      return {productJSON, prices};
    },
    getMscdData()
    {
      if (mergado_GTM_settings.withVat) {
        return JSON.parse($('[data-mscd]').attr('data-products-with-vat'));
      } else {
        return JSON.parse($('[data-mscd]').attr('data-products-without-vat'));
      }
    }
  }
}
