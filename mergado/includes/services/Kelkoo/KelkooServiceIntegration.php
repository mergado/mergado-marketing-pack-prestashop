<?php

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

namespace Mergado\includes\services\Kelkoo;

use Mergado;
use Mergado\includes\tools\CookieService;
use Mergado\includes\traits\SingletonTrait;

class KelkooServiceIntegration
{
    use SingletonTrait;

    protected $cookieService;
    protected $kelkooService;
    protected $shopId;

    protected function __construct()
    {
        $this->cookieService = CookieService::getInstance();
        $this->kelkooService = KelkooService::getInstance();
        $this->shopId = Mergado::getShopId();
    }

    public function insertKelkooHeader($module, $path)
    {
        if ($this->cookieService->advertismentEnabled()) {
            if ($this->kelkooService->isActive()) {
                return $module->display($path, 'includes/services/Kelkoo/templates/header.tpl');
            }
        }

        return '';
    }

    public function orderConfirmation($module, $smarty, $path, $orderId, $order, $orderProducts)
    {
        if ($this->kelkooService->isActive()) {
            $smarty->assign(array(
                'kelkooData' => $this->kelkooService->getOrderData($orderId, $order, $orderProducts),
            ));

           return $module->display($path, 'includes/services/Kelkoo/templates/orderConfirmation.tpl');
        }

        return '';
    }
}
