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
use ConfigurationCore as Configuration;
use JsonResponse;
use Mergado\Tools\CronRunningException;
use Mergado\Tools\LogClass;
use Mergado\Tools\SettingsClass;
use Mergado\Tools\XMLClass;
use ProductCore as Product;
use StockAvailableCore as StockAvailable;
use Tools;
use TranslateCore;
use XMLWriter;
use Mergado;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class XMLStockFeed extends BaseFeedSimple
{
    const ALERT_SECTION = 'stock';
    const FEED_PREFIX = 'stock';
    const FEED_NAME = 'stock';
    const FEED_DISPLAY_NAME = 'Heureka Availability';

    protected $defaultLang;
    protected $name;
    protected $nameWithToken;

    public function __construct()
    {
        $this->name = 'stock';
        $this->nameWithToken = $this->getOutputXmlName();

        $this->defaultLang = Configuration::get('PS_LANG_DEFAULT');

        parent::__construct(
            $this->name,
            $this->nameWithToken,
            XMLClass::FEED_COUNT['STOCK'],
            XMLClass::OPTIMIZATION['STOCK_FEED'],
            XMLClass::FEED_PRODUCTS_USER['STOCK'],
            XMLClass::DEFAULT_ITEMS_STEP['STOCK_FEED']
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

            JsonResponse::send_json_success(['success' => TranslateCore::getModuleTranslation('mergado', 'Heureka stock feed generated', 'AdminMergadoController'), 'feedStatus' => $result['result'], 'percentage' => $result['percentage']]);
        } catch (CronRunningException $e) {
            JsonResponse::send_json_code(['error' => TranslateCore::getModuleTranslation('mergado', 'Heureka stock feed generating already running. Please wait a minute and try it again.', 'AdminMergadoController')], 412);
        }
    }

    /**
     * @return string
     * @throws CronRunningException
     */
    public function generateXML($force = false)
    {
        $now = new DateTime();
        $this->createNecessaryDirs();

        if ($this->isFeedLocked($now) && !$force) {
            LogClass::log('STOCK FEED LOCKED - generating process can\'t proceed');
            throw new CronRunningException();
        } else {
            $this->setFeedLocked($now);

            $productsPerStep = $this->getProductsPerStep();

            $currentFilesCount = $this->getCurrentTempFilesCount();
            $start = $this->getStart($currentFilesCount, $productsPerStep);

            // If no temporary files, reset generating
//            WAS $start === 1 in WP
            if ($start === 0) {
                $this->resetFeedGenerating();
            }

            $productList = Product::getProducts($this->defaultLang, $start, $productsPerStep, 'id_product', 'ASC', false, true);

            // Step generating
            if ($this->isPartial($productsPerStep, $productList)) {
                $file = $this->tmpOutputDir . $currentFilesCount . '.xml';

                LogClass::log('Mergado log: Stock feed generator started - step ' . $currentFilesCount);
                $this->createXML($file, $productList);
                LogClass::log('Mergado log: Stock feed generator ended - step ' . $currentFilesCount);
                LogClass::log('Mergado log: Stock feed generator saved XML file - step ' . $currentFilesCount);

                $this->unlockFeed();

                return 'stepGenerated';

            // Normal generating
            } else if ($this->isNormal($productsPerStep, $productList)) {
                LogClass::log('Mergado log: Stock feed generator started');
                $this->createXML($this->xmlOutputFile, $productList);
                LogClass::log('Mergado log: Stock feed generator ended');
                LogClass::log('Mergado log: Stock feed generator saved XML file');

                $this->unlockFeed();
                return 'fullGenerated';

            // Merge XML
            } else {
                $this->updateFeedCount();
                $this->mergeTemporaryFiles();
                $this->unlockFeed();

                return 'merged';
            }
        }
    }

    /*******************************************************************************************************************
     * CREATE XML
     *******************************************************************************************************************/

    private function createXML($file, $productList) {
        $xml_new = new XMLWriter();
        $xml_new->openURI($file);
        $xml_new->startDocument('1.0', 'UTF-8');
        $xml_new->startElement('item_list');

        $export_out_of_stock_other = SettingsClass::getSettings(SettingsClass::EXPORT['DENIED_PRODUCTS_OTHER'], $this->shopID);

        foreach ($productList as $product) {
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

                    if ($qty <= 0 && $whenOutOfStock == 0 && !$export_out_of_stock_other) {
                        continue;
                    }

                    if ($qty > 0 || $export_out_of_stock_other) {
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

                if ($qty <= 0 && $whenOutOfStock == 0 && !$export_out_of_stock_other) {
                    // skip
                } else {
                    if ($qty > 0 || $export_out_of_stock_other) {
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
    }

    /*******************************************************************************************************************
     * MERGE XML
     *******************************************************************************************************************/

    /**
     * Merge files, create XML and delete temporary files
     * @return bool;
     */
    private function mergeTemporaryFiles()
    {
        LogClass::log('Merging XML files of stock feed.');

        return parent::mergeTemporaryFilesBase();
    }

    public function getOutputXmlName()
    {
        return $this->name . '_' . Tools::substr(hash('md5', 'stock' . Configuration::get('PS_SHOP_NAME')), 1, 11);
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    /**
     * @param $feedName
     * @return bool
     */
    public static function isStockFeed($feedName): bool {
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
        return parent::isWizardFinishedBase(XMLClass::WIZARD['FINISHED_STOCK'], $shopId);
    }

    /*******************************************************************************************************************
     * DATA FOR TEMPLATES
     *******************************************************************************************************************/

    /**
     * @return array
     */
    public function getDataForTemplates(): array
    {
        return parent::getDataForTemplatesBaseSimple(self::FEED_DISPLAY_NAME,self::FEED_NAME, self::ALERT_SECTION, 'feeds-other', $this->isWizardFinished($this->shopID));
    }

    /**
     * @return array
     */
    public function getWizardData(): array
    {
        return parent::getWizardDataBaseSimple(self::FEED_NAME,self::FEED_DISPLAY_NAME, self::ALERT_SECTION, 'feeds-other' );
    }
}
