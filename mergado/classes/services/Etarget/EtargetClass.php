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

namespace Mergado\Etarget;

use Mergado;
use Mergado\Tools\HelperClass;
use Mergado\Tools\SettingsClass;

class EtargetClass
{

    const ACTIVE = 'etarget';
    const ID = 'etarget_id';
    const HASH = 'etarget_hash';

    private $active;
    private $id;
    private $hash;

    public function __construct()
    {
    }

    /**
     * Check if service active
     *
     * @param $shopId
     * @return bool
     */

    public function isActive($shopId)
    {
        $active = $this->getActive($shopId);
        $code = $this->getId($shopId);

        if ($active === SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    public function getData($shopId)
    {
        return array(
            'id' => $this->getid($shopId),
            'hash' => $this->getHash($shopId),
        );
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    /**
     * @param $shopId
     * @return false|string|null
     */

    public function getActive($shopId)
    {
        if (!is_null($this->active)) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(self::ACTIVE, $shopId);

        return $this->active;
    }

    /**
     * @param $shopId
     * @return false|string|null
     */
    public function getid($shopId)
    {
        if (!is_null($this->id)) {
            return $this->id;
        }

        $this->id = SettingsClass::getSettings(self::ID, $shopId);

        return $this->id;
    }

    /**
     * @param $shopId
     * @return false|string|null
     */
    public function getHash($shopId)
    {
        if (!is_null($this->hash)) {
            return $this->hash;
        }

        $this->hash = SettingsClass::getSettings(self::HASH, $shopId);

        return $this->hash;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields()
    {
        return array(
            self::ACTIVE => [
                'fields' => [
                    self::ID,
                    self::HASH
                ]
            ],
        );
    }
}
