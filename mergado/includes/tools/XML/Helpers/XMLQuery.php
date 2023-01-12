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

namespace Mergado\Tools\XML;

use ConfigurationCore as Configuration;
use CurrencyCore as Currency;
use Mergado\Tools\HelperClass;
use Mergado\Tools\SettingsClass;
use PrestaShop\PrestaShop\Adapter\Entity\Customization;
use PrestaShop\PrestaShop\Adapter\Entity\Pack;
use ProductCore as Product;
use CategoryCore as Category;
use ManufacturerCore as Manufacturer;
use LinkCore as Link;
use SpecificPrice;
use TagCore as Tag;
use ValidateCore as Validate;
use CombinationCore as Combination;
use ShopCore as Shop;
use TaxCore as Tax;
use GroupCore as Group;
use Address;
use TaxManagerFactoryCore as TaxManagerFactory;
use ContextCore as Context;
use StockAvailableCore as StockAvailable;
use ImageCore as Image;
use Db as Db;
use ToolsCore as Tools;
use ObjectModel;
use Mergado;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

//TODO: Make me real object

class XMLQuery extends ObjectModel
{
    protected $currency;
    protected $shopID;
    protected $defaultLang;
    protected $defaultCurrency;

    public function __construct($currency = null)
    {
        if ($currency === null) {
            $this->currency = new Currency();
        } else {
            $this->currency = $currency;
        }

        $this->defaultLang = Configuration::get('PS_LANG_DEFAULT');
        $this->defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $this->shopID = Mergado::getShopId();
    }

    /*******************************************************************************************************************
     * XML GENERATORS
     *******************************************************************************************************************/

    /*******************************************************************************************************************
     * GET PRODUCTS INFO
     *******************************************************************************************************************/

    /**
     * @param $start
     * @param $limit
     * @param bool $lang
     * @return array
     */
    public function productsToFlat($start, $limit, $lang = false, $export_out_of_stock = false)
    {
        $flatProductList = [];

        if (!$lang) {
            $lang = $this->defaultLang;
        }

        $productsList = Product::getProducts($lang, $start, $limit, 'id_product', 'ASC', false, true);

        $export_both = SettingsClass::getSettings(SettingsClass::EXPORT['BOTH'], $this->shopID);
        $export_none = SettingsClass::getSettings(SettingsClass::EXPORT['NONE'], $this->shopID);
        $export_catalog = SettingsClass::getSettings(SettingsClass::EXPORT['CATALOG'], $this->shopID);
        $export_search = SettingsClass::getSettings(SettingsClass::EXPORT['SEARCH'], $this->shopID);
        $export_cost = SettingsClass::getSettings(SettingsClass::EXPORT['COST'], $this->shopID);

        foreach ($productsList as $productCore) {
            $product = new Product($productCore['id_product']);

            $export = false;

            if ($product->visibility === 'catalog' && ($export_catalog === 'on' || $export_catalog == 1)) {
                $export = true;
            }

            if ($product->visibility === 'search' && ($export_search === 'on' || $export_search == 1)) {
                $export = true;
            }

            if ($product->visibility === 'both' && ($export_both === 'on' || $export_both == 1)) {
                $export = true;
            }

            if ($product->visibility === 'none' && ($export_none == 'on' || $export_none == 1)) {
                $export = true;
            }

            if (!(bool)$product->show_price) {
                $export = false;
            }

            if (!$export) {
                continue;
            }

            $base = $this->productBase($product, $lang, $export_cost, $export_out_of_stock);

            if (array_key_exists('item_id', $base)) {
                $flatProductList[] = $base;
            } else {
                foreach ($base as $combination) {
                    $flatProductList[] = $combination;
                }
            }
        }

        return $flatProductList;
    }

    /**
     * @param $item
     * @param $lang
     * @return array|null
     */
    public function productBase($item, $lang, $export_cost = true, $export_out_of_stock = false)
    {
        $accessories = Product::getAccessoriesLight($lang, $item->id);
        $accessoriesExtended = [];
        if (!empty($accessories)) {
            foreach ($accessories as $accessory) {
                $accessoryTmp = [];
                if (!empty($accessoryTmp)) {
                    foreach ($accessoryTmp as $at) {
                        $accessoriesExtended[] = $at['id_product'] . '-' . $at['id_product_attribute'];
                    }
                } else {
                    $accessoriesExtended[] = $accessory['id_product'];
                }
            }
        }

        $category = '';
        $cat_iter_id = $item->id_category_default;

        do {
            $cat_tmp = new Category($cat_iter_id, $lang);
            $category = $cat_tmp->name . ' | ' . $category;
            $cat_iter_id = $cat_tmp->id_parent;
        } while ($cat_iter_id != null && $cat_iter_id != Configuration::get('PS_ROOT_CATEGORY') && $cat_iter_id != Configuration::get('PS_HOME_CATEGORY'));
        $category = Tools::substr($category, 0, -3);

        $combinations = $this->getProductCombination($item, $lang);
        $featuresTemp = Product::getFrontFeaturesStatic($lang, $item->id);
        $features = [];

        foreach ($featuresTemp as $feature) {
            $features[] = [
                'name' => $feature['name'],
                'value' => $feature['value'],
            ];
        }

        $manufacturer = new Manufacturer($item->id_manufacturer);

        $link = new Link();

        $mainImage = null;
        $id_country = Configuration::get('PS_COUNTRY_DEFAULT');

        $address = new Address();
        $address->id_country = $id_country;

        $tax_manager = TaxManagerFactory::getManager(
            $address, Product::getIdTaxRulesGroupByIdProduct((int)$item->id, null)
        );

        $tax_calculator = $tax_manager->getTaxCalculator();

        $context = Context::getContext();

        $productBase = null;
        $defaultCategory = new Category($item->id_category_default, $lang);


        $defaultCategoryName = $defaultCategory->name;
        if (_PS_VERSION_ >= Mergado::PS_V_17) {
            $defaultCategoryName = Tools::str2url($defaultCategoryName);
        }
        $itemgroupBase = $item->id;

        $whenOutOfStock = HelperClass::getStockStatusLogic($item->id);

        $productTags = Tag::getProductTags($item->id);

        if (isset($productTags[intval($lang)])) {
            $productTags = $productTags[intval($lang)];
        } else {
            $productTags = false;
        }

        if (!empty($combinations)) {
            foreach ($combinations as $combination) {

                $mainImage = null;
                $qty = Product::getQuantity($combination['id_product'], $combination['id_product_attribute']);

                if ($qty <= 0 && $whenOutOfStock == 0 && !$export_out_of_stock) {
                    continue;
                }

                $img = new Image();
                $imagesList = $img->getImages($lang, $combination['id_product'], $combination['id_product_attribute']);
                if (empty($imagesList)) {
                    $imagesList = $img->getImages($lang, $combination['id_product']);
                }

                $images = [];
                foreach ($imagesList as $imgx) {
                    $images[] = 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' .
                        $link->getImageLink($item->link_rewrite[$lang], $item->id . '-' . $imgx['id_image']);

                    if ($imgx['cover'] != null) {
                        $mainImage = 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' .
                            $link->getImageLink($item->link_rewrite[$lang], $item->id . '-' . $imgx['id_image']);
                    }
                }

                $specific_price = null;
                $price_vat = $this->getProductPrice(
                    $id_country,
                    $specific_price,
                    $context->shop->id,
                    $combination['id_product'],
                    $combination['id_product_attribute'],
                    true
                );

                $price_novat = $this->getProductPrice(
                    $id_country,
                    $specific_price,
                    $context->shop->id,
                    $combination['id_product'],
                    $combination['id_product_attribute'],
                    false
                );

                $discount_price_vat = $this->getProductDiscountPrice(
                    $id_country,
                    $specific_price,
                    $context->shop->id,
                    $combination['id_product'],
                    $combination['id_product_attribute'],
                    true
                );

                $discount_price_novat = $this->getProductDiscountPrice(
                    $id_country,
                    $specific_price,
                    $context->shop->id,
                    $combination['id_product'],
                    $combination['id_product_attribute'],
                    false
                );

                $saleDateInterval = $this->getSaleDateInterval($id_country, $context->shop->id, $combination['id_product'], $combination['id_product_attribute']);

                // If no discount don't create elements
                if ($discount_price_novat == $price_novat) {
                    $discount_price_vat = null;
                    $discount_price_novat = null;
                }

                $cost = null;
                $cost_vat = null;

                if ($export_cost) {
                    $cost = $combination['wholesale_price'] != 0 ? $combination['wholesale_price'] : $item->wholesale_price;
                    $cost_vat = $this->getProductWholesalePriceWithVat($combination['id_product'], $cost, $id_country, $this->shopID, $context);

                    $cost = $cost == 0 ? null : $cost;
                    $cost_vat = $cost_vat == 0 ? null : $cost_vat;
                }

                $params = [];

                foreach ($combination['attrs'] as $key => $value) {
                    $params[] = [
                        'name' => $key,
                        'value' => $value,
                    ];
                }

                $params = array_merge($params, $features);
                $itemgroup = [];
                foreach ($combinations as $g) {
                    if ($g['id_product_attribute'] != $combination['id_product_attribute']) {
                        $itemgroup[] = $g['id_product'] . '-' . $g['id_product_attribute'];
                    }
                }

                //$price = ToolsCore::convertPriceFull($price, $this->defaultCurrency, $this->currency);
                if ($mainImage === NULL) {
                    $mainImage = isset($images[0]) ? $images[0] : null;
                }

                $images = array_diff($images, [$mainImage]);

                //Change context lang cause of 1.7 generating link from context lang
                $originalLangId = Context::getContext()->language->id;
                Context::getContext()->language->id = $lang;

                if (isset($item->mpn) || isset($combination['mpn'])) {  // only from version PS 1.7.7.0+ but check if not manually exist
                    if (isset($combination['mpn']) && $combination['mpn'] == "") {
                        if (isset($item->mpn)) {
                            $mpn = $item->mpn;
                        } else {
                            $mpn = "";
                        }
                    }
                } else {
                    $mpn = '';
                }

                $productBase[] = [
                    'item_id' => $combination['id_product'] . '-' . $combination['id_product_attribute'],
                    'itemgroup_id' => $itemgroupBase,
                    'accessory' => $accessoriesExtended,
                    'availability' => HelperClass::getProductStockStatus($combination['id_product'], $combination['id_product_attribute']),
                    'stock_quantity' => $qty,
                    'category' => $category,
                    'condition' => $item->condition,
                    'delivery_days' => $this->getProductDeliveryDays($combination, $qty),
                    'description_short' => strip_tags($item->description_short[$lang]),
                    'description' => strip_tags($item->description[$lang]),
                    'mpn' => $mpn,
                    'ean' => ($combination['ean13'] == "" ? $item->ean13 : $combination['ean13']),
                    'reference' => ($combination['reference'] == "" ? $item->reference : $combination['reference']),
                    'image' => $mainImage,
                    'image_alternative' => $images,
                    'name_exact' => $combination['name'],
                    'params' => $params,
//                    'catalog_visibility' => ,
                    'producer' => $manufacturer->name,
                    'url' => $link->getProductLink($item->id, null, null, null, $lang, null, $combination['id_product_attribute'], false, false, true),
//                    'url' => $link->getProductLink($item->id, null, $defaultCategoryName, null, $lang, null, $combination['id_product_attribute'], false, false, true),
//                    'url' => $link->getProductLink($item, null, $defaultCategoryName, null, $lang, null, $combination['id_product_attribute'], false, false, true),
                    'price' => Tools::ps_round($price_novat, 2),
                    'price_vat' => Tools::ps_round($price_vat, 2),
                    'discount_price' => Tools::ps_round($discount_price_novat, 2),
                    'discount_price_vat' => Tools::ps_round($discount_price_vat, 2),
                    'sale_price_effective_date' => $saleDateInterval,
                    'cost' => $cost == null ? '' : $cost,
                    'cost_vat' => $cost_vat == null ? '' : $cost_vat,
                    'wholesale_price' => $combination['wholesale_price'] != 0 ? $combination['wholesale_price'] : $item->wholesale_price,
                    'shipping_size' => ($item->depth != 0 && $item->width != 0 && $item->height != 0) ? ($item->depth . ' x ' . $item->width . ' x ' . $item->height . ' ' . Configuration::get('PS_DIMENSION_UNIT')) : false,
                    'shipping_weight' => (($item->weight + $combination['weight']) != 0) ? ($item->weight + $combination['weight']) . ' ' . Configuration::get('PS_WEIGHT_UNIT') : false,
                    'vat' => $this->getVat($tax_calculator),
                    'catalog_visibility' => $visibility = $item->visibility,
                    'tags' => $productTags
                ];

                //Return original lang id
                Context::getContext()->language->id = $originalLangId;
            }
        } else {
            $qty = Product::getQuantity($item->id);

            if ($qty <= 0 && $whenOutOfStock == 0 && !$export_out_of_stock) {
                // skip
            } else {
                $imagesList = $item->getImages($lang);
                $images = [];
                foreach ($imagesList as $img) {
                    $images[] = 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' .
                        $link->getImageLink($item->link_rewrite[$lang], $item->id . '-' . $img['id_image']);
                    if ($img['cover'] != null) {
                        $mainImage = 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' .
                            $link->getImageLink($item->link_rewrite[$lang], $item->id . '-' . $img['id_image']);
                    }
                }

                $specific_price = null;
                $price_vat = $this->getProductPrice(
                    $id_country,
                    $specific_price,
                    $context->shop->id,
                    $item->id,
                    null,
                    true
                );

                $price_novat = $this->getProductPrice(
                    $id_country,
                    $specific_price,
                    $context->shop->id,
                    $item->id,
                    null,
                    false
                );

                $discount_price_vat = $this->getProductDiscountPrice(
                    $id_country,
                    $specific_price,
                    $context->shop->id,
                    $item->id,
                    null,
                    true
                );

                $discount_price_novat = $this->getProductDiscountPrice(
                    $id_country,
                    $specific_price,
                    $context->shop->id,
                    $item->id,
                    null,
                    false
                );

                $saleDateInterval = $this->getSaleDateInterval($id_country, $context->shop->id, $item->id, null);

                // If no discount don't create elements
                if ($discount_price_novat == $price_novat) {
                    $discount_price_vat = null;
                    $discount_price_novat = null;
                }

                $cost = null;
                $cost_vat = null;

                if ($export_cost) {
                    $cost = $item->wholesale_price;
                    $cost_vat = $this->getProductWholesalePriceWithVat($item->id, $cost, $id_country, $this->shopID, $context);

                    $cost = $cost == 0 ? null : $cost;
                    $cost_vat = $cost_vat == 0 ? null : $cost_vat;
                }

                $images = array_diff($images, [$mainImage]);

                //Change context lang cause of 1.7 context lang link creating
                $originalLangId = Context::getContext()->language->id;
                Context::getContext()->language->id = $lang;

                if (isset($item->mpn)) {  // only from version PS 1.7.7.0+ but check if not manually exist
                    $mpn = $item->mpn;
                } else {
                    $mpn = '';
                }

                $productBase = [
                    'item_id' => $item->id,
                    'itemgroup_id' => $itemgroupBase,
                    'accessory' => $accessoriesExtended,
                    'availability' => HelperClass::getProductStockStatus($item->id),
                    'stock_quantity' => Product::getQuantity($item->id),
                    'category' => $category,
                    'condition' => $item->condition,
                    'delivery_days' => $this->getProductDeliveryDays($item, $qty),
                    'description_short' => strip_tags($item->description_short[$lang]),
                    'description' => strip_tags($item->description[$lang]),
                    'mpn' => $mpn,
                    'ean' => $item->ean13,
                    'reference' => $item->reference,
                    'image' => $mainImage,
                    'image_alternative' => $images,
                    'name_exact' => $item->name[$lang],
                    'params' => $features,
                    'producer' => $manufacturer->name,
                    'url' => $link->getProductLink($item, null, $defaultCategoryName, null, $lang, null),
                    'price' => Tools::ps_round($price_novat, 2),
                    'price_vat' => Tools::ps_round($price_vat, 2),
                    'discount_price' => Tools::ps_round($discount_price_novat, 2),
                    'discount_price_vat' => Tools::ps_round($discount_price_vat, 2),
                    'sale_price_effective_date' => $saleDateInterval,
                    'cost' => $cost == null ? '' : $cost,
                    'cost_vat' => $cost_vat == null ? '' : $cost_vat,
                    'wholesale_price' => $item->wholesale_price,
                    'shipping_size' => ($item->depth != 0 || $item->width != 0 || $item->height != 0) ? ($item->depth . ' x ' . $item->width . ' x ' . $item->height . ' ' . Configuration::get('PS_DIMENSION_UNIT')) : false,
                    'shipping_weight' => ($item->weight != 0) ? ($item->weight . ' ' . Configuration::get('PS_WEIGHT_UNIT')) : false,
                    'vat' => $this->getVat($tax_calculator),
                    'tags' => $productTags,
                    'catalog_visibility' => $visibility = $item->visibility
                ];

                //Return original lang id
                Context::getContext()->language->id = $originalLangId;
            }
        }

        return $productBase;
    }

    public function getVat($tax_calculator) {
        if ($tax_calculator && isset($tax_calculator->taxes) && isset($tax_calculator->taxes[0])) {
            return $tax_calculator->taxes[0]->rate;
        } else {
            return '';
        }
    }

    // TODO: WHAT IS THIS<<????
    public function getProductWholesalePriceWithVat($id, $wholesale_price, $id_country, $shopId, $context)
    {
        //Count product cost
//        if(SettingsClass::getSettings(SettingsClass::EXPORT['COST'],  $shopId)) {
//            if($wholesale_price != 0) {
//                // Tax
//                $costAddress = new Address();
//                $costAddress->id_country = $id_country;
//                $costAddress->id_state = 0;
//                $costAddress->postcode = 0;
//
//                $tax_manager = TaxManagerFactory::getManager($costAddress, Product::getIdTaxRulesGroupByIdProduct((int) $id, $context));
//                $product_tax_calculator = $tax_manager->getTaxCalculator();
//
//                // Add Tax
//                return $product_tax_calculator->addTaxes($wholesale_price);
//            }
//        }

        return null;
    }

    /**
     * @param $product
     * @param $lang
     * @return array
     */
    public function getProductCombination($product, $lang)
    {
        $groups = [];
        $comb_array = [];
        $flatProductList = [];

        $combinations = $product->getAttributeCombinations($lang);

        if (is_array($combinations) && count($combinations) > 0) {
            if (is_array($combinations)) {
                foreach ($combinations as $combination) {

                    if (isset($combination['mpn'])) {  // only from version PS 1.7.7.0+ but check if not manually exist
                        $mpn = $combination['mpn'];
                    } else {
                        $mpn = '';
                    }

                    $comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                    $comb_array[$combination['id_product_attribute']]['unit_price_impact'] = $combination['unit_price_impact'];
                    $comb_array[$combination['id_product_attribute']]['price'] = $combination['price'];
                    $comb_array[$combination['id_product_attribute']]['minimal_quantity'] = $combination['minimal_quantity'];
                    $comb_array[$combination['id_product_attribute']]['quantity'] = $combination['quantity'];
                    $comb_array[$combination['id_product_attribute']]['ecotax'] = $combination['ecotax'];
                    $comb_array[$combination['id_product_attribute']]['mpn'] = $mpn;
                    $comb_array[$combination['id_product_attribute']]['ean13'] = $combination['ean13'];
                    $comb_array[$combination['id_product_attribute']]['weight'] = $combination['weight'];
                    $comb_array[$combination['id_product_attribute']]['reference'] = $combination['reference'];
                    $comb_array[$combination['id_product_attribute']]['id_shop'] = $combination['id_shop'];
                    $comb_array[$combination['id_product_attribute']]['wholesale_price'] = $combination['wholesale_price'];
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = [
                        $combination['group_name'],
                        $combination['attribute_name'],
                        $combination['id_attribute']
                    ];
                    if ($combination['is_color_group']) {
                        $groups[$combination['id_attribute_group']] = $combination['group_name'];
                    }
                }
            }


            if (isset($comb_array)) {
                foreach ($comb_array as $id_product_attribute => $product_attribute) {
                    $list = '';
                    $attrs = [];

                    asort($product_attribute['attributes']);

                    foreach ($product_attribute['attributes'] as $attribute) {
                        $list .= $attribute[1] . ' ';
                        $attrs[$attribute[0]] = $attribute[1];
                    }


                    $list = rtrim($list, ', ');

                    $comb_array[$id_product_attribute]['attributes'] = $list;
                    $comb_array[$id_product_attribute]['name'] = $list;
                    $flatProductList[] = [
                        'id_product_attribute' => $id_product_attribute,
                        'id_product' => $combination['id_product'],
                        'name' => $product->name[$lang] . ': ' . $list,
                        'ean13' => $comb_array[$id_product_attribute]['ean13'],
                        'mpn' => $comb_array[$id_product_attribute]['mpn'],
                        'reference' => $comb_array[$id_product_attribute]['reference'],
                        'price' => $comb_array[$id_product_attribute]['price'],
                        'ecotax' => $comb_array[$id_product_attribute]['ecotax'],
                        'quantity' => $comb_array[$id_product_attribute]['quantity'],
                        'weight' => $comb_array[$id_product_attribute]['weight'],
                        'unit_price_impact' => $comb_array[$id_product_attribute]['unit_price_impact'],
                        'minimal_quantity' => $comb_array[$id_product_attribute]['minimal_quantity'],
                        'wholesale_price' => $comb_array[$id_product_attribute]['wholesale_price'],
                        'id_shop' => $comb_array[$id_product_attribute]['id_shop'],
                        'attrs' => $attrs
                    ];
                }
            }
        }

        return $flatProductList;
    }

    /**
     * @param $category
     * @param $id_lang
     * @param $p
     * @param $n
     * @param null $order_by
     * @param null $order_way
     * @param bool $get_total
     * @param bool $active
     * @param bool $random
     * @param int $random_number_products
     * @param Context|null $context
     * @return array|int
     */
    public function getProducts($category, $id_lang, $p, $n, $order_by = null, $order_way = null, $get_total = false,
                                $active = true, $random = false, $random_number_products = 1, Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $id_supplier = (int)Tools::getValue('id_supplier');

        /** Return only the number of products */
        if ($get_total) {
            $sql = 'SELECT COUNT(cp.`id_product`) AS total
					FROM `' . _DB_PREFIX_ . 'product` p
					' . Shop::addSqlAssociation('product', 'p') . '
					LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON p.`id_product` = cp.`id_product`
					WHERE cp.`id_category` = ' . (int)$this->id .
                ($active ? ' AND product_shop.`active` = 1' : '') .
                ($id_supplier ? 'AND p.id_supplier = ' . (int)$id_supplier : '');

            return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        if ($p < 1) {
            $p = 1;
        }

        /** Tools::strtolower is a fix for all modules which are now using lowercase values for 'orderBy' parameter */
        $order_by = Validate::isOrderBy($order_by) ? Tools::strtolower($order_by) : 'position';
        $order_way = Validate::isOrderWay($order_way) ? Tools::strtoupper($order_way) : 'ASC';

        $order_by_prefix = false;
        if ($order_by == 'id_product' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        } elseif ($order_by == 'manufacturer' || $order_by == 'manufacturer_name') {
            $order_by_prefix = 'm';
            $order_by = 'name';
        } elseif ($order_by == 'position') {
            $order_by_prefix = 'cp';
        }

        if ($order_by == 'price') {
            $order_by = 'orderprice';
        }

        $nb_days_new_product = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        if (!Validate::isUnsignedInt($nb_days_new_product)) {
            $nb_days_new_product = 20;
        }

        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) AS quantity' . (Combination::isFeatureActive() ? ', IFNULL(product_attribute_shop.id_product_attribute, 0) AS id_product_attribute,
					product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity' : '') . ', pl.`description`, pl.`description_short`, pl.`available_now`,
					pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`,';

        if (_PS_VERSION_ >= Mergado::PS_V_16) {
            $sql .= 'image_shop.`id_image` id_image,il.`legend` as legend,';
        }

        $sql .= 'm.`name` AS manufacturer_name, cl.`name` AS category_default,
					DATEDIFF(product_shop.`date_add`, DATE_SUB("' . date('Y-m-d') . ' 00:00:00",
					INTERVAL ' . (int)$nb_days_new_product . ' DAY)) > 0 AS new, product_shop.price AS orderprice
				FROM `' . _DB_PREFIX_ . 'category_product` cp
				LEFT JOIN `' . _DB_PREFIX_ . 'product` p
					ON p.`id_product` = cp.`id_product`
				' . Shop::addSqlAssociation('product', 'p');

        if (_PS_VERSION_ < Mergado::PS_V_16) {
            $sql .= (Combination::isFeatureActive() ? ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop ON (product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int)$context->shop->id . ')' : '') . '
				' . Product::sqlStock('p', 0);
        } else {
            $sql .= (Combination::isFeatureActive() ? ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int)$context->shop->id . ')' : '') . '
				' . Product::sqlStock('p', 0);
        }

        $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
					ON (product_shop.`id_category_default` = cl.`id_category`
					AND cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
					ON (p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . ')';

        if (_PS_VERSION_ >= Mergado::PS_V_16) {
            $sql .= 'LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
					ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int)$context->shop->id . ') 
					LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$id_lang . ')';
        }


        $sql .= 'LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m
					ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE product_shop.`id_shop` = ' . (int)$context->shop->id . '
					AND cp.`id_category` = ' . (int)$category->id
            . ($active ? ' AND product_shop.`active` = 1' : '')
            . ($id_supplier ? ' AND p.id_supplier = ' . (int)$id_supplier : '');

        if ($random === true) {
            $sql .= ' ORDER BY RAND() LIMIT ' . (int)$random_number_products;
        } else {
            $sql .= ' ORDER BY ' . (!empty($order_by_prefix) ? $order_by_prefix . '.' : '') . '`' . bqSQL($order_by) . '` ' . pSQL($order_way) . '
			LIMIT ' . (((int)$p - 1) * (int)$n) . ',' . (int)$n;
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        if (!$result) {
            return [];
        }

        if ($order_by == 'orderprice') {
            Tools::orderbyPrice($result, $order_way);
        }

        /** Modify SQL result */
        return $this->getProductsProperties($id_lang, $result);
    }

    /**
     * @param $id_lang
     * @param $query_result
     * @return array
     */
    private function getProductsProperties($id_lang, $query_result)
    {
        $results_array = [];

        if (is_array($query_result)) {
            foreach ($query_result as $row) {
                if ($row2 = $this->getProductProperties($id_lang, $row)) {
                    $results_array[] = $row2;
                }
            }
        }

        return $results_array;
    }

    /**
     * @param $id_lang
     * @param $row
     * @param Context|null $context
     * @return bool
     */
    private function getProductProperties($id_lang, $row, Context $context = null)
    {
        if (!$row['id_product']) {
            return false;
        }

        if ($context == null) {
            $context = Context::getContext();
        }

        $id_product_attribute = $row['id_product_attribute'] = (!empty($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null);

        // Product::getDefaultAttribute is only called if id_product_attribute is missing from the SQL query at the origin of it:
        // consider adding it in order to avoid unnecessary queries
        $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
        if (Combination::isFeatureActive() && $id_product_attribute === null && ((isset($row['cache_default_attribute']) && ($ipa_default = $row['cache_default_attribute']) !== null) || ($ipa_default = Product::getDefaultAttribute($row['id_product'], !$row['allow_oosp'])))) {
            $id_product_attribute = $row['id_product_attribute'] = $ipa_default;
        }
        if (!Combination::isFeatureActive() || !isset($row['id_product_attribute'])) {
            $id_product_attribute = $row['id_product_attribute'] = 0;
        }

        // Tax
        $usetax = Tax::excludeTaxeOption();

        $cache_key = $row['id_product'] . '-' . $id_product_attribute . '-' . $id_lang . '-' . (int)$usetax;

        // Datas
        $row['category'] = Category::getLinkRewrite((int)$row['id_category_default'], (int)$id_lang);
        $row['link'] = $context->link->getProductLink((int)$row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);

        $row['attribute_price'] = 0;
        if ($id_product_attribute) {

            if (_PS_VERSION_ < Mergado::PS_V_17) {
                $row['attribute_price'] = (float)Product::getProductAttributePrice($id_product_attribute);
            } else {
                $row['attribute_price'] = (float)Combination::getPrice($id_product_attribute);
            }
        }

        $row['price_tax_exc'] = $this->getPriceStatic(
            (int)$row['id_product'], false, $id_product_attribute, (Product::$_taxCalculationMethod == PS_TAX_EXC ? 2 : 6)
        );

        if (Product::$_taxCalculationMethod == PS_TAX_EXC) {
            $row['price_tax_exc'] = Tools::ps_round($row['price_tax_exc'], 2);
            $row['price'] = $this->getPriceStatic(
                (int)$row['id_product'], true, $id_product_attribute, 6
            );
            $row['price_without_reduction'] = $this->getPriceStatic(
                (int)$row['id_product'], false, $id_product_attribute, 2, null, false, false
            );
        } else {
            $row['price'] = Tools::ps_round(
                $this->getPriceStatic(
                    (int)$row['id_product'], true, $id_product_attribute, 6
                ), 2
            );
            $row['price_without_reduction'] = $this->getPriceStatic(
                (int)$row['id_product'], true, $id_product_attribute, 6, null, false, false
            );
        }

        $row['reduction'] = $this->getPriceStatic(
            (int)$row['id_product'], (bool)$usetax, $id_product_attribute, 6, null, true, true, 1, true, null, null, null, $specific_prices
        );

        $row['specific_prices'] = $specific_prices;

        $row['quantity'] = Product::getQuantity(
            (int)$row['id_product'], 0, isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
        );

        $row['quantity_all_versions'] = $row['quantity'];

        if ($row['id_product_attribute']) {
            $row['quantity'] = Product::getQuantity(
                (int)$row['id_product'], $id_product_attribute, isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
            );
        }

        $row['id_image'] = Product::defineProductImage($row, $id_lang);
        $row['features'] = Product::getFrontFeaturesStatic((int)$id_lang, $row['id_product']);

        $row['attachments'] = [];
        if (!isset($row['cache_has_attachments']) || $row['cache_has_attachments']) {
            $row['attachments'] = Product::getAttachmentsStatic((int)$id_lang, $row['id_product']);
        }

        $row['virtual'] = ((!isset($row['is_virtual']) || $row['is_virtual']) ? 1 : 0);

        // Pack management
        $row['pack'] = (!isset($row['cache_is_pack']) ? Pack::isPack($row['id_product']) : (int)$row['cache_is_pack']);
        $row['packItems'] = $row['pack'] ? Pack::getItemTable($row['id_product'], $id_lang) : [];
        if ($row['pack'] && !Pack::isInStock($row['id_product'])) {
            $row['quantity'] = 0;
        }

        $row['customization_required'] = false;
        if (isset($row['customizable']) && $row['customizable'] && Customization::isFeatureActive()) {
            if (count(Product::getRequiredCustomizableFieldsStatic((int)$row['id_product']))) {
                $row['customization_required'] = true;
            }
        }

        $row = Product::getTaxesInformations($row, $context);
        return $row;
    }

    /**
     * @param $id_product
     * @param bool $usetax
     * @param null $id_product_attribute
     * @param int $decimals
     * @param null $divisor
     * @param bool $only_reduc
     * @param bool $usereduc
     * @param int $quantity
     * @param bool $force_associated_tax
     * @param null $id_customer
     * @param null $id_cart
     * @param null $id_address
     * @param null $specific_price_output
     * @param bool $with_ecotax
     * @param bool $use_group_reduction
     * @param Context|null $context
     * @param bool $use_customer_price
     * @return float
     */
    private function getPriceStatic($id_product, $usetax = true, $id_product_attribute = null, $decimals = 6, $divisor = null, $only_reduc = false, $usereduc = true, $quantity = 1, $force_associated_tax = false, $id_customer = null, $id_cart = null, $id_address = null, &$specific_price_output = null, $with_ecotax = true, $use_group_reduction = true, Context $context = null, $use_customer_price = true)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $cur_cart = $context->cart;

        if ($divisor !== null) {
            Tools::displayParameterAsDeprecated('divisor');
        }

        if (!Validate::isBool($usetax) || !Validate::isUnsignedId($id_product)) {
            die(Tools::displayError());
        }

        // Initializations
        $id_group = null;
        if ($id_customer) {
            $id_group = Customer::getDefaultGroupId((int)$id_customer);
        }
        if (!$id_group) {
            $id_group = (int)Group::getCurrent()->id;
        }

        $cart_quantity = 0;
        if ((int)$id_cart) {
            $cache_id = '$this->getPriceStatic_' . (int)$id_product . '-' . (int)$id_cart;
            if (!Cache::isStored($cache_id) || ($cart_quantity = Cache::retrieve($cache_id) != (int)$quantity)) {
                $sql = 'SELECT SUM(`quantity`)
				FROM `' . _DB_PREFIX_ . 'cart_product`
				WHERE `id_product` = ' . (int)$id_product . '
				AND `id_cart` = ' . (int)$id_cart;
                $cart_quantity = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                Cache::store($cache_id, $cart_quantity);
            } else {
                $cart_quantity = Cache::retrieve($cache_id);
            }
        }

        $id_currency = Validate::isLoadedObject($context->currency) ? (int)$context->currency->id : (int)Configuration::get('PS_CURRENCY_DEFAULT');

        // retrieve address informations
        $id_country = (int)$context->country->id;
        $id_state = 0;
        $zipcode = 0;

        if (!$id_address && Validate::isLoadedObject($cur_cart)) {
            $id_address = $cur_cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
        }

        if ($id_address) {
            $address_infos = Address::getCountryAndState($id_address);
            if ($address_infos['id_country']) {
                $id_country = (int)$address_infos['id_country'];
                $id_state = (int)$address_infos['id_state'];
                $zipcode = $address_infos['postcode'];
            }
        } elseif (isset($context->customer->geoloc_id_country)) {
            $id_country = (int)$context->customer->geoloc_id_country;
            $id_state = (int)$context->customer->id_state;
            $zipcode = $context->customer->postcode;
        }

        if (Tax::excludeTaxeOption()) {
            $usetax = false;
        }

        if ($usetax != false && !empty($address_infos['vat_number']) && $address_infos['id_country'] != Configuration::get('VATNUMBER_COUNTRY') && Configuration::get('VATNUMBER_MANAGEMENT')) {
            $usetax = false;
        }

        if (is_null($id_customer) && Validate::isLoadedObject($context->customer)) {
            $id_customer = $context->customer->id;
        }

        $return = Product::priceCalculation(
            $context->shop->id, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency, $id_group, $quantity, $usetax, $decimals, $only_reduc, $usereduc, $with_ecotax, $specific_price_output, $use_group_reduction, $id_customer, $use_customer_price, $id_cart, $cart_quantity
        );

        return $return;
    }

    public function getProductDeliveryDays($product, $availableQuantity)
    {
//     * Choose which parameters use for give information delivery.
//     * 0 - none
//     * 1 - use default information
//     * 2 - use product information
        $productLableSettingsUsage = isset($product->additional_delivery_times) ? $product->additional_delivery_times : NULL;
        $deliveryDaysDefault = SettingsClass::getSettings('delivery_days', $this->shopID);

        // Product is in stock
        if (trim($deliveryDaysDefault) == '') {
            if ($availableQuantity > 0) {
                if ($productLableSettingsUsage == 0) {
                    $deliveryDays = ''; // no delivery days
                } else if ( $productLableSettingsUsage == 1 ) {
                    $deliveryDays = Configuration::get('PS_LABEL_DELIVERY_TIME_AVAILABLE', $this->defaultLang); // global label
                } else if ( $productLableSettingsUsage == 2 ) {
                    $deliveryDays = $product->delivery_in_stock[1]; // label from specific product
                }

                // product is in preorder
            } else {
                if ($productLableSettingsUsage == 0) {
                    $deliveryDays = ''; // no delivery days
                } else if ( $productLableSettingsUsage == 1 ) {
                    $deliveryDays = Configuration::get('PS_LABEL_DELIVERY_TIME_OOSBOA', $this->defaultLang); // global label
                } else if ( $productLableSettingsUsage == 2 ) {
                    $deliveryDays = $product->delivery_out_stock[1]; // label from specific product
                }
            }
        } else {
            $deliveryDays = $deliveryDaysDefault;
        }

        return $deliveryDays;

    }

    public function getProductAvailability($availableQuantity, $whenOutOfStock)
    {
        if ($availableQuantity <= 0 && $whenOutOfStock == 1) {
            $availability = 'preorder';
        } else if ($availableQuantity > 0) {
            $availability = 'in stock';
        } else {
            $availability = 'out of stock';
        }

        return $availability;
    }

    public function getProductPrice($idCountry, $specificPrice, $shopId, $productId, $productAttributeId = null, $with_vat = false)
    {
        return Product::priceCalculation(
            $shopId, // ID shop
            $productId, // ID Product
            $productAttributeId, // ID Product atribut
            $idCountry, // ID Country
            0, // ID State
            0, // ZIP Code
            $this->currency->id, // Id Currency
            1, // ID Group
            1, // Quantity
            $with_vat, // Použít daň
            6, // Počet desetinných míst
            false, // Only reduct
            false, // Use reduct
            true, // With ekotax
            $specificPrice, // Specific price
            false, // Use group reduction
            0, // ID customer
            false, // Use customer price
            0, // ID Cart
            0);
    }

    public function getProductDiscountPrice($idCountry, $specificPrice, $shopId, $productId, $productAttributeId = null, $with_vat = false)
    {
        return Product::priceCalculation(
            $shopId, // ID shop
            $productId, // ID Product
            $productAttributeId, // ID Product atribut
            $idCountry, // ID Country
            0, // ID State
            0, // ZIP Code
            $this->currency->id, // Id Currency
            1, // ID Group
            1, // Quantity
            $with_vat, // Použít daň
            6, // Počet desetinných míst
            false, // Only reduct
            true, // Use reduct
            true, // With ekotax
            $specificPrice, // Specific price
            true, // Use group reduction
            0, // ID customer
            true, // Use customer price
            0, // ID Cart
            0);
    }

    public function getSaleDateInterval($id_country, $shopId, $productId, $productAttributeId = null)
    {
        $from = '';
        $to = '';

        $rule = SpecificPrice::getSpecificPrice(
            $productId,
            $shopId,
            $this->currency->id,
            $id_country,
            1,
            1,
            $productAttributeId
        );

        if (isset($rule['from']) && $rule['from'] != 0) {
            $from = date(DATE_ISO8601, strtotime($rule['from']));
        }

        if (isset($rule['to']) && $rule['to'] != 0) {
            $to = date(DATE_ISO8601, strtotime($rule['to']));
        }

        if ($from === '' && $to === '') {
            return '';
        }

        return implode('/', [$from, $to]);
    }
}
