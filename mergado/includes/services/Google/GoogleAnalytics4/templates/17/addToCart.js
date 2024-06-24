document.addEventListener('DOMContentLoaded', function () {
  $('body').on('click', '.add-to-cart', function () {
    addEvents($(this))
  });

  document.body.addEventListener('mergado_cart_item_added', function (e) {
    const {outputProducts} = mmp_GA4_helpers.functions.getCartItems([e.detail]);
    const total = outputProducts[0].price * outputProducts[0].quantity;

    mmp_GA4_helpers.events.sendAddToCart(
        total,
        outputProducts[0].currency,
        outputProducts[0].item_id,
        outputProducts[0].item_variant,
        outputProducts[0].item_name,
        outputProducts[0].item_category,
        outputProducts[0].quantity,
        outputProducts[0].price,
        outputProducts[0].discount
    );
  });

  function addEvents(target) {
    let itemQuantity;

    if (target.closest('.product-add-to-cart').find('#quantity_wanted').length > 0) {
      itemQuantity = target.closest('.product-add-to-cart').find('#quantity_wanted').val();
    } else {
      itemQuantity = 1;
    }

    let productJSON, prices;

    // Normal PS 1.7 data
    let productData = mmp_GA4_helpers.functions.getProductObject('#mergado-product-informations.mergado-product-data[data-product]')

    if (!productData) {
      // If someone modified PS 1.7 and added add to cart on product list page
      productData = mmp_GA4_helpers.functions.getProductObjectFromTarget($(target).closest('.product-item, .product-miniature'), '#mergado-product-informations.mergado-product-list-item-data[data-product]');
    }

    productJSON = productData.productJSON;
    prices = productData.prices;

    if (productJSON && prices) {
      const {
        itemDiscount,
        currency,
        itemIdMerged,
        itemName,
        itemPrice,
        itemVariantId,
        itemCategory
      } = mmp_GA4_helpers.functions.getProductData(productJSON, prices);
      const value = itemPrice * itemQuantity;

      mmp_GA4_helpers.events.sendAddToCart(value, currency, itemIdMerged, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount);
    }
  }
})
