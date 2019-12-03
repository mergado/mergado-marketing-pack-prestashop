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

namespace Mergado\Tools;

use Db;
use DbQueryCore as DbQuery;
use Language;
use Mergado;

class SettingsClass
{
    // Options
    const HEUREKA = array(
        'CONVERSIONS_CZ' => 'mergado_heureka_konverze_cz',
        'CONVERSIONS_CODE_CZ' => 'mergado_heureka_konverze_cz_kod',
        'CONVERSIONS_SK' => 'mergado_heureka_konverze_sk',
        'CONVERSIONS_CODE_SK' => 'mergado_heureka_konverze_sk_kod',
        'VERIFIED_CZ' => 'mergado_heureka_overeno_zakazniky_cz',
        'VERIFIED_SK' => 'mergado_heureka_overeno_zakazniky_sk',
        'VERIFIED_CODE_CZ' => 'mergado_heureka_overeno_zakazniky_kod_cz',
        'VERIFIED_CODE_SK' => 'mergado_heureka_overeno_zakazniky_kod_sk',
        'WIDGET_CZ' => 'mergado_heureka_widget_cz',
        'WIDGET_SK' => 'mergado_heureka_widget_sk',
    );

    const ZBOZI = array(
        'CONVERSIONS' => 'mergado_zbozi_konverze',
        'CONVERSIONS_ADVANCED' => 'mergado_zbozi_advanced_konverze',
        'SHOP_ID' => 'mergado_zbozi_shop_id',
        'SECRET' => 'mergado_zbozi_secret',
    );

    const NAJNAKUP = array(
        'CONVERSIONS' => 'mergado_najnakup_konverze',
        'SHOP_ID' => 'mergado_najnakup_shop_id'
    );

    const PRICEMANIA = array(
        'VERIFIED' => 'mergado_pricemania_overeny_obchod',
        'SHOP_ID' => '	mergado_pricemania_shop_id'
    );

    const SKLIK = array(
        'CONVERSIONS' => 'mergado_sklik_konverze',
        'CONVERSIONS_CODE' => 'mergado_sklik_konverze_kod',
        'CONVERSIONS_VALUE' => 'mergado_sklik_konverze_hodnota',
        'RETARGETING' => 'seznam_retargeting',
        'RETARGETING_ID' => 'seznam_retargeting_id',
    );

    const GOOGLE_ADS = array(
        'CONVERSIONS' => 'mergado_adwords_conversion',
        'CONVERSIONS_CODE' => 'mergado_adwords_conversion_code',
        'CONVERSIONS_LABEL' => 'mergado_adwords_conversion_label',
        'REMARKETING' => 'adwords_remarketing',
        'REMARKETING_ID' => 'adwords_remarketing_id',
    );

    const FB_PIXEL = array(
        'ACTIVE' => 'fb_pixel',
        'CODE' => 'fb_pixel_code'
    );

    const ETARGET = array(
        'ACTIVE' => 'etarget',
        'ID' => 'etarget_id',
        'HASH' => 'etarget_hash',
    );

    const GLAMI = array(
        'ACTIVE' => 'glami_active', // Activation of module
        'CODE' => 'glami_pixel_code' // Helper for code variables
    );

    const GLAMI_LANGUAGES = [
        'CZ' => 'glami-form-active-lang-CZ',
        'DE' => 'glami-form-active-lang-DE',
        'FR' => 'glami-form-active-lang-FR',
        'SK' => 'glami-form-active-lang-SK',
        'RO' => 'glami-form-active-lang--RO',
        'HU' => 'glami-form-active-lang-HU',
        'RU' => 'glami-form-active-lang-RU',
        'GR' => 'glami-form-active-lang-GR',
        'TR' => 'glami-form-active-lang-TR',
        'BG' => 'glami-form-active-lang-BG',
        'HR' => 'glami-form-active-lang-HR',
        'SI' => 'glami-form-active-lang-SI',
        'ES' => 'glami-form-active-lang-ES',
        'BR' => 'glami-form-active-lang-BR',
        'ECO' => 'glami-form-active-lang-ECO'];

    const FEED = array(
        'STATIC' => 'static_feed',
        'CATEGORY' => 'category_feed',
        'MAX_SIZE' => 'partial_feeds_size',
    );

    const EXPORT = array(
        'BOTH' => 'what_to_export_both',
        'CATALOG' => 'what_to_export_catalog',
        'SEARCH' => 'what_to_export_search'
    );

    const IMPORT = array(
//        'ENABLED' => 'import_products',
        'COUNT' => 'import_products_count',
        'LAST_UPDATE' => 'mergado_last_prices_import',
        'URL' => 'import_product_prices_url',
    );

    const NEW_MODULE_VERSION_AVAILABLE = 'mergado_module_version_available';

    // Module options
    const ENABLED = '1';
    const DISABLED = '0';

    // RSS feed
    const RSS_FEED = 'last_rss_feed_download';

    /*******************************************************************************************************************
     * SET
     *******************************************************************************************************************/

    /**
     * Save data in database for specific shop
     *
     * @param $key
     * @param $value
     * @param $shopID
     */
    public static function saveSetting($key, $value, $shopID)
    {
        $exists = Db::getInstance()->getRow(
            'SELECT id FROM ' . _DB_PREFIX_ . Mergado::MERGADO['TABLE_NAME'] . ' WHERE `key`="' . pSQL($key) . '" && `id_shop`="' . pSQL($shopID) . '"'
        );

        if ($exists) {
            Db::getInstance()->update(Mergado::MERGADO['TABLE_NAME'],
                array('value' => pSQL($value)),
                '`key` = "' . pSQL($key) . '" && `id_shop` = "' . pSQL($shopID) . '"');
        } else {
            Db::getInstance()->insert(Mergado::MERGADO['TABLE_NAME'], array(
                'key' => pSQL($key),
                'value' => pSQL($value),
                'id_shop' => pSQL($shopID),
            ));
        }
    }

    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    /**
     * Return value for specific shop
     *
     * @param $key
     * @param $shopId
     * @return false|string|null
     */
    public static function getSettings($key, $shopId)
    {
        $sql = new DbQuery();
        $sql->select('value');
        $sql->from(Mergado::MERGADO['TABLE_NAME']);
        $sql->where('`key`="' . pSQL($key) . '" AND `id_shop`="' . pSQL($shopId) . '"');

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Return all settings for specific shop
     *
     * @param $shopId
     * @return array|false|\mysqli_result|\PDOStatement|resource|null
     */
    public static function getWholeSettings($shopId)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(Mergado::MERGADO['TABLE_NAME']);
        $sql->where('`id_shop`="' . pSQL($shopId) . '"');

        return Db::getInstance()->executeS($sql);
    }

    /*******************************************************************************************************************
     * DELETE
     *******************************************************************************************************************/

    /**
     * Delete setting for specific shop
     *
     * @param $pattern
     * @param $shopId
     * @return bool
     */
    public static function clearSettings($pattern, $shopId)
    {
        $sql = new DbQuery();
        $sql->from(Mergado::MERGADO['TABLE_NAME']);
        $sql->where('`key` LIKE "' . pSQL($pattern) . '" AND `id_shop` ="' . pSQL($shopId) . '"');

        return Db::getInstance()->executeS($sql);
    }

    public static function getLangIso()
    {
        global $cookie;
        $lang = strtoupper(Language::getIsoById( (int)$cookie->id_lang ));

        if($lang == 'CS') {
            $lang = 'CZ';
        }

        return $lang;
    }
}
