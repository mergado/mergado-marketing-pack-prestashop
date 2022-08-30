<?php

namespace Mergado\includes\services\Google\Gtag;

use MediaCore;
use Mergado\includes\services\Google\GoogleAds\GoogleAdsService;
use Mergado\includes\services\Google\GoogleAnalytics4\GoogleAnalytics4Service;
use Mergado\includes\services\Google\GoogleAnalytics4\GoogleAnalytics4ServiceIntegration;
use Mergado\includes\services\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService;
use Mergado\includes\services\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsServiceIntegration;
use Mergado\includes\traits\SingletonTrait;
use Mergado\includes\tools\CookieService;

class GtagIntegrationHelper
{
    protected $googleUniversalAnalyticsService;
    protected $googleUniversalAnalyticsServiceIntegration;
    protected $googleAdsService;
    protected $googleAnalytics4Service;
    protected $googleAnalytics4ServiceIntegration;
    protected $cookieService;

    use SingletonTrait;

    protected function __construct()
    {
        $this->googleUniversalAnalyticsService = GoogleUniversalAnalyticsService::getInstance();
        $this->googleUniversalAnalyticsServiceIntegration = GoogleUniversalAnalyticsServiceIntegration::getInstance();
        $this->googleAdsService = GoogleAdsService::getInstance();
        $this->googleAnalytics4Service = GoogleAnalytics4Service::getInstance();
        $this->googleAnalytics4ServiceIntegration = GoogleAnalytics4ServiceIntegration::getInstance();
        $this->cookieService = CookieService::getInstance();
    }

    public function insertHeader($module, $smarty, $context, $path)
    {
        if ($this->cookieService->analyticalEnabled()) {
            $analyticalStorage = 'granted';
        } else {
            $analyticalStorage = 'denied';
        }

        if ($this->cookieService->advertismentEnabled()) {
            $advertisementStorage = 'granted';
        } else {
            $advertisementStorage = 'denied';
        }

        $gtagMainCode = '';

        //Google analytics
        $googleUniversalAnalyticsActive = $this->googleUniversalAnalyticsService->isActive();
        $googleAnalytics4Active = $this->googleAnalytics4Service->isActive();

        //Gogle ADS
        $googleAdsConversionsActive = $this->googleAdsService->isConversionsActive();
        $googleAdsRemarketingActive = $this->googleAdsService->isRemarketingActive();

        //Primarily use code for analytics so no need for config on all functions
        if ($googleUniversalAnalyticsActive) {
            $gaMeasurementId = $this->googleUniversalAnalyticsService->getCode();

            $gtagMainCode = $gaMeasurementId;
            $gtagAnalyticsCode = $gaMeasurementId;
        }

        if ($googleAnalytics4Active) {
            $ga4MeasurementId = $this->googleAnalytics4Service->getCode();

            if ($gtagMainCode == '') {
                $gtagMainCode = $ga4MeasurementId;
            }

            $gtagAnalytics4Code = $ga4MeasurementId;
        }

        if ($googleAdsRemarketingActive || $googleAdsConversionsActive) {
            $googleAdsConversionCode = $this->googleAdsService->getConversionsCode();

            if ($gtagMainCode == '') {
                $gtagMainCode = $googleAdsConversionCode;
            }
        }

        if (isset($gtagMainCode) && $gtagMainCode !== '') {
            $templateVariables = [
                'gtagMainCode' => $gtagMainCode,
                'mergadoDebug' => MERGADO_DEBUG,
                'googleAdsConversionCode' => isset($googleAdsConversionCode) && $googleAdsConversionCode !== '' ? $googleAdsConversionCode : false,
                'googleUniversalAnalyticsCode' => isset($gtagAnalyticsCode) && $gtagAnalyticsCode !== '' ? $gtagAnalyticsCode : false,
                'googleAnalytics4Code' => isset($gtagAnalytics4Code) && $gtagAnalytics4Code !== '' ? $gtagAnalytics4Code : false,
                'analyticalStorage' => $analyticalStorage,
                'advertisementStorage' => $advertisementStorage,
                'googleAdsRemarketingActive' => $googleAdsRemarketingActive,
                'cookiesAdvertisementEnabled' => $this->cookieService->advertismentEnabled(),
            ];

            $smarty->assign($templateVariables);

            $this->addHeaderJs($context, $path);

            return $module->display($path, 'includes/services/Google/Gtag/templates/gtagjs.tpl');
        }

        return '';
    }

    private function addHeaderJs($context, $path)
    {
        MediaCore::addJsDef(
            array (
                'mmp_gads' => array(
                    'remarketingActive' => $this->googleAdsService->isRemarketingActive(),
                    'remarketingType' => $this->googleAdsService->getRemarketingTypeForTemplate(),
                    'sendTo' => $this->googleAdsService->getConversionsCode()
                ),
                'mmp_gua' => array(
                    'enhancedActive' => $this->googleUniversalAnalyticsService->isActiveEnhancedEcommerce(),
                    'sendTo' => $this->googleUniversalAnalyticsService->getCode(),
                ),
            )
        );

        $context->controller->addJS($path . 'includes/services/Google/Gtag/templates/gtag.js');
    }
}
