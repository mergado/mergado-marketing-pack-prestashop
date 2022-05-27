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

use DateTime;
use JsonResponse;
use Mergado\Tools\CronRunningException;
use Mergado\Tools\LogClass;
use Mergado\Tools\SettingsClass;
use Mergado\Tools\XMLClass;
use TranslateCore;
use XMLWriter;
use Mergado;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class XMLProductFeed extends BaseFeedMulti
{
    const FEED_VERSION = 'http://www.mergado.com/ns/1.10';
    const FEED_PREFIX = 'mergado_feed_';
    const ALERT_SECTION = 'product';

    public function __construct($name)
    {
        parent::__construct(
            $name
        );
    }

    /*******************************************************************************************************************
     * XML GENERATORS
     *******************************************************************************************************************/

    /**
     * @param false $force
     * @param false $firstRun
     */
    public function generateXmlAjax($force = false, $firstRun = false)
    {

        try {
            $result = parent::generateXmlAjax($force, $firstRun);

            JsonResponse::send_json_success(['success' => TranslateCore::getModuleTranslation('mergado', 'Product feed generated', 'AdminMergadoController'), 'feedStatus' => $result['result'], 'percentage' => $result['percentage']]);
        } catch (CronRunningException $e) {
            JsonResponse::send_json_code(['error' => TranslateCore::getModuleTranslation('mergado', 'Product feed generating already running. Please wait a minute and try it again.', 'AdminMergadoController')], 412);
        }
    }

    /**
     * @param false $force
     * @return string
     * @throws CronRunningException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function generateXml($force = false)
    {
        $now = new DateTime();
        $this->createNecessaryDirs();

        if ($this->isFeedLocked($now) && !$force) {
            LogClass::log('PRODUCT FEED LOCKED - generating process can\'t proceed');
            throw new CronRunningException();
        } else {
            $this->setFeedLocked($now);

            $productsPerStep = $this->getProductsPerStep();

            $currentFilesCount = $this->getCurrentTempFilesCount();
            $start = $this->getStart($currentFilesCount, $productsPerStep);

            $export_out_of_stock = SettingsClass::getSettings(SettingsClass::EXPORT['DENIED_PRODUCTS'], $this->shopID);

            // If no temporary files, reset generating
            // WAS $start === 1 in WP
            if ($start === 0) {
                $this->resetFeedGenerating();
            }

            $xmlQuery = new XMLQuery($this->currency);
            $productListTotal = $xmlQuery->productsToFlat(0, 0, $this->language->id, $export_out_of_stock);

            // Get only products we need
            if ($productsPerStep !== 0 && count($productListTotal) > $productsPerStep) {
                $productList = array_slice($productListTotal, $start, $productsPerStep);
            } else {
                $productList = $productListTotal;
            }

            // Step generating
            if ($this->isPartial($productsPerStep, $productList)) {
                $file = $this->tmpOutputDir . $currentFilesCount . '.xml';

                LogClass::log('Mergado log: Product feed generator started [' . $this->name . '] - step ' . $currentFilesCount);
                $this->createXml($file, $productList);
                LogClass::log('Mergado log: Product feed generator ended [' . $this->name . '] - step ' . $currentFilesCount);
                LogClass::log('Mergado log: Product feed generator saved XML file [' . $this->name . '] - step ' . $currentFilesCount);

                $this->unlockFeed();

                return 'stepGenerated';

                // Normal generating
            } else if ($this->isNormal($productsPerStep, $productList)) {
                LogClass::log('Mergado log: Product feed generator started [' . $this->name . ']');
                $this->createXml($this->xmlOutputFile, $productList);
                LogClass::log('Mergado log: Product feed generator ended [' . $this->name . ']');
                LogClass::log('Mergado log: Product feed generator saved XML file [' . $this->name . ']');

                $this->unlockFeed();

                return 'fullGenerated';

                // Merge XML
            } else {
                $this->mergeTemporaryFiles();
                $this->unlockFeed();

                return 'merged';
            }
        }
    }

    private function createXml($file, $productList)
    {
        $xml_new = new XMLWriter();

        $xml_new->openURI($file);
        $xml_new->startDocument('1.0', 'UTF-8');
        $xml_new->startElement('CHANNEL');
        $xml_new->writeAttribute('xmlns', self::FEED_VERSION);
        $xml_new->startElement('LINK');
        $xml_new->text(_PS_BASE_URL_ . __PS_BASE_URI__);
        $xml_new->endElement();

        $xml_new->startElement('GENERATOR');
        $xml_new->text('mergado.prestashop.marketingpack.' . str_replace('.', '_', Mergado::MERGADO['VERSION']));
        $xml_new->endElement();

        foreach ($productList as $product) {

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

            if ($product['ean'] != 0) {
                $xml_new->startElement('EAN');
                $xml_new->text($product['ean']);
                $xml_new->endElement();
            }

                    if($product['mpn'] != "") {
                        $xml_new->startElement('MPN');
                        $xml_new->text($product['mpn']);
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

            if ($product['cost'] != '') {
                //Product COST
                $xml_new->startElement('COST');
                $xml_new->text($product['cost']);
                $xml_new->endElement();
            }

            if ($product['cost_vat'] != '') {
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

            if ($product['shipping_size']) {
                // Product size
                $xml_new->startElement('SHIPPING_SIZE');
                $xml_new->text($product['shipping_size']);
                $xml_new->endElement();
            }

            if ($product['shipping_weight']) {
                // Product weight
                $xml_new->startElement('SHIPPING_WEIGHT');
                $xml_new->text($product['shipping_weight']);
                $xml_new->endElement();
            }

            if ($product['tags']) {
                foreach ($product['tags'] as $tag) {
                    $xml_new->startElement('TAG');
                    $xml_new->text($tag);
                    $xml_new->endElement();
                }
            }

            $xml_new->startElement('CATALOG_VISIBILITY');
            $xml_new->text($product['catalog_visibility']);
            $xml_new->endElement();

            // END ITEM
            $xml_new->endElement();
        }

        $xml_new->endElement();
        $xml_new->endDocument();

        $xml_new->flush();
        unset($xml_new);
    }

    /*******************************************************************************************************************
     * MERGE XML
     *******************************************************************************************************************/

    /**
     * Merge xml files to final file
     *
     * @return bool
     */
    protected function mergeTemporaryFiles(): bool
    {
        LogClass::log('Merging XML files of Product feed.');
        return parent::mergeTemporaryFilesBase(self::FEED_VERSION);
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    /**
     * @param $feedName
     * @return bool
     */
    public static function isProductFeed($feedName): bool
    {
        return parent::isFeedType($feedName, self::FEED_PREFIX);
    }

    /*******************************************************************************************************************
     * WIZARD
     *******************************************************************************************************************/

    /**
     * @param $shopId
     * @return bool
     */
    public static function isWizardFinished($shopId): bool
    {
        return parent::isWizardFinishedBase(XMLClass::WIZARD['FINISHED_PRODUCT'], $shopId);
    }

    /*******************************************************************************************************************
     * DATA FOR TEMPLATES
     *******************************************************************************************************************/

    /**
     * @return array
     */
    public function getDataForTemplates(): array
    {
        return parent::getDataForTemplatesBaseMulti(self::ALERT_SECTION, 'feeds-product', $this->isWizardFinished($this->shopID));
    }

    /**
     * @return array
     */
    public function getWizardData(): array
    {
        return parent::getWizardDataBaseMulti(self::ALERT_SECTION, 'feeds-product');
    }
}
