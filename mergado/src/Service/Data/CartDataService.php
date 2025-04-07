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

use Carrier;
use Cart;
use Category;
use Context;
use Exception;
use Link;
use Manufacturer;
use Media;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Utility\SmartyTemplateLoader;
use Product;

class CartDataService extends AbstractBaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertCartAjaxHelpers(): void
    {
        $link = new Link();
        $parameters = array("action" => "getCartData");
        $ajax_link = $link->getModuleLink('mergado', 'ajax', $parameters);

        Media::addJsDef(
            array(
                "mergado_ajax_cart_get_data" => $ajax_link
            )
        );
    }

    public function insertCartData(Cart $cart): void
    {
        try {
            $cartData = $this->getCartData($cart);
            Media::addJsDef(['mergado_cart_data' => $cartData]);
        } catch (Exception $e) {
            $this->logger->error('Can\'t insert cart data', ['exception' => $e]);
        }
    }

    public function getAjaxCartData(): array
    {
        try {
            $cart = Context::getContext()->cart;

            return $this->getCartData($cart);
        } catch (Exception $e) {
            $this->logger->error('Can\'t get cart data', ['exception' => $e]);
        }

        return [];
    }

    public function insertShippingInfo(): void
    {
        $carriers = [];

        $langId = (int)Context::getContext()->language->id;

        foreach(Carrier::getCarriers($langId) as $option) {
            $carriers[] = ['id' => $option['id_carrier'], 'name' => $option['name']];
        }

        Media::addJsDef(['mergado_shipping_data' => [
            'carriers' => $carriers
        ]]);
    }

    public function getOldCartProductData($cartProducts): array
    {
        $langId = (int)Context::getContext()->language->id;

        $exportProducts = [];

        $cartProductsWithVat = [];
        $cartProductsWithoutVat = [];

        foreach ($cartProducts as $i => $product) {
            $category = new Category((int)$product['id_category_default'], (int)$langId);
            $manufacturer = new Manufacturer($product['id_manufacturer'], (int)$langId);
            $variant = ProductHelper::getProductAttributeName($product['id_product_attribute'], (int)$langId);

            $productData = [
                "id" => ProductHelper::getProductId($product),
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
                $productData['price'] = Product::getPriceStatic(
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
                $productData['price'] = Product::getPriceStatic(
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

    public function getCartDataPs16($module, $smarty, $cart): string
    {
        //For checkout in ps 1.6
        if (PrestashopVersionHelper::is16AndLower()) {
            $cartProducts = $cart->getProducts(true);

            $productData = $this->getOldCartProductData($cartProducts);

            $discounts = [];

            foreach ($cart->getDiscounts() as $item) {
                $discounts[] = $item['name'];
            }

            return SmartyTemplateLoader::render(
                $module,
                'views/templates/front/shoppingCart/cart_data.tpl',
            $smarty,
                [
                    'data' => htmlspecialchars(json_encode($productData['default'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithVat' => htmlspecialchars(json_encode($productData['withVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithoutVat' => htmlspecialchars(json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'cart_id' => $cart->id,
                    'orderUrl' => '', // Not needed for ps 1.6
                    'coupons' => join(', ', $discounts),
                ]
            );
        }

        return '';
    }

    public function getCartDataPs17($module, $smarty, $cart)
    {
        //Data for checkout in ps 1.7 ...
        if (PrestashopVersionHelper::is17AndHigher()) {
            $cartProducts = $cart->getProducts(true);

            $productData = $this->getOldCartProductData($cartProducts);

            if (PrestashopVersionHelper::is16AndLower()) {
                $smartyVariables = [
                    'data' => htmlspecialchars(json_encode($productData['default'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithVat' => htmlspecialchars(json_encode($productData['withVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithoutVat' => htmlspecialchars(json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                ];
            } else {
                $smartyVariables = [
                    'data' => json_encode($productData['default'], JSON_NUMERIC_CHECK),
                    'dataWithVat' => json_encode($productData['withVat'], JSON_NUMERIC_CHECK),
                    'dataWithoutVat' => json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK),
                ];
            }

            $discounts = [];

            foreach ($cart->getDiscounts() as $item) {
                $discounts[] = $item['name'];
            }

            global $smarty;
            $url = $smarty->tpl_vars['urls']->value['pages']['order'];


            return SmartyTemplateLoader::render(
                $module,
                'views/templates/front/shoppingCart/cart_data.tpl',
                $smarty,
                array_merge([
                    'cart_id' => $cart->id,
                    'orderUrl' => $url,
                    'coupons' => join(', ', $discounts),
                ], $smartyVariables)
            );
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function getCartData(Cart $cart): array
    {
        $outputProducts = [];

        foreach ($cart->getProducts() as $product) {
            $outputProducts[] = $this->getProductData($product);
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
            'coupons' => implode(', ', $cartRules)
        ];
    }

    private function getProductData($product): array
    {
        $langId = (int)Context::getContext()->language->id;
        $category = new Category((int)$product['id_category_default'], (int)$langId);

        if (isset($product['reduction'])) {
            $reduction = $product['reduction']; //PS 1.7
        } else {
            // PS 1.6
            $reduction = Product::getPriceStatic(
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
            $reduction_without_tax = Product::getPriceStatic(
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
            $price_without_reduction_without_tax = Product::getPriceStatic(
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
            $price_with_reduction_with_tax = Product::getPriceStatic(
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
            $price_with_reduction_without_tax = Product::getPriceStatic(
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
}
