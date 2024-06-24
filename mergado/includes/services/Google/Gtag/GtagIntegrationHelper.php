<?php

namespace Mergado\includes\services\Google\Gtag;

use MediaCore;
use Mergado\includes\helpers\ControllerHelper;
use Mergado\includes\helpers\CustomerHelper;
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

    const TEMPLATES_PATH = 'includes/services/Google/Gtag/templates/';

    use SingletonTrait;

    protected function __construct()
    {
        $this->googleUniversalAnalyticsService = GoogleUniversalAnalyticsService::getInstance();
        $this->googleAnalytics4Service = GoogleAnalytics4Service::getInstance();
        $this->googleAdsService = GoogleAdsService::getInstance();
        $this->googleUniversalAnalyticsServiceIntegration = GoogleUniversalAnalyticsServiceIntegration::getInstance();
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
            if (ControllerHelper::isOrderConfirmation()) {
                $customerData = CustomerHelper::getInstance()->getCustomerInfoOnOrderPage($context->controller->id_order);
            } else {
                $customerData = CustomerHelper::getInstance()->getCustomerInfo();
            }

            $universalAnalyticsEnhancedEcommerceActive = $this->googleUniversalAnalyticsService->isActiveEnhancedEcommerce();
            $analytics4EnhancedEcommerceActive = $this->googleAnalytics4Service->isActiveEcommerce();
            $adsEnhancedEcommerceActive = $this->googleAdsService->isEnhancedConversionsActive();

            $templateVariables = [
                'universalAnalyticsEnhancedEcommerceActive' => $universalAnalyticsEnhancedEcommerceActive,
                'analytics4EnhancedEcommerceActive' => $analytics4EnhancedEcommerceActive,
                'adsEnhancedEcommerceActive' => $adsEnhancedEcommerceActive,
                'gtagMainCode' => $gtagMainCode,
                'mergadoDebug' => MERGADO_DEBUG,
                'googleAdsConversionCode' => isset($googleAdsConversionCode) && $googleAdsConversionCode !== '' ? $googleAdsConversionCode : false,
                'googleUniversalAnalyticsCode' => isset($gtagAnalyticsCode) && $gtagAnalyticsCode !== '' ? $gtagAnalyticsCode : false,
                'googleAnalytics4Code' => isset($gtagAnalytics4Code) && $gtagAnalytics4Code !== '' ? $gtagAnalytics4Code : false,
                'googleAdsRemarketingActive' => $googleAdsRemarketingActive,
                'customerData' => count($customerData) > 0 ? json_encode($customerData) : false,
                'cookiesAdvertisementEnabled' => $this->cookieService->advertismentEnabled(),
                'analyticalStorage' => $analyticalStorage,
                'advertisementStorage' => $advertisementStorage,
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
            [
                'mergado_GAds_settings' => [
                    'remarketingActive' => $this->googleAdsService->isRemarketingActive(),
                    'remarketingType' => $this->googleAdsService->getRemarketingTypeForTemplate(),
                    'sendTo' => $this->googleAdsService->getConversionsCode(),
                ],
                'mergado_GUA_settings' => [
                    'enhancedActive' => $this->googleUniversalAnalyticsService->isActiveEnhancedEcommerce(),
                    'withVat' => $this->googleUniversalAnalyticsService->getConversionVatIncluded(),
                    'sendTo' => $this->googleUniversalAnalyticsService->getCode(),
                ],
            ]
        );

        $context->controller->addJS($path . 'includes/services/Google/GoogleUniversalAnalytics/helpers/helpers.js');
        $context->controller->addJS($path . 'includes/services/Google/Gtag/templates/gtag.js');
    }
}
