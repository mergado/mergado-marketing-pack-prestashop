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

$sql = "ALTER TABLE `" . _DB_PREFIX_ . "mergado` ADD `id_shop` int(11) unsigned NULL AFTER `id`;";
Db::getInstance()->execute($sql);

$sql = "ALTER TABLE `" . _DB_PREFIX_ . "mergado` DROP INDEX `key`;";
Db::getInstance()->execute($sql);

$sql = "UPDATE `" . _DB_PREFIX_ . "mergado` SET `id_shop` = 0;";
Db::getInstance()->execute($sql);

$sql = " CREATE TABLE `" . _DB_PREFIX_ . "mergado_news` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `description` text NOT NULL,
          `category` varchar(255) NOT NULL,
          `language` text NOT NULL,
          `pubDate` datetime,
          `shown` tinyint(1) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

Db::getInstance()->execute($sql);