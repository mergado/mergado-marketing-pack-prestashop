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
  prestashop.on('updateCart', async function (e) {
    if (e.reason.linkAction === "delete-from-cart") {
      // Take item from original array of items
      const products = mergado_cart_data.products;
      const removedProduct = products.filter(item => item.id === e.reason.idProduct && item.id_product_attribute === e.reason.idProductAttribute)

      const { outputProducts } = mmp_GA4_helpers.functions.getCartItems(removedProduct);
      const total = outputProducts[0].price * outputProducts[0].quantity;

      mmp_GA4_helpers.events.sendRemoveFromCart(total, prestashop.currency.iso_code, outputProducts);
    }
  });

  document.body.addEventListener('mergado_cart_item_removed', function (e) {
      const { outputProducts } = mmp_GA4_helpers.functions.getCartItems([e.detail]);
      const total = outputProducts[0].price * outputProducts[0].quantity;

      mmp_GA4_helpers.events.sendRemoveFromCart(total, prestashop.currency.iso_code, outputProducts);
  });
})
