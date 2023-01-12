<?php

declare(strict_types=1);

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

use DbQueryCore as DbQuery;
use Mergado\Tools\LogClass;

class OrderClass
{
    /**
     * @param int $orderId
     * @param int $shopId
     * @return bool
     */
    public static function isOrderCompleted(int $orderId, int $shopId) : bool {
        try {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from(Mergado::MERGADO['TABLE_ORDERS_NAME']);
            $sql->where('`id_order`="' . $orderId . '"');
            $sql->where('`id_shop`="' . $shopId . '"');
            $sql->where('`completed`="1"');

            return count(Db::getInstance()->executeS($sql)) > 0;
        } catch (Exception $e) {
            LogClass::log('Error in OrderClass::isOrderCompleted - ' . $e->getMessage());
        }

        return true;
    }

    /**
     * @param int $orderId
     * @param int $shopId
     * @return bool
     */
    public static function setOrderCompleted(int $orderId, int $shopId) : bool {
        try {
            return Db::getInstance()->insert(Mergado::MERGADO['TABLE_ORDERS_NAME'], [
                'id_order' => $orderId,
                'id_shop' => $shopId,
                'completed' => 1
            ]);
        } catch (Exception $e) {
            LogClass::log('Error in OrderClass::setOrderCompleted - ' . $e->getMessage());
        }

        return false;
    }
}
