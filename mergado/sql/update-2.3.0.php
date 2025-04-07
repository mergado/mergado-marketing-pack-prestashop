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
 *  @license   license.txt
 */

use Mergado\Manager\DatabaseManager;

$active_shops = Shop::getShops(true);

$shopId = 0;
$cz_active = DatabaseManager::getSettingsFromCache('top_glami-form-active-lang-CZ', false, $shopId);

if($cz_active == 1) {
    $cz_code = DatabaseManager::getSettingsFromCache('glami_top_code-CZ', '',$shopId);

    //Add activated CZ
    $sql = "INSERT INTO `" . _DB_PREFIX_ . "mergado` (`id_shop`, `key`, `value`) VALUES ('" . $shopId . "', 'glami_top_selection', '1')";
    DB::getInstance()->execute($sql);

    //Add code
    $sql = "INSERT INTO `" . _DB_PREFIX_ . "mergado` (`id_shop`, `key`, `value`) VALUES ('" . $shopId . "', 'glami_top_code', '" . $cz_code . "')";
    DB::getInstance()->execute($sql);
}

foreach ($active_shops as $item) {
    $shopId = $item['id_shop'];

    $cz_active = DatabaseManager::getSettingsFromCache('top_glami-form-active-lang-CZ', false, $shopId);

    if($cz_active == 1) {
        $cz_code = DatabaseManager::getSettingsFromCache('glami_top_code-CZ', '', $shopId);

        //Add activated CZ
        $sql = "INSERT INTO `" . _DB_PREFIX_ . "mergado` (`id_shop`, `key`, `value`) VALUES ('" . $shopId . "', 'glami_top_selection', '1')";
        DB::getInstance()->execute($sql);

        //Add code
        $sql = "INSERT INTO `" . _DB_PREFIX_ . "mergado` (`id_shop`, `key`, `value`) VALUES ('" . $shopId . "', 'glami_top_code', '" . $cz_code . "')";
        DB::getInstance()->execute($sql);
    }
}

//Remove all old items
$sql = "DELETE FROM `" . _DB_PREFIX_ . "mergado` WHERE `key` = 'glami_top_code-CZ' OR `key` = 'top_glami-form-active-lang-CZ'";
DB::getInstance()->execute($sql);
