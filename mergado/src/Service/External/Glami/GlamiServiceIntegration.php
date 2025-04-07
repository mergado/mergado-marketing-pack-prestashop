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


namespace Mergado\Service\External\Glami;

use Category;
use Context;
use Currency;
use Mergado;
use Mergado\Helper\ControllerHelper;
use Mergado\Helper\LanguageHelper;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\CookieService;
use Mergado\Utility\SmartyTemplateLoader;
use Product;
use Throwable;

class GlamiServiceIntegration extends AbstractBaseService
{
    /**
     * @var GlamiService
     */
    private $glamiService;

    /**
     * @var CookieService
     */
    private $cookieService;
    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    public const TEMPLATES_PATH_PIXEL = 'views/templates/services/Glami/pixel/';
    public const TEMPLATES_PATH_TOP = 'views/templates/services/Glami/top/';
    public const JS_PATH = 'views/js/services/Glami/';

    protected function __construct()
    {
        $this->glamiService = GlamiService::getInstance();
        $this->cookieService = CookieService::getInstance();
        $this->controllerHelper = ControllerHelper::getInstance();

        parent::__construct();
    }

    public function init($module, $smarty, $context, $path, $lang): string
    {
        try {
            if(!$this->glamiService->isPixelActive($lang)) {
                return '';
            }

            $pixelCode = $this->glamiService->getPixelLangCode($lang);

            $context->controller->addJS($path . self::JS_PATH . 'glami.js');

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH_PIXEL . 'init.tpl',
                $smarty,
                [
                    'glami_pixel_code' => $pixelCode,
                    'glami_lang' => strtolower($lang)
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function viewContent($module, $smarty, $lang, $categoryId = null, $productId = null): string
    {
        try {
            if (!$this->glamiService->isPixelActive($lang)) {
                return '';
            }

            if ($this->controllerHelper->isCategory()) {
                $category = new Category($categoryId, (int)Context::getContext()->language->id);
                $nb = 10;
                $products_tmp = $category->getProducts((int)Context::getContext()->language->id, 1, ($nb ?: 10));
                $products = array();

                foreach ($products_tmp as $product) {
                    if (isset($product['id_product_attribute']) && $product['id_product_attribute'] !== '' && $product['id_product_attribute'] != 0) {
                        $products['ids'][] = $product['id_product'] . '-' . $product['id_product_attribute'];
                    } else {
                        $products['ids'][] = $product['id_product'];
                    }

                    $products['name'][] = $product['name'];
                }

                return SmartyTemplateLoader::render(
                    $module,
                    self::TEMPLATES_PATH_PIXEL . 'viewContentCategory.tpl',
                    $smarty,
                    [
                        'glami_pixel_category' => $category,
                        'glami_pixel_productIds' => isset($products['ids']) ? json_encode($products['ids']) : json_encode($products),
                        'glami_pixel_productNames' => isset($products['name']) ? json_encode($products['name']) : json_encode($products)
                    ]
                );
            }

            if ($this->controllerHelper->isProductDetail()) {
                $product = new Product($productId, false, (int)Context::getContext()->language->id);

                return SmartyTemplateLoader::render(
                    $module,
                    self::TEMPLATES_PATH_PIXEL . 'viewContentProduct.tpl',
                    $smarty,
                    [
                        'glami_pixel_product' => $product
                    ]
                );
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function purchase($module, $smarty, $orderProducts, $orderId, $params, $lang): string
    {
        try {
            if (!$this->glamiService->isPixelActive($lang) || !$this->cookieService->advertismentEnabled()) {
                return '';
            }

            $products = $this->prepareProductData($orderProducts);

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH_PIXEL . 'purchase.tpl',
                $smarty,
                [
                    'glamiData' => $this->getGlamiOrderData($orderId, $params, $products),
                    'psVersionLowerThan17' => PrestashopVersionHelper::is16AndLower()
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function topPurchase($module, $smarty, $customerEmail, $orderProducts, $orderId): string
    {
        try {
            if (!$this->glamiService->isTopActive() || !$this->cookieService->advertismentEnabled()) {
                return '';
            }

            $products = $this->prepareProductData($orderProducts);

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH_TOP . 'purchase.tpl',
                $smarty,
                [
                    'glamiTopData' => $this->getGlamiTOPOrderData($orderId, $products, $customerEmail)
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function addToCart($module): string
    {
        try {
            $lang = LanguageHelper::getLang();

            if (!$this->glamiService->isPixelActive($lang) || $this->glamiService->isLanguageActive($lang)) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH_PIXEL . 'addToCart.tpl'
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    protected function getGlamiOrderData($orderId, $params, $glamiProducts): array
    {
        $withVat = $this->glamiService->getPixelConversionVatIncluded();
        $productIds = json_encode($glamiProducts['ids']);
        $productNames = json_encode($glamiProducts['names']);

        if (PrestashopVersionHelper::is16AndLower()) {
            if ($withVat) {
                $value = $params['objOrder']->total_products_wt;
            } else {
                $value = $params['objOrder']->total_products;
            }
            $currency = $params['currencyObj']->iso_code;
        } else {
            if ($withVat) {
                $value = $params['order']->total_products_wt;
            } else {
                $value = $params['order']->total_products;
            }
            $currency = Currency::getCurrency($params['order']->id_currency);
        }

        return array(
            'orderId' => $orderId,
            'productIds' => $productIds,
            'productNames' => $productNames,
            'value' => $value,
            'currency' => $currency,
        );
    }

    protected function getGlamiTOPOrderData($orderId, $glamiProducts, $customerEmail): array
    {
        $glamiTOPLanguageValues = $this->getGlamiTOPActiveDomain();

        return [
            'lang_iso' => LanguageHelper::getLangIso(),
            'lang_active' => $glamiTOPLanguageValues['type_code'],
            'url_active' => $glamiTOPLanguageValues['name'],
            'code' => $this->glamiService->getTopCode(),
            'orderId' => $orderId,
            'products' => json_encode($glamiProducts['full']),
            'email' => $customerEmail,
        ];
    }

    protected function prepareProductData($products): array
    {
        $glamiProducts = [];

        foreach ($products as $product) {
            $glamiProducts['full'] = ['id' => $product['product_id'] . '-' . $product['product_attribute_id'], 'name' => $product['product_name']];
            $glamiProducts['ids'][] = $product['product_id'] . '-' . $product['product_attribute_id'];
            $glamiProducts['names'][] = $product['product_name'];
        }

        return $glamiProducts;
    }

    /**
     * Return active language options for Glami TOP
     */
    protected function getGlamiTOPActiveDomain()
    {
        $activeLangId = $this->glamiService->getTopSelection();

        foreach (Mergado\Service\External\Glami\GlamiService::PIXEL_TOP_LANGUAGES as $item) {
            if ($item['id_option'] === (int)$activeLangId) {
                return $item;
            }
        }

        return false;
    }
}
