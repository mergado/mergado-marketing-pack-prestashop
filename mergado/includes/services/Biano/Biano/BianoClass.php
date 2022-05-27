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

namespace Mergado\includes\services\Biano\Biano;

use Context;
use CurrencyCore;
use Link;
use Mergado;
use Mergado\includes\services\Biano\BianoStar\BianoStarServiceIntegration;
use Mergado\Tools\HelperClass;
use Mergado\Tools\LanguagesClass;
use Mergado\Tools\SettingsClass;
use Product;

class BianoClass
{

    const ACTIVE = 'biano_active';
    const MERCHANT_ID = 'biano_merchant_id';
    const FORM_ACTIVE = 'biano-form-active-lang';
    const CONVERSION_VAT_INCl = 'biano_conversion_vat_incl';
    const LANG_OPTIONS = ['CZ', 'SK', 'RO', 'NL', 'HU'];

    private $active;
    private $formActive;
    private $conversionVatIncluded;


    public function __construct()
    {
    }

    /*******************************************************************************************************************
     * BIANO ACTIVATION
     ******************************************************************************************************************/

    /**
     * Return if main biano is activated
     *
     * @param $shopId
     * @return bool
     */
    public function isActive($shopId)
    {
        $bianoActive = $this->getActive($shopId);

        if ($bianoActive === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return if biano language is activated
     *
     * @param $shopId
     * @param $langCode
     * @return bool
     */
    public function isLanguageActive($langCode, $shopId)
    {
        $active = $this->getLanguageActive($langCode, $shopId);
        $merchantId = $this->getMerchantId($langCode, $shopId);

        if ($active === Mergado\Tools\SettingsClass::ENABLED && $merchantId && $merchantId !== '') {
            return true;
        } else {
            return false;
        }
    }

    /*******************************************************************************************************************
     * GET DATA
     ******************************************************************************************************************/

    /**
     * Return data necessary for biano purchase event
     *
     * @param $orderId
     * @param $order
     * @param $email
     * @param $products
     * @param $shopId
     * @param $consent
     * @return false|string
     */
    public function getPurchaseData($orderId, $order, $email, $products, $shopId, $consent)
    {
        $data = [];

        $currency = new CurrencyCore($order->id_currency);

        $data['id'] = "$orderId";
        $data['currency'] = $currency->iso_code;

        // If user selected with or without VAT
        if ($this->getConversionVatIncluded($shopId)) {
            $data['order_price'] = (string) $order->total_products_wt;
        } else {
            $data['order_price'] = (string) $order->total_products;
        }

        $productData = [];

        foreach ($products as $product) {
            $product_item = [
                "id" => $product['product_id'] . '-' . $product['product_attribute_id'],
                "quantity" => (int) $product['product_quantity'],
            ];

            // If user selected with or without VAT
            if ($this->getConversionVatIncluded($shopId)) {
                $product_item['unit_price'] = (string) $product['unit_price_tax_incl'];
            } else {
                $product_item['unit_price'] = (string) $product['unit_price_tax_excl'];
            }

            $product_item['name'] = $product['product_name'];

            $productObject = new Product($product['id_product']);

            $link = new Link();
            $product_item['image'] = $link->getImageLink($productObject->link_rewrite[Context::getContext()->language->id], $product['image']->id_image);

            $productData[] = $product_item;
        }

        $data['items'] = $productData;

        // Biano star
        $bianoStarServiceIntegration = new BianoStarServiceIntegration();
        $shippingDate = 0;

        if ($bianoStarServiceIntegration->shouldBeSent($consent)) {
            $bianoStarService = $bianoStarServiceIntegration->getService();

            foreach($products as $product) {
                if ($product['product_attribute_id'] && $product['product_attribute_id'] !== '' && $product['product_attribute_id'] !== '0') {
                    $productStatus = HelperClass::getProductStockStatus($product['product_id'], $product['product_attribute_id']);
                } else {
                    $productStatus = HelperClass::getProductStockStatus($product['product_id']);
                }

                if ($productStatus === 'in stock') {
                    if ($shippingDate < $bianoStarService->getShipmentInStock()) {
                        $shippingDate = $bianoStarService->getShipmentInStock();
                    }
                } else if ($productStatus === 'out of stock') {
                    if ($shippingDate < $bianoStarService->getShipmentOutOfStock()) {
                        $shippingDate = $bianoStarService->getShipmentOutOfStock();
                    }
                } else if ($productStatus === 'preorder') {
                    if ($shippingDate < $bianoStarService->getShipmentBackorder()) {
                        $shippingDate = $bianoStarService->getShipmentBackorder();
                    }
                }

            }

            $data['customer_email'] = $email;
            $data['shipping_date'] = Date('Y-m-d', strtotime('+' . $shippingDate . ' days'));;
        }

        // Encode strings to numbers
        $data = json_decode(json_encode($data, JSON_NUMERIC_CHECK), true);

        // Change id to String because Biano needs that
        $data['id'] = (string)$data['id'];

        return json_encode($data);
    }


    /*******************************************************************************************************************
     * GET VALUES
     *******************************************************************************************************************/

     /**
     * @param $shopId
     * @return false|string|null
     */
    public function getActive($shopId)
    {
        if (!is_null($this->active)) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(self::ACTIVE, $shopId);

        return $this->active;
    }

    /**
     * Return language active field value
     *
     * @param $langCode
     * @param $shopId
     * @return false|string|null
     */
    public function getLanguageActive($langCode, $shopId)
    {
        $name = self::getActiveLangFieldName($langCode);

        return Mergado\Tools\SettingsClass::getSettings($name, $shopId);
    }

    /**
     * @param $langCode
     * @param $shopId
     * @return false|string|null
     */
    public function getMerchantId($langCode, $shopId)
    {
        $name = self::getMerchantIdFieldName($langCode);

        return SettingsClass::getSettings($name, $shopId);
    }

    /**
     * @param $shopId
     * @return false|string|null
     */
    public function getFormActive($shopId)
    {
        if (!is_null($this->formActive)) {
            return $this->formActive;
        }

        $this->formActive = SettingsClass::getSettings(self::FORM_ACTIVE, $shopId);

        return $this->formActive;
    }

    /**
     * @param $shopId
     * @return false|string|null
     */
    public function getConversionVatIncluded($shopId)
    {
        if (!is_null($this->conversionVatIncluded)) {
            return $this->conversionVatIncluded;
        }

        $this->conversionVatIncluded = SettingsClass::getSettings(self::CONVERSION_VAT_INCl, $shopId);

        return $this->conversionVatIncluded;
    }

    /*******************************************************************************************************************
     * GET VALUES - STATIC
     ******************************************************************************************************************/


    /**
     * Return name for biano language field
     * @param $langCode - CZ/SK etc.
     * @return string
     */
    public static function getActiveLangFieldName($langCode)
    {
        return self::FORM_ACTIVE . '-' . $langCode;
    }

    /**
     * Return name for merchant language field
     * @param $langCode - CZ/SK etc.
     * @return string
     */
    public static function getMerchantIdFieldName($langCode)
    {
        return self::MERCHANT_ID . '-' . $langCode;
    }


    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    /**
     * @param $languages
     * @return \array[][]
     */

    public static function getToggleFields($languages)
    {
        $bianoFields = [];
        $bianoMainFields = [];

        foreach($languages as $lang) {
            $langName = LanguagesClass::getLangIso(strtoupper($lang['iso_code']));

            //Get names for language
            $langFieldName = self::getActiveLangFieldName($langName);
            $merchantIdFieldName = self::getMerchantIdFieldName($langName);

            //Asign to arrays
            $bianoMainFields[] = self::getActiveLangFieldName($langName);
            $bianoMainFields[] = self::CONVERSION_VAT_INCl;
            $bianoFields[$langFieldName]['fields'] = [$merchantIdFieldName];
        }

        return [
            self::ACTIVE => [
                'fields' => $bianoMainFields,
                'sub-check' => $bianoFields,
            ],
        ];
    }
}

