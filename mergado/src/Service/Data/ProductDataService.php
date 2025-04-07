<?php declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Service\Data;

use Category;
use Context;
use Mergado\Helper\ControllerHelper;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\AbstractBaseService;
use Product;

class ProductDataService extends AbstractBaseService
{
    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    public function __construct()
    {
        $this->controllerHelper = ControllerHelper::getInstance();

        parent::__construct();
    }

    /**
     * Displayed in PRODUCT LIST, PRODUCT DETAIL and PRODUCT MODAL
     */

    public function insertProductData($product, $additionalClasses): string
    {
        global $cookie;

        $productData = $product['product'];

        $langId = (int)Context::getContext()->language->id;
        $currency = \Currency::getCurrency($cookie->id_currency);

        if(PrestashopVersionHelper::is16AndLower()) {
            if($this->controllerHelper->isProductDetail()) {
                $outputData = $this->getPs16ProductDetailAttributesData($productData, $langId, $currency);

                return $this->returnAllAttributes($outputData, $additionalClasses);
            }

            $outputData = $this->getPs16ProductArrayData($productData, $langId, $currency);
        } else {
            $outputData = $this->getPS17ProductData($productData, $currency);
        }

        return $this->returnSimple($outputData, $additionalClasses);
    }

    private function returnSimple($outputData, $additionalClasses): string
    {
        return sprintf('<div id="mergado-product-informations" class="%s" style="display: none !important; position: absolute; top: 0; left: -500px;" data-product="%s"></div>',
            $additionalClasses,
            htmlspecialchars(json_encode($outputData, JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8')
        );
    }

    private function returnAllAttributes($outputData, $additionalClasses): string
    {
        return sprintf('<div id="mergado-product-informations" class="%s" style="display: none !important; position: absolute; top: 0; left: -500px;" data-product-attributes="%s"></div>',
            $additionalClasses,
            htmlspecialchars(json_encode($outputData, JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8')
        );
    }

    private function getPs16ProductArrayData($productData, $langId, $currency): array
    {
        $name = $productData['name'];
        $id = $productData['id_product'];
        $id_product_attribute = $productData['id_product_attribute'] = (!empty($productData['id_product_attribute']) ? (int)$productData['id_product_attribute'] : null);
        $id_category_default = $productData['id_category_default'];
        $category = new Category((int)$id_category_default, (int)$langId);

        return [
            'name' => $name,
            'id' => $id,
            'id_product_attribute' => $id_product_attribute,
            'id_merged' => ProductHelper::getMergedId($id, $id_product_attribute),
            'price_with_reduction_with_tax' => Product::getPriceStatic($id, true,  $id_product_attribute, 2),
            'price_with_reduction_without_tax' => Product::getPriceStatic($id, false,  $id_product_attribute, 2),
            'price_without_reduction_with_tax' => Product::getPriceStatic(
                (int)$id,
                true,
                $id_product_attribute,
                2,
                null,
                false,
                false
            ),
            'price_without_reduction_without_tax' => Product::getPriceStatic(
                (int)$id,
                false,
                $id_product_attribute,
                2,
                null,
                false,
                false
            ),
            'reduction_with_tax' => Product::getPriceStatic(
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
            'reduction_without_tax' => Product::getPriceStatic(
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
    }

    private function getPs16ProductDetailAttributesData(Product $productData, $langId, $currency): array
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

            $productOutput = $this->getPs16ProductArrayData($product, $langId, $currency);

            $outputData[ProductHelper::getMergedId($product['id_product'], $product['id_product_attribute'])] = $productOutput;
        }

        return $outputData;
    }

    private function getPS17ProductData($productData, array $currency): array
    {
        if (isset($productData['price_without_reduction_without_tax'])) {
            $productData['price_without_reduction_without_tax'] = Product::getPriceStatic(
                (int)$productData['id_product'],
                false,
                $productData['id_product_attribute'],
                2,
                null,
                false,
                false
            );
        }

        if (isset($productData['reduction_without_tax'])) {
            $productData['reduction_without_tax'] = Product::getPriceStatic(
                (int)$productData['id_product'],
                false,
                $productData['id_product_attribute'],
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
            );
        }

        return [
            'name' => $productData['name'],
            'id' => $productData['id_product'],
            'id_product_attribute' => $productData['id_product_attribute'],
            'id_merged' => ProductHelper::getMergedId($productData['id_product'], $productData['id_product_attribute']),
            'price_with_reduction_with_tax' => Product::getPriceStatic(
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
}
