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
use CombinationCore;
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
     * @return false|string
     */

    public static function getGtagjsPurchaseData($orderId, $order, $products, $langId)
    {
        $data = array();

        $currency = new CurrencyCore($order->id_currency);

        $data['transaction_id'] = "$orderId";
        $data['affiliation'] = ConfigurationCore::get('PS_SHOP_NAME');
        $data['value'] = $order->total_paid;
        $data['currency'] = $currency->iso_code;
        $data['tax'] = $order->total_paid_tax_incl - $order->total_paid_tax_excl;
        $data['shipping'] = $order->total_shipping;

        $productData = array();

        foreach ($products as $product) {

            $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
            $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
            $productVariant = Mergado\Tools\HelperClass::getProductAttributeName($product['product_attribute_id'], $langId);

            $product_item = array(
                "id" => $product['product_id'] . '-' . $product['product_attribute_id'],
                "name" => $product['product_name'],
//                "list_name" => "",
                "brand" => $manufacturer->name,
                "category" => $category->name,
                "variant" => $productVariant,
//                "list_position" => "",
                "quantity" => $product['product_quantity'],
                "price" => $product['total_price_tax_incl'] / $product['product_quantity'],
            );
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
        $code = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['CODE'], $shopId);

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
        $code = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['CODE'], $shopId);
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
        $code = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['CODE'], $shopId);
        $ecommerce = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['ECOMMERCE'], $shopId);
        $ecommerceEnhanced = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_GTAGJS['ECOMMERCE_ENHANCED'], $shopId);

        if($active === Mergado\Tools\SettingsClass::ENABLED && $ecommerceEnhanced === Mergado\Tools\SettingsClass::ENABLED && $ecommerce === Mergado\Tools\SettingsClass::ENABLED && $tracking === Mergado\Tools\SettingsClass::ENABLED && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /*******************************************************************************************************************
     * Google Tag Manager
     ******************************************************************************************************************/

    /**
     * @param $orderId
     * @param $order
     * @param $products
     * @param $langId
     * @return false|string
     */

    public static function getGTMPurchaseData($orderId, $order, $products, $langId)
    {
        $data = array();

        $currency = new CurrencyCore($order->id_currency);

        $data['actionField']['id'] = "$orderId";
        $data['actionField']['affiliation'] = ConfigurationCore::get('PS_SHOP_NAME');
        $data['actionField']['revenue'] = $order->total_paid;
        $data['actionField']['tax'] = $order->total_paid_tax_incl - $order->total_paid_tax_excl;
        $data['actionField']['shipping'] = $order->total_shipping;
        $data['actionField']['coupon'] = '';

        $cartRules = array();
        foreach($order->getCartRules() as $item) {
            $cartRules[] = $item['name'];
        }

        if($cartRules !== array()) {
            $data['actionField']['coupon'] = join(', ', $cartRules);
        }

        $productData = array();

        foreach ($products as $product) {

            $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
            $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
            $productVariant = Mergado\Tools\HelperClass::getProductAttributeName($product['product_attribute_id'], $langId);

            $product_item = array(
                "name" => $product['product_name'],
                "id" => $product['product_id'] . '-' . $product['product_attribute_id'],
                "price" => $product['total_price_tax_incl'] / $product['product_quantity'],
                "brand" => $manufacturer->name,
                "category" => $category->name,
                "variant" => $productVariant,
                "quantity" => $product['product_quantity'],
            );
            $productData[] = $product_item;
        }

        $data['products'] = $productData;

        return json_encode($data);
    }

    /**
     * Return if ecommerce is active
     * @param $shopId
     * @return bool
     */
    public static function isGTMActive($shopId)
    {
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['ACTIVE'], $shopId);
        $tracking = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['TRACKING'], $shopId);
        $code = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['CODE'], $shopId);

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
    public static function isGTMEcommerceActive($shopId)
    {
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['ACTIVE'], $shopId);
        $tracking = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['TRACKING'], $shopId);
        $code = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['CODE'], $shopId);
        $ecommerce = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['ECOMMERCE'], $shopId);

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
    public static function isGTMEcommerceEnhancedActive($shopId)
    {
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['ACTIVE'], $shopId);
        $tracking = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['TRACKING'], $shopId);
        $code = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['CODE'], $shopId);
        $ecommerce = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['ECOMMERCE'], $shopId);
        $ecommerceEnhanced = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_TAG_MANAGER['ECOMMERCE_ENHANCED'], $shopId);

        if($active === Mergado\Tools\SettingsClass::ENABLED && $ecommerceEnhanced === Mergado\Tools\SettingsClass::ENABLED && $ecommerce === Mergado\Tools\SettingsClass::ENABLED && $tracking === Mergado\Tools\SettingsClass::ENABLED && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /*******************************************************************************************************************
     * Google Ads - remarketing
     ******************************************************************************************************************/

    /**
     * Return if google Ads remarketing is active
     * @param $shopId
     * @return bool
     */
    public static function isGAdsRemarketingActive($shopId)
    {
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_ADS['REMARKETING'], $shopId);
//        $code = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_ADS['REMARKETING_ID'], $shopId);

        if($active === Mergado\Tools\SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }

    /*******************************************************************************************************************
     * Google Ads - conversions
     ******************************************************************************************************************/

    /**
     * Return if google Ads conversions is active
     * @param $shopId
     * @return bool
     */
    public static function isGAdsConversionsActive($shopId)
    {
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_ADS['CONVERSIONS'], $shopId);
        $code = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GOOGLE_ADS['CONVERSIONS_CODE'], $shopId);

        if($active === Mergado\Tools\SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }
}
