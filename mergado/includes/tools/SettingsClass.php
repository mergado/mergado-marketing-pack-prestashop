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
    //TODO: MAKE CLASSES FOR US AND MOVE US ALL THERE ..

    // Options
    const HEUREKA = [
        'CONVERSIONS_CZ' => 'mergado_heureka_konverze_cz',
        'CONVERSIONS_SK' => 'mergado_heureka_konverze_sk',
        'CONVERSIONS_CODE_CZ' => 'mergado_heureka_konverze_cz_kod',
        'CONVERSIONS_CODE_SK' => 'mergado_heureka_konverze_sk_kod',
        'VERIFIED_CZ' => 'mergado_heureka_overeno_zakazniky_cz',
        'VERIFIED_SK' => 'mergado_heureka_overeno_zakazniky_sk',
        'VERIFIED_CODE_CZ' => 'mergado_heureka_overeno_zakazniky_kod_cz',
        'VERIFIED_CODE_SK' => 'mergado_heureka_overeno_zakazniky_kod_sk',
        'VERIFIED_WITH_ITEMS_CZ' => 'mergado_heureka_overeno_zakazniky_with_items_cz',
        'VERIFIED_WITH_ITEMS_SK' => 'mergado_heureka_overeno_zakazniky_with_items_sk',
        'WIDGET_CZ' => 'mergado_heureka_widget_cz',
        'WIDGET_SK' => 'mergado_heureka_widget_sk',
        'WIDGET_ID_CZ' => 'mergado_heureka_widget_id_cz',
        'WIDGET_ID_SK' => 'mergado_heureka_widget_id_sk',
        'WIDGET_POSITION_CZ' => 'mergado_heureka_widget_position_cz',
        'WIDGET_POSITION_SK' => 'mergado_heureka_widget_position_sk',
        'WIDGET_TOP_MARGIN_CZ' => 'mergado_heureka_widget_top_margin_cz',
        'WIDGET_TOP_MARGIN_SK' => 'mergado_heureka_widget_top_margin_sk',
        'CONVERSION_VAT_INCL_CZ' => 'mergado_heureka_conversion_vat_incl_cz',
        'CONVERSION_VAT_INCL_SK' => 'mergado_heureka_conversion_vat_incl_sk',
    ];

    const PRICEMANIA = [
        'VERIFIED' => 'mergado_pricemania_overeny_obchod',
        'SHOP_ID' => 'mergado_pricemania_shop_id'
    ];

    const GLAMI = [
        'ACTIVE' => 'glami_active', // Activation of module
        'CODE' => 'glami_pixel_code', // Helper for code variables
        'CONVERSION_VAT_INCL' => 'glami_conversion_vat_incl',
        'SELECTION_TOP' => 'glami_top_selection',
        'ACTIVE_TOP' => 'glami_top_active',
        'CODE_TOP' => 'glami_top_code',
    ];

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

    const GLAMI_TOP_LANGUAGES = [
        ['id_option' => 1, 'name' => 'glami.cz', 'type_code' => 'cz'],
        ['id_option' => 2, 'name' => 'glami.de', 'type_code' => 'de'],
        ['id_option' => 3, 'name' => 'glami.fr', 'type_code' => 'fr'],
        ['id_option' => 4, 'name' => 'glami.sk', 'type_code' => 'sk'],
        ['id_option' => 5, 'name' => 'glami.ro', 'type_code' => 'ro'],
        ['id_option' => 6, 'name' => 'glami.hu', 'type_code' => 'hu'],
        ['id_option' => 7, 'name' => 'glami.ru', 'type_code' => 'ru'],
        ['id_option' => 8, 'name' => 'glami.gr', 'type_code' => 'gr'],
        ['id_option' => 9, 'name' => 'glami.com.tr', 'type_code' => 'tr'],
        ['id_option' => 10, 'name' => 'glami.bg', 'type_code' => 'bg'],
        ['id_option' => 11, 'name' => 'glami.hr', 'type_code' => 'hr'],
        ['id_option' => 12, 'name' => 'glami.si', 'type_code' => 'si'],
        ['id_option' => 13, 'name' => 'glami.es', 'type_code' => 'es'],
        ['id_option' => 14, 'name' => 'glami.com.br', 'type_code' => 'br'],
        ['id_option' => 15, 'name' => 'glami.eco', 'type_code' => 'eco'],
    ];

    const FEED = [
        'STATIC' => 'static_feed',
        'CATEGORY' => 'category_feed'
    ];

    // Checkboxes saves 'on' instead of '1' as ON/OFF.. Take care!
    const EXPORT = [
        'BOTH' => 'what_to_export_both',
        'CATALOG' => 'what_to_export_catalog',
        'SEARCH' => 'what_to_export_search',
        'NONE' => 'what_to_export_none',
        'COST' => 'm_export_wholesale_prices',
        'DENIED_PRODUCTS' => 'mmp_export_denied_products',
        'DENIED_PRODUCTS_OTHER' => 'mmp_export_denied_products_other',
    ];

    const IMPORT = [
//        'ENABLED' => 'import_products',
        'COUNT' => 'import_products_count',
        'LAST_UPDATE' => 'mergado_last_prices_import',
        'URL' => 'import_product_prices_url',
    ];

    const NEW_MODULE_VERSION_AVAILABLE = 'mergado_module_version_available';

    // Module options
    const ENABLED = '1';
    const DISABLED = '0';

    // RSS feed
    const RSS_FEED = 'last_rss_feed_download';
    const RSS_FEED_LOCK = 'unfinished_rss_downloads';

    const COOKIE_NEWS = 'mmp-cookie-news';

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
    public static function saveSetting($key, $value, $shopID, $html = false)
    {
        $exists = Db::getInstance()->getRow(
            'SELECT id FROM ' . _DB_PREFIX_ . Mergado::MERGADO['TABLE_NAME'] . ' WHERE `key`="' . pSQL($key) . '" && `id_shop`="' . pSQL($shopID) . '"'
        );

        if ($exists) {
            return Db::getInstance()->update(Mergado::MERGADO['TABLE_NAME'],
                ['value' => pSQL(trim($value), $html)],
                '`key` = "' . pSQL($key) . '" && `id_shop` = "' . pSQL($shopID) . '"');
        } else {
            return Db::getInstance()->insert(Mergado::MERGADO['TABLE_NAME'], [
                'key' => pSQL($key),
                'value' => pSQL(trim($value), $html),
                'id_shop' => pSQL($shopID),
            ]);
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
        return Db::getInstance()->delete(Mergado::MERGADO['TABLE_NAME'], '`key` LIKE "' . pSQL($pattern) . '" AND `id_shop` ="' . pSQL($shopId) . '"');
    }
}
