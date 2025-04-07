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
  sendEvent(); //First load

  function sendEvent()
  {
    let products = [];
    let existingIds = [];

    const pageItems = $('#mergado-product-informations.mergado-product-list-item-data[data-product]');

    if (pageItems.length > 0) {
      pageItems.each(function() {
        const item = JSON.parse($(this).attr('data-product'));


        if (!existingIds.includes(item.id_merged)) {
          existingIds.push(item.id_merged);
          products.push(
              item
          )
        }
      });
    }

    if (products.length > 0) {
      let outputProducts = [];

      products.forEach((product) => {
        const prices = mmp_GA4_helpers.functions.getProductPrices(product);

        const products = {
          'item_id': product.id_merged,
          'item_name': product.name,
          'currency': product.currency,
          'item_category': product.category_name,
          'price': prices.price,
          'discount': prices.discount,
          'quantity': 1,
        };

        if (product.id_product_attribute) {
          products['item_variant'] = product.id_product_attribute;
        }

        outputProducts.push(products);
      });

      mmp_GA4_helpers.events.sendViewItemList(outputProducts);
    }
  }
})
