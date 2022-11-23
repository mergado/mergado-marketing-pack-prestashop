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
use ToolsCore;
use TranslateCore;
use XMLWriter;
use Mergado;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class XMLStaticFeed extends BaseFeedSimple
{
    const ALERT_SECTION = 'static';
    const FEED_PREFIX = 'static';
    const FEED_NAME = 'static';
    const FEED_DISPLAY_NAME = 'Analytical';

    protected $name;
    protected $nameWithToken;

    public function __construct()
    {
        $this->name = 'static_feed';
        $this->nameWithToken = $this->getOutputXmlName();

        parent::__construct(
            $this->name,
            $this->nameWithToken,
            XMLClass::FEED_COUNT['STATIC'],
            XMLClass::OPTIMIZATION['STATIC_FEED'],
            XMLClass::FEED_PRODUCTS_USER['STATIC'],
            XMLClass::DEFAULT_ITEMS_STEP['STATIC_FEED']
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

            JsonResponse::send_json_success(['success' => TranslateCore::getModuleTranslation('mergado', 'Analytical feed generated', 'AdminMergadoController'), 'feedStatus' => $result['result'], 'percentage' => $result['percentage']]);
        } catch (CronRunningException $e) {
            JsonResponse::send_json_code(['error' => TranslateCore::getModuleTranslation('mergado', 'Analyticial feed generating already running. Please wait a minute and try it again.', 'AdminMergadoController')], 412);
        }
    }

    /**
     * @param false $force
     * @return string
     * @throws CronRunningException
     */
    public function generateXML($force = false)
    {
        $now = new DateTime();
        $this->createNecessaryDirs();

        if ($this->isFeedLocked($now) && !$force) {
            LogClass::log('ANALYTICAL/STATIC FEED LOCKED - generating process can\'t proceed');
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

            $xmlQuery = new XMLQuery();
            $export_out_of_stock_other = SettingsClass::getSettings(SettingsClass::EXPORT['DENIED_PRODUCTS_OTHER'], $this->shopID);
            $products = $xmlQuery->productsToFlat($start, $productsPerStep, intval(Configuration::get('PS_LANG_DEFAULT')), $export_out_of_stock_other);

            // Step generating
            if ($this->isPartial($productsPerStep, $products)) {
                $file = $this->tmpOutputDir . $currentFilesCount . '.xml';

                LogClass::log('Mergado log: Analytical/Static feed generator started - step ' . $currentFilesCount);
                $this->createXML($file, $products);
                LogClass::log('Mergado log: Analytical/Static feed generator ended - step ' . $currentFilesCount);
                LogClass::log('Mergado log: Analytical/Static feed generator saved XML file - step ' . $currentFilesCount);

                $this->unlockFeed();

                return 'stepGenerated';

                // Normal generating
            } else if ($this->isNormal($productsPerStep, $products)) {

                LogClass::log('Mergado log: Analytical/Static feed generator started');
                $this->createXML($this->xmlOutputFile, $products);
                LogClass::log('Mergado log: Analytical/Static feed generator ended');
                LogClass::log('Mergado log: Analytical/Static feed generator saved XML file');

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


    public function createXml($file, $products)
    {
        $xml_new = new XMLWriter();
        $xml_new->openURI($file);
        $xml_new->startDocument('1.0', 'UTF-8');
        $xml_new->startElement('PRODUCTS');
        $xml_new->writeAttribute('xmlns', 'http://www.mergado.com/ns/analytic/1.1');

        $xml_new->startElement('DATE');
        $xml_new->text(date('d-m-Y'));
        $xml_new->endElement();

        foreach ($products as $product) {

            // START ITEM
            $xml_new->startElement('PRODUCT');

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
        LogClass::log('Merging XML files of analytical/static feed.');

        return parent::mergeTemporaryFilesBase();
    }

    public function getOutputXmlName()
    {
        return $this->name . '_' . ToolsCore::getAdminTokenLite('AdminModules');
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    public static function isStaticFeed($feedName) {
        return parent::isFeedType($feedName, self::FEED_PREFIX);
    }

    /*******************************************************************************************************************
     * WIZARD
     *******************************************************************************************************************/

    public static function isWizardFinished($shopId)
    {
        return parent::isWizardFinishedBase(XMLClass::WIZARD['FINISHED_STATIC'], $shopId);
    }

    /*******************************************************************************************************************
     * DATA FOR TEMPLATES
     *******************************************************************************************************************/

    /**
     * @return array
     */
    public function getDataForTemplates(): array
    {
        $data = [
            'createExportInMergadoUrl' => 'https://app.mergado.com/new-project/prefill/?url=' . $this->getFeedUrl() . '&inputFormat=mergado.cz.stats',
        ];

        return array_replace(parent::getDataForTemplatesBaseSimple(self::FEED_DISPLAY_NAME,self::FEED_NAME, self::ALERT_SECTION, 'feeds-other', $this->isWizardFinished($this->shopID)), $data);
    }

    /**
     * @return array
     */
    public function getWizardData(): array
    {
        return parent::getWizardDataBaseSimple(self::FEED_NAME, self::FEED_DISPLAY_NAME,self::ALERT_SECTION, 'feeds-other');
    }
}
