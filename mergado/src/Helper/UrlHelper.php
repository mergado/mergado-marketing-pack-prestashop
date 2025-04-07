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

use Context;
use Mergado;
use Mergado\Traits\SingletonTrait;
use Tools;

class UrlHelper {

    use SingletonTrait;

    /**
     * @var String
     */
    private $defaultPageUrl;

    public function __construct()
    {
        $context = Context::getContext();
        $this->defaultPageUrl = $context->link->getAdminLink('AdminMergado', true);
    }

    public function getPageLink($name): string
    {
        return $this->defaultPageUrl . '&page=' . $name;
    }

    public function getPageLinkWithTab($name, $tab): string
    {
        return $this->defaultPageUrl . '&page=' . $name . '&mmp-tab=' . $tab;
    }

    public static function getShopUrl(): string
    {
        return Tools::getShopDomainSsl(true, true);
    }

    public static function getShopModuleUrl(): string
    {
        return self::getShopUrl() . _MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'];
    }

    public static function getAdminControllerUrl(): string
    {
        $context = Context::getContext();
        return $context->link->getAdminLink('AdminMergado', true);
    }
}
