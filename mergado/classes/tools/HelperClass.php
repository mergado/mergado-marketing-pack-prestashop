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

namespace Mergado\Tools;
use CombinationCore;
use Mergado;

class HelperClass
{
    /*******************************************************************************************************************
     * GET CART / ORDER
     *******************************************************************************************************************/

    /**
     * Return cartId
     *
     * @param $params - hook parameters
     * @return false|string|null
     */
    public static function getOrderCartId($params)
    {

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $orderCartId = $params['objOrder']->id_cart;
        } else {
            $orderCartId = $params['order']->id_cart;
        }

        return $orderCartId;
    }

    /**
     * Return orderId
     *
     * @param $params - hook parameters
     * @return false|string|null
     */

    public static function getOrderId($params)
    {
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $orderId = $params['objOrder']->id;
        } else {
            $orderId = $params['order']->id;
        }

        return $orderId;
    }

    /**
     * Return product id with attribtue if exist
     *
     * @param $product
     * @return false|string|null
     */
    public static function getProductId($product)
    {
        //PS 1.6
        if(_PS_VERSION_ < Mergado::PS_V_17) {
            if(is_array($product)) {
                $id = $product['id_product'];

                if(isset($product['id_product_attribute']) && $product['id_product_attribute'] !== '' && $product['id_product_attribute'] !== '0') {
                    $id = $id . '-' . $product['id_product_attribute'];
                }

            } else {
                $id = $product->id;

                if(isset($product->id_product_attribute) && $product->id_product_attribute !== '' && $product->id_product_attribute !== '0') {
                    $id = $id . '-' . $product->id_product_attribute;
                }
            }
        //PS 1.7
        } else {
            $id = $product['id_product'];

            if(isset($product['id_product_attribute']) && $product['id_product_attribute'] !== '' && $product['id_product_attribute'] !== '0') {
                $id = $id . '-' . $product['id_product_attribute'];
            }
        }

        return $id;
    }

    public static function getProductAttributeName($product_attribute_id, $langId)
    {
        $combination = new CombinationCore($product_attribute_id);
        $attrNames = $combination->getAttributesName((int)$langId);

        $names = [];

        foreach($attrNames as $item) {
            $names[] = $item['name'];
        }

        return implode(", ", $names);
    }
}
