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
    itemCategory
  } = mmp_GA4_helpers.functions.getProductData(productJSON, prices);

  const value = itemPrice * itemQuantity;

  mmp_GA4_helpers.events.sendViewItem(value, currency, itemIdMerged, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount);
})
