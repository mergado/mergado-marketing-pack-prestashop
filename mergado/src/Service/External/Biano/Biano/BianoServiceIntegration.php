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
 * @author    www.mergado.cz
 * @copyright 2016 Mergado technologies, s. r. o.
 * @license   LICENSE.txt
 */

namespace Mergado\Service\External\Biano\Biano;

use Context;
use Currency;
use Link;
use Mergado;
use Mergado\Helper\LanguageHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\Biano\BianoStar\BianoStarService;
use Mergado\Service\External\Biano\BianoStar\BianoStarServiceIntegration;
use Mergado\Utility\SmartyTemplateLoader;
use Product;
use Throwable;

class BianoServiceIntegration extends AbstractBaseService
{
    /**
     * @var BianoService
     */
    private $bianoService;

    public const TEMPLATES_PATH = 'views/templates/services/Biano/';
    public const JS_PATH = 'views/js/services/Biano/';

    /**
     * @var string
     */
    private $lang;

    protected function __construct()
    {
        $this->bianoService = BianoService::getInstance();

        $this->lang = LanguageHelper::getLang();

        parent::__construct();
    }

    public function viewProductDetail($productId, Mergado $module, $smarty): string
    {
        try {
            if (!$this->bianoService->isActive($this->lang)) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'viewProductDetail.tpl',
                $smarty,
                [
                    'productId' => ProductHelper::getProductId($productId),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function header(Mergado $module, $context, $smarty, $path): string
    {
        try {
            if (!$this->bianoService->isActive($this->lang)) {
                return '';
            }

            $display = '';

            $smarty->assign(array(
                'langCode' => $this->lang,
                'merchantId' => $this->bianoService->getMerchantId($this->lang),
            ));

            if (array_key_exists($this->lang, BianoService::LANG_OPTIONS)) {
                $display .= SmartyTemplateLoader::render(
                    $module,
                    self::TEMPLATES_PATH . 'initDefault.tpl',
                    $smarty,
                    [
                        'domain' => BianoService::LANG_OPTIONS[$this->lang],
                    ]
                );
            } else {
                $display .= $module->display(__MERGADO_DIR__, self::TEMPLATES_PATH . 'initFallback.tpl');
            }

            $display .= SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'headerInit.tpl',
                $smarty,
                [
                    'merchantId' => $this->bianoService->getMerchantId($this->lang),
                ]
            );

            $context->controller->addJS($path . self::JS_PATH . 'biano.js');
            return $display;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function viewPage(Mergado $module, $context, $path): string
    {
        try {
            if (!$this->bianoService->isActive($this->lang)) {
                return '';
            }
            $context->controller->addJS($path . self::JS_PATH . 'biano.js');

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'viewPage.tpl'
            );

        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function purchase($orderId, $order, $orderCustomer, $orderProducts, $context, $smarty, Mergado $module): string
    {
        try {

            if (!$this->bianoService->isActive($this->lang)) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'purchase.tpl',
                $smarty,
                [
                    'bianoPurchaseData' => $this->getPurchaseData($orderId, $order, $orderCustomer->email, $orderProducts, $context->cookie->__get(BianoStarService::CONSENT_NAME)),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    private function getPurchaseData($orderId, $order, $email, $products, $consent)
    {
        $data = [];

        $currency = new Currency($order->id_currency);

        $data['id'] = (string)$orderId;
        $data['currency'] = $currency->iso_code;

        // If user selected with or without VAT
        if ($this->bianoService->isConversionWithVat()) {
            $data['order_price'] = (string)$order->total_products_wt;
        } else {
            $data['order_price'] = (string)$order->total_products;
        }

        $productData = [];

        foreach ($products as $product) {
            $product_item = [
                "id" => $product['product_id'] . '-' . $product['product_attribute_id'],
                "quantity" => (int)$product['product_quantity'],
            ];

            // If user selected with or without VAT
            if ($this->bianoService->isConversionWithVat()) {
                $product_item['unit_price'] = (string)$product['unit_price_tax_incl'];
            } else {
                $product_item['unit_price'] = (string)$product['unit_price_tax_excl'];
            }

            $product_item['name'] = $product['product_name'];

            $productObject = new Product($product['id_product']);

            $link = new Link();
            $product_item['image'] = $link->getImageLink($productObject->link_rewrite[Context::getContext()->language->id], $product['image']->id_image);

            $productData[] = $product_item;
        }

        $data['items'] = $productData;

        // Biano star
        $bianoStarServiceIntegration = BianoStarServiceIntegration::getInstance();
        $shippingDate = 0;

        $bianoStarService = $bianoStarServiceIntegration->getService();

        if ($consent !== '1' && $bianoStarService->isActive($this->lang)) {
            foreach ($products as $product) {
                if ($product['product_attribute_id'] && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                    $productStatus = ProductHelper::getProductStockStatus($product['product_id'], $product['product_attribute_id']);
                } else {
                    $productStatus = ProductHelper::getProductStockStatus($product['product_id']);
                }

                if ($productStatus === 'in stock') {
                    if ($shippingDate < $bianoStarService->getShipmentInStock()) {
                        $shippingDate = $bianoStarService->getShipmentInStock();
                    }
                } else if ($productStatus === 'out of stock') {
                    if ($shippingDate < $bianoStarService->getShipmentOutOfStock()) {
                        $shippingDate = $bianoStarService->getShipmentOutOfStock();
                    }
                } else if ($productStatus === 'preorder') {
                    if ($shippingDate < $bianoStarService->getShipmentBackorder()) {
                        $shippingDate = $bianoStarService->getShipmentBackorder();
                    }
                }

            }

            $data['customer_email'] = $email;
            $data['shipping_date'] = Date('Y-m-d', strtotime('+' . $shippingDate . ' days'));
        }

        // Encode strings to numbers
        $data = json_decode(json_encode($data, JSON_NUMERIC_CHECK), true);

        // Change id to String because Biano needs that
        $data['id'] = (string)$data['id'];

        return json_encode($data);
    }
}

