<?php

namespace Mergado\includes\helpers;

use CartCore;
use CategoryCore;
use ContextCore;
use LinkCore;
use ManufacturerCore;
use MediaCore;
use Mergado\Tools\HelperClass;
use ProductCore;

class CartHelper
{
    public static function insertCartAjaxHelpers()
    {
        $link = new LinkCore();
        $parameters = array("action" => "getCartData");
        $ajax_link = $link->getModuleLink('mergado', 'ajax', $parameters);

        MediaCore::addJsDef(
            array(
                "mergado_ajax_cart_get_data" => $ajax_link
            )
        );
    }

    public static function insertCartData(CartCore $cart)
    {
        $cartData = self::getCartData($cart);
        MediaCore::addJsDef(['mergado_cart_data' => $cartData]);
    }

    public static function getAjaxCartData()
    {
        $cart = ContextCore::getContext()->cart;

        return self::getCartData($cart);
    }

    protected static function getCartData(CartCore $cart)
    {
        $outputProducts = [];

        foreach ($cart->getProducts() as $product) {
            $outputProducts[] = self::getProductData($product);
        }

        $cartRules = [];

        foreach ($cart->getCartRules() as $rule) {
            $cartRules[] = $rule['name'];
        }

        return [
            'products' => $outputProducts,
            'order_total_with_taxes' => $cart->getOrderTotal(true),
            'order_total_without_taxes' => $cart->getOrderTotal(false),
            'order_shipping_with_taxes' => $cart->getTotalShippingCost(null, true),
            'order_shipping_without_taxes' => $cart->getTotalShippingCost(null, false),
            'coupons' => join(', ', $cartRules)
        ];
    }

    private static function getProductData($product)
    {
        $langId = (int)ContextCore::getContext()->language->id;
        $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);

        if (isset($product['reduction'])) {
            $reduction = $product['reduction']; //PS 1.7
        } else {
            // PS 1.6
            $reduction = ProductCore::getPriceStatic(
                (int)$product['id_product'],
                true,
                $product['id_product_attribute'],
                2,
                null,
                true,
                true,
                1,
                true
            );
        }

        if (isset($product['reduction_without_tax'])) {
            $reduction_without_tax = $product['reduction_without_tax']; // PS 1.7
        } else {
            // PS 1.6
            $reduction_without_tax = ProductCore::getPriceStatic(
                (int)$product['id_product'],
                false,
                $product['id_product_attribute'],
                2,
                null,
                true,
                true,
                1,
                true
            );
        }

        if (isset($product['price_without_reduction_without_tax'])) {
            $price_without_reduction_without_tax = $product['price_without_reduction_without_tax']; // PS 1.7
        } else {
            // PS 1.6
            $price_without_reduction_without_tax = ProductCore::getPriceStatic(
                (int)$product['id_product'],
                false,
                $product['id_product_attribute'],
                2,
                null,
                false,
                false
            );
        }

        if (isset($product['price_with_reduction_with_tax'])) {
            $price_with_reduction_with_tax = $product['price_with_reduction']; // PS 1.7
        } else {
            // PS 1.6
            $price_with_reduction_with_tax = ProductCore::getPriceStatic(
                (int)$product['id_product'],
                true,
                $product['id_product_attribute'],
                2,
                null,
                false,
                true
            );
        }

        if (isset($product['price_with_reduction_without_tax'])) {
            $price_with_reduction_without_tax = $product['price_with_reduction_without_tax']; // PS 1.7
        } else {
            // PS 1.6
            $price_with_reduction_without_tax = ProductCore::getPriceStatic(
                (int)$product['id_product'],
                false,
                $product['id_product_attribute'],
                2,
                null,
                false,
                true
            );
        }

        return [
            'name' => $product['name'],
            'id' => $product['id_product'],
            'id_product_attribute' => $product['id_product_attribute'],
            'id_merged' => ProductHelper::getMergedId($product['id_product'], $product['id_product_attribute']),
            'price_with_reduction_without_tax' => $price_with_reduction_without_tax,
            'price_with_reduction_with_tax' => $price_with_reduction_with_tax,
            'price_without_reduction_with_tax' => $product['price_without_reduction'],
            'price_without_reduction_without_tax' => $price_without_reduction_without_tax,
            'reduction_with_tax' => $reduction,
            'reduction_without_tax' => $reduction_without_tax,
            'category' => $product['category'],
            'category_name' => $category->name,
            'quantity' => $product['quantity'],
        ];
    }

    public static function insertShippingInfo()
    {
        $carriers = [];

        $langId = (int)ContextCore::getContext()->language->id;

        foreach(\CarrierCore::getCarriers($langId) as $option) {
            $carriers[] = ['id' => $option['id_carrier'], 'name' => $option['name']];
        }

        MediaCore::addJsDef(['mergado_shipping_data' => [
            'carriers' => $carriers
        ]]);
    }

    /**
     * TODO REMAKE THIS WHOLE PROCESS
     */
    public static function getOldCartProductData($cartProducts)
    {
        $langId = (int)ContextCore::getContext()->language->id;

        $exportProducts = [];

        $cartProductsWithVat = [];
        $cartProductsWithoutVat = [];

        foreach ($cartProducts as $i => $product) {
            $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
            $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
            $variant = HelperClass::getProductAttributeName($product['id_product_attribute'], (int)$langId);

            $productData = [
                "id" => \Mergado\Tools\HelperClass::getProductId($product),
                "name" => $product['name'],
                "brand" => $manufacturer->name,
                "category" => $category->name,
                "variant" => $variant,
                "list_position" => $i,
                "quantity" => $product['cart_quantity'],
                "price" => (string) ($product['total_wt'] / $product['cart_quantity']),
            ];

            $exportProducts[] = $productData;

            if (isset($product['price_with_reduction_with_tax'])) {
                $productData['price'] = $product['price_with_reduction']; // PS 1.7
            } else {
                // PS 1.6
                $productData['price'] = ProductCore::getPriceStatic(
                    (int)$product['id_product'],
                    true,
                    $product['id_product_attribute'],
                    2,
                    null,
                    false,
                    true
                );
            }
            $cartProductsWithVat[] = $productData;

            if (isset($product['price_with_reduction_without_tax'])) {
                $productData['price'] = $product['price_with_reduction_without_tax']; // PS 1.7
            } else {
                // PS 1.6
                $productData['price'] = ProductCore::getPriceStatic(
                    (int)$product['id_product'],
                    false,
                    $product['id_product_attribute'],
                    2,
                    null,
                    false,
                    true
                );
            }

            $cartProductsWithoutVat[] = $productData;
        }

        return ['default' => $exportProducts, 'withVat' => $cartProductsWithVat, 'withoutVat' => $cartProductsWithoutVat];
    }
}
