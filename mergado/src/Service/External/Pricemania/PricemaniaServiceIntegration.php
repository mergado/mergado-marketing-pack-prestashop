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


namespace Mergado\Service\External\Pricemania;

use Exception;
use Mergado\Service\AbstractBaseService;
use Throwable;
use Cart;
use Language;

class PricemaniaServiceIntegration extends AbstractBaseService
{
    /**
     * @var PricemaniaService
     */
    private $pricemaniaService;

    protected function __construct()
    {
        $this->pricemaniaService = PricemaniaService::getInstance();

        parent::__construct();
    }

    public function send($order, string $langIso): bool
    {
        try {
            $active = $this->pricemaniaService->isActive();


            if ($active) {
                try {
                    return $this->submitRequest($order, $langIso);
                } catch (Throwable $e) {
                    $this->logger->error('Submit of Pricemania failed', ['exception' => $e]);
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return false;
    }

    /**
     * @throws Exception
     */
    protected function submitRequest($order, $langIso): bool
    {
        $shopId = $this->pricemaniaService->getShopId();
        $langId = Language::getIdByIso($langIso);
        $cartId = $order['cart']->id;

        $pm = new PricemaniaObject($shopId);
        $cart = new Cart($cartId, $langId);

        $products = $cart->getProducts();

        foreach ($products as $product) {
            $exactName = $product['name'];

            if (array_key_exists('attributes_small', $product) && $product['attributes_small'] != '') {
                $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                $exactName .= ': ' . implode(' ', $tmpName);
            }


            $pm->addProduct($exactName);
        }

        $pm->setOrder([
            'email' => $order['customer']->email,
            'orderId' => $order['order']->id
        ]);

        $pm->send();

        return true;
    }
}
