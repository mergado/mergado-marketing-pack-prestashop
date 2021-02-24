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
 * @author    www.mergado.cz
 * @copyright 2016 Mergado technologies, s. r. o.
 * @license   LICENSE.txt
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_2_3_0($module)
{
    include __DIR__ . "/../sql/update-2.3.0.php";

    $module->registerHook('displayProductFooter');
    $module->registerHook('displayShoppingCart');
    $module->registerHook('displayAfterBodyOpeningTag');
    $module->registerHook('displayBeforeBodyClosingTag');

    Tools::clearCache();
    return true;
}
