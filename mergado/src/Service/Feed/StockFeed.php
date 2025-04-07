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

use Configuration;
use DateTime;
use Exception;
use Mergado\Exception\CronRunningException;
use Mergado\Manager\DatabaseManager;
use Mergado\Service\LogService;
use Mergado\Service\SettingsService;
use Mergado\Service\Feed\Base\BaseFeedSimple;
use Mergado\Query\FeedQuery;
use Mergado\Utility\JsonResponse;
use Product;
use StockAvailable;
use Tools;
use Translate;
use XMLWriter;

class StockFeed extends BaseFeedSimple
{
    public const ALERT_SECTION = 'stock';
    public const FEED_PREFIX = 'stock';
    public const FEED_NAME = 'stock';
    public const FEED_DISPLAY_NAME = 'Heureka Availability';

    public const FEED_COUNT_DB_NAME = 'feed-last-cron-stock-count';

    public const USER_ITEM_COUNT_PER_STEP_DB_NAME = 'feed-form-stock';
    public const LOWERED_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-form-stock-user';
    public const WIZARD_FINISHED_DB_NAME = 'mmp-wizard-finished-stock';
    public const DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-stock-default-step';

    protected $defaultLang;
    protected $name;
    protected $nameWithToken;

    /**
     * @var LogService
     */
    private $logger;

    /**
     * @var FeedQuery
     */
    private $feedQuery;

    public function __construct()
    {
        $this->logger = LogService::getInstance();
        $this->feedQuery = FeedQuery::getInstance();

        $this->name = 'stock';
        $this->nameWithToken = $this->getOutputXmlName();

        $this->defaultLang = Configuration::get('PS_LANG_DEFAULT');

        parent::__construct(
            $this->name,
            $this->nameWithToken,
            self::FEED_COUNT_DB_NAME,
            self::USER_ITEM_COUNT_PER_STEP_DB_NAME,
            self::LOWERED_ITEM_COUNT_PER_STEP_DB_NAME,
            self::DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME
        );
    }

    /*******************************************************************************************************************
     * XML GENERATORS
     *******************************************************************************************************************/

    /**
     * @throws Exception
     */
    public function generateXmlAjax(bool $force = false, bool $firstRun = false): void
    {
        try {
            $result = $this->generateXmlAjaxBase($force, $firstRun);

            JsonResponse::send_json_success(['success' => Translate::getModuleTranslation('mergado', 'Heureka stock feed generated', 'AdminMergadoController'), 'feedStatus' => $result['result'], 'percentage' => $result['percentage']]);
        } catch (CronRunningException $e) {
            JsonResponse::send_json_code(['error' => Translate::getModuleTranslation('mergado', 'Heureka stock feed generating already running. Please wait a minute and try it again.', 'AdminMergadoController')], 412);
        }
    }

    /**
     * @throws CronRunningException
     */
    public function generateXML(bool $force = false): string
    {
        $now = new DateTime();
        $this->createNecessaryDirs();

        if ($this->isFeedLocked($now) && !$force) {
            $this->logger->info('STOCK FEED LOCKED - generating process can\'t proceed');
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
            if (self::isPartial($productsPerStep, $productList)) {
                $file = $this->tmpOutputDir . $currentFilesCount . '.xml';

                $this->logger->info('Mergado log: Stock feed generator started - step ' . $currentFilesCount);
                $this->createXML($file, $productList);
                $this->logger->info('Mergado log: Stock feed generator ended - step ' . $currentFilesCount);
                $this->logger->info('Mergado log: Stock feed generator saved XML file - step ' . $currentFilesCount);

                $this->unlockFeed();

                return 'stepGenerated';

                // Normal generating
            } else if (self::isNormal($productsPerStep, $productList)) {
                $this->logger->info('Mergado log: Stock feed generator started');
                $this->createXML($this->xmlOutputFile, $productList);
                $this->logger->info('Mergado log: Stock feed generator ended');
                $this->logger->info('Mergado log: Stock feed generator saved XML file');

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

    private function createXML(string $file, array $productList = []): void
    {
        $xml_new = new XMLWriter();
        $xml_new->openURI($file);
        $xml_new->startDocument('1.0', 'UTF-8');
        $xml_new->startElement('item_list');

        $export_out_of_stock_other = DatabaseManager::getSettingsFromCache(SettingsService::EXPORT['DENIED_PRODUCTS_OTHER']);

        foreach ($productList as $product) {
            $p = new Product($product['id_product']);
            $combinations = $this->feedQuery->getProductCombination($p, $this->defaultLang);
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
                        $xml_new->text((string)$qty);
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
                        $xml_new->writeAttribute('id', (string)$product['id_product']);

                        $xml_new->startElement('stock_quantity');
                        $xml_new->text((string)$qty);
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

    private function mergeTemporaryFiles(): bool
    {
        $this->logger->info('Merging XML files of stock feed.');

        return $this->mergeTemporaryFilesBase();
    }

    public function getOutputXmlName(): string
    {
        return $this->name . '_' . Tools::substr(hash('md5', 'stock' . Configuration::get('PS_SHOP_NAME')), 1, 11);
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    public static function isStockFeed($feedName): bool
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
        return $this->getDataForTemplatesBaseSimple(self::FEED_DISPLAY_NAME, self::FEED_NAME, self::ALERT_SECTION, 'feeds-other', self::isWizardFinished());
    }

    public function getWizardData(): array
    {
        return $this->getWizardDataBaseSimple(self::FEED_NAME, self::FEED_DISPLAY_NAME, self::ALERT_SECTION, 'feeds-other');
    }
}
