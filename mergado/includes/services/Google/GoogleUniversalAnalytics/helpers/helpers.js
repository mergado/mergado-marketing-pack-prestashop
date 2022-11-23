/***********************************************************************************************************************
 * GTAG FUNCTIONS
 **********************************************************************************************************************/

var mmp_GUA_helpers = {
  events: {
    sendAddToCart: function (id, name, category, price, quantity, currency, sendTo) {
      gtag('event', 'add_to_cart', {
        "currency" : currency,
        "items": [
          {
            "id": id,
            "name": name,
            "category": category,
            "quantity": quantity,
            "price": price,
            "google_business_vertical": mergado_GAds_settings.remarketingType,
          }
        ],
        "send_to": sendTo,
      });
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
    sendViewItem: function(id, name, currency, sendTo) {
      gtag('event', 'view_item', {
        "currency" : currency,
        "items": [
          {
            "id": id,
            "name": name,
            'google_business_vertical': mergado_GAds_settings.remarketingType
          }
        ],
        "send_to": sendTo,
      });
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
    getProductPrice(productJSON) {
      if (mergado_GUA_settings.withVat) {
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
    getProductObject(selector) {
      let productJSON, price;

      if ($(selector).length > 0) {
        productJSON = JSON.parse($(selector).attr('data-product'));
        price = mmp_GUA_helpers.functions.getProductPrice(productJSON);

        return {productJSON, price};
      }
    },
    getMscdData()
    {
      if (mergado_GUA_settings.withVat) {
        return JSON.parse($('[data-mscd]').attr('data-products-with-vat'));
      } else {
        return JSON.parse($('[data-mscd]').attr('data-products-without-vat'));
      }
    },
    getProductObjectAttribute(mergedId) {
      let productJSON, prices;

      let attributesData = $('#mergado-product-informations.mergado-product-data[data-product-attributes]');

      if (attributesData.length > 0) {
        attributesData = JSON.parse(attributesData.attr('data-product-attributes'));
        productJSON = attributesData[mergedId];
        price = mmp_GUA_helpers.functions.getProductPrice(productJSON);
      }

      return {productJSON, price};
    },
    // getCartItems(products) {
    //   let totalProducts = 0;
    //   let outputProducts = [];
    //
    //   if (products.length > 0) {
    //     products.forEach(function (product) {
    //       const prices = mmp_GA4_helpers.functions.getProductPrices(product)
    //
    //       const {
    //         itemDiscount,
    //         itemIdMerged,
    //         itemName,
    //         itemPrice,
    //         itemVariantId,
    //         itemCategory,
    //         coupon
    //       } = mmp_GA4_helpers.functions.getProductData(product, prices);
    //
    //       totalProducts += itemPrice;
    //
    //       const itemObject = {
    //         "item_id": itemIdMerged,
    //         "item_name": itemName,
    //         "item_category": itemCategory,
    //         "quantity": product.quantity,
    //         "price": itemPrice,
    //         "discount": itemDiscount
    //       };
    //
    //       if (itemVariantId) {
    //         itemObject['item_variant'] = itemVariantId;
    //       }
    //
    //       if (coupon) {
    //         itemObject['coupon'] = coupon;
    //       }
    //
    //       outputProducts.push(
    //           itemObject
    //       )
    //     });
    //   }
    //
    //   return {totalProducts, outputProducts};
    // },
    // getCartData() {
    //   return $.getJSON(mergado_ajax_cart_get_data, function (data) {
    //       return data;
    //   });
    // },
    // getCartValue(cartData) {
    //   let cartValue;
    //
    //   if (mergado_GA4_settings.withVat) {
    //     cartValue = cartData.order_total_with_taxes;
    //
    //     if (!mergado_GA4_settings.withShipping) {
    //       cartValue = cartValue - cartData.order_shipping_with_taxes;
    //     }
    //
    //   } else {
    //     cartValue = cartData.order_total_without_taxes;
    //
    //     if (!mergado_GA4_settings.withShipping) {
    //       cartValue = cartValue - cartData.order_shipping_without_taxes;
    //     }
    //   }
    //
    //   return cartValue;
    // },
    // getCarrier(shippingId) {
    //   const carriers = window.mergado_shipping_data.carriers;
    //
    //   return carriers.filter(obj => {
    //     return obj.id == shippingId
    //   });
    // }
  }
}
