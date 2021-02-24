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

namespace Mergado\Google;

use Mergado;
use Mergado\Tools\SettingsClass;

class GoogleAdsClass
{
    const CONVERSIONS_ACTIVE = 'mergado_adwords_conversion';
    const REMARKETING_ACTIVE = 'adwords_remarketing';
    const CONVERSIONS_CODE = 'mergado_adwords_conversion_code';
    const CONVERSIONS_LABEL = 'mergado_adwords_conversion_label';

    private $conversionsActive;
    private $remarketingActive;
    private $conversionsCode;
    private $conversionsLabel;

    // Main settings variables
    private $multistoreShopId;

    public function __construct($multistoreShopId)
    {
        $this->multistoreShopId = $multistoreShopId;
    }

    /*******************************************************************************************************************
     * IS
     *******************************************************************************************************************/

    /**
     * @return bool
     */
    public function isConversionsActive()
    {
        $active = $this->getConversionsActive();
        $code = $this->getConversionsCode();
        $label = $this->getConversionsLabel();

        // I dont need code if Google Analytics is enabled
        if ($active === SettingsClass::ENABLED && $code && $code !== '' && $label && $label !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isRemarketingActive()
    {
        $active = $this->getRemarketingActive();
        $code = $this->getConversionsCode();

        // I dont need code if Google Analytics is enabled
        if ($active === SettingsClass::ENABLED && $code && $code !== '') {
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
    public function getConversionsActive()
    {
        if (!is_null($this->conversionsActive)) {
            return $this->conversionsActive;
        }

        $this->conversionsActive = SettingsClass::getSettings(self::CONVERSIONS_ACTIVE, $this->multistoreShopId);

        return $this->conversionsActive;
    }

    /**
     * @return false|string|null
     */
    public function getRemarketingActive()
    {
        if (!is_null($this->remarketingActive)) {
            return $this->remarketingActive;
        }

        $this->remarketingActive = SettingsClass::getSettings(self::REMARKETING_ACTIVE, $this->multistoreShopId);

        return $this->remarketingActive;
    }

    /**
     * @return false|string|null
     */
    public function getConversionsCode()
    {
        if (!is_null($this->conversionsCode)) {
            return $this->conversionsCode;
        }

        $code = SettingsClass::getSettings(self::CONVERSIONS_CODE, $this->multistoreShopId);

        if (preg_match("/^[A-Z]{2}-/i", substr($code, 0, 3))) {
            $this->conversionsCode = $code;
        } else {
            $this->conversionsCode = 'AW-' . $code;
        }

        return $this->conversionsCode;
    }

    /**
     * @return false|string|null
     */
    public function getConversionsLabel()
    {
        if (!is_null($this->conversionsLabel)) {
            return $this->conversionsLabel;
        }

        $this->conversionsLabel = SettingsClass::getSettings(self::CONVERSIONS_LABEL, $this->multistoreShopId);

        return $this->conversionsLabel;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    /**
     * @return array[]
     */
    public static function getToggleFields()
    {
        return array(
            self::CONVERSIONS_ACTIVE => [
                'fields' => [
                    self::CONVERSIONS_LABEL,
                ]
            ],
        );
    }
}
