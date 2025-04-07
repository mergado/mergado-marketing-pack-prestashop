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

class NavigationAccessHelper
{
    use SingletonTrait;

    public static function userRefreshedPage(): bool
    {
        return (isset($_SERVER['HTTP_CACHE_CONTROL']) && ($_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0'));
    }

    public static function isInternalRedirect(): bool
    {
        global $smarty;

        if(PrestashopVersionHelper::is16AndLower()) {
            $shopUrl = $smarty->tpl_vars['base_dir']->value;
        } else {
            $shopUrl = $smarty->tpl_vars['urls']->value['shop_domain_url'];
        }

        return isset($_SERVER["HTTP_REFERER"]) && (strpos($_SERVER["HTTP_REFERER"], $shopUrl) !== false);
    }

    public static function isRedirectFromCartPage($context): bool
    {
        return isset($_SERVER["HTTP_REFERER"]) && (strpos($_SERVER['HTTP_REFERER'], $context->link->getPageLink('cart')) !== false);
    }
}
