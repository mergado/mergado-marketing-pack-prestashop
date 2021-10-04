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

use AddressCore;
use CartCore as Cart;
use DateTime;
use LanguageCore as Language;
use ConfigurationCore as Configuration;
use CurrencyCore as Currency;
use Mergado\Tools\SettingsClass;
use Mergado\Tools\XMLClass;
use PrestaShop\PrestaShop\Adapter\Entity\Customization;
use PrestaShop\PrestaShop\Adapter\Entity\Pack;
use ProductCore as Product;
use CategoryCore as Category;
use ManufacturerCore as Manufacturer;
use LinkCore as Link;
use EmployeeCore as Employee;
use SimpleXMLElement;
use SpecificPrice;
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
use Exception;
use ObjectModel;
use ProductCore;
use XMLWriter;
use Mergado;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class XMLProductFeed extends ObjectModel
{
    const FEED_VERSION = 'http://www.mergado.com/ns/1.10';

    const MAX_PRODUCTS = 'partial_feeds_size';

    protected $language;
    protected $defaultLang;
    protected $defaultCurrency;
    protected $currency;
    protected $shopID;
    protected $feedBase;

    private $tmpDir;
    private $tmpShop;
    private $tmpShopDir;
    private $xmlDir;


    public function __construct($shopId, $name = null, $feedBase = null, $language = null, $currency = null)
    {
        $this->language = $language;
        $this->currency = $currency;
        $this->shopID = $shopId;
        $this->feedBase = $feedBase;

        $this->tmpDir = XMLClass::TMP_DIR . 'xml/';
        $this->tmpShop = $this->tmpDir . $this->shopID . '/';
        $this->tmpShopDir = $this->tmpShop . $name . '/';
        $this->xmlDir = XMLClass::XML_DIR . $this->shopID . '/';
    }

    /*******************************************************************************************************************
     * XML GENERATORS
    *******************************************************************************************************************/

    /**
     * @return bool
     * @throws Exception
     */
    public function generateXML()
    {
        try {
            // Temporary files count
            $stepProducts = $this->getTotalFilesCount();
            $currentFilesCount = $this->getTempNumber();

            $start = $currentFilesCount === 0 ? 0 : ($currentFilesCount * $stepProducts);
            $limit = $stepProducts;

            $xmlQuery = new XMLQuery($this->currency);

            $export_out_of_stock = SettingsClass::getSettings(SettingsClass::EXPORT['DENIED_PRODUCTS'], $this->shopID);
            $productsListTotal = $xmlQuery->productsToFlat(0, 0, $this->language->id, $export_out_of_stock);

            if($stepProducts !== 0 && $productsListTotal > $stepProducts) {
                // Get only products we need
                $productsList = array_slice($productsListTotal, $start, $limit);
            } else {
                $productsList = $productsListTotal;
            }

            // Final output directory
            $out = $this->tmpShopDir . ($currentFilesCount + 1) . '.xml';
            $storage = $this->xmlDir . $this->feedBase . '.xml';

            if ($stepProducts === 0 || $stepProducts >= count($productsListTotal)  || count($productsList) === 0) {
                $out = $storage;
            }

            // Create directories if not exist
            $this->createDirs();

            if (count($productsList) > 0) {
                $xml_new = new XMLWriter();

                $xml_new->openURI($out);
                $xml_new->startDocument('1.0', 'UTF-8');
                $xml_new->startElement('CHANNEL');
                $xml_new->writeAttribute('xmlns', self::FEED_VERSION);
                $xml_new->startElement('LINK');
                $xml_new->text(_PS_BASE_URL_.__PS_BASE_URI__);
                $xml_new->endElement();

                $xml_new->startElement('GENERATOR');
                $xml_new->text('mergado.prestashop.marketingpack.' . str_replace('.', '_', Mergado::MERGADO['VERSION']));
                $xml_new->endElement();

                foreach ($productsList as $product) {

                    // START ITEM
                    $xml_new->startElement('ITEM');

                    // Product ID
                    $xml_new->startElement('ITEM_ID');
                    $xml_new->text($product['item_id']);
                    $xml_new->endElement();

                    // Product ITEMGROUP
                    $xml_new->startElement('ITEMGROUP_ID');
                    $xml_new->text($product['itemgroup_id']);
                    $xml_new->endElement();

                    if($product['ean'] != 0) {
                        $xml_new->startElement('EAN');
                        $xml_new->text($product['ean']);
                        $xml_new->endElement();
                    }

                    $xml_new->startElement('PRODUCTNO');
                    $xml_new->text($product['reference']);
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
                    if ($product['delivery_days'] != '') {
                        $xml_new->startElement('DELIVERY_DAYS');
                        $xml_new->text($product['delivery_days']);
                        $xml_new->endElement();
                    }

                    // Product currency
                    $xml_new->startElement('CURRENCY');
                    $xml_new->text($this->currency->iso_code);
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

                    // Product discount price NO VAT
                    if ($product['discount_price'] != '') {
                        $xml_new->startElement('PRICE_DISCOUNT');
                        $xml_new->text($product['discount_price']);
                        $xml_new->endElement();
                    }

                    // Product discount price VAT
                    if ($product['discount_price_vat']) {
                        $xml_new->startElement('PRICE_DISCOUNT_VAT');
                        $xml_new->text($product['discount_price_vat']);
                        $xml_new->endElement();
                    }

                    if ($product['sale_price_effective_date'] != '') {
                        $xml_new->startElement('SALE_PRICE_EFFECTIVE_DATE');
                        $xml_new->text($product['sale_price_effective_date']);
                        $xml_new->endElement();
                    }

                    if($product['cost'] != '') {
                        //Product COST
                        $xml_new->startElement('COST');
                        $xml_new->text($product['cost']);
                        $xml_new->endElement();
                    }

                    if($product['cost_vat'] != '') {
                        //Product COST_VAT
                        $xml_new->startElement('COST_VAT');
                        $xml_new->text($product['cost_vat']);
                        $xml_new->endElement();
                    }

                    // Product availability
                    $xml_new->startElement('AVAILABILITY');
                    $xml_new->text($product['availability']);
                    $xml_new->endElement();

                    // Product condition
                    $xml_new->startElement('CONDITION');
                    $xml_new->text($product['condition']);
                    $xml_new->endElement();

                    // Product stock quanity
                    $xml_new->startElement('STOCK_QUANTITY');
                    $xml_new->text($product['stock_quantity']);
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

                    if($product['shipping_size']) {
                        // Product size
                        $xml_new->startElement('SHIPPING_SIZE');
                        $xml_new->text($product['shipping_size']);
                        $xml_new->endElement();
                    }

                    if($product['shipping_weight']) {
                        // Product weight
                        $xml_new->startElement('SHIPPING_WEIGHT');
                        $xml_new->text($product['shipping_weight']);
                        $xml_new->endElement();
                    }

                    // END ITEM
                    $xml_new->endElement();
                }

                $xml_new->endElement();
                $xml_new->endDocument();

                $xml_new->flush();
                unset($xml_new);

            } elseif (count($productsList) == 0) {
                if ($this->mergeXmlFile($storage, $this->tmpShopDir)) {
                    $files = glob($this->tmpShopDir . '*');

                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception('XML feed error');
        }
        return true;
    }


    /*******************************************************************************************************************
     * MERGE XML
     *******************************************************************************************************************/

    /**
     * Merge xml files to final file
     *
     * @param $storage
     * @param $tmpShopDir
     * @return bool
     */
    private function mergeXmlFile($storage, $tmpShopDir)
    {
        $loop = 0;

        $xmlstr = '<CHANNEL xmlns="' . self::FEED_VERSION . '">';

        foreach (glob($tmpShopDir . '*.xml') as $file) {
            $xml = Tools::simplexml_load_file($file);

            $innerLoop = 0;
            foreach ($xml as $item) {
                if ($loop != 0 && (preg_match('/^mergado.prestashop/', $item[0]) || ($innerLoop == 0 || $innerLoop == 1))) {
                    $innerLoop++;
                    continue;
                } else {
                    $innerLoop++;
                    $xmlstr .= $item->asXml();
                }
            }

            $loop++;
        }

        $xmlstr .= '</CHANNEL>';

        $xml_new = new XMLWriter();

        $xml_new->openURI($storage);
        $xml_new->startDocument('1.0', 'UTF-8');
        $xml_new->writeRaw($xmlstr);
        $xml_new->endDocument();

        return true;
    }

    public function getTotalFilesCount()
    {
        return (int)XMLClass::getTotalFilesCount(self::MAX_PRODUCTS, $this->shopID);
    }

    public function getTempNumber()
    {
        return XMLClass::getTempNumber($this->tmpShopDir);
    }

    public function createDirs()
    {
        XMLClass::createDIR(
            array(
                $this->tmpDir,
                $this->tmpShop,
                $this->tmpShopDir,
                $this->xmlDir
            )
        );
    }
}
