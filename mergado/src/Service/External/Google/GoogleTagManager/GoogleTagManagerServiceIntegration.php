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


namespace Mergado\Service\External\Google\GoogleTagManager;

use Category;
use Configuration;
use Currency;
use Manufacturer;
use Media;
use Mergado;
use Mergado\Helper\ControllerHelper;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\Data\CustomerDataService;
use Mergado\Utility\SmartyTemplateLoader;
use Order;
use Throwable;
use Tools;

class GoogleTagManagerServiceIntegration extends AbstractBaseService
{
    /**
     * @var GoogleTagManagerService
     */
    private $googleTagManagerService;

    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    /**
     * @var CustomerDataService
     */
    private $customerDataService;

    public const TEMPLATES_PATH = 'views/templates/services/GoogleTagManager/';
    public const JS_PATH = 'views/js/services/GoogleTagManager/';

    protected function __construct()
    {
        $this->googleTagManagerService = GoogleTagManagerService::getInstance();
        $this->controllerHelper = ControllerHelper::getInstance();
        $this->customerDataService = CustomerDataService::getInstance();

        parent::__construct();
    }

    public function userClickedProduct($context, $path): void
    {
        try {
            if (!$this->googleTagManagerService->isEnhancedEcommerceActive()) {
                return;
            }

            if (isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"]) {
                global $smarty;

                if (PrestashopVersionHelper::is16AndLower()) {
                    $shopUrl = $smarty->tpl_vars['base_dir']->value;
                } else {
                    $shopUrl = $smarty->tpl_vars['urls']->value['shop_domain_url'];
                }

                if (strpos($_SERVER["HTTP_REFERER"], $shopUrl) !== false) {
                    $context->controller->addJS($path . self::JS_PATH . 'productClick.js');
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function insertDefaultCode($module, $smarty): string
    {
        try {
            // Default code
            if (!$this->googleTagManagerService->isActive()) {
                return '';
            }

            $customerData = $this->getCustomerData(Tools::getValue('id_order'));

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'defaultCode.tpl',
                $smarty,
                [
                    'gtm_analytics_id' => $this->googleTagManagerService->getCode(),
                    'gtm_enhanced_conversions_active' => $this->googleTagManagerService->isEnhancedEcommerceActive(),
                    'gtm_set_customer_data_for_gtag_services' => $this->googleTagManagerService->isSendCustomerDataActive(),
                    'user_data_gtag' => count($customerData['gtag']) > 0 ? json_encode($customerData['gtag']) : false,
                    'user_data_gtm' => count($customerData['gtm']) > 0 ? json_encode($customerData['gtm']) : false,
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function insertDefaultBodyCode(): string
    {
        try {
            if (!$this->googleTagManagerService->isActive()) {
                return '';
            }

            $code = $this->googleTagManagerService->getCode();

            ob_start();
            ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript>
                <iframe src="//www.googletagmanager.com/ns.html?id=<?= $code ?>"
                        height="0" width="0" style="display:none;visibility:hidden"></iframe>
            </noscript>
            <!-- End Google Tag Manager (noscript) -->
            <?php
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function insertDefaultHelpers($context, $path): void
    {
        try {
            // Enhanced ecommerce data
            if (!$this->googleTagManagerService->isEnhancedEcommerceActive()) {
                return;
            }

            Media::addJsDef(
                [
                    'mergado_GTM_settings' => [
                        'withVat' => $this->googleTagManagerService->getConversionVatIncluded(),
                        'maxViewListItems' => $this->googleTagManagerService->getViewListItemsCount()
                    ]
                ]
            );

            $context->controller->addJS($path . self::JS_PATH . 'helpers.js');
            $context->controller->addJS($path . self::JS_PATH . 'gtm.js');
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function orderConfirmation($module, $smarty, $context): string
    {
        try {
            if (!$this->googleTagManagerService->isEcommerceActive()) {
                return '';
            }

            if (!$this->controllerHelper->isOrderConfirmation()) {
                return '';
            }

            $orderId = Tools::getValue('id_order');
            $order = new Order($orderId);
            $currency = new Currency($order->id_currency);

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'orderConfirmation.tpl',
                $smarty,
                [
                    'gtm_ecommerce' => $this->googleTagManagerService->isEcommerceActive(),
                    'gtm_ecommerceEnhanced' => $this->googleTagManagerService->isEnhancedEcommerceActive(),
                    'gtm_purchase_data' => $this->getPurchaseData($orderId, $order, (int)$context->language->id),
                    'gtm_transaction_data' => $this->getTransactionData($orderId, $order, (int)$context->language->id),
                    'gtm_currencyCode' => $currency->iso_code
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    /**
     * HELPER FUNCTIONS
     */

    /**
     * @return false|string
     */
    private function getPurchaseData($orderId, $order, $langId)
    {
        $data = [];
        $products = $order->getProducts();

        $withVat = $this->googleTagManagerService->getConversionVatIncluded();

        $data['actionField']['id'] = "$orderId";
        $data['actionField']['affiliation'] = Configuration::get('PS_SHOP_NAME');
        $data['actionField']['revenue'] = (string)$order->total_paid;
        $data['actionField']['tax'] = (string)($order->total_paid_tax_incl - $order->total_paid_tax_excl);
        $data['actionField']['shipping'] = (string)$order->total_shipping_tax_excl;
        $data['actionField']['coupon'] = '';

        $cartRules = [];
        foreach ($order->getCartRules() as $item) {
            $cartRules[] = $item['name'];
        }

        if ($cartRules !== []) {
            $data['actionField']['coupon'] = join(', ', $cartRules);
        }

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
                "name" => $product['product_name'],
                "id" => $idProduct,
                "brand" => $manufacturer->name,
                "category" => $category->name,
                "variant" => $productVariant,
                "quantity" => (int)$product['product_quantity'],
            ];

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = (string)$product['unit_price_tax_incl'];
            } else {
                $product_item['price'] = (string)$product['unit_price_tax_excl'];
            }

            $productData[] = $product_item;
        }

        $data['products'] = $productData;

        return json_encode($data, JSON_NUMERIC_CHECK);
    }

    /**
     * @return false|string
     */
    private function getTransactionData($orderId, $order, $langId)
    {
        $data = [];
        $products = $order->getProducts();
        $withVat = $this->googleTagManagerService->getConversionVatIncluded();

        $data['transactionId'] = (string)$orderId;
        $data['transactionAffiliation'] = Configuration::get('PS_SHOP_NAME');
        $data['transactionTotal'] = (string)$order->total_paid;
        $data['transactionTax'] = number_format((float)$order->total_paid_tax_incl - $order->total_paid_tax_excl, 2);
        $data['transactionShipping'] = (string)$order->total_shipping_tax_excl;

        $productData = [];

        foreach ($products as $product) {
            $category = new Category((int)$product['id_category_default'], (int)$langId);

            if ($product['product_attribute_id'] && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                $idProduct = $product['product_id'] . '-' . $product['product_attribute_id'];
            } else {
                $idProduct = $product['product_id'];
            }

            $product_item = [
                "name" => $product['product_name'],
                "sku" => (string)$idProduct,
                "category" => $category->name,
                "quantity" => (int)$product['product_quantity'],
            ];

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = number_format((float)$product['unit_price_tax_incl'], 2);
            } else {
                $product_item['price'] = number_format((float)$product['unit_price_tax_excl'], 2);
            }

            $productData[] = $product_item;
        }

        $data['transactionProducts'] = $productData;

        return json_encode($data, JSON_NUMERIC_CHECK);
    }

    private function getCustomerData($orderId = null): array
    {
        if (!$this->googleTagManagerService->isEnhancedEcommerceActive()) {
            return [
                'gtag' => [],
                'gtm' => []
            ];
        }

        if ($this->controllerHelper->isOrderConfirmation()) {
            $customerData = $this->customerDataService->getCustomerInfoOnOrderPage($orderId);
        } else {
            $customerData = $this->customerDataService->getCustomerInfo();
        }

        $customerDataGtm = $customerData;

        // Has different key than GTAG
        if (isset($customerDataGtm['phone'])) {
            $customerDataGtm['phone_number'] = $customerDataGtm['phone'];
            unset($customerDataGtm['phone']);
        }

        return ['gtag' => $customerData, 'gtm' => $customerDataGtm];
    }
}
