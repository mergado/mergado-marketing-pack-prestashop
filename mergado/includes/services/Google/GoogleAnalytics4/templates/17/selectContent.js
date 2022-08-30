document.addEventListener('DOMContentLoaded', function () {
  let GA4_select_content_counter = 0;

  $('body').on('click', '[data-product-attribute]', function () {
    GA4_select_content_counter = GA4_select_content_counter + 1;
  });

  prestashop.on('updatedProduct', function () {
    if (GA4_select_content_counter > 0) {
      GA4_select_content_counter = GA4_select_content_counter - 1;

      const { productJSON, prices } = mmp_GA4_helpers.functions.getProductObject('#mergado-product-informations.mergado-product-data[data-product]');

      const {
        itemIdMerged
      } = mmp_GA4_helpers.functions.getProductData(productJSON, prices);

      mmp_GA4_helpers.events.sendSelectContent(itemIdMerged);

    }
  });
});
