document.addEventListener('DOMContentLoaded', function () {
  let GA4_select_payment_counter = 1;
  const checkedItem = $('#checkout-payment-step.-current [id^=payment-option-]:checked');

  if (checkedItem.length > 0) {
    sendEvent(checkedItem);
  }

  $('.payment-options input').on('click', async function () {
    GA4_select_payment_counter = GA4_select_payment_counter + 1;
  });

  prestashop.on('changedCheckoutStep', function () {
    const checkedItem = $('#checkout-payment-step.-current [id^=payment-option-]:checked');

    if (checkedItem.length > 0) {
      sendEvent(checkedItem);
    }
  });

  async function sendEvent(element) {
    if (GA4_select_payment_counter > 0) {
      GA4_select_payment_counter = GA4_select_payment_counter - 1;
      const value = element.attr('id');
      const label = $('label[for="' + value + '"] span').text();

      const cartData = await mmp_GA4_helpers.functions.getCartData();
      const {outputProducts} = mmp_GA4_helpers.functions.getCartItems(cartData.products);
      const cartValue = mmp_GA4_helpers.functions.getCartValue(cartData);

      mmp_GA4_helpers.events.sendAddPaymentInfo(cartValue, label, prestashop.currency.iso_code, outputProducts, cartData.coupons);
    }
  }
})
