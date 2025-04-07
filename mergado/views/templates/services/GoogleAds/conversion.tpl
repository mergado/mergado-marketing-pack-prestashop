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
<div id="googleAdsConversions">
    <script>
        gtag('event', 'conversion', {
            'send_to': '{$gads_sendTo}',
            'value':
            {if $gads_withVat}
                {if $gads_withShipping}
                    mergado_order_data.total_products_wt + mergado_order_data.total_shipping_tax_incl
                {else}
                    mergado_order_data.total_products_wt
                {/if}
            {else}
                {if $gads_withShipping}
                  mergado_order_data.total_products + mergado_order_data.total_shipping_tax_excl
                {else}
                  mergado_order_data.total_products
                {/if}
            {/if},
            'currency': '{$gads_currency}',
            'transaction_id': '{$gads_transactionId}',
        });
    </script>
</div>
