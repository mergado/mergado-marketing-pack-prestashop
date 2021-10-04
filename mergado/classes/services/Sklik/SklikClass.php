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

namespace Mergado\Sklik;

use Mergado;
use Mergado\Tools\SettingsClass;

class SklikClass
{
    const CONVERSIONS_ACTIVE = 'mergado_sklik_konverze';
    const CONVERSIONS_CODE = 'mergado_sklik_konverze_kod';
    const CONVERSIONS_VALUE = 'mergado_sklik_konverze_hodnota';
    const CONVERSION_VAT_INCL = 'sklik_conversion_vat_incl';

    const RETARGETING_ACTIVE = 'seznam_retargeting';
    const RETARGETING_ID = 'seznam_retargeting_id';

    private $conversionsActive;
    private $conversionsCode;
    private $conversionsValue;
    private $conversionsVatIncluded;
    private $retargetingActive;
    private $retargetingId;

    public function __construct()
    {
    }

    /******************************************************************************************************************
     * CONVERSIONS
     *****************************************************************************************************************/

    /**
     * @param $shopId
     * @return bool
     */

    public function isConversionsActive($shopId)
    {
        $active = $this->getConversionsActive($shopId);
        $code = $this->getConversionsCode($shopId);

        if ($active === SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return necessary data for Sklik conversions
     * @param $order
     * @param $shopId
     * @return array
     */

    public function getConversionsData($order, $shopId) {
        $active = $this->getConversionsActive($shopId);
        $conversionCode = $this->getConversionsCode($shopId);
        $conversionValue = $this->getConversionsValue($shopId);

        // Value of order preset by user
        if (trim($conversionValue) === '') {

            // If user selected with or without VAT
            if ($this->getConversionsVatIncluded($shopId)) {
                $conversionValue = (float) $order->total_products_wt;
            } else {
                $conversionValue = (float) $order->total_products;
            }
        }

        return array(
            'active' => $active,
            'conversionCode' => $conversionCode,
            'conversionValue' => $conversionValue,
        );
    }

    /******************************************************************************************************************
     * RETARGETING
     *****************************************************************************************************************/
    /**
     * @param $shopId
     * @return bool
     */

    public function isRetargetingActive($shopId)
    {
        $active = $this->getRetargetingActive($shopId);
        $code = $this->getRetargetingId($shopId);

        if ($active === Mergado\Tools\SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }


    /*******************************************************************************************************************
     * GET FIELD
     ******************************************************************************************************************/

    /**
     * Return name of conversions active
     * @param $shopId
     * @return string
     */
    public function getConversionsActive($shopId)
    {
        if (!is_null($this->conversionsActive)) {
            return $this->conversionsActive;
        }

        $this->conversionsActive = SettingsClass::getSettings(self::CONVERSIONS_ACTIVE, $shopId);

        return $this->conversionsActive;
    }

    /**
     * Return name of conversions code
     * @param $shopId
     * @return string
     */
    public function getConversionsCode($shopId)
    {
        if (!is_null($this->conversionsCode)) {
            return $this->conversionsCode;
        }

        $this->conversionsCode = SettingsClass::getSettings(self::CONVERSIONS_CODE, $shopId);

        return $this->conversionsCode;
    }

    /**
     * Return name of conversions code
     * @param $shopId
     * @return string
     */
    public function getConversionsValue($shopId)
    {
        if (!is_null($this->conversionsValue)) {
            return $this->conversionsValue;
        }

        $this->conversionsValue = SettingsClass::getSettings(self::CONVERSIONS_VALUE, $shopId);

        return $this->conversionsValue;
    }

    /**
     * Return merchant id field value
     *
     * @param $shopId
     * @return false|string|null
     */
    public function getConversionsVatIncluded($shopId)
    {
        if (!is_null($this->conversionsVatIncluded)) {
            return $this->conversionsVatIncluded;
        }

        $this->conversionsVatIncluded = SettingsClass::getSettings(self::CONVERSION_VAT_INCL, $shopId);

        return $this->conversionsVatIncluded;
    }

    public function getRetargetingActive($shopId)
    {
        if (!is_null($this->retargetingActive)) {
            return $this->retargetingActive;
        }

        $this->retargetingActive = SettingsClass::getSettings(self::RETARGETING_ACTIVE, $shopId);

        return $this->retargetingActive;
    }

    public function getRetargetingId($shopId)
    {
        if (!is_null($this->retargetingId)) {
            return $this->retargetingId;
        }

        $this->retargetingId = SettingsClass::getSettings(self::RETARGETING_ID, $shopId);

        return $this->retargetingId;
    }


    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields()
    {
        return array(
            self::CONVERSIONS_ACTIVE => [
                'fields' => [
                    self::CONVERSIONS_CODE,
                    self::CONVERSIONS_VALUE,
                    self::CONVERSION_VAT_INCL,
                ]
            ],
            self::RETARGETING_ACTIVE => [
                'fields' => [
                    self::RETARGETING_ID
                ]
            ],
        );
    }
};