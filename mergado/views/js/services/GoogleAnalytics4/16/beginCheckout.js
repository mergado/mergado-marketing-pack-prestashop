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
  if (typeof mergado_cart_data !== 'undefined' && $('body#order-opc').length > 0) {
      sendEvent();
  }

  //Multistep
  $('body#order .cart_navigation .standard-checkout').on('click', function () {
      sendEvent();
  });

  function sendEvent() {
    const cartData = mergado_cart_data;
    const products = cartData.products;

    const {outputProducts} = mmp_GA4_helpers.functions.getCartItems(products);
    const total = mmp_GA4_helpers.functions.getCartValue(cartData);

    if (outputProducts.length > 0) {
      mmp_GA4_helpers.events.sendBeginCheckout(total, window.currency.iso_code, outputProducts, cartData.coupons);
    }
  }
});
