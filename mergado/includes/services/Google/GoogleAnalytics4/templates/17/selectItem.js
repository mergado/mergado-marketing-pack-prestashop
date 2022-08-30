document.addEventListener('DOMContentLoaded', function () {
  let itemQuantity = 1;

  const { productJSON, prices } = mmp_GA4_helpers.functions.getProductObject('#mergado-product-informations.mergado-product-data[data-product]');

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
