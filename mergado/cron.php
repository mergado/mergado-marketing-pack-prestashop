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

require_once '../../config/config.inc.php';

$feed = null;
if (isset($argv) && $argv != null) {
    $token = $argv[1];
    $token = str_replace('--token=', '', $token);

    if (Tools::substr(Tools::encrypt('mergado/cron'), 0, 10) != $token || !Module::isInstalled('mergado')) {
        die('Bad token');
    }

    $feed = $argv[2];
    $feed = str_replace('--feed=', '', $feed);
} else {
    /* Kontrola bezpečnostního tokenu */
    if (Tools::substr(Tools::encrypt('mergado/cron'), 0, 10) != Tools::getValue('token')
            || !Module::isInstalled('mergado')) {
        die('Bad token');
    }

    $feed = Tools::getValue('feed');
}

require_once _PS_MODULE_DIR_.'mergado/classes/MergadoClass.php';

$mergado = new MergadoClass();
if ($mergado->generateMergadoFeed($feed)) {
    echo '<div style="height: 16px; width: 16px; border-radius: 100%; background: green;display:inline-block;margin-right:4px;"></div> feed generated';
    die();
} else {
    echo '<div style="height: 16px; width: 16px; border-radius: 100%; background: red;display:inline-block;margin-right:4px;"></div> error occured';
    die();
}
