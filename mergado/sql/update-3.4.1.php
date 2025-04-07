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

$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mergado_orders` (
    `id_order` int(11),
    `id_shop` int(11),
    `completed` TINYINT(1) NOT NULL
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

Db::getInstance()->execute($sql);
