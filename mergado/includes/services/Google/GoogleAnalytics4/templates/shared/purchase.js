document.addEventListener('DOMContentLoaded', function () {
  if(typeof mergado_order_data !== 'undefined') {
    const orderData = mergado_order_data;

    const transactionId = orderData.orderId;

    let value = orderData.total_paid_tax_incl;
    const tax = Math.round((orderData.total_paid_tax_incl - orderData.total_paid_tax_excl + Number.EPSILON) * 100) / 100;
    let shipping = orderData.total_shipping_tax_incl;
    const currency = orderData.currency.iso_code;
    const coupon = orderData.coupons;

    if (!mergado_GA4_settings.withVat) {
      value = orderData.total_paid_tax_excl;
      shipping = orderData.total_shipping_tax_excl;
    }

    if (!mergado_GA4_settings.withShipping) {
      value = value - shipping;
    }

    let items = [];

    orderData.products.forEach((item) => {
      let price = item.regular_price_tax_incl;
      let discount = item.reduction_tax_incl;

      if (!mergado_GA4_settings.withVat) {
        price = item.regular_price_tax_excl;
        discount = item.reduction_tax_excl;
      }

      const productCoupon = '';

      const productObject = {
        'item_id': item.merged,
        'item_name': item.name,
        'item_category': item.category_name,
        'quantity': item.quantity,
        'price': price,
        'discount': discount,
      };

      if (productCoupon) {
        productObject['coupon'] = productCoupon;
      }

      items.push(productObject);
    })

    mmp_GA4_helpers.events.sendPurchase(transactionId, value, tax, shipping, currency, items, coupon);
  }
})
