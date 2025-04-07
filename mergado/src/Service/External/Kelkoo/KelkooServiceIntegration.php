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

namespace Mergado\Service\External\Kelkoo;

use Mergado;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\CookieService;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class KelkooServiceIntegration extends Mergado\Service\AbstractBaseService
{
    protected $cookieService;
    protected $kelkooService;

    public const TEMPLATES_PATH = 'views/templates/services/Kelkoo/';

    protected function __construct()
    {
        $this->cookieService = CookieService::getInstance();
        $this->kelkooService = KelkooService::getInstance();

        parent::__construct();
    }

    public function insertKelkooHeader($module): string
    {
        try {
            if (!$this->kelkooService->isActive()) {
                return '';
            }

            if (!$this->cookieService->advertismentEnabled()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'header.tpl'
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function orderConfirmation($module, $smarty, $orderId, $order, $orderProducts): string
    {
        try {
            if (!$this->kelkooService->isActive()) {
                return '';
            }

            if (!$this->cookieService->advertismentEnabled()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'orderConfirmation.tpl',
                $smarty,
                [
                    'kelkooData' => $this->getOrderData($orderId, $order, $orderProducts),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function getOrderData($orderId, $order, $products): array
    {
        try {
            $productsKelkoo = [];
            $withVat = $this->kelkooService->getConversionVatIncluded();

            // If VAT included or not
            if ($withVat) {
                $orderTotal = (float)$order->total_products_wt;
            } else {
                $orderTotal = (float)$order->total_products;
            }

            //Same for 1.6 and 1.7
            foreach ($products as $product) {
                $productKelkoo = [
                    'productname' => $product['product_name'],
                    'productid' => $product['product_reference'],
                    'quantity' => $product['product_quantity'],
                ];

                // If VAT included or not
                if ($withVat) {
                    $productKelkoo['price'] = $product['unit_price_tax_incl'];
                } else {
                    $productKelkoo['price'] = $product['unit_price_tax_excl'];
                }

                $productsKelkoo[] = $productKelkoo;
            }

            return [
                'IS_PS_17' => PrestashopVersionHelper::is17AndHigher(),
                'productsJson' => json_encode($productsKelkoo),
                'sales' => $orderTotal,
                'orderId' => $orderId,
                'country' => $this->kelkooService->getActiveDomain(),
                'merchantId' => $this->kelkooService->getComId(),
            ];
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return [];
    }
}
