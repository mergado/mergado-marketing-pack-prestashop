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

use CartCore as Cart;
use DateTime;
use JsonResponse;
use Mergado\Tools\CronRunningException;
use Mergado\Tools\LogClass;
use Mergado\Tools\XMLClass;
use CategoryCore as Category;
use LinkCore as Link;
use EmployeeCore as Employee;
use ContextCore as Context;
use TranslateCore;
use XMLWriter;
use Mergado;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class XMLCategoryFeed extends BaseFeedMulti
{
    const FEED_VERSION = 'http://www.mergado.com/ns/category/1.10';
    const FEED_PREFIX = 'category_mergado_feed_';
    const ALERT_SECTION = 'category';

    public function __construct($name)
    {
        parent::__construct(
            $name,
            self::FEED_PREFIX,
            XMLClass::FEED_COUNT['CATEGORY'],
            XMLClass::OPTIMIZATION['CATEGORY_FEED'],
            XMLClass::FEED_PRODUCTS_USER['CATEGORY'],
            XMLClass::DEFAULT_ITEMS_STEP['CATEGORY_FEED']
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

            JsonResponse::send_json_success(['success' => TranslateCore::getModuleTranslation('mergado', 'Category feed generated', 'AdminMergadoController'), 'feedStatus' => $result['result'], 'percentage' => $result['percentage']]);
        } catch (CronRunningException $e) {
            JsonResponse::send_json_code(['error' => TranslateCore::getModuleTranslation('mergado', 'Category feed generating already running. Please wait a minute and try it again.', 'AdminMergadoController')], 412);
        }
    }

    /**
     * @param false $force
     * @return string
     * @throws CronRunningException
     */
    public function generateXml($force = false) {
        $now = new DateTime();
        $this->createNecessaryDirs();

        if($this->isFeedLocked($now) && !$force) {
            LogClass::log('CATEGORY FEED LOCKED - generating process can\'t proceed');
            throw new CronRunningException();
        } else {
            $this->setFeedLocked($now);
            $categoriesPerStep = $this->getProductsPerStep();

            $currentFilesCount = $this->getCurrentTempFilesCount();
            $start = $this->getStart($currentFilesCount, $categoriesPerStep);

            if ($start === 0) {
                $this->resetFeedGenerating();
            }

            $categoryListTotal = Category::getSimpleCategories($this->language->id);

            // isPartial and isNormal not working if total is sent ...
            if($categoriesPerStep !== 0 && count($categoryListTotal) > $categoriesPerStep) {
                // Get only products we need
                $categoryList = array_slice($categoryListTotal, $start, $categoriesPerStep);
            } else {
                $categoryList = $categoryListTotal;
            }

            // Step generating
            if ($this->isPartial($categoriesPerStep, $categoryList)) {
                $file = $this->tmpOutputDir . $currentFilesCount . '.xml';

                LogClass::log('Mergado log: Category feed generator started [' . $this->name . '] - step ' . $currentFilesCount);
                $this->createXml($file, $categoryList);
                LogClass::log('Mergado log: Category feed generator ended [' . $this->name . '] - step ' . $currentFilesCount);
                LogClass::log('Mergado log: Category feed generator saved XML file [' . $this->name . '] - step ' . $currentFilesCount);

                $this->unlockFeed();

                return 'stepGenerated';

            // Normal generating
            } else if ($this->isNormal($categoriesPerStep, $categoryList)) {

                LogClass::log('Mergado log: Category feed generator started [' . $this->name . ']');
                $this->createXml($this->xmlOutputFile, $categoryList);
                LogClass::log('Mergado log: Category feed generator ended [' . $this->name . ']');
                LogClass::log('Mergado log: Category feed generator saved XML file [' . $this->name . ']');

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

    private function createXML($file, $categoryList) {
        $xml_new = new XMLWriter();
        $xml_new->openURI($file);
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
        LogClass::log('Merging XML files of Category feed.');
        return parent::mergeTemporaryFilesBase(self::FEED_VERSION);
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    /**
     * @param $feedName
     * @return bool
     */
    public static function isCategoryFeed($feedName): bool {
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
        return parent::isWizardFinishedBase(XMLClass::WIZARD['FINISHED_CATEGORY'], $shopId);
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
            'createExportInMergadoUrl' => 'https://app.mergado.com/new-project/prefill/?url=' . $this->getFeedUrl() . '&inputFormat=mergado.cz.category',
        ];

        return array_replace(parent::getDataForTemplatesBaseMulti(self::ALERT_SECTION,'feeds-other', $this->isWizardFinished($this->shopID)), $data);
    }

    /**
     * @return array
     */
    public function getWizardData(): array
    {
        return parent::getWizardDataBaseMulti(self::ALERT_SECTION, 'feeds-other');
    }
}
