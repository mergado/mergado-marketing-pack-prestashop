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

use Db;
use Mergado\Manager\DatabaseManager;
use Mergado\Service\SettingsService;
use Shop;

class ShopModuleEnablerHelper
{
    public static function enableInAllShops($moduleId, $force_all = false): bool
    {
        // Retrieve all shops where the module is enabled
        $list = Shop::getShops(true, null, true);

        if (!$moduleId || !is_array($list)) {
            return false;
        }

        $sql = 'SELECT `id_shop` FROM `' . _DB_PREFIX_ . 'module_shop`
                WHERE `id_module` = ' . (int)$moduleId .
            ((!$force_all) ? ' AND `id_shop` IN(' . implode(', ', $list) . ')' : '');

        // Store the results in an array
        $items = array();
        $results = Db::getInstance()->executeS($sql);

        if ($results) {
            foreach ($results as $row) {
                $items[] = (string) $row['id_shop']; // Convert to string for strict comparison
            }
        }

        // Enable module in the shop where it is not enabled yet
        foreach ($list as $id) {
            if (!in_array((string) $id, $items, true)) {
                Db::getInstance()->execute(
                    'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'module_shop` (`id_module`, `id_shop`)
                VALUES (' . (int)$moduleId . ', ' . (int)$id . ')'
                );
            }

            // Set export to both enabled for all shops
            DatabaseManager::saveSetting(SettingsService::EXPORT['BOTH'], 'on', $id);
        }

        return true;
    }
}
