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


<!-- Global site tag (gtag.js) - Google Analytics -->
<script>
    var gtagMain = '{$gtagMainCode}';
</script>

<script async src="https://www.googletagmanager.com/gtag/js?id={$gtagMainCode}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    {if isset($googleAnalyticsCode)}
        gtag('config', '{$googleAnalyticsCode}');
    {/if}

    {if isset($gAdsConversionCode) && $gAdsRemarketingActive}
        gtag('config', 'AW-{$gAdsConversionCode}', {literal}{'send_page_view': true}{/literal});
    {else}
        gtag('config', 'AW-{$gAdsConversionCode}', {literal}{'send_page_view': false}{/literal});
    {/if}
</script>
