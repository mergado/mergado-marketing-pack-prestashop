<?php declare(strict_types=1);

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

namespace Mergado\Service\Feed;

use Cart;
use Category;
use Context;
use DateTime;
use Employee;
use Link;
use Mergado;
use Mergado\Exception\CronRunningException;
use Mergado\Service\LogService;
use Mergado\Service\Feed\Base\BaseFeedMulti;
use Mergado\Query\FeedQuery;
use Mergado\Utility\JsonResponse;
use PrestaShopDatabaseException;
use PrestaShopException;
use Translate;
use XMLWriter;

class CategoryFeed extends BaseFeedMulti
{
    public const FEED_VERSION = 'http://www.mergado.com/ns/category/1.10';
    public const FEED_PREFIX = 'category_mergado_feed_';
    public const ALERT_SECTION = 'category';

    public const FEED_COUNT_DB_NAME = 'feed-last-cron-category-run-count';
    public const USER_ITEM_COUNT_PER_STEP_DB_NAME = 'feed-form-category';
    public const LOWERED_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-form-category-user';
    public const WIZARD_FINISHED_DB_NAME = 'mmp-wizard-finished-category';
    public const DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-category-default-step';

    /**
     * @var LogService
     */
    private $logger;

    /**
     * @var FeedQuery
     */
    private $feedQuery;

    public function __construct($name)
    {
        $this->logger = LogService::getInstance();
        $this->feedQuery = FeedQuery::getInstance();

        parent::__construct(
            $name,
            self::FEED_PREFIX,
            self::FEED_COUNT_DB_NAME,
            self::USER_ITEM_COUNT_PER_STEP_DB_NAME,
            self::LOWERED_ITEM_COUNT_PER_STEP_DB_NAME,
            self::DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME
        );
    }

    /*******************************************************************************************************************
     * XML GENERATORS
     *******************************************************************************************************************/

    public function generateXmlAjax(bool $force = false, bool $firstRun = false): void
    {
        try {
            $result = $this->generateXmlAjaxBase($force, $firstRun);

            JsonResponse::send_json_success(['success' => Translate::getModuleTranslation('mergado', 'Category feed generated', 'AdminMergadoController'), 'feedStatus' => $result['result'], 'percentage' => $result['percentage']]);
        } catch (CronRunningException $e) {
            JsonResponse::send_json_code(['error' => Translate::getModuleTranslation('mergado', 'Category feed generating already running. Please wait a minute and try it again.', 'AdminMergadoController')], 412);
        }
    }

    /**
     * @throws CronRunningException
     */
    public function generateXml(bool $force = false): string
    {
        $now = new DateTime();
        $this->createNecessaryDirs();

        if ($this->isFeedLocked($now) && !$force) {
            $this->logger->info('CATEGORY FEED LOCKED - generating process can\'t proceed');
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
            if ($categoriesPerStep !== 0 && count($categoryListTotal) > $categoriesPerStep) {
                // Get only products we need
                $categoryList = array_slice($categoryListTotal, $start, $categoriesPerStep);
            } else {
                $categoryList = $categoryListTotal;
            }

            // Step generating
            if (self::isPartial($categoriesPerStep, $categoryList)) {
                $file = $this->tmpOutputDir . $currentFilesCount . '.xml';

                $this->logger->info('Mergado log: Category feed generator started [' . $this->name . '] - step ' . $currentFilesCount);
                $this->createXml($file, $categoryList);
                $this->logger->info('Mergado log: Category feed generator ended [' . $this->name . '] - step ' . $currentFilesCount);
                $this->logger->info('Mergado log: Category feed generator saved XML file [' . $this->name . '] - step ' . $currentFilesCount);

                $this->unlockFeed();

                return 'stepGenerated';

                // Normal generating
            } else if (self::isNormal($categoriesPerStep, $categoryList)) {

                $this->logger->info('Mergado log: Category feed generator started [' . $this->name . ']');
                $this->createXml($this->xmlOutputFile, $categoryList);
                $this->logger->info('Mergado log: Category feed generator ended [' . $this->name . ']');
                $this->logger->info('Mergado log: Category feed generator saved XML file [' . $this->name . ']');

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

    private function createXML(string $file, array $categoryList = []): void
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

        $link = new Link();

        foreach ($categoryList as $cat) {
            if ((int)$cat['id_category'] === 1 || (int)$cat['id_category'] === 2) {
                continue;
            }

            $category = new Category($cat['id_category'], $this->language->id);
            $categoryLink = $link->getCategoryLink($category, $category->link_rewrite, $this->language->id);
            $context = new Context();
            $context->cart = new Cart();
            $context->employee = new Employee();
            $products = $this->feedQuery->getProducts($category, $this->language->id, 0, 10);

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
            $xml_new->text((string)$category->id);
            $xml_new->endElement();

            $xml_new->startElement('CATEGORY_URL');
            $xml_new->text((string)$categoryLink);
            $xml_new->endElement();

            $xml_new->startElement('CATEGORY_QUANTITY');
            $xml_new->text((string)count($products));
            $xml_new->endElement();

            $xml_new->startElement('CATEGORY_DESCRIPTION');
            $xml_new->text('<![CDATA[' . $category->description . ']]');
            $xml_new->endElement();

            $xml_new->startElement('CATEGORY_MIN_PRICE_VAT');
            $xml_new->text((string)$cheapest);
            $xml_new->endElement();

            $xml_new->startElement('CATEGORY_MAX_PRICE_VAT');
            $xml_new->text((string)$expensive);
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
     */
    protected function mergeTemporaryFiles(): bool
    {
        $this->logger->info('Merging XML files of Category feed.');
        return $this->mergeTemporaryFilesBase(self::FEED_VERSION);
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    public static function isCategoryFeed($feedName): bool
    {
        return parent::isFeedType($feedName, self::FEED_PREFIX);
    }


    /*******************************************************************************************************************
     * WIZARD
     *******************************************************************************************************************/

    public static function isWizardFinished(): bool
    {
        return parent::isWizardFinishedBase(self::WIZARD_FINISHED_DB_NAME);
    }

    /*******************************************************************************************************************
     * DATA FOR TEMPLATES
     *******************************************************************************************************************/

    public function getDataForTemplates(): array
    {
        $data = [
            'createExportInMergadoUrl' => 'https://app.mergado.com/new-project/prefill/?url=' . $this->getFeedUrl() . '&inputFormat=mergado.cz.category',
        ];

        return array_replace($this->getDataForTemplatesBaseMulti(self::ALERT_SECTION, 'feeds-other', self::isWizardFinished()), $data);
    }

    public function getWizardData(): array
    {
        return $this->getWizardDataBaseMulti(self::ALERT_SECTION, 'feeds-other');
    }
}
