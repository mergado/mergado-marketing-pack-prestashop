<?php

namespace Mergado\includes\helpers;

use Mergado;

class RefererHelper
{
    public static function pageHasBeenRefreshed(): bool
    {
        return (isset($_SERVER['HTTP_CACHE_CONTROL']) && ($_SERVER['HTTP_CACHE_CONTROL'] == 'max-age=0'));
    }

    public static function userNotCameFromOutside(): bool
    {
        global $smarty;

        if(_PS_VERSION_ < Mergado::PS_V_17) {
            $shopUrl = $smarty->tpl_vars['base_dir']->value;
        } else {
            $shopUrl = $smarty->tpl_vars['urls']->value['shop_domain_url'];
        }

        return isset($_SERVER["HTTP_REFERER"]) && (strpos($_SERVER["HTTP_REFERER"], $shopUrl) !== false);
    }

    public static function userCameFromCartPage($context)
    {
        return isset($_SERVER["HTTP_REFERER"]) && (strpos($_SERVER['HTTP_REFERER'], $context->link->getPageLink('cart')) !== false);
    }
}
