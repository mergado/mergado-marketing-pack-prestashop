<?php

namespace Mergado\includes\services\Google\GoogleUniversalAnalytics;

use CategoryCore;
use ConfigurationCore;
use CurrencyCore;
use ManufacturerCore;
use Mergado;
use Mergado\includes\services\Google\GoogleAds\GoogleAdsService;
use Mergado\includes\services\Google\Gtag\GtagIntegrationHelper;
use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\HelperClass;

class GoogleUniversalAnalyticsServiceIntegration
{
    /**
     * @var GoogleUniversalAnalyticsService
     */
    private $googleUniversalAnalyticsService;
    /**
     * @var false|string|null
     */
    private $sendTo;

    use SingletonTrait;

    protected function __construct()
    {
        $this->googleUniversalAnalyticsService = GoogleUniversalAnalyticsService::getInstance();
        $this->sendTo = $this->googleUniversalAnalyticsService->getCode();
    }

    /*******************************************************************************************************************
     * GTAG JS
     ******************************************************************************************************************/

    /**
     * @param $orderId
     * @param $order
     * @param $products
     * @param $langId
     * @param $shopId
     * @return false|string
     */

    public function getPurchaseData($orderId, $order, $products, $langId, $shopId)
    {
        $googleAdsService = GoogleAdsService::getInstance();

        $data = [];

        $currency = new CurrencyCore($order->id_currency);
        $withVat = $this->googleUniversalAnalyticsService->getConversionVatIncluded();

        $data['transaction_id'] = "$orderId";
        $data['affiliation'] = ConfigurationCore::get('PS_SHOP_NAME');
        $data['currency'] = $currency->iso_code;
        $data['tax'] = (string) ($order->total_paid_tax_incl - $order->total_paid_tax_excl);
        $data['shipping'] = (string) $order->total_shipping_tax_excl;

        if ($withVat) {
            $data['value'] = (string) $order->total_paid_tax_incl;
        } else {
            $data['value'] = (string) $order->total_paid_tax_excl;
        }

        $productData = [];

        foreach ($products as $product) {

            $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
            $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
            $productVariant = HelperClass::getProductAttributeName($product['product_attribute_id'], $langId);

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
                "google_business_vertical" => $googleAdsService->getRemarketingTypeForTemplate()
            ];

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = (string) $product['unit_price_tax_incl'];
            } else {
                $product_item['price'] = (string) $product['unit_price_tax_excl'];
            }

            $productData[] = $product_item;
        }

        $data['items'] = $productData;

        return json_encode($data, JSON_NUMERIC_CHECK);
    }

    public function userClickedProduct($context, $path)
    {
        if(isset($_SERVER["HTTP_REFERER"])) {
            if($_SERVER["HTTP_REFERER"]) {
                global $smarty;

                if(_PS_VERSION_ < Mergado::PS_V_17) {
                    $shopUrl = $smarty->tpl_vars['base_dir']->value;
                } else {
                    $shopUrl = $smarty->tpl_vars['urls']->value['shop_domain_url'];
                }

                if(strpos($_SERVER["HTTP_REFERER"], $shopUrl) !== false) {
                    if($this->googleUniversalAnalyticsService->isActiveEnhancedEcommerce()) {
                        $context->controller->addJS($path . GtagIntegrationHelper::TEMPLATES_PATH . 'productClick.js');
                    }
                }
            }
        }
    }
}
