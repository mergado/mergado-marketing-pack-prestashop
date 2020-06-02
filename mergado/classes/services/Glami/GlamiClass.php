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

namespace Mergado\Glami;

use Mergado;

class GlamiClass
{

    /**
     * Return active language options for Glami TOP
     *
     */
    public static function getGlamiTOPActiveDomain($shopID)
    {
        $activeLangId = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['SELECTION_TOP'], $shopID);

        foreach(Mergado\Tools\SettingsClass::GLAMI_TOP_LANGUAGES as $item) {
            if($item['id_option'] === (int)$activeLangId) {
                return $item;
            }
        }

        return false;
    }
}
