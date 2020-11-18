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

{if isset($glamiData['orderId']) && $glamiData['orderId'] !== '' && $glamiData['orderId']}
    <script>
        if (typeof glami !== 'undefined') {
            glami('track', 'Purchase', {
                item_ids: {$glamiData['productIds'] nofilter},
                product_names: {$glamiData['productNames'] nofilter},
                value: {$glamiData['value']},
                currency: {if _PS_VERSION_ < 1.7}'{$glamiData['currency']}'{else}'{$currency.iso_code}'{/if},
                transaction_id: '{$glamiData['orderId']}'
            });
        }
    </script>
{/if}