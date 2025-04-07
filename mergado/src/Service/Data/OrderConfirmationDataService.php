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
use Media;
use Mergado\Service\AbstractBaseService;
use Product;

class OrderConfirmationDataService extends AbstractBaseService
{
    public function insertOrderData($orderId, $orderCurrency, $order, $orderProducts): void
    {
        $outputProducts = [];

        foreach ($orderProducts as $product) {
            $outputProducts[] = $this->getProductData($product);
        }

        $cartRules = [];

        foreach($order->getCartRules() as $rule) {
            $cartRules[] = $rule['name'];
        }

        Media::addJsDef([
            'mergado_order_data' => [
                'orderId' => $orderId,
                'currency' => $orderCurrency['iso_code'],
                'payment_method_name' => $order->payment,
                'total_discounts' => (float)$order->total_discounts,
                'total_discounts_tax_incl' => (float)$order->total_discounts_tax_incl,
                'total_discounts_tax_excl' => (float)$order->total_discounts_tax_excl,
                'total_paid' => (float)$order->total_paid,
                'total_paid_tax_incl' => (float)$order->total_paid_tax_incl,
                'total_paid_tax_excl' => (float)$order->total_paid_tax_excl,
                'total_paid_real' => (float)$order->total_paid_real,
                'total_products' => (float)$order->total_products,
                'total_products_wt' => (float)$order->total_products_wt,
                'total_shipping' => (float)$order->total_shipping,
                'total_shipping_tax_incl' => (float)$order->total_shipping_tax_incl,
                'total_shipping_tax_excl' => (float)$order->total_shipping_tax_excl,
                'products' => $outputProducts,
                'coupons' => implode(', ', $cartRules)
            ],
        ]);
    }


    private function getProductData($product): array
    {
        $langId = (int)Context::getContext()->language->id;
        $category = new Category((int)$product['id_category_default'], (int)$langId);

        $regular_price_tax_incl = Product::getPriceStatic($product['id_product'], true, $product['product_attribute_id'], 6, null, false, false);
        $regular_price_tax_excl = Product::getPriceStatic($product['id_product'], false, $product['product_attribute_id'], 6, null, false, false);

        $outputProduct = [
            'name' => $product['product_name'],
            'category_name' => $category->name,
            'quantity' => (int)$product['product_quantity'],

            "reduction" => (float)$product['reduction_amount'],
            "reduction_tax_incl" => (float)$product['reduction_amount_tax_incl'],
            "reduction_tax_excl" => (float)$product['reduction_amount_tax_excl'],

            "total_price_tax_incl" => (float)$product['total_price_tax_incl'],
            "total_price_tax_excl" => (float)$product['total_price_tax_excl'],
            "unit_price_tax_incl" => (float)$product['unit_price_tax_incl'],
            "unit_price_tax_excl" => (float)$product['unit_price_tax_excl'],
            "original_product_price" => (float)$product['original_product_price'],

            'regular_price_tax_excl' => $regular_price_tax_excl,
            'regular_price_tax_incl' => $regular_price_tax_incl,
        ];

        $outputProduct['id'] = $product['id_product'];

        if (isset($product['product_attribute_id']) && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] != 0) {
            $outputProduct['id_merged'] = $product['id_product'] . '-' . $product['product_attribute_id'];
            $outputProduct['id_product_attribute'] = $product['product_attribute_id'];
        } else {
            $outputProduct['id_merged'] = $product['id_product'];
            $outputProduct['id_product_attribute'] = '';
        }

        return $outputProduct;
    }
}
