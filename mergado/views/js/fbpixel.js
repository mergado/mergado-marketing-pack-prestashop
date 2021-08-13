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
                contents: [{'id': $_id, 'quantity': 1}],
                // value: 4.99,
                currency: $_currency,
            });
        });

        $('#add_to_cart button').on('click', function () {
            var $_currency = $(this).closest('form').find('[itemprop="priceCurrency"]').attr('content');
            var $_id = $(this).closest('form').find('#product_page_product_id').val();
            var $_name = $('h1[itemprop="name"]').text();
            var $_price = $(this).closest('form').find('#our_price_display').text().replace(',', '.').replace($('#mergadoSetup').attr('data-currencySymbol'), '').trim();
            var $_quantity = $(this).closest('form').find('#quantity_wanted').val();


            if ($(this).closest('form').find('#idCombination').length > 0) {
                $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
            }

            fbq('track', 'AddToCart', {
                content_name: $_name,
                content_ids: [$_id],
                content_type: 'product',
                contents: [{'id': $_id, 'quantity': $_quantity}],
                // value: 4.99,
                currency: $_currency,
            });
        });

        //PS 1.7
        if($('body#checkout').length > 0 || $('body#order').length > 0 || $('body#order-opc').length > 0) {
            if ($('[data-mscd]').length > 0 && $('[data-mscd-cart-id]').length > 0) {
                var items = JSON.parse($('[data-mscd]').attr('data-mscd'));

                // var content_ids = items.map(function(a) {return a.id;});
                var content_ids = [];
                var contents = [];
                var value = 0;
                var num_items = 0;

                var curr = '';
                if(typeof prestashop !== 'undefined') {
                    curr = prestashop.currency.iso_code;
                } else {
                    curr = currency.iso_code;
                }

                items.forEach(function(element) {
                    content_ids.push(element.id);
                    contents.push({'id': element.id, 'quantity': element.quantity});
                    num_items = num_items + parseInt(element.quantity);
                    value = value + parseFloat(element.price * element.quantity);
                });

                value = value.toFixed(2);

                fbq('track', 'InitiateCheckout', {
                    content_ids: content_ids,
                    contents: contents,
                    value: value,
                    currency: curr,
                    num_items: num_items,
                });
            }
        }

        // In product detail and modal in PS1.7 --------------------------------------------
        $(document).ready(function () {
            $('.add-to-cart').on('click', addEvents);

            if(typeof prestashop !== 'undefined') {
                prestashop.on(
                    'updatedProduct',
                    function(event) {
                        $('.add-to-cart').off('click', { data: event }, addEvents);
                        $('.add-to-cart').on('click', { data: event }, addEvents);
                        sendCustomizationEvent(event);
                    }
                );
            } else {
                setMutationObserver();
            }
        });

        function addEvents(event) {
            if($('#product-details[data-product]').length > 0) {
                var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
                var $_id = productJSON.id;
                var $_name = productJSON.name;
                var $_price = productJSON.price_amount;
                var $_category = productJSON.category_name;
                var $_quantity = $(this).closest('form').find('#quantity_wanted').val();

                if(productJSON.id_product_attribute !== "" && productJSON.id_product_attribute != 0) {
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

                var $_quantity = $(this).closest('form').find('#quantity_wanted').val();

                if (!$_quantity) {
                    $_quantity = 1;
                }

                if (event && event.data.data.id_product_attribute) {
                    $_id = $_id + '-' + event.data.data.id_product_attribute;
                } else if ($(this).closest('form').find('#idCombination').length > 0) {
                    $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
                }
            }

            var $_currency = $('.product-price').find('[itemprop="priceCurrency"]').attr('content');

            fbq('track', 'AddToCart', {
                content_name: $_name,
                content_category: $_category,
                content_ids: [$_id],
                contents: [{'id': $_id, 'quantity': $_quantity}],
                content_type: 'product',
                value: $_price,
                currency: $_currency,
            });
        }

        function sendCustomizationEvent(event = null, element = null)
        {
            if($('#product-details[data-product]').length > 0) {
                var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
                var $_id = productJSON.id;
                var $_name = productJSON.name;
                var $_price = productJSON.price_amount;
                var $_category = productJSON.category_name;
                var $_quantity = $(this).closest('form').find('#quantity_wanted').val();

                if (!$_quantity) {
                    if ($('body#product #quantity_wanted').length > 0) {
                        $_quantity = $('body#product #quantity_wanted').val();
                    } else {
                        $_quantity = 1;
                    }
                }

                if (event !== null && event.id_product_attribute != 0) {
                    $_id = $_id + '-' + event.id_product_attribute;
                } else if (productJSON.id_product_attribute !== "" && productJSON.id_product_attribute != 0) {
                    $_id = $_id + '-' + productJSON.id_product_attribute;
                }
            } else {
                if (element == null) {
                    element = $('.modal .add-to-cart');
                }

                var $_id = $(element).closest('form').find('#product_page_product_id').val();
                var $_name = $('h1[itemprop="name"]').text();
                var $_price = $('.product-price').find('[itemprop="price"]').attr('content');
                var $_quantity = $(element).closest('form').find('#quantity_wanted').val();

                if($_name === '') {
                    $_name = $('.modal-body h1').text();
                }

                if (!$_quantity) {
                    $_quantity = 1;
                }

                if (event !== null && event.id_product_attribute != 0) {
                    $_id = $_id + '-' + event.id_product_attribute;
                } else if($(element).closest('form').find('#idCombination').length > 0) {
                    $_id = $_id + '-' + $(element).closest('form').find('#idCombination').val();
                }
            }

            var $_currency = $('.product-price').find('[itemprop="priceCurrency"]').attr('content');

            fbq('track', 'CustomizeProduct', {
                content_name: $_name,
                content_ids: [$_id],
                contents: [{'id': $_id, 'quantity': $_quantity}],
                content_type: 'product',
                value: $_price,
                currency: $_currency,
            });
        }

        function setMutationObserver()
        {
            MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

            var trackChange = function(element) {
                var observer = new MutationObserver(function(mutations, observer) {
                    if(mutations[0].attributeName == "value") {
                        $(element).trigger("change");
                    }
                });
                observer.observe(element, {
                    attributes: true
                });
            }

            // Just pass an element to the function to start tracking
            if ($('#idCombination').length > 0) {
                trackChange( $("#idCombination")[0]);
            }

            $('#idCombination').on('change', function() {
                sendCustomizationEvent(null, $(this));
            });
        }
    }
});