{*
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
*}

{if $glami_pixel_orderId !== ''}
    <script>
        {if isset($glami_pixel_orderId) && $glami_pixel_orderId}
        document.onload = function () {
            glami('track', 'Purchase', {
                item_ids: {$glami_pixel_productIds nofilter},
                product_names: {$glami_pixel_productNames nofilter},
                value: {$glami_pixel_value},
                currency: {if _PS_VERSION_ < 1.7}'{$glami_pixel_currency}'{else}'{$currency.iso_code}'{/if},
                transaction_id: '{$glami_pixel_orderId}'
            });
        };
        {/if}
    </script>
{/if}