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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_1_3_2($module) {
	
    unlink(__DIR__ . '/../classes/CartItem.php');
    unlink(__DIR__ . '/../classes/NajNakup.php');
    unlink(__DIR__ . '/../classes/Pricemania.php');
    unlink(__DIR__ . '/../classes/ZboziKonverze.php');

    return true;
}