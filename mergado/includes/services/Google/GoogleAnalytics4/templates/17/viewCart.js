document.addEventListener('DOMContentLoaded', function () {
  if (typeof mergado_cart_data !== 'undefined') {
    const cartData = mergado_cart_data;
    const products = cartData.products;

    const {outputProducts} = mmp_GA4_helpers.functions.getCartItems(products);
    const cartValue = mmp_GA4_helpers.functions.getCartValue(cartData);

    if (outputProducts.length > 0) {
      mmp_GA4_helpers.events.sendViewCart(cartValue, prestashop.currency.iso_code, outputProducts);
    }
  }
});
