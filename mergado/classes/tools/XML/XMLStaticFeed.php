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

use LanguageCore as Language;
use ConfigurationCore as Configuration;
use CurrencyCore as Currency;
use Mergado\Tools\XMLClass;
use ObjectModel;
use XMLWriter;
use Mergado;

require_once _PS_MODULE_DIR_ . 'mergado/mergado.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/LogClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/SettingsClass.php';

class XMLStaticFeed extends ObjectModel
{
    protected $language;
    protected $defaultLang;
    protected $defaultCurrency;
    protected $currency;
    protected $shopID;

    public function __construct()
    {
        $this->language = new Language();
        $this->currency = new Currency();
        $this->defaultLang = Configuration::get('PS_LANG_DEFAULT');
        $this->defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $this->shopID = Mergado::getShopId();
    }

    /**
     * @param $feedBase
     * @param $products
     * @param $shopID
     * @return bool
     */
    public function generateXML($feedBase, $products, $shopID)
    {
        $tmpDir = XMLClass::TMP_DIR . 'xmlStatic/';
        $tmpShopDir = $tmpDir . $shopID . '/';
        $xmlDir = XMLClass::XML_DIR . $shopID . '/';

        $out = $tmpShopDir . 'static_feed' . '_' . $feedBase . '.xml';
        $storage = $xmlDir . 'static_feed' . '_' . $feedBase . '.xml';

        XMLClass::createDIR(array($tmpDir, $tmpShopDir, $xmlDir));

        $xml_new = new XMLWriter();
        $xml_new->openURI($out);
        $xml_new->startDocument('1.0', 'UTF-8');
        $xml_new->startElement('PRODUCTS');
        $xml_new->writeAttribute('xmlns', 'http://www.mergado.com/ns/analytic/1.1');

        $xml_new->startElement('DATE');
        $xml_new->text(date('d-m-Y'));
        $xml_new->endElement();

        foreach ($products as $product) {

            // START ITEM
            $xml_new->startElement('ITEM');

            // Product ID
            $xml_new->startElement('ITEM_ID');
            $xml_new->text($product['item_id']);
            $xml_new->endElement();

            // Product price
            $xml_new->startElement('MERGADO_COST');
            $xml_new->text($product['wholesale_price']);
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
}
