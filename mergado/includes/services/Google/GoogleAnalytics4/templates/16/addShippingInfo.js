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
