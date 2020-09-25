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
{if $googleAds === '1' && $googleAdsCode && $googleAdsCode !== '' && $googleAdsLabel && $googleAdsLabel !== ''}
    {* PS 1.6 *}
    {if isset($currency->iso_code)}
        {$c_iso_code = $currency->iso_code}
    {* PS 1.7 *}
    {else}
        {$c_iso_code = $currency['iso_code']}
    {/if}

    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
    </script>

    <script>
        gtag('event', 'conversion', {
            'send_to': 'AW-{$googleAdsCode}/{$googleAdsLabel}',
            'value': {$total},
            'currency': '{$c_iso_code}',
            'transaction_id': '{$conversionOrderId}'
        });
    </script>
{/if}