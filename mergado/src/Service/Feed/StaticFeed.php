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
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;
use Translate;
use XMLWriter;

class StaticFeed extends BaseFeedSimple
{
    public const ALERT_SECTION = 'static';
    public const FEED_PREFIX = 'static';
    public const FEED_NAME = 'static';
    public const FEED_DISPLAY_NAME = 'Analytical';

    public const FEED_COUNT_DB_NAME = 'feed-last-cron-static-run-count';

    public const USER_ITEM_COUNT_PER_STEP_DB_NAME = 'feed-form-static';
    public const LOWERED_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-form-static-user';
    public const WIZARD_FINISHED_DB_NAME = 'mmp-wizard-finished-static';
    public const DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-static-default-step';

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

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->logger = LogService::getInstance();
        $this->feedQuery = FeedQuery::getInstance();

        $this->name = 'static_feed';
        $this->nameWithToken = $this->getOutputXmlName();

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

            JsonResponse::send_json_success(['success' => Translate::getModuleTranslation('mergado', 'Analytical feed generated', 'AdminMergadoController'), 'feedStatus' => $result['result'], 'percentage' => $result['percentage']]);
        } catch (CronRunningException $e) {
            JsonResponse::send_json_code(['error' => Translate::getModuleTranslation('mergado', 'Analyticial feed generating already running. Please wait a minute and try it again.', 'AdminMergadoController')], 412);
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
            $this->logger->info('ANALYTICAL/STATIC FEED LOCKED - generating process can\'t proceed');
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

            $export_out_of_stock_other = DatabaseManager::getSettingsFromCache(SettingsService::EXPORT['DENIED_PRODUCTS_OTHER']);
            $products = $this->feedQuery->productsToFlat($start, $productsPerStep, null, intval(Configuration::get('PS_LANG_DEFAULT')), $export_out_of_stock_other);

            // Step generating
            if (self::isPartial($productsPerStep, $products)) {
                $file = $this->tmpOutputDir . $currentFilesCount . '.xml';

                $this->logger->info('Mergado log: Analytical/Static feed generator started - step ' . $currentFilesCount);
                $this->createXML($file, $products);
                $this->logger->info('Mergado log: Analytical/Static feed generator ended - step ' . $currentFilesCount);
                $this->logger->info('Mergado log: Analytical/Static feed generator saved XML file - step ' . $currentFilesCount);

                $this->unlockFeed();

                return 'stepGenerated';

                // Normal generating
            } else if (self::isNormal($productsPerStep, $products)) {

                $this->logger->info('Mergado log: Analytical/Static feed generator started');
                $this->createXML($this->xmlOutputFile, $products);
                $this->logger->info('Mergado log: Analytical/Static feed generator ended');
                $this->logger->info('Mergado log: Analytical/Static feed generator saved XML file');

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

    public function createXml($file, $products): void
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
            $xml_new->text((string)$product['item_id']);
            $xml_new->endElement();

            // Product price
            $xml_new->startElement('MERGADO_COST');
            $xml_new->text((string)$product['wholesale_price']);
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

    private function mergeTemporaryFiles(): bool
    {
        $this->logger->info('Merging XML files of analytical/static feed.');

        return $this->mergeTemporaryFilesBase();
    }

    public function getOutputXmlName(): string
    {
        return $this->name . '_' . Tools::getAdminTokenLite('AdminModules');
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    public static function isStaticFeed($feedName): bool
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
            'createExportInMergadoUrl' => 'https://app.mergado.com/new-project/prefill/?url=' . $this->getFeedUrl() . '&inputFormat=mergado.cz.stats',
        ];

        return array_replace($this->getDataForTemplatesBaseSimple(self::FEED_DISPLAY_NAME, self::FEED_NAME, self::ALERT_SECTION, 'feeds-other', self::isWizardFinished()), $data);
    }

    public function getWizardData(): array
    {
        return $this->getWizardDataBaseSimple(self::FEED_NAME, self::FEED_DISPLAY_NAME, self::ALERT_SECTION, 'feeds-other');
    }
}
