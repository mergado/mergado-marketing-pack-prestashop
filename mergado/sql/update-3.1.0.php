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

$sql = "ALTER TABLE `" . _DB_PREFIX_ . "mergado_news` ADD `link` varchar(255) NOT NULL AFTER `category`";
Db::getInstance()->execute($sql);