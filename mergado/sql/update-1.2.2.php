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
$sql = array();
$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'mergado` ADD UNIQUE (`key`)';
$sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'mergado` (`key`, `value`) VALUES ("what_to_export_both", 1)';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}