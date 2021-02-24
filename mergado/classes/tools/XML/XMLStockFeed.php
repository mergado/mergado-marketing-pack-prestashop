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

use Exception;
use LanguageCore as Language;
use ConfigurationCore as Configuration;
use CurrencyCore as Currency;
use Mergado\Tools\XMLClass;
use ProductCore as Product;
use StockAvailableCore as StockAvailable;
use ObjectModel;
use Tools;
use XMLWriter;
use Mergado;

require_once _PS_MODULE_DIR_ . 'mergado/mergado.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/LogClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/SettingsClass.php';

class XMLStockFeed extends ObjectModel
{
    const MAX_PRODUCTS = 'partial_feeds_stock_size';

    protected $defaultLang;
    protected $shopID;
    protected $feedBase;
    protected $name;

    private $tmpDir;
    private $tmpShop;
    private $tmpShopDir;
    private $xmlDir;

    public function __construct($shopId, $feedBase = null, $name = null)
    {
        $this->defaultLang = Configuration::get('PS_LANG_DEFAULT');
        $this->shopID = $shopId;

        if ($feedBase === null) {
            $this->feedBase = 'stock_' . Tools::getAdminTokenLite('AdminModules');
        } else {
            $this->feedBase = $feedBase;
        }

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
     */
    public function generateXML()
    {
        try {
            // Temporary files count
            $stepProducts = $this->getTotalFilesCount();
            $currentFilesCount = $this->getTempNumber();

            $start = $currentFilesCount === 0 ? 0 : ($currentFilesCount * $stepProducts);
            $limit = $stepProducts;

            $productsListTotal = Product::getProducts($this->defaultLang, 0, 0, 'id_product', 'ASC', false, true);

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

            $this->createDirs();

            if (count($productsList) > 0) {
                $xml_new = new XMLWriter();
                $xml_new->openURI($out);
                $xml_new->startDocument('1.0', 'UTF-8');
                $xml_new->startElement('item_list');

                foreach ($productsList as $product) {
                    $p = new Product($product['id_product']);
                    $xmlQuery = new XMLQuery();
                    $combinations = $xmlQuery->getProductCombination($p, $this->defaultLang);
                    $whenOutOfStock = StockAvailable::outOfStock($p->id);

                    if ($whenOutOfStock == 2) {
                        $whenOutOfStock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
                    }

                    if (count($combinations)) {
                        foreach ($combinations as $combination) {
                            $qty = StockAvailable::getQuantityAvailableByProduct($combination['id_product'], $combination['id_product_attribute']);

                            if ($qty <= 0 && $whenOutOfStock == 0) {
                                continue;
                            }

                            if ($qty > 0) {
                                $xml_new->startElement('item');
                                $xml_new->writeAttribute('id', $combination['id_product'] . '-' . $combination['id_product_attribute']);
                                $xml_new->startElement('stock_quantity');
                                $xml_new->text($qty);
                                $xml_new->endElement();

                                $xml_new->endElement();
                            }
                        }
                    } else {

                        $qty = StockAvailable::getQuantityAvailableByProduct($product['id_product']);

                        if ($qty <= 0 && $whenOutOfStock == 0) {
                            // skip
                        } else {
                            if ($qty > 0) {
                                $xml_new->startElement('item');
                                $xml_new->writeAttribute('id', $product['id_product']);

                                $xml_new->startElement('stock_quantity');
                                $xml_new->text($qty);
                                $xml_new->endElement();

                                $xml_new->endElement();
                            }
                        }
                    }
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
        $xmlstr = '<CHANNEL xmlns="http://www.mergado.com/ns/1.8">';

        foreach (glob($tmpShopDir . '*.xml') as $file) {
            $xml = Tools::simplexml_load_file($file);

            foreach ($xml as $item) {
                    $xmlstr .= $item->asXml();
            }
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
