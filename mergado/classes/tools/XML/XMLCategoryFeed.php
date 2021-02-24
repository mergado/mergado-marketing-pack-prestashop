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

use Cache;
use CartCore as Cart;
use Customer;
use Exception;
use LanguageCore as Language;
use ConfigurationCore as Configuration;
use CurrencyCore as Currency;
use Mergado\Tools\XMLClass;
use PrestaShop\PrestaShop\Adapter\Entity\Customization;
use PrestaShop\PrestaShop\Adapter\Entity\Pack;
use ProductCore as Product;
use CategoryCore as Category;
use LinkCore as Link;
use EmployeeCore as Employee;
use ValidateCore as Validate;
use CombinationCore as Combination;
use ShopCore as Shop;
use TaxCore as Tax;
use GroupCore as Group;
use Address;
use ContextCore as Context;
use Db as Db;
use ToolsCore as Tools;
use ObjectModel;
use XMLWriter;
use Mergado;

require_once _PS_MODULE_DIR_ . 'mergado/mergado.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/LogClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/SettingsClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/XML/Helpers/XMLQuery.php';

class XMLCategoryFeed extends ObjectModel
{
    const FEED_VERSION = 'http://www.mergado.com/ns/category/1.7';

    const MAX_PRODUCTS = 'partial_feeds_category_size';

    protected $language;
    protected $shopID;
    protected $feedBase;
    protected $name;

    private $tmpDir;
    private $tmpShop;
    private $tmpShopDir;
    private $xmlDir;

    public function __construct($shopID, $feedBase = null, $name = null, $language = null)
    {
        $this->language = $language;
        $this->shopID = $shopID;
        $this->feedBase = $feedBase;
        $this->name = $name;

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
            $stepCategories = $this->getTotalFilesCount();
            $currentFilesCount = $this->getTempNumber();

            $start = $currentFilesCount === 0 ? 0 : ($currentFilesCount * $stepCategories);
            $limit = $stepCategories;

            $categoryListTotal = Category::getSimpleCategories($this->language->id);

            if($stepCategories !== 0 && $categoryListTotal > $stepCategories) {
                // Get only products we need
                $categoryList = array_slice($categoryListTotal, $start, $limit);
            } else {
                $categoryList = $categoryListTotal;
            }

            // Final output directory
            $out = $this->tmpShopDir . ($currentFilesCount + 1) . '.xml';
            $storage = $this->xmlDir . $this->feedBase . '.xml';

            if ($stepCategories === 0 || $stepCategories >= count($categoryListTotal)  || count($categoryList) === 0) {
                $out = $storage;
            }

            // Create directories if not exist
            $this->createDirs();

            if (count($categoryList) > 0) {
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

                $link = new Link();

                foreach ($categoryList as $cat) {
                    if ($cat['id_category'] == 1 || $cat['id_category'] == 2) {
                        continue;
                    }

                    $category = new Category($cat['id_category'], $this->language->id);
                    $categoryLink = $link->getCategoryLink($category, $category->link_rewrite, $this->language->id);
                    $context = new Context();
                    $context->cart = new Cart();
                    $context->employee = new Employee();
                    $xmlQuery = new XMLQuery();
                    $products = $xmlQuery->getProducts($category, $this->language->id, 0, 10);

                    $cheapest = (float)isset($products[0]) ? $products[0]['price'] : 0;
                    $expensive = 0;

                    $breadcrumbs = $category->getParentsCategories($this->language->id);
                    $categorytext = "";
                    foreach (array_reverse($breadcrumbs) as $crumb) {
                        if ($crumb['id_category'] == 1 || $crumb['id_category'] == 2) {
                            continue;
                        }

                        $categorytext .= $crumb['name'];
                        $categorytext .= ' | ';
                    }

                    $categorytext = substr($categorytext, 0, -3);

                    foreach ($products as $product) {
                        $price = (float)$product['price'];

                        if ($price > $expensive) {
                            $expensive = $price;
                        }

                        if ($price < $cheapest) {
                            $cheapest = $price;
                        }
                    }


                    // START ITEM
                    $xml_new->startElement('ITEM');

                    $xml_new->startElement('CATEGORY_NAME');
                    $xml_new->text('<![CDATA[' . $category->name . ']]');
                    $xml_new->endElement();

                    $xml_new->startElement('CATEGORY');
                    $xml_new->text('<![CDATA[' . $categorytext . ']]');
                    $xml_new->endElement();

                    $xml_new->startElement('CATEGORY_ID');
                    $xml_new->text($category->id);
                    $xml_new->endElement();

                    $xml_new->startElement('CATEGORY_URL');
                    $xml_new->text($categoryLink);
                    $xml_new->endElement();

                    $xml_new->startElement('CATEGORY_QUANTITY');
                    $xml_new->text(count($products));
                    $xml_new->endElement();

                    $xml_new->startElement('CATEGORY_DESCRIPTION');
                    $xml_new->text('<![CDATA[' . $category->description . ']]');
                    $xml_new->endElement();

                    $xml_new->startElement('CATEGORY_MIN_PRICE_VAT');
                    $xml_new->text($cheapest);
                    $xml_new->endElement();

                    $xml_new->startElement('CATEGORY_MAX_PRICE_VAT');
                    $xml_new->text($expensive);
                    $xml_new->endElement();

                    // END ITEM
                    $xml_new->endElement();
                }

                $xml_new->endElement();
                $xml_new->endDocument();
                $xml_new->flush();
                unset($xml_new);
            } elseif (count($categoryList) == 0) {
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
