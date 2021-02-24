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

namespace Mergado\Zbozi;

use Mergado;
use Mergado\Tools\SettingsClass;

class ZboziClass
{
    const ACTIVE = 'mergado_zbozi_konverze';
    const ADVANCED_ACTIVE = 'mergado_zbozi_advanced_konverze';
    const VAT_INCL = 'mergado_zbozi_conversion_vat_incl';
    const SHOP_ID = 'mergado_zbozi_shop_id';
    const KEY = 'mergado_zbozi_secret';
    const OPT_OUT = 'zbozi_opt_out_text-';

    // Input variables
    private $active;
    private $advancedActive;
    private $vatIncluded;
    private $shopId;
    private $key;

    // Main settings variables
    private $multistoreShopId;

    public function __construct($multistoreShopId)
    {
        $this->multistoreShopId = $multistoreShopId;
    }

    public function isActive()
    {
        $active = $this->getActive();
        $key = $this->getKey();

        if ($active === SettingsClass::ENABLED && $key && $key !== '') {
            return true;
        } else {
            return false;
        }
    }

    public function isAdvancedActive()
    {
        $active = $this->getAdvancedActive();

        if ($active === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    /**
     * @return false|string|null
     */
    public function getActive()
    {
        if (!is_null($this->active)) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(self::ACTIVE, $this->multistoreShopId);

        return $this->active;
    }

    /**
     * @return false|string|null
     */
    public function getAdvancedActive()
    {
        if (!is_null($this->advancedActive)) {
            return $this->advancedActive;
        }

        $this->advancedActive = SettingsClass::getSettings(self::ADVANCED_ACTIVE, $this->multistoreShopId);

        return $this->advancedActive;
    }

    /**
     * @return false|string|null
     */
    public function getVatIncluded()
    {
        if (!is_null($this->vatIncluded)) {
            return $this->vatIncluded;
        }

        $this->vatIncluded = SettingsClass::getSettings(self::VAT_INCL, $this->multistoreShopId);

        return $this->vatIncluded;
    }

    /**
     * @return false|string|null
     */
    public function getShopId()
    {
        if (!is_null($this->shopId)) {
            return $this->shopId;
        }

        $this->shopId = SettingsClass::getSettings(self::SHOP_ID, $this->multistoreShopId);

        return $this->shopId;
    }

    /**
     * @return false|string|null
     */
    public function getKey()
    {
        if (!is_null($this->key)) {
            return $this->key;
        }

        $this->key = SettingsClass::getSettings(self::KEY, $this->multistoreShopId);

        return $this->key;
    }

    /**
     * @param $lang
     * @return false|string|null
     */
    public function getOptOut($lang)
    {
        return SettingsClass::getSettings(self::OPT_OUT . $lang, $this->multistoreShopId);
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields($languages)
    {
        $langFields = [];

        foreach ($languages as $key => $lang) {
            $langName = SettingsClass::getLangIso(strtoupper($lang['iso_code']));
            $langFields[] = self::OPT_OUT . $langName;
        }

        $otherFields = array(
            self::ADVANCED_ACTIVE,
            self::SHOP_ID,
            self::KEY,
            self::VAT_INCL,
        );

        return array(
            self::ACTIVE => array(
                'fields' => array_merge($langFields, $otherFields)
            )
        );
    }
}

