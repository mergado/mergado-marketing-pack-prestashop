<?php declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Service\External\Google\Gtag;

use Category;
use Configuration;
use Currency;
use Manufacturer;
use Mergado\Helper\ControllerHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\Google\GoogleAds\GoogleAdsService;
use Mergado\Service\External\Google\GoogleAnalytics4\GoogleAnalytics4Service;
use Mergado\Service\External\Google\GoogleAnalytics4\GoogleAnalytics4ServiceIntegration;
use Mergado\Service\External\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService;
use Mergado\Service\External\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsServiceIntegration;
use Mergado\Service\CookieService;
use Mergado\Service\Data\CustomerDataService;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class GtagIntegrationHelper extends AbstractBaseService
{
    protected $googleUniversalAnalyticsService;
    protected $googleUniversalAnalyticsServiceIntegration;
    protected $googleAdsService;
    protected $googleAnalytics4Service;
    protected $googleAnalytics4ServiceIntegration;
    protected $cookieService;

    public const TEMPLATES_PATH = 'views/templates/services/Gtag/';
    public const JS_PATH = 'views/js/services/Gtag/';

    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    protected function __construct()
    {
        $this->googleUniversalAnalyticsService = GoogleUniversalAnalyticsService::getInstance();
        $this->googleAnalytics4Service = GoogleAnalytics4Service::getInstance();
        $this->googleAdsService = GoogleAdsService::getInstance();
        $this->googleUniversalAnalyticsServiceIntegration = GoogleUniversalAnalyticsServiceIntegration::getInstance();
        $this->googleAnalytics4ServiceIntegration = GoogleAnalytics4ServiceIntegration::getInstance();
        $this->cookieService = CookieService::getInstance();
        $this->controllerHelper = ControllerHelper::getInstance();

        parent::__construct();
    }

    public function insertHeader($module, $smarty, $context, $path) : string
    {
        try {
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

            //Google Analytics
            $googleUniversalAnalyticsActive = $this->googleUniversalAnalyticsService->isActive();
            $googleAnalytics4Active = $this->googleAnalytics4Service->isActive();

            //Google ADS
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

                if ($gtagMainCode === '') {
                    $gtagMainCode = $ga4MeasurementId;
                }

                $gtagAnalytics4Code = $ga4MeasurementId;
            }

            if ($googleAdsRemarketingActive || $googleAdsConversionsActive) {
                $googleAdsConversionCode = $this->googleAdsService->getConversionsCode();

                if ($gtagMainCode === '') {
                    $gtagMainCode = $googleAdsConversionCode;
                }
            }

            if (isset($gtagMainCode) && $gtagMainCode !== '') {
                if ($this->controllerHelper->isOrderConfirmation()) {
                    $customerData = CustomerDataService::getInstance()->getCustomerInfoOnOrderPage($context->controller->id_order);
                } else {
                    $customerData = CustomerDataService::getInstance()->getCustomerInfo();
                }

                $universalAnalyticsEnhancedEcommerceActive = $this->googleUniversalAnalyticsService->isActiveEnhancedEcommerce();
                $analytics4EnhancedEcommerceActive = $this->googleAnalytics4Service->isActiveEcommerce();
                $adsEnhancedEcommerceActive = $this->googleAdsService->isEnhancedConversionsActive();

                $this->addHeaderJs($context, $path);

                return SmartyTemplateLoader::render(
                    $module,
                    self::TEMPLATES_PATH . 'gtagjs.tpl',
                    $smarty,
                    [
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
                    ]
                );
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    private function addHeaderJs($context, $path): void
    {
        $context->controller->addJS($path . GoogleUniversalAnalyticsServiceIntegration::JS_PATH . 'helpers.js');
        $context->controller->addJS($path . self::JS_PATH . 'gtag-16.js');
        $context->controller->addJS($path . self::JS_PATH . 'gtag-17.js');
        $context->controller->addJS($path . self::JS_PATH . 'gtag-shared.js');
    }

    public static function getOrderDataForAds($order, $orderId, $withVat, $shippingPriceIncluded, $sendTo): array
    {
        $data = [];

        $currency = new Currency($order->id_currency);

        $data['transaction_id'] = "$orderId";
        $data['affiliation'] = Configuration::get('PS_SHOP_NAME');
        $data['currency'] = $currency->iso_code;
        $data['tax'] = (string)($order->total_paid_tax_incl - $order->total_paid_tax_excl);
        $data['shipping'] = (string)$order->total_shipping_tax_excl;
        $data['sendTo'] = $sendTo;

        if ($withVat) {
            if ($shippingPriceIncluded) {
                $data['value'] = (string)$order->total_paid_tax_incl;
            } else {
                $data['value'] = (string) ($order->total_paid_tax_incl - $order->total_shipping_tax_incl);
            }
        } else {
            if ($shippingPriceIncluded) {
                $data['value'] = (string)$order->total_paid_tax_excl;
            } else {
                $data['value'] = (string) ($order->total_paid_tax_excl - $order->total_shipping_tax_excl);
            }
        }

        return $data;
    }

    public static function getOrderProductsForAds($products, $langId, $withVat, $googleAdsBusinessType = null) : array
    {
        $productData = [];

        foreach ($products as $product) {
            $category = new Category((int)$product['id_category_default'], (int)$langId);
            $manufacturer = new Manufacturer($product['id_manufacturer'], (int)$langId);
            $productVariant = ProductHelper::getProductAttributeName($product['product_attribute_id'], $langId);

            if ($product['product_attribute_id'] && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                $idProduct = $product['product_id'] . '-' . $product['product_attribute_id'];
            } else {
                $idProduct = $product['product_id'];
            }

            $product_item = [
                "id" => $idProduct,
                "name" => $product['product_name'],
                //                "list_name" => "",
                "brand" => $manufacturer->name,
                "category" => $category->name,
                "variant" => $productVariant,
                //                "list_position" => "",
                "quantity" => $product['product_quantity'],
            ];

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = (string)$product['unit_price_tax_incl'];
            } else {
                $product_item['price'] = (string)$product['unit_price_tax_excl'];
            }

            if ($googleAdsBusinessType) {
                $product_item['google_business_vertical'] = $googleAdsBusinessType;
            }

            $productData[] = $product_item;
        }

        return $productData;
    }
}
