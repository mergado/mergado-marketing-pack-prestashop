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
use Configuration;
use Mergado;
use Product;
use StockAvailable;

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
            if (isset($product['product_id']) && isset($product['product_attribute_id'])) {
                $id = $product['product_id'];

                if(isset($product['product_attribute_id']) && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                    $id = $id . '-' . $product['product_attribute_id'];
                }
            } else {
                $id = $product['id_product'];

                if(isset($product['id_product_attribute']) && $product['id_product_attribute'] !== '' && $product['id_product_attribute'] !== '0') {
                    $id = $id . '-' . $product['id_product_attribute'];
                }
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

    public static function getProductStockStatus($productId, $productAttributeId = null)
    {
        if ($productAttributeId !== null) {
            $availableQuantity = Product::getQuantity($productId, $productAttributeId);
        } else {
            $availableQuantity = Product::getQuantity($productId);
        }

        $whenOutOfStock = self::getStockStatusLogic($productId);

        if ($availableQuantity <= 0 && $whenOutOfStock == 1) {
            $availability = 'preorder';
        } else if ($availableQuantity > 0) {
            $availability = 'in stock';
        } else {
            $availability = 'out of stock';
        }

        return $availability;
    }

    public static function getStockStatusLogic($productId) {
        $whenOutOfStock = StockAvailable::outOfStock($productId);

        // 0 - no orders if out of stock
        // 1 - orders if out of stock allowed
        // 2 - settings of product is same as main global settings
        if ($whenOutOfStock == 2) {
            $whenOutOfStock = Configuration::get('PS_ORDER_OUT_OF_STOCK'); // set global settings as the used one
        }

        return $whenOutOfStock;
    }
}
