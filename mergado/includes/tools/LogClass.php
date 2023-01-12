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
use Context;
use Mergado;
use ToolsCore as Tools;

class LogClass
{
    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    /**
     * @return mixed
     */
    public static function getLogToken()
    {
        return Configuration::get('MERGADO_LOG_TOKEN');
    }


    public static function getLogDir()
    {
        $token = self::getLogToken();
        $folder = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/log/';
        $file = 'log_' . $token . '.txt';

        if (!$token) {
            $file = 'log.txt';
        }

        if (file_exists($folder)) {
            if (file_exists($folder . $file)) {
                return $folder . $file;
            }
        }

        return false;
    }

    /*******************************************************************************************************************
     * SET
     *******************************************************************************************************************/

    /**
     * @param $message
     */
    public static function log($message)
    {
        $token = self::getLogToken();

        $folder = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/log/';
        $file = 'log_' . $token . '.txt';

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        if (file_exists($folder . $file)) {
            if (filesize($folder . $file) > 5000 * 1024) {
                $content = file($folder . $file);
                $countLines = count($content);

                $content = array_slice($content, $countLines / 2);

                $f = fopen($folder . $file, 'w');
                fwrite($f, implode('', $content));
                fclose($f);
            }
        }

        $f = fopen($folder . $file, "a");
        fwrite($f, date('d-m-Y H:i:s') . " " . $message . " |<\n");
        fclose($f);
    }

    public static function setLogToken()
    {
        Configuration::updateValue('MERGADO_LOG_TOKEN', Tools::getAdminTokenLite('AdminMergadoLog'));
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
