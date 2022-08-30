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

    const {
      productJSON,
      prices
    } = mmp_GA4_helpers.functions.getProductObject('#mergado-product-informations.mergado-product-data[data-product]');

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
})
