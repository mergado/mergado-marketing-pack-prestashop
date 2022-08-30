document.addEventListener('DOMContentLoaded', function () {
  let itemQuantity = 1;

  var baseBlock = $('#add_to_cart').closest('form');
  var attribute_id = baseBlock.find('#idCombination').val();

  const mergedId = window.id_product + '-' + attribute_id;

  const { productJSON, prices } = mmp_GA4_helpers.functions.getProductObjectAttribute(mergedId);

  const {
    itemDiscount,
    currency,
    itemIdMerged,
    itemName,
    itemPrice,
    itemVariantId,
    itemCategory,
  } = mmp_GA4_helpers.functions.getProductData(productJSON, prices);

  mmp_GA4_helpers.events.sendSelectItem(itemIdMerged, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount, currency);
})
