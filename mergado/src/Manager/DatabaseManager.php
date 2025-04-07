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


namespace Mergado\Manager;

use Db;
use DbQuery;
use Mergado;
use Mergado\Helper\ShopHelper;
use Mergado\Service\CacheService;

class DatabaseManager
{
    /*******************************************************************************************************************
     * SET
     *******************************************************************************************************************/

    public static function saveSetting($key, $value, $shopID, $html = false): bool
    {
        $exists = Db::getInstance()->getRow(
            'SELECT id FROM ' . _DB_PREFIX_ . Mergado::MERGADO['TABLE_NAME'] . ' WHERE `key`="' . pSQL($key) . '" && `id_shop`="' . pSQL($shopID) . '"'
        );

        if (is_string($value)) {
            $finalValue = trim($value);
        } else {
            $finalValue = $value;
        }

        if ($exists) {
            return Db::getInstance()->update(Mergado::MERGADO['TABLE_NAME'],
                ['value' => pSQL($finalValue, $html)],
                '`key` = "' . pSQL($key) . '" && `id_shop` = "' . pSQL($shopID) . '"');
        }

        return Db::getInstance()->insert(Mergado::MERGADO['TABLE_NAME'], [
            'key' => pSQL($key),
            'value' => pSQL($finalValue, $html),
            'id_shop' => pSQL($shopID),
        ]);
    }

    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    /**
     * Return value for specific shop
     */
    public static function getSettingsFromCache(string $key, $defaultReturn = false, int $shopId = null)
    {
        $wholeDataForCurrentShop = self::getWholeSettings($shopId, false);

        $foundItem = null;

        foreach ($wholeDataForCurrentShop as $item) {
            if ($item['key'] == $key) {
                $foundItem = $item['value'];
                break;
            }
        }

        return $foundItem ?? $defaultReturn;
    }

    public static function getSettingsWithoutCache(string $key, $defaultReturn = false, int $shopId = null)
    {
        $shop = $shopId ?? ShopHelper::getId();

        $sql = new DbQuery();
        $sql->select('value');
        $sql->from(Mergado::MERGADO['TABLE_NAME']);
        $sql->where('`key`="' . pSQL($key) . '" AND `id_shop`="' . pSQL($shop) . '"');

        return Db::getInstance()->getValue($sql) ?? $defaultReturn;
    }

    public static function reFetchWholeSettingsInCache(int $shopId): void
    {
        self::getWholeSettingsFromDatabase($shopId, true);
    }

    /**
     * Return all settings for specific shop
     */
    public static function getWholeSettings(int $shopId = null, bool $skipCache = false)
    {
        // Get current shopId if not set
        $shop = $shopId ?? ShopHelper::getId();

        return self::getWholeSettingsFromDatabase($shop, $skipCache);
    }

    private static function getWholeSettingsFromDatabase(int $shopId, bool $skipCache = false)
    {
        // Get cache if set or not forced direct query
        if ($skipCache) {
            return self::fetchWholeSettings($shopId);
        }

        return CacheService::get($shopId . '_settings', static function () use ($shopId) {
            return self::fetchWholeSettings($shopId);
        });
    }

    private static function fetchWholeSettings(int $shopId)
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

    public static function deleteShopItemsByPattern(string $pattern, int $shopId, bool $updateWholeSettingsCache = false): bool
    {
        $result =  Db::getInstance()
            ->delete(
                Mergado::MERGADO['TABLE_NAME'],
                '`key` LIKE "' . pSQL($pattern) . '" AND `id_shop` ="' . pSQL((string)$shopId) . '"'
            );

        if($updateWholeSettingsCache) {
            self::getWholeSettings($shopId, true);
        }

        return $result;
    }
}
