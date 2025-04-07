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

use DateTime;
use Mergado;
use Mergado\Exception\CronRunningException;
use Mergado\Manager\DatabaseManager;
use Mergado\Service\LogService;
use Mergado\Service\SettingsService;
use Mergado\Service\Feed\Base\BaseFeedMulti;
use Mergado\Query\FeedQuery;
use Mergado\Utility\JsonResponse;
use Translate;
use XMLWriter;

class ProductFeed extends BaseFeedMulti
{
    public const FEED_VERSION = 'http://www.mergado.com/ns/1.10';
    public const FEED_PREFIX = 'mergado_feed_';
    public const ALERT_SECTION = 'product';

    public const FEED_COUNT_DB_NAME = 'feed-last-cron-product-count';

    public const USER_ITEM_COUNT_PER_STEP_DB_NAME = 'feed-form-products';
    public const LOWERED_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-form-products-user';
    public const WIZARD_FINISHED_DB_NAME = 'mmp-wizard-finished-product';
    public const DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-products-default-step';

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

            JsonResponse::send_json_success(['success' => Translate::getModuleTranslation('mergado', 'Product feed generated', 'AdminMergadoController'), 'feedStatus' => $result['result'], 'percentage' => $result['percentage']]);
        } catch (CronRunningException $e) {
            JsonResponse::send_json_code(['error' => Translate::getModuleTranslation('mergado', 'Product feed generating already running. Please wait a minute and try it again.', 'AdminMergadoController')], 412);
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
            $this->logger->info('PRODUCT FEED LOCKED - generating process can\'t proceed');
            throw new CronRunningException();
        } else {
            $this->setFeedLocked($now);

            $productsPerStep = $this->getProductsPerStep();

            $currentFilesCount = $this->getCurrentTempFilesCount();
            $start = $this->getStart($currentFilesCount, $productsPerStep);

            $export_out_of_stock = DatabaseManager::getSettingsFromCache(SettingsService::EXPORT['DENIED_PRODUCTS']);

            // If no temporary files, reset generating
            // WAS $start === 1 in WP
            if ($start === 0) {
                $this->resetFeedGenerating();
            }

            $productList = $this->feedQuery->productsToFlat($start, $productsPerStep, $this->currency, $this->language->id, $export_out_of_stock);

            // Step generating
            if (self::isPartial($productsPerStep, $productList)) {
                $file = $this->tmpOutputDir . $currentFilesCount . '.xml';

                $this->logger->info('Mergado log: Product feed generator started [' . $this->name . '] - step ' . $currentFilesCount);
                $this->createXml($file, $productList);
                $this->logger->info('Mergado log: Product feed generator ended [' . $this->name . '] - step ' . $currentFilesCount);
                $this->logger->info('Mergado log: Product feed generator saved XML file [' . $this->name . '] - step ' . $currentFilesCount);

                $this->unlockFeed();

                return 'stepGenerated';

                // Normal generating
            } else if (self::isNormal($productsPerStep, $productList)) {
                $this->logger->info('Mergado log: Product feed generator started [' . $this->name . ']');
                $this->createXml($this->xmlOutputFile, $productList);
                $this->logger->info('Mergado log: Product feed generator ended [' . $this->name . ']');
                $this->logger->info('Mergado log: Product feed generator saved XML file [' . $this->name . ']');

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

    private function createXml(string $file, array $productList = []): void
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
            $xml_new->text((string)$product['item_id']);
            $xml_new->endElement();

            // Product ITEMGROUP
            $xml_new->startElement('ITEMGROUP_ID');
            $xml_new->text((string)$product['itemgroup_id']);
            $xml_new->endElement();

            if ($product['ean'] != 0) {
                $xml_new->startElement('EAN');
                $xml_new->text((string)$product['ean']);
                $xml_new->endElement();
            }

            if ($product['mpn'] !== "") {
                $xml_new->startElement('MPN');
                $xml_new->text((string)$product['mpn']);
                $xml_new->endElement();
            }

            $xml_new->startElement('PRODUCTNO');
            $xml_new->text((string)$product['reference']);
            $xml_new->endElement();

            // Product name
            $xml_new->startElement('NAME_EXACT');
            $xml_new->text((string)$product['name_exact']);
            $xml_new->endElement();

            // Product category
            $xml_new->startElement('CATEGORY');
            $xml_new->text((string)$product['category']);
            $xml_new->endElement();

            // Product description
            $xml_new->startElement('DESCRIPTION');
            $xml_new->text((string)$product['description']);
            $xml_new->endElement();

            // Product short description
            $xml_new->startElement('DESCRIPTION_SHORT');
            $xml_new->text((string)$product['description_short']);
            $xml_new->endElement();

            // Product delivery days
            if ($product['delivery_days'] != '') {
                $xml_new->startElement('DELIVERY_DAYS');
                $xml_new->text((string)$product['delivery_days']);
                $xml_new->endElement();
            }

            // Product currency
            $xml_new->startElement('CURRENCY');
            $xml_new->text((string)$this->currency->iso_code);
            $xml_new->endElement();

            // Product image
            $xml_new->startElement('IMAGE');
            $xml_new->text((string)$product['image']);
            $xml_new->endElement();

            // Product alternative images
            foreach ($product['image_alternative'] as $img) {
                $xml_new->startElement('IMAGE_ALTERNATIVE');
                $xml_new->text((string)$img);
                $xml_new->endElement();
            }

            // Product accessory
            foreach ($product['accessory'] as $ac) {
                $xml_new->startElement('ACCESSORY');
                $xml_new->text((string)$ac);
                $xml_new->endElement();
            }

            // Product PRODUCER
            $xml_new->startElement('PRODUCER');
            $xml_new->text((string)$product['producer']);
            $xml_new->endElement();

            // Product URL
            $xml_new->startElement('URL');
            $xml_new->text((string)$product['url']);
            $xml_new->endElement();

            // Product VAT
            $xml_new->startElement('VAT');
            $xml_new->text((string)$product['vat']);
            $xml_new->endElement();

            // Product price
            $xml_new->startElement('PRICE');
            $xml_new->text((string)$product['price']);
            $xml_new->endElement();

            // Product price VAT
            $xml_new->startElement('PRICE_VAT');
            $xml_new->text((string)$product['price_vat']);
            $xml_new->endElement();

            // Product discount price NO VAT
            if ($product['discount_price']) {
                $xml_new->startElement('PRICE_DISCOUNT');
                $xml_new->text((string)$product['discount_price']);
                $xml_new->endElement();
            }

            // Product discount price VAT
            if ($product['discount_price_vat']) {
                $xml_new->startElement('PRICE_DISCOUNT_VAT');
                $xml_new->text((string)$product['discount_price_vat']);
                $xml_new->endElement();
            }

            if ($product['sale_price_effective_date'] != '') {
                $xml_new->startElement('SALE_PRICE_EFFECTIVE_DATE');
                $xml_new->text((string)$product['sale_price_effective_date']);
                $xml_new->endElement();
            }

            if ($product['cost'] != '') {
                //Product COST
                $xml_new->startElement('COST');
                $xml_new->text((string)$product['cost']);
                $xml_new->endElement();
            }

            if ($product['cost_vat'] != '') {
                //Product COST_VAT
                $xml_new->startElement('COST_VAT');
                $xml_new->text((string)$product['cost_vat']);
                $xml_new->endElement();
            }

            // Product availability
            $xml_new->startElement('AVAILABILITY');
            $xml_new->text((string)$product['availability']);
            $xml_new->endElement();

            // Product condition
            $xml_new->startElement('CONDITION');
            $xml_new->text((string)$product['condition']);
            $xml_new->endElement();

            // Product stock quanity
            $xml_new->startElement('STOCK_QUANTITY');
            $xml_new->text((string)$product['stock_quantity']);
            $xml_new->endElement();

            // Product params
            foreach ($product['params'] as $param) {
                $xml_new->startElement('PARAM');
                $xml_new->startElement('NAME');
                $xml_new->text((string)$param['name']);
                $xml_new->endElement();

                $xml_new->startElement('VALUE');
                $xml_new->text((string)$param['value']);
                $xml_new->endElement();
                $xml_new->endElement();
            }

            if ($product['shipping_size']) {
                // Product size
                $xml_new->startElement('SHIPPING_SIZE');
                $xml_new->text((string)$product['shipping_size']);
                $xml_new->endElement();
            }

            if ($product['shipping_weight']) {
                // Product weight
                $xml_new->startElement('SHIPPING_WEIGHT');
                $xml_new->text((string)$product['shipping_weight']);
                $xml_new->endElement();
            }

            if ($product['tags']) {
                foreach ($product['tags'] as $tag) {
                    $xml_new->startElement('TAG');
                    $xml_new->text((string)$tag);
                    $xml_new->endElement();
                }
            }

            $xml_new->startElement('CATALOG_VISIBILITY');
            $xml_new->text((string)$product['catalog_visibility']);
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

    protected function mergeTemporaryFiles(): bool
    {
        $this->logger->info('Merging XML files of Product feed.');
        return $this->mergeTemporaryFilesBase(self::FEED_VERSION);
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    public static function isProductFeed($feedName): bool
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
        return $this->getDataForTemplatesBaseMulti(self::ALERT_SECTION, 'feeds-product', self::isWizardFinished());
    }

    public function getWizardData(): array
    {
        return $this->getWizardDataBaseMulti(self::ALERT_SECTION, 'feeds-product');
    }
}
