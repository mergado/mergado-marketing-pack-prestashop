document.addEventListener('DOMContentLoaded', function () {
  $('.ajax_cart_block_remove_link, .cart_quantity_delete').on('click', async function () {
    const urlParams = new URLSearchParams($(this).attr('href'));

    let id = urlParams.get('id_product');
    const ipa = urlParams.get('ipa');
    const currency = window.currency.iso_code;

    // Old ones
    const products = window.mergado_cart_data.products;
    const removedProduct = products.filter(item => item.id === id && item.id_product_attribute === ipa);
    const { outputProducts } = mmp_GA4_helpers.functions.getCartItems(removedProduct);

    const total = outputProducts[0].price * outputProducts[0].quantity;

    mmp_GA4_helpers.events.sendRemoveFromCart(total, currency, outputProducts);
  });
})
