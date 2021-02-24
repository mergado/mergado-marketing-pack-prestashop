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

namespace Mergado\Google;

use CategoryCore;
use ConfigurationCore;
use ManufacturerCore;
use Mergado;
use Mergado\Tools\SettingsClass;

class GoogleTagManagerClass
{
    const ACTIVE = 'mergado_google_tag_manager_active';
    const CODE = 'mergado_google_tag_manager_code';
    const ECOMMERCE_ACTIVE = 'mergado_google_tag_manager_ecommerce';
    const ECOMMERCE_ENHANCED_ACTIVE = 'mergado_google_tag_manager_ecommerce_enhanced';
    const CONVERSION_VAT_INCL = 'mergado_google_tag_manager_conversion_vat_incl';
    const VIEW_LIST_ITEMS_COUNT = 'mergado_google_tag_manager_view_list_items_count';

    private $active;
    private $code;
    private $ecommerceActive;
    private $enhancedEcommerceActive;
    private $conversionVatIncluded;
    private $viewListItemsCount;

    // Main settings variables
    private $multistoreShopId;

    public function __construct($multistoreShopId)
    {
        $this->multistoreShopId = $multistoreShopId;
    }

    /******************************************************************************************************************
     * IS
     ******************************************************************************************************************/

    /**
     * @return bool
     */
    public function isActive()
    {
        $active = $this->getActive();
        $code = $this->getCode();

        if ($active === SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isEcommerceActive()
    {
        $active = $this->getActive();
        $code = $this->getCode();
        $ecommerceActive = $this->getEcommerceActive();

        if ($active === SettingsClass::ENABLED && $code && $code !== '' && $ecommerceActive === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isEnhancedEcommerceActive()
    {
        $active = $this->getActive();
        $code = $this->getCode();
        $ecommerceActive = $this->getEcommerceActive();
        $enhancedEcommerceActive = $this->getEnhancedEcommerceActive();

        if ($active === SettingsClass::ENABLED && $code && $code !== '' && $ecommerceActive === SettingsClass::ENABLED && $enhancedEcommerceActive === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }


    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    /**
     * @return false|string|null
     */
    public function getActive()
    {
        if (!is_null($this->active)) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(self::ACTIVE, $this->multistoreShopId);

        return $this->active;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        if (!is_null($this->code)) {
            return $this->code;
        }

        $code = SettingsClass::getSettings(self::CODE, $this->multistoreShopId);

        if (trim($code) !== '' && substr( $code, 0, 4 ) !== "GTM-") {
            $this->code = 'GTM-' . $code;
        } else {
            $this->code = $code;
        }

        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getEcommerceActive()
    {
        if (!is_null($this->ecommerceActive)) {
            return $this->ecommerceActive;
        }

        $this->ecommerceActive = SettingsClass::getSettings(self::ECOMMERCE_ACTIVE, $this->multistoreShopId);

        return $this->ecommerceActive;
    }

    /**
     * @return mixed
     */
    public function getEnhancedEcommerceActive()
    {
        if (!is_null($this->enhancedEcommerceActive)) {
            return $this->enhancedEcommerceActive;
        }

        $this->enhancedEcommerceActive = SettingsClass::getSettings(self::ECOMMERCE_ENHANCED_ACTIVE, $this->multistoreShopId);

        return $this->enhancedEcommerceActive;
    }

    /**
     * @return mixed
     */
    public function getConversionVatIncluded()
    {
        if (!is_null($this->conversionVatIncluded)) {
            return $this->conversionVatIncluded;
        }

        $this->conversionVatIncluded = SettingsClass::getSettings(self::CONVERSION_VAT_INCL, $this->multistoreShopId);

        return $this->conversionVatIncluded;
    }

    /**
     * @return mixed
     */
    public function getViewListItemsCount()
    {
        if (!is_null($this->viewListItemsCount)) {
            return $this->viewListItemsCount;
        }

        $viewListItemsCount = SettingsClass::getSettings(self::VIEW_LIST_ITEMS_COUNT, $this->multistoreShopId);

        if (trim($viewListItemsCount) === '') {
            $this->viewListItemsCount = 0;
        } else {
            $this->viewListItemsCount = $viewListItemsCount;
        }

        return $this->viewListItemsCount;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    /**
     * @return array[]
     */
    public static function getToggleFields()
    {
        return array(
            self::ACTIVE => [
                'fields' => [
                    self::CODE,
                    self::ECOMMERCE_ACTIVE,
                    self::ECOMMERCE_ENHANCED_ACTIVE,
                    self::CONVERSION_VAT_INCL,
                ],
                'sub-check' => [
                    self::ECOMMERCE_ACTIVE => [
                        'fields' => [
                            self::ECOMMERCE_ENHANCED_ACTIVE,
                        ],
                    ],
                ]
            ],
        );
    }

    /**
     * @param $orderId
     * @param $order
     * @param $langId
     * @return false|string
     */
    public function getPurchaseData($orderId, $order, $langId)
    {
        $data = array();
        $products = $order->getProducts();

        $withVat = $this->getConversionVatIncluded();

        // Default is with vat
        if ($withVat === false) {
            $withVat = true;
        }

        $data['actionField']['id'] = "$orderId";
        $data['actionField']['affiliation'] = ConfigurationCore::get('PS_SHOP_NAME');
        $data['actionField']['revenue'] = $order->total_paid;
        $data['actionField']['tax'] = (string) ($order->total_paid_tax_incl - $order->total_paid_tax_excl);
        $data['actionField']['shipping'] = $order->total_shipping_tax_excl;
        $data['actionField']['coupon'] = '';

        $cartRules = array();
        foreach($order->getCartRules() as $item) {
            $cartRules[] = $item['name'];
        }

        if($cartRules !== array()) {
            $data['actionField']['coupon'] = join(', ', $cartRules);
        }

        $productData = array();

        foreach ($products as $product) {

            $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
            $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
            $productVariant = Mergado\Tools\HelperClass::getProductAttributeName($product['product_attribute_id'], $langId);

            if ($product['product_attribute_id'] && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                $idProduct = $product['product_id'] . '-' . $product['product_attribute_id'];
            } else {
                $idProduct = $product['product_id'];
            }

            $product_item = array(
                "name" => $product['product_name'],
                "id" => $idProduct,
                "brand" => $manufacturer->name,
                "category" => $category->name,
                "variant" => $productVariant,
                "quantity" => (int) $product['product_quantity'],
            );

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = $product['unit_price_tax_incl'];
            } else {
                $product_item['price'] = $product['unit_price_tax_excl'];
            }

            $productData[] = $product_item;
        }

        $data['products'] = $productData;

        return json_encode($data);
    }

    /**
     * @param $orderId
     * @param $order
     * @param $langId
     * @return false|string
     */
    public function getTransactionData($orderId, $order, $langId)
    {
        $data = array();
        $products = $order->getProducts();
        $withVat = $this->getConversionVatIncluded();

        // Default is with vat
        if ($withVat === false) {
            $withVat = true;
        }

        $data['transactionId'] = "$orderId";
        $data['transactionAffiliation'] = ConfigurationCore::get('PS_SHOP_NAME');
        $data['transactionTotal'] = (float) $order->total_paid;
        $data['transactionTax'] = (float) number_format((float) $order->total_paid_tax_incl - $order->total_paid_tax_excl, 2);
        $data['transactionShipping'] = (float) $order->total_shipping_tax_excl;

        $productData = array();

        foreach ($products as $product) {
            $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);

            if ($product['product_attribute_id'] && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                $idProduct = $product['product_id'] . '-' . $product['product_attribute_id'];
            } else {
                $idProduct = $product['product_id'];
            }

            $product_item = array(
                "name" => $product['product_name'],
                "sku" => (string) $idProduct,
                "category" => $category->name,
                "quantity" => (int) $product['product_quantity'],
            );

            // If VAT included or not
            if ($withVat) {
                $product_item['price'] = (float) number_format((float) $product['unit_price_tax_incl'], 2);
            } else {
                $product_item['price'] = (float) number_format((float) $product['unit_price_tax_excl'], 2);
            }

            $productData[] = $product_item;
        }

        $data['transactionProducts'] = $productData;

        return json_encode($data);
    }
}
