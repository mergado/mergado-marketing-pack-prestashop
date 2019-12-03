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

    <script type="text/javascript">
        /* <![CDATA[ */
        var google_conversion_id = {$googleAdsCode};
        var google_conversion_label = "{$googleAdsLabel}";

        var google_conversion_language = "{$languageCode}";
        var google_conversion_value = {$total};
        var google_conversion_currency = "{$c_iso_code}";

        var google_conversion_format = "1";
        var google_conversion_color = "666666";
        var google_remarketing_only = "false";
        /* ]]> */
    </script>
    <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
    <noscript>
        <img height=1 width=1 border=0
             src="//www.googleadservices.com/pagead/conversion/{$googleAdsCode}/?value={$total}&amp;currency_code={$c_iso_code}&amp;label={$googleAdsLabel}&amp;guid=ON&amp;script=0">
    </noscript>
{/if}