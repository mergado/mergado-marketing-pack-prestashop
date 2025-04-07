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
  $('body').on('click', '.payment_module a', async function () {
    const name = $(this).attr('title');
    let cartData = mergado_cart_data;

    if ($('body#orderOPC').length > 0) {
      cartData = await mmp_GA4_helpers.functions.getCartData();
    }

    const {outputProducts} = mmp_GA4_helpers.functions.getCartItems(cartData.products);
    const cartValue = mmp_GA4_helpers.functions.getCartValue(cartData);

    mmp_GA4_helpers.events.sendAddPaymentInfo(cartValue, name, window.currency.iso_code, outputProducts, cartData.coupons);
  });
})
