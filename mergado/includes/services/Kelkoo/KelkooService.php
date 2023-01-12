<?php
declare(strict_types=1);

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

namespace Mergado\includes\services\Kelkoo;

use Mergado;
use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\SettingsClass;

class KelkooService
{
    const ACTIVE = 'kelkoo_active';
    const COM_ID = 'kelkoo_merchant_id';
    const COUNTRY = 'kelkoo_country';
    const CONVERSION_VAT_INCL = 'kelkoo_conversion_vat_incl';

    const COUNTRIES = [
        ['id_option' => 1, 'name' => 'Austria', 'type_code' => 'at'],
        ['id_option' => 2, 'name' => 'Belgium', 'type_code' => 'be'],
        ['id_option' => 3, 'name' => 'Brazil', 'type_code' => 'br'],
        ['id_option' => 4, 'name' => 'Switzerland', 'type_code' => 'ch'],
        ['id_option' => 5, 'name' => 'Czech Republic', 'type_code' => 'cz'],
        ['id_option' => 6, 'name' => 'Germany', 'type_code' => 'de'],
        ['id_option' => 7, 'name' => 'Denmark', 'type_code' => 'dk'],
        ['id_option' => 8, 'name' => 'Spain', 'type_code' => 'es'],
        ['id_option' => 9, 'name' => 'Finland', 'type_code' => 'fi'],
        ['id_option' => 10, 'name' => 'France', 'type_code' => 'fr'],
        ['id_option' => 11, 'name' => 'Ireland', 'type_code' => 'ie'],
        ['id_option' => 12, 'name' => 'Italy', 'type_code' => 'it'],
        ['id_option' => 13, 'name' => 'Mexico', 'type_code' => 'mx'],
        ['id_option' => 14, 'name' => 'Flemish Belgium', 'type_code' => 'nb'],
        ['id_option' => 15, 'name' => 'Netherlands', 'type_code' => 'nl'],
        ['id_option' => 16, 'name' => 'Norway', 'type_code' => 'no'],
        ['id_option' => 17, 'name' => 'Poland', 'type_code' => 'pl'],
        ['id_option' => 18, 'name' => 'Portugal', 'type_code' => 'pt'],
        ['id_option' => 19, 'name' => 'Russia', 'type_code' => 'ru'],
        ['id_option' => 20, 'name' => 'Sweden', 'type_code' => 'se'],
        ['id_option' => 21, 'name' => 'United Kingdom', 'type_code' => 'uk'],
        ['id_option' => 22, 'name' => 'United States', 'type_code' => 'us'],
    ];

    private $active;
    private $comId;
    private $country;
    private $conversionVatIncluded;

    private $multistoreShopId;

    use SingletonTrait;

    protected function __construct()
    {
        $this->multistoreShopId = Mergado::getShopId();
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        $kelkoo_active = $this->getActive();
        $kelkoo_country = $this->getCountry();
        $kelkoo_merchant_id = $this->getComId();

        if ($kelkoo_active === SettingsClass::ENABLED && $kelkoo_country && $kelkoo_country !== '' && $kelkoo_merchant_id && $kelkoo_merchant_id !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return active language options for Kelkoo
     * @return false|mixed
     */
    public function getActiveDomain()
    {
        $activeLangId = $this->getCountry();

        foreach(self::COUNTRIES as $item) {
            if($item['id_option'] === (int)$activeLangId) {
                return $item['type_code'];
            }
        }

        return false;
    }

    /**
     * Return necessary data for Kelkoo tracking
     * @param $orderId
     * @param $order
     * @param $products
     * @return array
     */

    public function getOrderData($orderId, $order, $products) : array {
        $productsKelkoo = [];

        // If VAT included or not
        if ($this->getConversionVatIncluded()) {
            $orderTotal = (float) $order->total_products_wt;
        } else {
            $orderTotal = (float) $order->total_products;
        }

        //Same for 1.6 and 1.7
        foreach ($products as $product) {
            $productKelkoo = [
                'productname' => $product['product_name'],
                'productid' => $product['product_reference'],
                'quantity' => $product['product_quantity'],
            ];

            // If VAT included or not
            if ($this->getConversionVatIncluded()) {
                $productKelkoo['price'] = $product['unit_price_tax_incl'];
            } else {
                $productKelkoo['price'] = $product['unit_price_tax_excl'];
            }

            $productsKelkoo[] = $productKelkoo;
        }

        return [
            'IS_PS_17' => _PS_VERSION_ >= Mergado::PS_V_17 ? true : false,
            'productsJson' => json_encode($productsKelkoo),
            'sales' => $orderTotal,
            'orderId' => $orderId,
            'country' => $this->getActiveDomain(),
            'merchantId' => $this->getComId(),
        ];
    }

    /*******************************************************************************************************************
     * GET FIELD
     ******************************************************************************************************************/

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
     * @return false|string|null
     */
    public function getComId()
    {
        if (!is_null($this->comId)) {
            return $this->comId;
        }

        $this->comId = SettingsClass::getSettings(self::COM_ID, $this->multistoreShopId);

        return $this->comId;
    }

    /**
     * @return false|string|null
     */
    public function getCountry()
    {
        if (!is_null($this->country)) {
            return $this->country;
        }

        $this->country = SettingsClass::getSettings(self::COUNTRY, $this->multistoreShopId);

        return $this->country;
    }

    /**
     * @return false|string|null
     */
    public function getConversionVatIncluded()
    {
        if (!is_null($this->conversionVatIncluded)) {
            return $this->conversionVatIncluded;
        }

        $this->conversionVatIncluded = SettingsClass::getSettings(self::CONVERSION_VAT_INCL, $this->multistoreShopId);

        return $this->conversionVatIncluded;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    /**
     * @return string[][][]
     */
    public static function getToggleFields() : array
    {
        return [
            self::ACTIVE => [
                'fields' => [
                    self::COUNTRY,
                    self::COM_ID,
                    self::CONVERSION_VAT_INCL
                ]
            ]
        ];
    }
}
