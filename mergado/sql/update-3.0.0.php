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
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   LICENSE.txt
 */

use Mergado\Tools\XMLClass;

$active_shops = ShopCore::getShops(true);

$shopId = 0;

foreach ($active_shops as $item) {
    $shopId = $item['id_shop'];

    Mergado\Tools\SettingsClass::saveSetting(XMLClass::DEFAULT_ITEMS_STEP['PRODUCT_FEED'], 1500, $shopId);
    Mergado\Tools\SettingsClass::saveSetting(XMLClass::DEFAULT_ITEMS_STEP['CATEGORY_FEED'], 3000, $shopId);
    Mergado\Tools\SettingsClass::saveSetting(XMLClass::DEFAULT_ITEMS_STEP['STOCK_FEED'], 5000, $shopId);
    Mergado\Tools\SettingsClass::saveSetting(XMLClass::DEFAULT_ITEMS_STEP['STATIC_FEED'], 5000, $shopId);
    Mergado\Tools\SettingsClass::saveSetting(XMLClass::DEFAULT_ITEMS_STEP['IMPORT_FEED'], 3000, $shopId);

    if (glob(_PS_MODULE_DIR_ . 'mergado/xml/' . $shopId . '/mergado_feed*.xml')) {
        Mergado\Tools\SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_PRODUCT'], 1, $shopId);
    }

    if (glob(_PS_MODULE_DIR_ . 'mergado/xml/' . $shopId . '/category_mergado_feed*.xml')) {
        Mergado\Tools\SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_CATEGORY'], 1, $shopId);
    }

    if (glob(_PS_MODULE_DIR_ . 'mergado/xml/' . $shopId . '/stock*.xml')) {
        Mergado\Tools\SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_STOCK'], 1, $shopId);
    }

    if (glob(_PS_MODULE_DIR_ . 'mergado/xml/' . $shopId . '/static_feed*.xml')) {
        Mergado\Tools\SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_STATIC'], 1, $shopId);
    }
}