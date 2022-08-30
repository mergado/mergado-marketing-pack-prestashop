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
