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
