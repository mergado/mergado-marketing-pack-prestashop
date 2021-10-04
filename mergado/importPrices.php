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

namespace Mergado;

use Mergado;
use Mergado\Tools\ImportPricesClass;
use Tools;
use Module;

require_once '../../config/config.inc.php';
include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

if (isset($argv) && $argv != null) {
    $token = $argv[1];
    $token = str_replace('--token=', '', $token);

    if (Tools::substr(Tools::encrypt('mergado/importPrices'), 0, 10) != $token || !Module::isInstalled(Mergado::MERGADO['MODULE_NAME'])) {
        die('Bad token');
    }
} else {
    /* Kontrola bezpečnostního tokenu */
    if (Tools::substr(Tools::encrypt('mergado/importPrices'), 0, 10) != Tools::getValue('token')
            || !Module::isInstalled(Mergado::MERGADO['MODULE_NAME'])) {
        die('Bad token');
    }
}

$pricesClass = new ImportPricesClass();
$status = $pricesClass->importPrices();

if ($status) {
    echo '<div style="height: 16px; width: 16px; border-radius: 100%; background: green;display:inline-block;margin-right:4px;"></div>Prices imported successfully';
    die();
} else {
    echo '<div style="height: 16px; width: 16px; border-radius: 100%; background: red;display:inline-block;margin-right:4px;"></div> Error occured';
    die();
}