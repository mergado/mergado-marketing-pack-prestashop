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


namespace Mergado\Helper;

use Mergado\Traits\SingletonTrait;
use Tools;

class ControllerHelper
{
    use SingletonTrait;

    private $controller;

    public function __construct()
    {
        $this->controller = Tools::getValue('controller');
    }

    public function isIndex(): bool
    {
        return $this->controller === 'index';
    }

    public function isCart(): bool
    {
        return $this->controller === 'cart';
    }

    public function isProductDetail(): bool
    {
        return $this->controller === 'product';
    }

    public function isCategory(): bool
    {
        return $this->controller === 'category';
    }

    public function isSearch(): bool
    {
        return $this->controller === 'search';
    }

    /**
     * Any checkout step
     */
    public function isCheckout(): bool
    {
        return $this->controller === 'order';
    }

    public function isOnePageCheckout(): bool
    {
        return $this->controller === 'orderopc';
    }

    public function isOrderConfirmation(): bool
    {
        return $this->controller === 'orderconfirmation';
    }
}
