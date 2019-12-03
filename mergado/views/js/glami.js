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

$(document).ready(function () {
    $('.ajax_add_to_cart_button').on('click', function () {
        var $_currency = $(this).closest('li').find('[itemprop="priceCurrency"]').attr('content');
        var $_id = $(this).attr('data-id-product') + '-' + $(this).attr('data-id-product-attribute');
        var $_name = $(this).closest('li').find('.product-name').text().replace(/\t/g, '').trim();
        // var $_price = $(this).closest('li').find('.content_price .price.product-price').first().text().replace(',', '.').replace($('#mergadoSetup').attr('data-currencySymbol'), '').replace(/\t/g, '').trim();

        glami('track', 'AddToCart', {
            item_ids: [$_id],
            product_names: [$_name],
            //value: $_price,
            currency: $_currency
        });
    });

    $('#add_to_cart button').on('click', function () {
        var $_currency = $(this).closest('form').find('[itemprop="priceCurrency"]').attr('content');
        var $_id = $(this).closest('form').find('#product_page_product_id').val() + '-' + $(this).find('#idCombination').val();
        var $_name = $('h1[itemprop="name"]').text();
        var $_price = $(this).closest('form').find('#our_price_display').text().replace(',', '.').replace($('#mergadoSetup').attr('data-currencySymbol'), '').trim();

        glami('track', 'AddToCart', {
            item_ids: [$_id],
            product_names: [$_name],
            value: $_price,
            currency: $_currency
        });
    });
});
