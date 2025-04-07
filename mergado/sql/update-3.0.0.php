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
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Service\ProductPriceImportService;

$active_shops = Shop::getShops(true);

$shopId = 0;

foreach ($active_shops as $item) {
    $shopId = $item['id_shop'];

    DatabaseManager::saveSetting(ProductFeed::DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME, 1500, $shopId);
    DatabaseManager::saveSetting(CategoryFeed::DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME, 3000, $shopId);
    DatabaseManager::saveSetting(StockFeed::DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME, 5000, $shopId);
    DatabaseManager::saveSetting(StaticFeed::DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME, 5000, $shopId);
    DatabaseManager::saveSetting(ProductPriceImportService::DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME, 3000, $shopId);

    if (glob(_PS_MODULE_DIR_ . 'mergado/xml/' . $shopId . '/mergado_feed*.xml')) {
        DatabaseManager::saveSetting(ProductFeed::WIZARD_FINISHED_DB_NAME, 1, $shopId);
    }

    if (glob(_PS_MODULE_DIR_ . 'mergado/xml/' . $shopId . '/category_mergado_feed*.xml')) {
        DatabaseManager::saveSetting(CategoryFeed::WIZARD_FINISHED_DB_NAME, 1, $shopId);
    }

    if (glob(_PS_MODULE_DIR_ . 'mergado/xml/' . $shopId . '/stock*.xml')) {
        DatabaseManager::saveSetting(StockFeed::WIZARD_FINISHED_DB_NAME, 1, $shopId);
    }

    if (glob(_PS_MODULE_DIR_ . 'mergado/xml/' . $shopId . '/static_feed*.xml')) {
        DatabaseManager::saveSetting(StaticFeed::WIZARD_FINISHED_DB_NAME, 1, $shopId);
    }
}
