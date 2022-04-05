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

namespace Mergado\Biano;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

use Mergado\Tools\LanguagesClass;
use Mergado\Tools\SettingsClass;


class BianoStarClass
{
    const ACTIVE = 'mmp_biano_star_active';
    const SHIPMENT_IN_STOCK = 'mmp_biano_star_shipment_in_stock';
    const SHIPMENT_BACKORDER = 'mmp_biano_star_shipment_backorder';
    const SHIPMENT_OUT_OF_STOCK = 'mmp_biano_star_shipment_out_of_stock';

    const OPT_OUT = 'mmp_biano_start_opt_out_text_';

    const DEFAULT_OPT = 'Do not send a satisfaction questionnaire within the Biano Star program.';

    private $active;
    private $shipmentInStock;
    private $shipmentBackorder;
    private $shipmentOutOfStock;

    // Main settings variables
    private $multistoreShopId;

    public function __construct($multistoreShopId)
    {
        $this->multistoreShopId = $multistoreShopId;
    }

    /******************************************************************************************************************
     * IS
     ******************************************************************************************************************/

    /**
     * Biano star is dependant on Biano
     * Check of Biano activation is omitted because this function is used only inside Biano call.
     *
     * @return bool
     */
    public function isActive($lang) {
        $active = $this->getActive();
        $bianoService = new BianoClass();

        if ( $active === '1' && $bianoService->isActive($lang)) {
            return true;
        } else {
            return false;
        }
    }

    /*******************************************************************************************************************
     * GET VALUES
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

    public function getShipmentInStock() {
        if (!is_null($this->shipmentInStock)) {
            return $this->shipmentInStock;
        }

        $this->shipmentInStock = SettingsClass::getSettings(self::SHIPMENT_IN_STOCK, $this->multistoreShopId);

        return $this->shipmentInStock;
    }

    public function getShipmentBackorder() {
        if (!is_null($this->shipmentBackorder)) {
            return $this->shipmentBackorder;
        }

        $this->shipmentBackorder = SettingsClass::getSettings(self::SHIPMENT_BACKORDER, $this->multistoreShopId);

        return $this->shipmentBackorder;
    }

    public function getShipmentOutOfStock() {
        if (!is_null($this->shipmentOutOfStock)) {
            return $this->shipmentOutOfStock;
        }

        $this->shipmentOutOfStock = SettingsClass::getSettings(self::SHIPMENT_OUT_OF_STOCK, $this->multistoreShopId);

        return $this->shipmentOutOfStock;
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

    /**
     * @param $languages
     * @return array[]
     */
    public static function getToggleFields($languages)
    {
        $langFields = [];

        foreach ($languages as $lang) {
            $langName = LanguagesClass::getLangIso(strtoupper($lang['iso_code']));
            $langFields[] = self::OPT_OUT . $langName;
        }

        $otherFields = [
            self::SHIPMENT_IN_STOCK,
            self::SHIPMENT_BACKORDER,
            self::SHIPMENT_OUT_OF_STOCK
        ];

        return [
            self::ACTIVE => [
                'fields' => array_merge($langFields, $otherFields),
                'sub-check' => $otherFields
            ]
        ];
    }
}
