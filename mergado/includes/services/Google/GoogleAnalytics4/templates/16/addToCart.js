document.addEventListener('DOMContentLoaded', function () {
  //PS 1.6
  $('.ajax_add_to_cart_button').on('click', function () {
    addAjaxButtonEvent($(this));
  });

  //PS 1.6
  $('#add_to_cart button').on('click', function () {
    addButtonEvent($(this));
  });

  function addAjaxButtonEvent(element) {
    const dataItem = element.closest('li').find('.mergado-product-list-item-data[data-product]');
    const itemQuantity = 1;

    if (dataItem.length > 0) {
      const productData = JSON.parse(dataItem.attr('data-product'));

      const prices = mmp_GA4_helpers.functions.getProductPrices(productData);
      const {
        itemDiscount,
        currency,
        itemIdMerged,
        itemName,
        itemPrice,
        itemVariantId,
        itemCategory
      } = mmp_GA4_helpers.functions.getProductData(productData, prices);

      const value = itemPrice * itemQuantity;

      mmp_GA4_helpers.events.sendAddToCart(value, currency, itemIdMerged, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount);
    }
  }

  function addButtonEvent(element) {
    const buyBlock = element.closest('form');

    let itemQuantity;

    if (buyBlock.find('#quantity_wanted').length > 0) {
      itemQuantity = buyBlock.find('#quantity_wanted').val();
    } else {
      itemQuantity = 1;
    }

    const attribute_id = buyBlock.find('#idCombination').val();

    const mergedId = window.id_product + '-' + attribute_id;

    const {productJSON, prices} = mmp_GA4_helpers.functions.getProductObjectAttribute(mergedId);

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

  // function addEvents(target) {
  //   let itemQuantity;
  //
  //   if (target.closest('.product-add-to-cart').find('#quantity_wanted').length > 0) {
  //     itemQuantity = target.closest('.product-add-to-cart').find('#quantity_wanted').val();
  //   } else {
  //     itemQuantity = 1;
  //   }
  //
  //   const {
  //     productJSON,
  //     prices
  //   } = mmp_GA4_helpers.functions.getProductObject('#mergado-product-informations.mergado-product-data[data-product]');
  //
  //   const {
  //     itemDiscount,
  //     currency,
  //     itemIdMerged,
  //     itemName,
  //     itemPrice,
  //     itemVariantId,
  //     itemCategory
  //   } = mmp_GA4_helpers.functions.getProductData(productJSON, prices);
  //
  //   const value = itemPrice * itemQuantity;
  //
  //   mmp_GA4_helpers.events.sendAddToCart(value, currency, itemIdMerged, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount);
  // }
})
