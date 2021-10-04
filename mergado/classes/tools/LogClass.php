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

namespace Mergado\Tools;

use ConfigurationCore as Configuration;
use Mergado;

class LogClass
{
    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    /**
     * @return mixed
     */
    public static function getLogLite()
    {

        $token = Configuration::get('MERGADO_LOG_TOKEN');
        return $token;
    }

    /*******************************************************************************************************************
     * SET
     *******************************************************************************************************************/

    /**
     * @param $message
     */
    public static function log($message)
    {
        $shopID = Mergado::getShopId();


        if (SettingsClass::getSettings('mergado_dev_log', $shopID)) {
            $token = Configuration::get('MERGADO_LOG_TOKEN');

            $folder = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/log/';
            $file = 'log_' . $token . '.txt';

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $f = fopen($folder . $file, "a");
            fwrite($f, date('d-m-Y H:i:s') . " " . $message . " |<\n");
            fclose($f);
        }
    }

    /*******************************************************************************************************************
     * DELETE
     *******************************************************************************************************************/

    public static function deleteLog()
    {
        $folder = _PS_MODULE_DIR_ . '/log/';

        if (file_exists($folder)) {

            foreach (glob($folder . "/*.*") as $filename) {
                if (is_file($filename)) {
                    unlink($filename);
                }
            }
        }
    }
}
