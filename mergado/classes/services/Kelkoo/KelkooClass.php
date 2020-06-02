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

namespace Mergado\Kelkoo;

use Mergado;

class KelkooClass
{

    public static function isKelkooActive($shopId)
    {
        $kelkoo_active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::KELKOO['ACTIVE'], $shopId);
        $kelkoo_country = Mergado\Kelkoo\KelkooClass::getKelkooActiveDomain($shopId);
        $kelkoo_merchant_id = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::KELKOO['COM_ID'], $shopId);

        if ($kelkoo_active === Mergado\Tools\SettingsClass::ENABLED && $kelkoo_country && $kelkoo_country !== '' && $kelkoo_merchant_id && $kelkoo_merchant_id !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return active language options for Kelkoo
     * @param $shopID
     * @return bool|mixed
     */
    public static function getKelkooActiveDomain($shopID)
    {
        $activeLangId = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::KELKOO['COUNTRY'], $shopID);

        foreach(Mergado\Tools\SettingsClass::KELKOO_COUNTRIES as $item) {
            if($item['id_option'] === (int)$activeLangId) {
                return $item['type_code'];
            }
        }

        return false;
    }

    /**
     * Return necessary data for Kelkoo tracking
     * @param $orderId
     * @param $order
     * @param $products
     * @param $shopId
     * @return array
     */

    public static function getKelkooOrderData($orderId, $order, $products, $shopId) {
        $productsKelkoo = array();

        //Same for 1.6 and 1.7
        foreach ($products as $product) {

            $productKelkoo = array('productname' => $product['product_name'],
                'productid' => $product['product_reference'],
                'quantity' => $product['product_quantity'],
                'price' => $product['unit_price_tax_incl']);
            $productsKelkoo[] = $productKelkoo;
        }

        return array(
            'PS_VERSION' => _PS_VERSION_,
            'kelkoo_products_json' => json_encode($productKelkoo),
            'kelkoo_sales' => $order->getOrdersTotalPaid(),
            'kelkoo_orderId' => $orderId,
            'kelkoo_country' => Mergado\Kelkoo\KelkooClass::getKelkooActiveDomain($shopId),
            'kelkoo_merchant_id' => Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::KELKOO['COM_ID'], $shopId),
        );
    }
}
