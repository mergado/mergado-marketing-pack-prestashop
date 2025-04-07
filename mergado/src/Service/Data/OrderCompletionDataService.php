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


namespace Mergado\Service\Data;

use Db;
use DbQuery;
use Exception;
use Mergado;

class OrderCompletionDataService extends Mergado\Service\AbstractBaseService
{
    public function isOrderCompleted(int $orderId, int $shopId): bool
    {
        try {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from(Mergado::MERGADO['TABLE_ORDERS_NAME']);
            $sql->where('`id_order`="' . $orderId . '"');
            $sql->where('`id_shop`="' . $shopId . '"');
            $sql->where('`completed`="1"');

            return count(Db::getInstance()->executeS($sql)) > 0;
        } catch (Exception $e) {
            $this->logger->error('Query to check if order is completed failed', ['exception' => $e]);
        }

        return true;
    }

    public function setOrderCompleted(int $orderId, int $shopId): bool
    {
        try {
            return Db::getInstance()->insert(Mergado::MERGADO['TABLE_ORDERS_NAME'], [
                'id_order' => $orderId,
                'id_shop' => $shopId,
                'completed' => 1
            ]);
        } catch (Exception $e) {
            $this->logger->error('Can\'t change order to completed', ['exception' => $e]);
        }

        return false;
    }
}
