<?php

namespace Mergado\includes\helpers;

use CategoryCore;
use ContextCore;
use Mergado;
use ProductCore;

class ProductHelper
{
    /**
     * Displayed in PRODUCT LIST, PRODUCT DETAIL and PRODUCT MODAL
     */

    public static function insertProductData($product, $additionalClasses)
    {
        global $cookie;

        $productData = $product['product'];

        $langId = (int)ContextCore::getContext()->language->id;
        $currency = \CurrencyCore::getCurrency($cookie->id_currency);

        if(_PS_VERSION_ < Mergado::PS_V_17) {
            if(ControllerHelper::isProductDetail()) {
                $outputData =self::getPs16ProductDetailAttributesData($productData, $langId, $currency);

                return self::returnAllAttributes($outputData, $additionalClasses);
            } else {
                $outputData = self::getPs16ProductArrayData($productData, $langId, $currency);
            }
        } else {
            $outputData = self::getPS17ProductData($productData, $currency);
        }

        return self::returnSimple($outputData, $additionalClasses);
    }

    protected static function returnSimple($outputData, $additionalClasses)
    {
        return sprintf('<div id="mergado-product-informations" class="%s" style="display: none !important; position: absolute; top: 0; left: -500px;" data-product="%s"></div>',
            $additionalClasses,
            htmlspecialchars(json_encode($outputData, JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8')
        );
    }

    protected static function returnAllAttributes($outputData, $additionalClasses)
    {
        return sprintf('<div id="mergado-product-informations" class="%s" style="display: none !important; position: absolute; top: 0; left: -500px;" data-product-attributes="%s"></div>',
            $additionalClasses,
            htmlspecialchars(json_encode($outputData, JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8')
        );
    }

    protected static function getPs16ProductArrayData($productData, $langId, $currency)
    {
        $name = $productData['name'];
        $id = $productData['id_product'];
        $id_product_attribute = $productData['id_product_attribute'] = (!empty($productData['id_product_attribute']) ? (int)$productData['id_product_attribute'] : null);
        $id_category_default = $productData['id_category_default'];
        $category = new CategoryCore((int)$id_category_default, (int)$langId);

        $outputData = [
            'name' => $name,
            'id' => $id,
            'id_product_attribute' => $id_product_attribute,
            'id_merged' => self::getMergedId($id, $id_product_attribute),
            'price_with_reduction_with_tax' => ProductCore::getPriceStatic($id, true,  $id_product_attribute, 2),
            'price_with_reduction_without_tax' => ProductCore::getPriceStatic($id, false,  $id_product_attribute, 2),
            'price_without_reduction_with_tax' => ProductCore::getPriceStatic(
                (int)$id,
                true,
                $id_product_attribute,
                2,
                null,
                false,
                false
            ),
            'price_without_reduction_without_tax' => ProductCore::getPriceStatic(
                (int)$id,
                false,
                $id_product_attribute,
                2,
                null,
                false,
                false
            ),
            'reduction_with_tax' => ProductCore::getPriceStatic(
                (int)$id,
                true,
                $id_product_attribute,
                2,
                null,
                true,
                true,
                1,
                true,
                null,
                null,
                null,
                $specific_prices
            ),
            'reduction_without_tax' => ProductCore::getPriceStatic(
                (int)$id,
                false,
                $id_product_attribute,
                2,
                null,
                true,
                true,
                1,
                true,
                null,
                null,
                null,
                $specific_prices
            ),
            'category_name' => $category->name,
            'currency' => $currency['iso_code'],
        ];

        return $outputData;
    }

    protected static function getPs16ProductDetailAttributesData(ProductCore $productData, $langId, $currency)
    {
        $attributes = $productData->getAttributeCombinations($langId);

        $outputData = [];

        foreach($attributes as $combination) {
            $product = [
                'name' => $productData->name,
                'id_product' => $combination['id_product'],
                'id_product_attribute' => $combination['id_product_attribute'],
                'id_category_default' => $productData->id_category_default
            ];

            $productOutput = self::getPs16ProductArrayData($product, $langId, $currency);

            $outputData[self::getMergedId($product['id_product'], $product['id_product_attribute'])] = $productOutput;
        }

        return $outputData;
    }

    protected static function getPS17ProductData($productData, $currency)
    {
        return [
            'name' => $productData['name'],
            'id' => $productData['id_product'],
            'id_product_attribute' => $productData['id_product_attribute'],
            'id_merged' => self::getMergedId($productData['id_product'], $productData['id_product_attribute']),
            'price_with_reduction_with_tax' => ProductCore::getPriceStatic(
                (int)$productData['id_product'],
                true,
                $productData['id_product_attribute'],
                2,
                null,
                false,
                true
            ),
            'price_with_reduction_without_tax' => $productData['price_tax_exc'],
            'price_without_reduction_with_tax' => $productData['price_without_reduction'],
            'price_without_reduction_without_tax' => $productData['price_without_reduction_without_tax'],
            'reduction_with_tax' => $productData['reduction'],
            'reduction_without_tax' => $productData['reduction_without_tax'],
            'category' => $productData['category'],
            'category_name' => $productData['category_name'],
            'currency' => $currency['iso_code']
        ];
    }

    public static function getMergedId($id, $id_product_attribute)
    {
        if ($id_product_attribute) {
            $id_merged = $id . '-' . $id_product_attribute;
        } else {
            $id_merged = $id;
        }

        return $id_merged;
    }
}
