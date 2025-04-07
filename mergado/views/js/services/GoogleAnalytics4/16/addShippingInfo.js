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
  var lock = false;

  const checkedItem = $('.delivery_option_radio:checked');

  if (checkedItem.length > 0) {
    sendEvent(checkedItem);
  }

  $('body').on('click', '.delivery_option_radio', async function () {
    sendEvent($(this));
  });

  async function sendEvent(element) {
    if(!lock) {
      lock = true;

      const shippingId = element.val().replace(',', '');
      const carrier = mmp_GA4_helpers.functions.getCarrier(shippingId);
      const cartData = await mmp_GA4_helpers.functions.getCartData();
      const {outputProducts} = mmp_GA4_helpers.functions.getCartItems(cartData.products);
      const cartValue = mmp_GA4_helpers.functions.getCartValue(cartData);

      mmp_GA4_helpers.events.sendAddShippingInfo(cartValue, carrier[0].name, window.currency.iso_code, outputProducts, cartData.coupons);

      setTimeout(function () {
        lock = false;
      }, 500);
    }
  }
})
