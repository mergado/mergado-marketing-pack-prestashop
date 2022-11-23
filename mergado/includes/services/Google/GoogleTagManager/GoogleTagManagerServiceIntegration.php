<?php

namespace Mergado\includes\services\Google\GoogleTagManager;

use CategoryCore;
use ConfigurationCore;
use CurrencyCore;
use ManufacturerCore;
use MediaCore;
use Mergado;
use Mergado\includes\helpers\ControllerHelper;
use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\HelperClass;
use Order;
use Tools;

class GoogleTagManagerServiceIntegration
{
    /**
     * @var GoogleTagManagerService
     */
    private $googleTagManagerService;

    use SingletonTrait;

    protected function __construct()
    {
        $this->googleTagManagerService = GoogleTagManagerService::getInstance();
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
                    if($this->googleTagManagerService->isEnhancedEcommerceActive()) {
                        $context->controller->addJS($path . GoogleTagManagerService::TEMPLATES_PATH . 'productClick.js');
                    }
                }
            }
        }
    }

    public function insertDefaultCode($module, $smarty, $context, $path)
    {
        // Default code
        if ($this->googleTagManagerService->isActive()) {
            $smarty->assign(array(
                'gtm_analytics_id' => $this->googleTagManagerService->getCode(),
            ));
            return $module->display($path, GoogleTagManagerService::TEMPLATES_PATH . 'defaultCode.tpl');
        }

        return '';
    }

    /**
     * TODO: IMPROVE
     */
    public function insertDefaultBodyCode()
    {
        $output = '';

        if($this->googleTagManagerService->isActive()) {
            $code = $this->googleTagManagerService->getCode();
            ob_start();
            ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?= $code ?>"
                              height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
            <?php
            $output = ob_get_contents();
            ob_end_clean();
        }

        return $output;
    }

    /**
     * Insert gtag events
     *
     * @param $context
     * @param $path
     * @return void
     */
    public function insertDefaultHelpers($context, $path)
    {
        // Enhanced ecommerce data
        if ($this->googleTagManagerService->isEnhancedEcommerceActive()) {
            MediaCore::addJsDef(
                [
                    'mergado_GTM_settings' => [
                        'withVat' => $this->googleTagManagerService->getConversionVatIncluded(),
                        'maxViewListItems' => $this->googleTagManagerService->getViewListItemsCount()
                    ]
                ]
            );

            $context->controller->addJS($path . GoogleTagManagerService::HELPERS_PATH . 'helpers.js');
            $context->controller->addJS($path . GoogleTagManagerService::TEMPLATES_PATH . 'gtm.js');
        }
    }

    public function orderConfirmation($module, $smarty, $context, $path)
    {
        if (ControllerHelper::isOrderConfirmation() && $this->googleTagManagerService->isEcommerceActive()) {
            $orderId = Tools::getValue('id_order');
            $order = new Order($orderId);
            $currency = new CurrencyCore($order->id_currency);

            $smarty->assign(array(
                'gtm_ecommerce' => $this->googleTagManagerService->isEcommerceActive(),
                'gtm_ecommerceEnhanced' => $this->googleTagManagerService->isEnhancedEcommerceActive(),
                'gtm_purchase_data' => $this->getPurchaseData($orderId, $order, (int)$context->language->id),
                'gtm_transaction_data' => $this->getTransactionData($orderId, $order, (int)$context->language->id),
                'gtm_currencyCode' => $currency->iso_code,
            ));

            return $module->display($path, GoogleTagManagerService::TEMPLATES_PATH . 'orderConfirmation.tpl');
        }

        return '';
    }



    /**
     * HELPING FUNCTIONS
     */

    /**
     * @param $orderId
     * @param $order
     * @param $langId
     * @return false|string
     */
    public function getPurchaseData($orderId, $order, $langId)
    {
        $data = [];
        $products = $order->getProducts();

        $withVat = $this->googleTagManagerService->getConversionVatIncluded();

        $data['actionField']['id'] = "$orderId";
        $data['actionField']['affiliation'] = ConfigurationCore::get('PS_SHOP_NAME');
        $data['actionField']['revenue'] = (string) $order->total_paid;
        $data['actionField']['tax'] = (string) ($order->total_paid_tax_incl - $order->total_paid_tax_excl);
        $data['actionField']['shipping'] = (string) $order->total_shipping_tax_excl;
        $data['actionField']['coupon'] = '';

        $cartRules = [];
        foreach($order->getCartRules() as $item) {
            $cartRules[] = $item['name'];
        }

        if($cartRules !== []) {
            $data['actionField']['coupon'] = join(', ', $cartRules);
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
                "name" => $product['product_name'],
                "id" => $idProduct,
                "brand" => $manufacturer->name,
                "category" => $category->name,
                "variant" => $productVariant,
                "quantity" => (int) $product['product_quantity'],
            ];

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = (string) $product['unit_price_tax_incl'];
            } else {
                $product_item['price'] = (string) $product['unit_price_tax_excl'];
            }

            $productData[] = $product_item;
        }

        $data['products'] = $productData;

        return json_encode($data, JSON_NUMERIC_CHECK);
    }

    /**
     * @param $orderId
     * @param $order
     * @param $langId
     * @return false|string
     */
    public function getTransactionData($orderId, $order, $langId)
    {
        $data = [];
        $products = $order->getProducts();
        $withVat = $this->googleTagManagerService->getConversionVatIncluded();

        $data['transactionId'] = "$orderId";
        $data['transactionAffiliation'] = ConfigurationCore::get('PS_SHOP_NAME');
        $data['transactionTotal'] = (string) $order->total_paid;
        $data['transactionTax'] = number_format((float) $order->total_paid_tax_incl - $order->total_paid_tax_excl, 2);
        $data['transactionShipping'] = (string) $order->total_shipping_tax_excl;

        $productData = [];

        foreach ($products as $product) {
            $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);

            if ($product['product_attribute_id'] && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                $idProduct = $product['product_id'] . '-' . $product['product_attribute_id'];
            } else {
                $idProduct = $product['product_id'];
            }

            $product_item = [
                "name" => $product['product_name'],
                "sku" => (string) $idProduct,
                "category" => $category->name,
                "quantity" => (int) $product['product_quantity'],
            ];

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = number_format((float) $product['unit_price_tax_incl'], 2);
            } else {
                $product_item['price'] = number_format((float) $product['unit_price_tax_excl'], 2);
            }

            $productData[] = $product_item;
        }

        $data['transactionProducts'] = $productData;

        return json_encode($data, JSON_NUMERIC_CHECK);
    }
}
