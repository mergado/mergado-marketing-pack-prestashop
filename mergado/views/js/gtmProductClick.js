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

// click on product
document.addEventListener("DOMContentLoaded", function(event) {
    if(typeof dataLayer !== 'undefined') {
        if($('body#product').length > 0) {
            var $_id, $_name, $_category, $_currency;

            if($('[data-product]').length > 0) {
                var productJSON = JSON.parse($('[data-product]').attr('data-product'));
                $_id = productJSON.id;
                $_name = productJSON.name;
                $_category = productJSON.category_name;

                if (typeof prestashop !== 'undefined') {
                    $_currency = prestashop.currency.iso_code
                }

                if(productJSON.id_product_attribute !== "" && productJSON.id_product_attribute !== '0') {
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

                if (baseBlock.find('#idCombination').length > 0) {
                    $_id = $_id + '-' + baseBlock.find('#idCombination').val();
                }
            }

            dataLayer.push({
                'event': 'productClick',
                'ecommerce': {
                    'currencyCode': $_currency,
                    'click': {
                        'products': [{
                            'name': $_name,
                            'id': $_id,
                            'category': $_category,
                        }]
                    }
                }
            });
        }
    }
});
