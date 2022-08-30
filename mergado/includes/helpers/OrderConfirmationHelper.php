<?php

namespace Mergado\includes\helpers;

use CategoryCore;
use ContextCore;
use MediaCore;
use ProductCore;

class OrderConfirmationHelper
{
    public static function insertOrderData($orderId, $orderCurrency, $order, $orderProducts)
    {
        $outputProducts = [];

        foreach ($orderProducts as $product) {
            $outputProducts[] = self::getProductData($product);
        }

        $cartRules = [];

        foreach($order->getCartRules() as $rule) {
            $cartRules[] = $rule['name'];
        }

        MediaCore::addJsDef([
            'mergado_order_data' => [
                'orderId' => $orderId,
                'currency' => $orderCurrency['iso_code'],
                'payment_method_name' => $order->payment,
                'total_discounts' => floatval($order->total_discounts),
                'total_discounts_tax_incl' => floatval($order->total_discounts_tax_incl),
                'total_discounts_tax_excl' => floatval($order->total_discounts_tax_excl),
                'total_paid' => floatval($order->total_paid),
                'total_paid_tax_incl' => floatval($order->total_paid_tax_incl),
                'total_paid_tax_excl' => floatval($order->total_paid_tax_excl),
                'total_paid_real' => floatval($order->total_paid_real),
                'total_products' => floatval($order->total_products),
                'total_products_wt' => floatval($order->total_products_wt),
                'total_shipping' => floatval($order->total_shipping),
                'total_shipping_tax_incl' => floatval($order->total_shipping_tax_incl),
                'total_shipping_tax_excl' => floatval($order->total_shipping_tax_excl),
                'products' => $outputProducts,
                'coupons' => join(', ', $cartRules)
            ],
        ]);
    }


    private static function getProductData($product)
    {
        $langId = (int)ContextCore::getContext()->language->id;
        $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);

        $regular_price_tax_incl = ProductCore::getPriceStatic($product['id_product'], true, $product['product_attribute_id'], 6, null, false, false);
        $regular_price_tax_excl = ProductCore::getPriceStatic($product['id_product'], false, $product['product_attribute_id'], 6, null, false, false);

        $outputProduct = [
            'name' => $product['product_name'],
            'category_name' => $category->name,
            'quantity' => (int)$product['product_quantity'],

            "reduction" => floatval($product['reduction_amount']),
            "reduction_tax_incl" => floatval($product['reduction_amount_tax_incl']),
            "reduction_tax_excl" => floatval($product['reduction_amount_tax_excl']),

            "total_price_tax_incl" => floatval($product['total_price_tax_incl']),
            "total_price_tax_excl" => floatval($product['total_price_tax_excl']),
            "unit_price_tax_incl" => floatval($product['unit_price_tax_incl']),
            "unit_price_tax_excl" => floatval($product['unit_price_tax_excl']),
            "original_product_price" => floatval($product['original_product_price']),

            'regular_price_tax_excl' => $regular_price_tax_excl,
            'regular_price_tax_incl' => $regular_price_tax_incl,
        ];

        if (isset($product['product_attribute_id']) && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] != 0) {
            $outputProduct['id'] = $product['id_product'];
            $outputProduct['id_merged'] = $product['id_product'] . '-' . $product['product_attribute_id'];
            $outputProduct['id_product_attribute'] = $product['product_attribute_id'];
        } else {
            $outputProduct['id'] = $product['id_product'];
            $outputProduct['id_merged'] = $product['id_product'];
            $outputProduct['id_product_attribute'] = '';
        }

        return $outputProduct;
    }
}
