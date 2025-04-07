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
<script async src="https://www.googletagmanager.com/gtag/js?id={$gtagMainCode}"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('consent', 'default', {
      'analytics_storage': '{$analyticalStorage}',
      'ad_storage': '{$advertisementStorage}',
      'ad_user_data': '{$advertisementStorage}',
      'ad_personalization': '{$advertisementStorage}',
    });

    {if $googleUniversalAnalyticsCode}
        gtag('config', '{$googleUniversalAnalyticsCode}');
    {/if}

    {if $googleAnalytics4Code}
        gtag('config', '{$googleAnalytics4Code}' {if $mergadoDebug}, { 'debug_mode': true } {/if});
    {/if}

    {if $googleAdsConversionCode && $googleAdsRemarketingActive}
        {if $cookiesAdvertisementEnabled}
            gtag('config', '{$googleAdsConversionCode}');
        {else}
            gtag('config', '{$googleAdsConversionCode}', {literal}{'allow_ad_personalization_signals': false}{/literal});
        {/if}
    {elseif $googleAdsConversionCode}
        gtag('config', '{$googleAdsConversionCode}', {literal}{'allow_ad_personalization_signals': false}{/literal});
    {/if}

    {if $googleAnalytics4Code || googleUniversalAnalyticsCode}
        window.mmp.cookies.sections.analytical.functions.gtagAnalytics = function () {
          gtag('consent', 'update', {
            'analytics_storage': 'granted'
          });
        };
    {/if}

    {if $googleAdsConversionCode}
        window.mmp.cookies.sections.advertisement.functions.gtagAds = function () {
          gtag('consent', 'update', {
            'ad_storage': 'granted',
            'ad_user_data': 'granted',
            'ad_personalization': 'granted',
          });

          gtag('config', '{$googleAdsConversionCode}', {literal}{'allow_ad_personalization_signals': true}{/literal});
        };
    {/if}

    {if $universalAnalyticsEnhancedEcommerceActive}
        gtag('config','{$googleUniversalAnalyticsCode}', {literal}{'allow_enhanced_conversions': true}{/literal});

        {if $customerData}
            gtag('config', '{$googleUniversalAnalyticsCode}', {$customerData nofilter});
        {/if}
    {/if}

    {if $analytics4EnhancedEcommerceActive}
        gtag('config','{$googleAnalytics4Code}', {literal}{'allow_enhanced_conversions': true}{/literal});

        {if $customerData}
            gtag('config', '{$googleAnalytics4Code}', {$customerData nofilter});
        {/if}
    {/if}

    {if $adsEnhancedEcommerceActive}
        gtag('config','{$googleAdsConversionCode}', {literal}{'allow_enhanced_conversions': true}{/literal});

        {if $customerData}
            gtag('config', '{$googleAdsConversionCode}', {$customerData nofilter});
        {/if}
    {/if}
</script>
