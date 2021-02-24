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
document.addEventListener("DOMContentLoaded", function(event) {
    if(typeof fbq !== 'undefined') {
        $('.ajax_add_to_cart_button').on('click', function () {
            var $_currency = $(this).closest('li').find('[itemprop="priceCurrency"]').attr('content');
            var $_id = $(this).attr('data-id-product');
            var $_name = $(this).closest('li').find('.product-name').text().replace(/\t/g, '').trim();
            // var $_price = $(this).closest('li').find('.content_price .price.product-price').first().text().replace(',', '.').replace($('#mergadoSetup').attr('data-currencySymbol'), '').replace(/\t/g, '').trim();

            if ($(this).attr('data-id-product-attribute')) {
                $_id = $_id + '-' + $(this).attr('data-id-product-attribute');
            }

            fbq('track', 'AddToCart', {
                content_name: $_name,
                content_ids: [$_id],
                content_type: 'product',
                // value: 4.99,
                currency: $_currency,
            });
        });

        $('#add_to_cart button').on('click', function () {
            var $_currency = $(this).closest('form').find('[itemprop="priceCurrency"]').attr('content');
            var $_id = $(this).closest('form').find('#product_page_product_id').val();
            var $_name = $('h1[itemprop="name"]').text();
            var $_price = $(this).closest('form').find('#our_price_display').text().replace(',', '.').replace($('#mergadoSetup').attr('data-currencySymbol'), '').trim();

            if ($(this).closest('form').find('#idCombination').length > 0) {
                $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
            }

            fbq('track', 'AddToCart', {
                content_name: $_name,
                content_ids: [$_id],
                content_type: 'product',
                // value: 4.99,
                currency: $_currency,
            });
        });

        // In product detail and modal in PS1.7 --------------------------------------------
        $(document).ready(function () {
            $('.add-to-cart').on('click', addEvents);

            if(typeof prestashop !== 'undefined') {
                prestashop.on(
                    'updatedProduct',
                    function() {
                        $('.add-to-cart').off('click', addEvents);
                        $('.add-to-cart').on('click', addEvents);
                    }
                );
            }
        });

        function addEvents() {
            if($('[data-product]').length > 0) {
                var productJSON = JSON.parse($('[data-product]').attr('data-product'));
                var $_id = productJSON.id;
                var $_name = productJSON.name;
                var $_price = productJSON.price_amount;
                var $_category = productJSON.category_name;

                if(productJSON.id_product_attribute !== "") {
                    $_id = $_id + '-' + productJSON.id_product_attribute;
                }
            } else {
                var $_id = $(this).closest('form').find('#product_page_product_id').val();
                var $_name = $('h1[itemprop="name"]').text();
                var $_price = $('.product-price').find('[itemprop="price"]').attr('content');
                var $_category = '';

                if($_name === '') {
                    $_name = $('.modal-body h1').text();
                }

                if($(this).closest('form').find('#idCombination').length > 0) {
                    $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
                }
            }

            var $_currency = $('.product-price').find('[itemprop="priceCurrency"]').attr('content');

            fbq('track', 'AddToCart', {
                content_name: $_name,
                content_category: $_category,
                content_ids: [$_id],
                content_type: 'product',
                value: $_price,
                currency: $_currency,
            });
        }
    }
});