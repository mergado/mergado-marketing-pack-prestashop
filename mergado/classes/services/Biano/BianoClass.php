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

use Cassandra\Set;
use CategoryCore;
use ConfigurationCore;
use CurrencyCore;
use ManufacturerCore;
use Mergado;

class BianoClass
{
    /*******************************************************************************************************************
     * BIANO ACTIVATION
     ******************************************************************************************************************/

    /**
     * Return if main biano is activated
     *
     * @param $shopId
     * @return bool
     */
    public static function isActive($shopId)
    {
        $bianoActive = self::getActiveField($shopId);

        if ($bianoActive === Mergado\Tools\SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return if biano language is activated
     *
     * @param $shopId
     * @param $langCode
     * @return bool
     */
    public static function isLanguageActive($langCode, $shopId)
    {
        $bianoLanguageActive = self::getLanguageActiveField($langCode, $shopId);
        $bianoMerchantId = self::getMerchantIdField($langCode, $shopId);

        if ($bianoLanguageActive === Mergado\Tools\SettingsClass::ENABLED && $bianoMerchantId && $bianoMerchantId !== '') {
            return true;
        } else {
            return false;
        }
    }

    /*******************************************************************************************************************
     * GET DATA
     ******************************************************************************************************************/

    /**
     * Return data necessary for biano purchase event
     *
     * @param $orderId
     * @param $order
     * @param $products
     * @param $langId
     * @return false|string
     */
    public static function getPurchaseData($orderId, $order, $products, $langId)
    {
        $data = array();

        $currency = new CurrencyCore($order->id_currency);

        $data['id'] = "$orderId";
        $data['order_price'] = (float) $order->total_paid;
        $data['currency'] = $currency->iso_code;

        $productData = array();

        foreach ($products as $product) {
            $product_item = array(
                "id" => $product['product_id'] . '-' . $product['product_attribute_id'],
                "quantity" => (int) $product['product_quantity'],
                "unit_price" => (float) ($product['total_price_tax_incl'] / $product['product_quantity']),
            );
            $productData[] = $product_item;
        }

        $data['items'] = $productData;

        return json_encode($data);
    }


    /*******************************************************************************************************************
     * GET FIELD
     ***************************************************************************************************************** /
     *
     * /*
     * Return active field value
     *
     * @param $shopId
     * @return false|string|null
     */
    public static function getActiveField($shopId)
    {
        $fieldName = Mergado\Tools\SettingsClass::BIANO['ACTIVE'];

        return Mergado\Tools\SettingsClass::getSettings($fieldName, $shopId);
    }

    /**
     * Return language active field value
     *
     * @param $langCode
     * @param $shopId
     * @return false|string|null
     */
    public static function getLanguageActiveField($langCode, $shopId)
    {
        $languageActiveName = self::getActiveLangFieldName($langCode);

        return Mergado\Tools\SettingsClass::getSettings($languageActiveName, $shopId);
    }

    /**
     * Return merchant id field value
     *
     * @param $langCode
     * @param $shopId
     * @return false|string|null
     */
    public static function getMerchantIdField($langCode, $shopId)
    {
        $merchantFieldName = self::getMerchantIdFieldName($langCode);

        return Mergado\Tools\SettingsClass::getSettings($merchantFieldName, $shopId);
    }


    /*******************************************************************************************************************
     * FORM HELPERS
     ******************************************************************************************************************/

    /**
     * Return name for biano language field
     * @param $langCode - CZ/SK etc.
     * @return string
     */
    public static function getActiveLangFieldName($langCode)
    {
        return Mergado\Tools\SettingsClass::BIANO['FORM_ACTIVE'] . '-' . $langCode;
    }

    /**
     * Return name for merchant language field
     * @param $langCode - CZ/SK etc.
     * @return string
     */
    public static function getMerchantIdFieldName($langCode)
    {
        return Mergado\Tools\SettingsClass::BIANO['MERCHANT_ID'] . '-' . $langCode;
    }
}
