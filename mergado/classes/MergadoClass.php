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
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   LICENSE.txt
 */

require_once _PS_MODULE_DIR_ . 'mergado/classes/ZboziKonverze.php';

class MergadoClass extends ObjectModel
{

    public static $feedPrefix = 'mergado_feed_';
    protected $language;
    protected $defaultLang;
    protected $defaultCurrency;
    protected $currency;

    // Heureka
    const HEUREKA_URL = 'http://www.heureka.cz/direct/dotaznik/objednavka.php';
    const HEUREKA_URL_SK = 'http://www.heureka.sk/direct/dotaznik/objednavka.php';

    public static $definition = array(
        'table' => 'mergado',
        'primary' => 'id',
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->language = new Language();
        $this->currency = new Currency();
        $this->defaultLang = Configuration::get('PS_LANG_DEFAULT');
        $this->defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        parent::__construct($id, $id_lang, $id_shop);
    }

    public function generateMergadoFeed($feedBase)
    {
        $base = explode('-', str_replace(self::$feedPrefix, '', $feedBase));
        $feedBase = $feedBase . '_' .
                Tools::substr(hash('md5', $base[0] . '-' . $base[1] . Configuration::get('PS_SHOP_NAME')), 1, 11);
        $this->language = $this->language->getLanguageByIETFCode($this->language->getLanguageCodeByIso($base[0]));
        $this->currency = new Currency($this->currency->getIdByIsoCode($base[1]));

        $products = $this->productsToFlat(false, $this->language->id);
        $xml = $this->generateXML($products, $feedBase, $this->currency);

        return $xml;
    }

    public function generateXML($products, $feedBase, $currency)
    {
        $out = _PS_MODULE_DIR_ . 'mergado/tmp/' . $feedBase . '.xml';
        $storage = _PS_MODULE_DIR_ . 'mergado/xml/' . $feedBase . '.xml';

        $xml_new = new XMLWriter();
        $xml_new->openURI($out);
        $xml_new->startDocument('1.0', 'UTF-8');
        $xml_new->startElement('CHANNEL');

        foreach ($products as $product) {

            // START ITEM
            $xml_new->startElement('ITEM');

            // Product ID
            $xml_new->startElement('ITEM_ID');
            $xml_new->text($product['item_id']);
            $xml_new->endElement();

            // Product name
            $xml_new->startElement('NAME_EXACT');
            $xml_new->text($product['name_exact']);
            $xml_new->endElement();

            // Product category
            $xml_new->startElement('CATEGORY');
            $xml_new->text($product['category']);
            $xml_new->endElement();

            // Product description
            $xml_new->startElement('DESCRIPTION');
            $xml_new->text($product['description']);
            $xml_new->endElement();
            
            // Product short description
            $xml_new->startElement('DESCRIPTION_SHORT');
            $xml_new->text($product['description_short']);
            $xml_new->endElement();

            // Product delivery days
            $xml_new->startElement('DELIVERY_DAYS');
            $xml_new->text($product['delivery_days']);
            $xml_new->endElement();

            // Product currency
            $xml_new->startElement('CURRENCY');
            $xml_new->text($currency->iso_code);
            $xml_new->endElement();

            // Product image
            $xml_new->startElement('IMAGE');
            $xml_new->text($product['image']);
            $xml_new->endElement();

            // Product alternative images
            foreach ($product['image_alternative'] as $img) {
                $xml_new->startElement('IMAGE_ALTERNATIVE');
                $xml_new->text($img);
                $xml_new->endElement();
            }

            // Product accessory
            foreach ($product['accessory'] as $ac) {
                $xml_new->startElement('ACCESSORY');
                $xml_new->text($ac);
                $xml_new->endElement();
            }

            // Product PRODUCER
            $xml_new->startElement('PRODUCER');
            $xml_new->text($product['producer']);
            $xml_new->endElement();

            // Product URL
            $xml_new->startElement('URL');
            $xml_new->text($product['url']);
            $xml_new->endElement();

            // Product VAT
            $xml_new->startElement('VAT');
            $xml_new->text($product['vat']);
            $xml_new->endElement();

            // Product price
            $xml_new->startElement('PRICE');
            $xml_new->text($product['price']);
            $xml_new->endElement();

            // Product price VAT
            $xml_new->startElement('PRICE_VAT');
            $xml_new->text($product['price_vat']);
            $xml_new->endElement();

            // Product availability
            $xml_new->startElement('AVAILABILITY');
            $xml_new->text($product['availability']);
            $xml_new->endElement();

            // Product condition
            $xml_new->startElement('CONDITION');
            $xml_new->text($product['condition']);
            $xml_new->endElement();

            // Product params
            foreach ($product['params'] as $param) {
                $xml_new->startElement('PARAM');
                $xml_new->startElement('NAME');
                $xml_new->text($param['name']);
                $xml_new->endElement();

                $xml_new->startElement('VALUE');
                $xml_new->text($param['value']);
                $xml_new->endElement();
                $xml_new->endElement();
            }

            // Product size
            $xml_new->startElement('SHIPPING_SIZE');
            $xml_new->text($product['shipping_size']);
            $xml_new->endElement();

            // Product weight
            $xml_new->startElement('SHIPPING_WEIGHT');
            $xml_new->text($product['shipping_weight']);
            $xml_new->endElement();

            // END ITEM
            $xml_new->endElement();
        }

        $xml_new->endElement();
        $xml_new->endDocument();
        $xml_new->flush();
        unset($xml_new);

        if (!copy($out, $storage)) {
            @unlink($out);
            @unlink($storage);

            return false;
        } else {
            unlink($out);
        }

        return true;
    }

    public function productsToFlat($productId = false, $lang = false)
    {
        $flatProductList = array();
        $productsList = array();

        if (!$lang) {
            $lang = $this->defaultLang;
        }

        if ($productId) {
            $productsList = $this->getProduct($lang, $productId);
        } else {
            $productsList = Product::getProducts($lang, 0, 0, 'id_product', 'ASC', false, true);
        }

        foreach ($productsList as $productCore) {
            $product = new Product($productCore['id_product']);
            $base = $this->productBase($product, $lang);
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

    public function productBase($item, $lang)
    {
        $accessories = ProductCore::getAccessoriesLight($lang, $item->id);
        $accessoriesExtended = array();
        if (!empty($accessories)) {
            foreach ($accessories as $accessory) {
                $accessoryTmp = array(); //$this->getProductCombination(new Product($accessory['id_product']), $lang);
                if (!empty($accessoryTmp)) {
                    foreach ($accessoryTmp as $at) {
                        $accessoriesExtended[] = $at['id_product'] . '-' . $at['id_product_attribute'];
                    }
                } else {
                    $accessoriesExtended[] = $accessory['id_product'];
                }
            }
        }

        //$category = CategoryCore::getCategoryInformations(array($item->id_category_default), $lang);
        $category = '';
        $cat_iter_id = $item->id_category_default;

        do {
            $cat_tmp = new CategoryCore($cat_iter_id, $lang);
            $category = $cat_tmp->name . ' | ' . $category;
            $cat_iter_id = $cat_tmp->id_parent;
        } while ($cat_iter_id != null && $cat_iter_id != Configuration::get('PS_ROOT_CATEGORY') && $cat_iter_id != Configuration::get('PS_HOME_CATEGORY'));
        $category = Tools::substr($category, 0, -3);

        $combinations = $this->getProductCombination($item, $lang);
        $featuresTemp = Product::getFrontFeaturesStatic($lang, $item->id);
        $features = array();

        foreach ($featuresTemp as $feature) {
            $features[] = array(
                'name' => $feature['name'],
                'value' => $feature['value'],
            );
        }

        $manufacturer = new Manufacturer($item->id_manufacturer);

        $link = new LinkCore();

        $imagesList = $item->getImages($lang);
        $images = array();
        $mainImage = null;

        $id_country = Configuration::get('PS_COUNTRY_DEFAULT');

        $address = new Address();
        $address->id_country = $id_country;

        $tax_manager = TaxManagerFactory::getManager(
            $address,
            Product::getIdTaxRulesGroupByIdProduct((int) $item->id, null)
        );
        $tax_calculator = $tax_manager->getTaxCalculator();

        foreach ($imagesList as $img) {
            $images[] = 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' .
                    $link->getImageLink($item->link_rewrite[$lang], $item->id . '-' . $img['id_image']);
            if ($img['cover'] != null) {
                $mainImage = 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' .
                        $link->getImageLink($item->link_rewrite[$lang], $item->id . '-' . $img['id_image']);
            }
        }

        $productBase = null;

        if (!empty($combinations)) {
            foreach ($combinations as $combination) {
                $sp = SpecificPrice::getSpecificPrice(
                    $item->id,
                    $combination['id_shop'],
                    $this->currency->id,
                    0,
                    0,
                    1,
                    $combination['id_product_attribute']
                );
                $price = $item->price + $combination['price'] + $combination['unit_price_impact'];

                if (!empty($sp) && $sp && $sp['from_quantity'] <= 1) {
                    if ($sp['reduction_type'] === 'percentage') {
                        $price += ($sp['price'] * ($price * $sp['reduction']));
                    } elseif ($sp['reduction_type'] === 'amount') {
                        $price += ($sp['price'] * $sp['reduction']);
                    }
                }

                $params = array();

                foreach ($combination['attrs'] as $key => $value) {
                    $params[] = array(
                        'name' => $key,
                        'value' => $value,
                    );
                }


                $params = array_merge($params, $features);
                
                $itemgroup = array();
                foreach ($combinations as $g) {
                    if ($g['id_product_attribute'] != $combination['id_product_attribute']) {
                        $itemgroup[] = $g['id_product'] . '-' . $g['id_product_attribute'];
                    }
                }

                $price = ToolsCore::convertPriceFull($price, $this->defaultCurrency, $this->currency);
                $productBase[] = array(
                    'item_id' => $combination['id_product'] . '-' . $combination['id_product_attribute'],
                    'accessory' => $accessoriesExtended,
                    'availability' => (ProductCore::getQuantity(
                        $combination['id_product'],
                        $combination['id_product_attribute']
                    ) > 0) ? 'in stock' : 'out of stock',
                    'category' => $category,
                    'condition' => $item->condition,
                    'delivery_days' => (ProductCore::getQuantity(
                        $combination['id_product'],
                        $combination['id_product_attribute']
                    ) > 0) ? 0 : 7,
                    'description_short' => strip_tags($item->description_short[$lang]),
                    'description' => strip_tags($item->description[$lang]),
                    'ean' => $combination['ean13'],
                    'image' => $mainImage,
                    'image_alternative' => $images,
                    'itemgroup_id' => $itemgroup,
                    'name_exact' => $combination['name'],
                    'params' => $params,
                    'producer' => $manufacturer->name,
                    'url' => $link->getProductLink(
                        $item,
                        null,
                        null,
                        null,
                        $lang,
                        null,
                        $combination['id_product_attribute']
                    ),
                    'price' => Tools::ps_round(
                        $price,
                        Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                    ),
                    'price_vat' => Tools::ps_round(
                        $price * (1 + ($tax_calculator->taxes[0]->rate / 100)),
                        Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                    ),
                    'shipping_size' => $item->depth . ' x ' . $item->width . ' x ' . $item->height . ' ' .
                    Configuration::get('PS_DIMENSION_UNIT'),
                    'shipping_weight' => ($item->weight + $combination['weight']) . ' ' .
                    Configuration::get('PS_WEIGHT_UNIT'),
                    'vat' => $tax_calculator->taxes[0]->rate,
                );
            }
        } else {
            $sp = SpecificPrice::getSpecificPrice($item->id, $item->id_shop_default, $this->currency->id, 0, 0, 1);
            $price = $item->price;

            if (!empty($sp) && $sp && $sp['from_quantity'] <= 1) {
                if ($sp['reduction_type'] === 'percentage') {
                    $price += ($sp['price'] * ($price * $sp['reduction']));
                } elseif ($sp['reduction_type'] === 'amount') {
                    $price += ($sp['price'] * $sp['reduction']);
                }
            }

            $productBase = array(
                'item_id' => $item->id,
                'accessory' => $accessoriesExtended,
                'availability' => (ProductCore::getQuantity($item->id) > 0) ? 'in stock' : 'out of stock',
                'category' => $category,
                'condition' => $item->condition,
                'delivery_days' => (ProductCore::getQuantity($item->id) > 0) ? 0 : 7,
                'description_short' => strip_tags($item->description_short[$lang]),
                'description' => strip_tags($item->description[$lang]),
                'ean' => $item->ean13,
                'image' => $mainImage,
                'image_alternative' => $images,
                'name_exact' => $item->name[$lang],
                'params' => $features,
                'producer' => $manufacturer->name,
                'url' => $link->getProductLink($item, null, null, null, $lang, null),
                'price' => Tools::ps_round($price, Configuration::get('PS_PRICE_DISPLAY_PRECISION')),
                'price_vat' => Tools::ps_round(
                    $price * (1 + ($tax_calculator->taxes[0]->rate / 100)),
                    Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                ),
                'shipping_size' => $item->depth . ' x ' . $item->width . ' x ' . $item->height . ' ' .
                Configuration::get('PS_DIMENSION_UNIT'),
                'shipping_weight' => $item->weight . ' ' . Configuration::get('PS_WEIGHT_UNIT'),
                'vat' => $tax_calculator->taxes[0]->rate,
            );
        }


        return $productBase;
    }

    public function getProductCombination($product, $lang)
    {
        $groups = array();
        $comb_array = array();
        $flatProductList = array();

        $combinations = $product->getAttributeCombinations($lang);

        if (is_array($combinations) && count($combinations) > 0) {
            if (is_array($combinations)) {
                foreach ($combinations as $combination) {
                    $comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                    $comb_array[$combination['id_product_attribute']]['unit_price_impact'] = $combination['unit_price_impact'];
                    $comb_array[$combination['id_product_attribute']]['price'] = $combination['price'];
                    $comb_array[$combination['id_product_attribute']]['minimal_quantity'] = $combination['minimal_quantity'];
                    $comb_array[$combination['id_product_attribute']]['quantity'] = $combination['quantity'];
                    $comb_array[$combination['id_product_attribute']]['ecotax'] = $combination['ecotax'];
                    $comb_array[$combination['id_product_attribute']]['ean13'] = $combination['ean13'];
                    $comb_array[$combination['id_product_attribute']]['weight'] = $combination['weight'];
                    $comb_array[$combination['id_product_attribute']]['id_shop'] = $combination['id_shop'];
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = array(
                        $combination['group_name'],
                        $combination['attribute_name'],
                        $combination['id_attribute']
                    );
                    if ($combination['is_color_group']) {
                        $groups[$combination['id_attribute_group']] = $combination['group_name'];
                    }
                }
            }


            if (isset($comb_array)) {
                foreach ($comb_array as $id_product_attribute => $product_attribute) {
                    $list = '';
                    $attrs = array();

                    asort($product_attribute['attributes']);

                    foreach ($product_attribute['attributes'] as $attribute) {
                        $list .= $attribute[1] . ' ';
                        $attrs[$attribute[0]] = $attribute[1];
                    }
                    

                    $list = rtrim($list, ', ');

                    $comb_array[$id_product_attribute]['attributes'] = $list;
                    $comb_array[$id_product_attribute]['name'] = $list;

                    $flatProductList[] = array(
                        'id_product_attribute' => $id_product_attribute,
                        'id_product' => $combination['id_product'],
                        'name' => $product->name[$lang] . ': ' . $list,
                        'ean13' => $comb_array[$id_product_attribute]['ean13'],
                        'price' => $comb_array[$id_product_attribute]['price'],
                        'ecotax' => $comb_array[$id_product_attribute]['ecotax'],
                        'quantity' => $comb_array[$id_product_attribute]['quantity'],
                        'weight' => $comb_array[$id_product_attribute]['weight'],
                        'unit_price_impact' => $comb_array[$id_product_attribute]['unit_price_impact'],
                        'minimal_quantity' => $comb_array[$id_product_attribute]['minimal_quantity'],
                        'id_shop' => $comb_array[$id_product_attribute]['id_shop'],
                        'attrs' => $attrs
                    );
                }
            }
        }

        return $flatProductList;
    }

    public static function getSettings($query)
    {
        return Db::getInstance()->getRow('SELECT `value` FROM `' . _DB_PREFIX_ . 'mergado` WHERE `key` = "' . pSQL($query) . '"');
    }

    public function heurekaVerify($apiKey, $order, $lang)
    {
        $url = null;

        if ($lang === 'cs') {
            $url = self::HEUREKA_URL;
        }

        if ($lang === 'sk') {
            $url = self::HEUREKA_URL_SK;
        }

        $url .= '?id=' . $apiKey;
        $url .= '?email=' . urlencode($order['customer']->email);

        $cart = new Cart($order['cart']->id, LanguageCore::getIdByIso($lang));
        $products = $cart->getProducts();

        foreach ($products as $product) {
            $exactName = $product['name'];

            if (array_key_exists('attributes_small', $product) && $product['attributes_small'] != '') {
                $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                $exactName .= ': ' . implode(' ', $tmpName);
            }

            $url .= '&produkt[]=' . urlencode($exactName);
            if ($product['id_product_attribute'] === '0') {
                $url .= '&itemId[]=' . urlencode($product['id_product']);
            } else {
                $url .= '&itemId[]=' . urlencode($product['id_product'] . '-' . $product['id_product_attribute']);
            }
        }

        if (isset($this->orderId)) {
            $url .= '&orderid=' . urlencode($order['order']->id);
        }

        $this->sendRequest($url);
    }

    private function sendRequest($url)
    {
        $parsed = parse_url($url);
        $fp = fsockopen($parsed['host'], 80, $errno, $errstr, 5);
        if (!$fp) {
            throw new Exception($errstr . ' (' . $errno . ')');
        } else {
            $return = '';
            $out = 'GET ' . $parsed['path'] . '?' . $parsed['query'] . " HTTP/1.1\r\n" .
                    'Host: ' . $parsed['host'] . "\r\n" .
                    "Connection: Close\r\n\r\n";
            fputs($fp, $out);
            while (!feof($fp)) {
                $return .= fgets($fp, 128);
            }
            fclose($fp);
            $returnParsed = explode("\r\n\r\n", $return);

            return empty($returnParsed[1]) ? '' : trim($returnParsed[1]);
        }
    }

    public function sendZboziKonverze($order, $lang)
    {
        $active = self::getSettings('mergado_zbozi_konverze');
        $id = self::getSettings('mergado_zbozi_shop_id');
        $secret = self::getSettings('mergado_zbozi_secret');

        if ($active['value'] === '1') {
            try {
                $zbozi = new ZboziKonverze($id['value'], $secret['value']);

                // testovací režim
                // $zbozi->useSandbox(true);

                $cart = new Cart($order['cart']->id, LanguageCore::getIdByIso($lang));
                $products = $cart->getProducts();

                foreach ($products as $product) {
                    $exactName = $product['name'];

                    if (array_key_exists('attributes_small', $product) && $product['attributes_small'] != '') {
                        $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                        $exactName .= ': ' . implode(' ', $tmpName);
                    }

                    $pid = $product['id_product'];
                    if ($product['id_product_attribute'] != '0') {
                        $pid .= '-' . $product['id_product_attribute'];
                    }

                    $zbozi->addCartItem(array(
                        'productName' => $exactName,
                        'itemId' => $pid,
                        'unitPrice' => $product['price_wt'],
                        'quantity' => $product['quantity'],
                    ));
                }

                $carrier = new CarrierCore($order['order']->id_carrier);
                $zbozi->setOrder(array(
                    'email' => $order['customer']->email,
                    'deliveryType' => $carrier->name,
                    'deliveryPrice' => (string) $order['order']->total_shipping,
                    'deliveryDate' => $order['order']->delivery_date,
                    'orderId' => $order['order']->id,
                    'otherCosts' => '0',
                    'paymentType' => $order['order']->payment,
                    'totalPrice' => $order['order']->total_paid,
                ));

                $zbozi->send();
            } catch (ZboziKonverzeException $e) {
                // handle errors
                echo 'Error: ' . $e->getMessage();
            }
        }
    }
}
