document.addEventListener('DOMContentLoaded', function () {
  sendEvent(); //First load

  prestashop.on('updateProductList', function (e) {
    sendEvent(); //Pagination or sorting
  });

  function sendEvent()
  {
    let products = [];
    let existingIds = [];

    const pageItems = $('#mergado-product-informations.mergado-product-list-item-data[data-product]');

    if (pageItems.length > 0) {
      pageItems.each(function() {
        const item = JSON.parse($(this).attr('data-product'));


        if (!existingIds.includes(item.id_merged)) {
          existingIds.push(item.id_merged);
          products.push(
              item
          )
        }
      });
    }

    if (products.length > 0) {
      let outputProducts = [];

      products.forEach((product) => {
        const prices = mmp_GA4_helpers.functions.getProductPrices(product);

        const products = {
          'item_id': product.id_merged,
          'item_name': product.name,
          'currency': product.currency,
          'item_category': product.category_name,
          'price': prices.price,
          'discount': prices.discount,
          'quantity': 1,
        };

        if (product.id_product_attribute) {
          products['item_variant'] = product.id_product_attribute;
        }

        outputProducts.push(products);
      });

      mmp_GA4_helpers.events.sendViewItemList(outputProducts);
    }
  }
})
