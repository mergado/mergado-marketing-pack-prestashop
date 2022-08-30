document.addEventListener('DOMContentLoaded', function () {
  let GA4_select_shipping_counter = 1;
  const checkedItem = $('#checkout-delivery-step.-current [id^=delivery_option_]:checked');

  if (checkedItem.length > 0) {
    sendEvent(checkedItem);
  }

  $('.delivery-option input').on('click', function () {
    GA4_select_shipping_counter = GA4_select_shipping_counter + 1;
  });

  prestashop.on('changedCheckoutStep', function (e) {
    const checkedItem = $('#checkout-delivery-step.-current [id^=delivery_option_]:checked');

    if (checkedItem.length > 0) {
      setTimeout(() => {
        sendEvent(checkedItem);
      }, 1000);
    }
  });

  async function sendEvent(element) {
    if (GA4_select_shipping_counter > 0) {
      GA4_select_shipping_counter = GA4_select_shipping_counter - 1;
      const value = element.attr('id');
      const label = $('label[for="' + value + '"]').find('.carrier-name').text();

      const cartData = await mmp_GA4_helpers.functions.getCartData();
      const {outputProducts} = mmp_GA4_helpers.functions.getCartItems(cartData.products);
      const cartValue = mmp_GA4_helpers.functions.getCartValue(cartData);

      mmp_GA4_helpers.events.sendAddShippingInfo(cartValue, label, prestashop.currency.iso_code, outputProducts, cartData.coupons);
    }
  }
})
