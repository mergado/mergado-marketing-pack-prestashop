<?php

namespace Mergado\Tools;

use Context;
use Mergado;
use ShopCore as Shop;
use ToolsCore as Tools;
use ConfigurationCore as Configuration;
use ShopUrlCore;

class UrlManager {
    public static function getAdminControllerUrl()
    {
        $context = Context::getContext();
        return $context->link->getAdminLink('AdminMergado', true);
    }


    /**
     * Returns cron URL
     *
     * @param $key
     * @return string
     */
    public static function getCronUrl($key, $shopId)
    {
        if (Shop::isFeatureActive()) {
            return self::getMultistoreShopUrl($shopId) . 'modules/' . Mergado::MERGADO['MODULE_NAME'] . '/cron.php?feed=' . $key .
                '&token=' . Tools::substr(Tools::encrypt('mergado/cron'), 0, 10);
        } else {
            return self::getModuleUrl() . 'cron.php?feed=' . $key .
                '&token=' . Tools::substr(Tools::encrypt('mergado/cron'), 0, 10);
        }
    }

    public static function getFeedUrl($key, $shopId)
    {
        return self::getModuleUrl() . 'xml/' . $shopId . '/' . $key . '.xml';
    }

    /**
     * Return improt cron URL
     *
     * @param $shopId
     * @return string
     */
    public static function getImportCronUrl($shopId)
    {
        if (Shop::isFeatureActive()) {
            return self::getMultistoreShopUrl($shopId) . 'modules/' . Mergado::MERGADO['MODULE_NAME'] . '/importPrices.php?' .
                '&token=' . Tools::substr(Tools::encrypt('mergado/importPrices'), 0, 10);
        } else {
            return self::getModuleUrl() . 'importPrices.php?' .
                '&token=' . Tools::substr(Tools::encrypt('mergado/importPrices'), 0, 10);
        }
    }

    public static function getModuleUrl()
    {
        return UrlManager::getBaseUrl() . _MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/';
    }

    /**
     * Return Base url of MainShop
     *
     * @return string
     */
    public static function getBaseUrl()
    {
        return 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' . Tools::getShopDomain(false, true);
    }

    /**
     * Return Specific ShopUrl with domains, physical urls and virtual urls
     *
     * @return string
     */
    public static function getMultistoreShopUrl($shopId)
    {
        $shop_urls = [];

        foreach (ShopUrlCore::getShopUrls() as $shopUrl) {
            if ($shopUrl->id_shop == $shopId) {
                $shop_urls['domain'] = $shopUrl->domain;
                $shop_urls['physical_uri'] = $shopUrl->physical_uri;
                $shop_urls['virtual_uri'] = $shopUrl->virtual_uri;
            }
        }

        return 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' . $shop_urls['domain'] . $shop_urls['physical_uri'] . $shop_urls['virtual_uri'];
    }
}