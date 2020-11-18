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

namespace Mergado\Facebook;

use Mergado;
use Mergado\Tools\HelperClass;
use Mergado\Tools\SettingsClass;

class FacebookClass
{
    const ACTIVE = 'fb_pixel';
    const CODE = 'fb_pixel_code';
    const CONVERSION_VAT_INCL = 'fb_pixel_conversion_vat_incl';

    private $active;
    private $code;
    private $conversionVatIncluded;

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
        $code = $this->getCode($shopId);

        if ($active === SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get data for order confirmed
     *
     * @param $params
     * @param $products
     * @param $shopId
     * @return array
     */

    public function getFbPixelData($params, $products, $shopId)
    {
        $withVat = self::getConversionVatIcluded($shopId);

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            if ($withVat) {
                $orderValue = $params['objOrder']->total_products_wt;
            } else {
                $orderValue = $params['objOrder']->total_products;
            }

        } else {
            if ($withVat) {
                $orderValue = $params['order']->total_products_wt;
            } else {
                $orderValue = $params['order']->total_products;
            }
        }

        return array(
            'active' => $this->getActive($shopId),
            'products' => $this->getProducts($products),
            'orderValue' => $orderValue,
        );
    }

    private function getProducts($products) {
        $fbProducts = array();

        foreach ($products as $product) {
            $fbProducts[] = HelperClass::getProductId($product);
        }

        return $fbProducts;
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
    public function getCode($shopId)
    {
        if (!is_null($this->code)) {
            return $this->code;
        }

        $this->code = SettingsClass::getSettings(self::CODE, $shopId);

        return $this->code;
    }

    /**
     * @param $shopId
     * @return false|string|null
     */
    public function getConversionVatIcluded($shopId)
    {
        if (!is_null($this->conversionVatIncluded)) {
            return $this->conversionVatIncluded;
        }

        $this->conversionVatIncluded = SettingsClass::getSettings(self::CONVERSION_VAT_INCL, $shopId);

        return $this->conversionVatIncluded;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields()
    {
        return array(
            self::ACTIVE => [
                'fields' => [
                    self::CODE,
                    self::CONVERSION_VAT_INCL,
                ]
            ],
        );
    }
}
