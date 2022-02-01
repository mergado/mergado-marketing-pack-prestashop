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
<script async="true" src="https://www.googletagmanager.com/gtag/js?id={$gtagMainCode}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('consent', 'default', {
      'analytics_storage': '{$gtagCookieSettings['analytics_storage']}',
      'ad_storage': '{$gtagCookieSettings['ad_storage']}',
    });

    {if isset($googleAnalyticsCode)}
        gtag('config', '{$googleAnalyticsCode}');

        window.mmp.cookies.sections.analytical.functions.gtagAnalytics = function () {
          gtag('consent', 'update', {
            'analytics_storage': 'granted'
          });
        };
    {/if}

    {if isset($gAdsConversionCode) && $gAdsRemarketingActive}
        gtag('config', '{$gAdsConversionCode}');
    {elseif isset($gAdsConversionCode)}
        gtag('config', '{$gAdsConversionCode}', {literal}{'allow_ad_personalization_signals': false}{/literal});
    {/if}

    {if isset($gAdsConversionCode)}
        window.mmp.cookies.sections.advertisement.functions.gtagAds = function () {
          gtag('consent', 'update', {
            'ad_storage': 'granted'
          });

          gtag('config', '{$gAdsConversionCode}', {literal}{'allow_ad_personalization_signals': true}{/literal});
        };
    {/if}
</script>
