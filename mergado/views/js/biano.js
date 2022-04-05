/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   LICENSE.txt
 */

// PS 1.7 - start on document ready when jQuery is already loaded
document.addEventListener("DOMContentLoaded", function (event) {
    if (typeof bianoTrack !== 'undefined') {
        biano.init();
    }
});


var biano = {
    init: function() {
      this.initAddToCartPs16();
      this.initAddToCartPs17();
    },
    initAddToCartPs16: function () {
        $('.ajax_add_to_cart_button').on('click', function () {
            var $_currency = $(this).closest('li').find('[itemprop="priceCurrency"]').attr('content');
            var $_id = $(this).attr('data-id-product');
            var $_quantity = 1;
            var $_price = parseFloat($(this).closest('li').find('.content_price [itemprop="price"].product-price').text().replace(/[^0-9$.,]/g, "").replace(',', '.'));

            if ($(this).attr('data-id-product-attribute')) {
                $_id = $_id + '-' + $(this).attr('data-id-product-attribute');
            }

            biano.sendAddToCartEvent($_id, $_quantity, $_price, $_currency);
        });

        $('#add_to_cart button').on('click', function () {
            var $_id = $(this).closest('form').find('#product_page_product_id').val();
            var $_price = parseFloat(parseFloat($(this).closest('form').find('#our_price_display').attr('content')).toFixed(2));
            var $_quantity = parseInt($(this).closest('form').find('#quantity_wanted').val());
            var $_currency = $(this).closest('form').find('[itemprop="priceCurrency"]').attr('content');

            if ($(this).closest('form').find('#idCombination').length > 0) {
                $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
            }

            biano.sendAddToCartEvent($_id, $_quantity, $_price, $_currency);
        });
    },
    initAddToCartPs17: function () {
        $('body').on('click', '.add-to-cart', function () {
            addEvents($(this));
        });

        function addEvents(target) {
            var $_id, $_quantity, $_price, $_currency;

            if ($('#product-details[data-product]').length > 0) {
                var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
                $_id = productJSON.id;
                $_price = productJSON.price_amount;
                $_quantity = productJSON.quantity_wanted;
                $_currency = prestashop.currency.iso_code;

                if (productJSON.id_product_attribute !== "") {
                    $_id = $_id + '-' + productJSON.id_product_attribute;
                }
            } else {
                $_id = target.closest('form').find('#product_page_product_id').val();
                $_price = parseInt($('.product-price').find('[itemprop="price"]').attr('content'));

                // Source from 1783
                if (isNaN($_price)) {
                    $_price = parseInt($('.product-price').find('[content]').attr('content'));
                }

                $_quantity = parseInt(target.closest('form').find('#quantity_wanted').val());
                $_currency = prestashop.currency.iso_code;

                if (target.closest('form').find('#idCombination').length > 0) {
                    $_id = $_id + '-' + target.closest('form').find('#idCombination').val();
                }
            }

            biano.sendAddToCartEvent($_id, $_quantity, $_price, $_currency);
        }
    },
    sendAddToCartEvent: function(id, quantity, unitPrice, currency) {
        bianoTrack('track', 'add_to_cart', {
            id: id,
            quantity: quantity,
            unit_price: unitPrice,
            currency: currency,
        });
    }
};
