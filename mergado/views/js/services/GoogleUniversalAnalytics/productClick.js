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
 *  @license   license.txt
 */

// click on product
document.addEventListener("DOMContentLoaded", function(event) {
    if(typeof gtag !== 'undefined') {
        if($('body#product').length > 0) {
            var $_id, $_name, $_category, $_currency;

            if($('#product-details[data-product]').length > 0) {
                var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
                $_id = productJSON.id;
                $_name = productJSON.name;
                $_category = productJSON.category_name;

                if (typeof prestashop !== 'undefined') {
                    $_currency = prestashop.currency.iso_code
                }

                if(productJSON.id_product_attribute !== "" && productJSON.id_product_attribute !== "0") {
                    $_id = $_id + '-' + productJSON.id_product_attribute;
                }
            } else {
                var baseBlock = $('#add_to_cart').closest('form');

                $_id = baseBlock.find('#product_page_product_id').val();
                $_name = $('h1[itemprop="name"]').text();
                $_category = '';
                $_currency = currency.iso_code;

                if ($_name === '') {
                    $_name = $('.modal-body h1').text();
                }

                if (baseBlock.find('#idCombination').length > 0 && baseBlock.find('#idCombination').val() !== '' && baseBlock.find('#idCombination').val() !== '0') {
                    $_id = $_id + '-' + baseBlock.find('#idCombination').val();
                }
            }

            gtag('event', 'select_content', {
                "content_type": "product",
                "currency" : $_currency,
                "items": [
                    {
                        "id": $_id,
                        "name": $_name,
                        // "list_name": "Search Results",
                        // "brand": "Google",
                        "category": $_category,
                        // "variant": "Black",
                        // "list_position": 1,
                        // "quantity": 2,
                        // "price": '2.0'
                    }
                ],
                "send_to": mergado_gua_settings.sendTo,
            });
        }
    }
});
