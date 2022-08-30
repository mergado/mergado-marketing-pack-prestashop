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
