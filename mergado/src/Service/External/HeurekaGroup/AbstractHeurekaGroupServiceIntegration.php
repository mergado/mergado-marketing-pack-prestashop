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


namespace Mergado\Service\External\HeurekaGroup;

use Cart;
use Configuration;
use Currency;
use Mergado\Helper\ControllerHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\CookieService;
use Mergado\Service\External\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\Service\External\ArukeresoFamily\Compari\CompariService;
use Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\Service\External\Heureka\HeurekaCzService;
use Mergado\Service\External\Heureka\HeurekaSkService;
use Mergado\Service\LogService;
use Mergado\Utility\SmartyTemplateLoader;
use PrestaShopDatabaseException;
use PrestaShopException;
use Throwable;
use Tools;

abstract class AbstractHeurekaGroupServiceIntegration
{
    /**
     * @var $service ArukeresoService|PazaruvajService|CompariService|HeurekaCzService|HeurekaSkService
     */
    private $service;

    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    /**
     * @var LogService
     */
    protected $logger;

    /**
     * @var CookieService
     */
    private $cookiesService;

    public const TEMPLATES_PATH = 'views/templates/services/HeurekaGroup/';

    public function __construct($service)
    {
        $this->service = $service;
        $this->logger = LogService::getInstance();
        $this->controllerHelper = ControllerHelper::getInstance();
        $this->cookiesService = CookieService::getInstance();
    }

    public function productDetailView($module, $smarty) : string
    {
        try {
            if (!$this->controllerHelper->isProductDetail() || !$this->cookiesService->advertismentEnabled()) {
                return '';
            }

            if ($this->service && $this->service->isConversionActive()) {
                return SmartyTemplateLoader::render(
                    $module,
                    self::TEMPLATES_PATH . 'productDetailView.tpl',
                    $smarty,
                    [
                        'sdkUrl' => $this->service::CONVERSION_SDK_URL,
                        'variableName' => $this->service::CONVERSION_VARIABLE_NAME,
                        'serviceLang' => $this->service::CONVERSION_SERVICE_LANG,
                    ]
                );
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function conversion($orderCartId, $module, $smarty, $order) : string
    {
        try {
            if (!$this->cookiesService->advertismentEnabled()) {
                return '';
            }

            if (!$this->service->isConversionActive()) {
                return '';
            }

            $currency = new Currency($order->id_currency);

            $apiKey = $this->service->getConversionApiKey();

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'conversion.tpl',
                $smarty,
                [
                    'sdkUrl' => $this->service::CONVERSION_SDK_URL,
                    'variableName' => $this->service::CONVERSION_VARIABLE_NAME,
                    'serviceLang' => $this->service::CONVERSION_SERVICE_LANG,
                    'apiKey' => $apiKey,
                    'orderId' => $order->id,
                    'products' => $this->getTotalPriceAndProductsForConversion($orderCartId),
                    'totalPriceWithVat' => (float)$order->total_products_wt,
                    'currency' => $currency->iso_code
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    protected function getTotalPriceAndProductsForConversion($orderCartId): array
    {
        $cart = new Cart($orderCartId);
        $cartProducts = $cart->getProducts();

        $products = [];

        foreach ($cartProducts as $product) {
            $products[] = [
                'id' => ProductHelper::getProductId($product),
                'name' => $product['name'],
                'quantity' => $product['quantity'],
                'unitPriceWithVat' => Tools::ps_round($product['price_wt'], Configuration::get('PS_PRICE_DISPLAY_PRECISION')),
            ];
        }

        return $products;
    }
}
