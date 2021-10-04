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

use CategoryCore;
use ConfigurationCore;
use CurrencyCore;
use ManufacturerCore;
use Mergado;

class GoogleClass
{

    /*******************************************************************************************************************
     * GTAG JS
     ******************************************************************************************************************/

    /**
     * @param $orderId
     * @param $order
     * @param $products
     * @param $langId
     * @param $shopId
     * @return false|string
     */

    public static function getGtagjsPurchaseData($orderId, $order, $products, $langId, $shopId)
    {
        $googleAdsClass = new GoogleAdsClass($shopId);

        $data = array();

        $currency = new CurrencyCore($order->id_currency);
        $withVat = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['CONVERSION_VAT_INCL'], $shopId);

        // Default is with vat
        if ($withVat === false) {
            $withVat = true;
        }

        $data['transaction_id'] = "$orderId";
        $data['affiliation'] = ConfigurationCore::get('PS_SHOP_NAME');
        $data['value'] = $order->total_paid;
        $data['currency'] = $currency->iso_code;
        $data['tax'] = (string) ($order->total_paid_tax_incl - $order->total_paid_tax_excl);
        $data['shipping'] = $order->total_shipping_tax_excl;

        $productData = array();

        foreach ($products as $product) {

            $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
            $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
            $productVariant = Mergado\Tools\HelperClass::getProductAttributeName($product['product_attribute_id'], $langId);

            if ($product['product_attribute_id'] && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                $idProduct = $product['product_id'] . '-' . $product['product_attribute_id'];
            } else {
                $idProduct = $product['product_id'];
            }

            $product_item = array(
                "id" => $idProduct,
                "name" => $product['product_name'],
//                "list_name" => "",
                "brand" => $manufacturer->name,
                "category" => $category->name,
                "variant" => $productVariant,
//                "list_position" => "",
                "quantity" => $product['product_quantity'],
                "google_business_vertical" => $googleAdsClass->getRemarketingTypeForTemplate()
            );

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = $product['unit_price_tax_incl'];
            } else {
                $product_item['price'] = $product['unit_price_tax_excl'];
            }

            $productData[] = $product_item;
        }

        $data['items'] = $productData;

        return json_encode($data);
    }

    /**
     * Return if ecommerce is active
     * @param $shopId
     * @return bool
     */
    public static function isGtagjsActive($shopId)
    {
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['ACTIVE'], $shopId);
        $tracking = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['TRACKING'], $shopId);
        $code = Mergado\Google\GoogleClass::getGoogleAnalyticsCode($shopId);

        if($active === Mergado\Tools\SettingsClass::ENABLED && $tracking === Mergado\Tools\SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return if ecommerce is active
     * @param $shopId
     * @return bool
     */
    public static function isGtagjsEcommerceActive($shopId)
    {
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['ACTIVE'], $shopId);
        $tracking = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['TRACKING'], $shopId);
        $code = Mergado\Google\GoogleClass::getGoogleAnalyticsCode($shopId);
        $ecommerce = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['ECOMMERCE'], $shopId);

        if($active === Mergado\Tools\SettingsClass::ENABLED && $ecommerce === Mergado\Tools\SettingsClass::ENABLED && $tracking === Mergado\Tools\SettingsClass::ENABLED && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return active if enhanced ecommerce is active
     * @param $shopId
     * @return bool
     */
    public static function isGtagjsEcommerceEnhancedActive($shopId)
    {
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['ACTIVE'], $shopId);
        $tracking = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['TRACKING'], $shopId);
        $code = Mergado\Google\GoogleClass::getGoogleAnalyticsCode($shopId);
        $ecommerce = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['ECOMMERCE'], $shopId);
        $ecommerceEnhanced = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['ECOMMERCE_ENHANCED'], $shopId);

        if($active === Mergado\Tools\SettingsClass::ENABLED && $ecommerceEnhanced === Mergado\Tools\SettingsClass::ENABLED && $ecommerce === Mergado\Tools\SettingsClass::ENABLED && $tracking === Mergado\Tools\SettingsClass::ENABLED && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * GET CODES - TODO rewrite me whole to object as others are
     */

    public static function getGoogleAnalyticsCode($shopId)
    {
        $googleAnalyticsCode = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['CODE'], $shopId);

        // add prefix if not exist
        if (substr( $googleAnalyticsCode, 0, 3 ) !== "UA-") {
            $googleAnalyticsCode = 'UA-' . $googleAnalyticsCode;
        }

        return $googleAnalyticsCode;
    }
}
