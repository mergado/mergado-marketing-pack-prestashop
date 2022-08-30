document.addEventListener('DOMContentLoaded', function () {
  let cartData = mergado_cart_data;

  prestashop.on('updateCart', async function (e) {
    let newCartData = await mmp_GA4_helpers.functions.getCartData();

    if (JSON.stringify(cartData.products) !== JSON.stringify(newCartData.products)) {
      const diff = newCartData.products.reduce((output, product1) => {
        const product2 = cartData.products.find((item) => {
          return item.id === product1.id && item.id_product_attribute === product1.id_product_attribute;
        });

        if (product1.quantity != product2.quantity) {
          const resultProduct = structuredClone(product1);
          resultProduct.quantity = product1.quantity - product2.quantity;
          output.push(resultProduct)
        }

        return output;
      }, []);

      diff.forEach((product) => {
        const clonedProduct = structuredClone(product);
        clonedProduct.quantity = Math.abs(clonedProduct.quantity);

        const mergadoEventItemRemoved = new CustomEvent('mergado_cart_item_removed', {'detail': clonedProduct});
        const mergadoEventItemAdded = new CustomEvent('mergado_cart_item_added', {'detail': clonedProduct});

        if (product.quantity > 0) {
          document.body.dispatchEvent(mergadoEventItemAdded);
        } else if (product.quantity < 0) {
          document.body.dispatchEvent(mergadoEventItemRemoved);
        }
      });

      cartData = newCartData;
    }
  }, cartData);
});
