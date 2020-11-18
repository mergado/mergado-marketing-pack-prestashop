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

{if $fbPixelData['active'] == 1}
    {* PS 1.6 *}
    {if isset($currency->iso_code)}
        {$c_iso_code = $currency->iso_code}
    {* PS 1.7 *}
    {else}
        {$c_iso_code = $currency['iso_code']}
    {/if}

    <script>
        {* PS 1.7 - start on document ready when jQuery is already loaded *}
        document.addEventListener("DOMContentLoaded", function(event) {
            $(function () {
                fbq('track', 'Purchase', {
                    content_ids: [{$fbPixelData['products']|json_encode nofilter}], // Used nofilter because of console errors
                    content_type: 'product',
                    value: {$fbPixelData['orderValue']},
                    currency: '{$c_iso_code}'
                });
            });
        });
    </script>
{/if}
