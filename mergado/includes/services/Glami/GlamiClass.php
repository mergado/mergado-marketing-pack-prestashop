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

namespace Mergado\Glami;

use CurrencyCore;
use Mergado;

class GlamiClass
{

    public static function getGlamiOrderData($orderId, $params, $glamiProducts, $email, $shopId) {
        $withVat = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['CONVERSION_VAT_INCL'], $shopId);
        $active = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['ACTIVE'], $shopId);
        $productIds = json_encode($glamiProducts['ids']);
        $productNames = json_encode($glamiProducts['names']);

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            if ($withVat) {
                $value = $params['objOrder']->total_products_wt;
            } else {
                $value = $params['objOrder']->total_products;
            }
            $currency = $params['currencyObj']->iso_code;
        } else {
            if ($withVat) {
                $value = $params['order']->total_products_wt;
            } else {
                $value = $params['order']->total_products;
            }
            $currency = CurrencyCore::getCurrency($params['order']->id_currency);
        }

        return array (
            'active' => $active,
            'orderId' => $orderId,
            'productIds' => $productIds,
            'productNames' => $productNames,
            'value' => $value,
            'currency' => $currency,
        );
    }

    public static function getGlamiTOPOrderData($orderId, $glamiProducts, $customerEmail, $shopId) {
        $glamiTOPLanguageValues = Mergado\Glami\GlamiClass::getGlamiTOPActiveDomain($shopId);

        $data = [
            'active' => Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['ACTIVE_TOP'], $shopId),
            'lang_active'=> $glamiTOPLanguageValues['type_code'],
            'url_active'=> $glamiTOPLanguageValues['name'],
            'code' => Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['CODE_TOP'], $shopId),
            'orderId' => $orderId,
            'products' => json_encode($glamiProducts['full']),
            'email' => $customerEmail,
        ];

        return $data;
    }

    public static function prepareProductData($products)
    {
        $glamiProducts = [];

        foreach ($products as $product) {
            $glamiProducts['full'] = ['id' => $product['product_id'] . '-' . $product['product_attribute_id'], 'name' => $product['product_name']];
            $glamiProducts['ids'][] = $product['product_id'] . '-' . $product['product_attribute_id'];
            $glamiProducts['names'][] = $product['product_name'];
        }

        return $glamiProducts;
    }

    /**
     * Return active language options for Glami TOP
     *
     */
    public static function getGlamiTOPActiveDomain($shopID)
    {
        $activeLangId = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['SELECTION_TOP'], $shopID);

        foreach(Mergado\Tools\SettingsClass::GLAMI_TOP_LANGUAGES as $item) {
            if($item['id_option'] === (int)$activeLangId) {
                return $item;
            }
        }

        return false;
    }
}
