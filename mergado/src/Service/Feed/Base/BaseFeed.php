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
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Service\Feed\Base;

use Mergado;
use Mergado\Helper\ShopHelper;
use Mergado\Helper\UrlHelper;
use Mergado\Manager\DirectoryManager;
use Mergado\Service\AlertService;
use Mergado\Service\LogService;
use Mergado\Exception\CronRunningException;
use Mergado\Manager\DatabaseManager;
use Tools;
use Translate;

class BaseFeed
{
    public const TMP_DIR = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/tmp/';
    public const XML_DIR = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/xml/';

    protected $shopID;
    protected $name;
    protected $nameWithToken;
    protected $optimizationVariableName;
    protected $loweredCountVariableName;
    protected $defaultCountVariableName;

    protected $feedCountVariableName;

    protected $tmpDir;
    protected $tmpShopDir;
    protected $xmlOutputFile;
    protected $tmpOutputDir;
    protected $xmlOutputDir;

    /**
     * @var LogService
     */
    private $logger;

    /**
     * @var AlertService
     */
    private $alertService;

    public function __construct(
        $name,
        $nameWithToken,
        $optimizationVariableName,
        $loweredCountVariableName,
        $defaultCountVariableName,
        $tmpDir,
        $tmpShopDir,
        $tmpOutputDir,
        $xmlOutputFile,
        $xmlOutputDir,
        $feedCountVariableName
    )
    {
        $this->name = $name;
        $this->nameWithToken = $nameWithToken;
        $this->shopID = ShopHelper::getId();

        $this->optimizationVariableName = $optimizationVariableName;
        $this->loweredCountVariableName = $loweredCountVariableName;
        $this->defaultCountVariableName = $defaultCountVariableName;

        $this->feedCountVariableName = $feedCountVariableName;

        $this->tmpDir = $tmpDir;
        $this->tmpShopDir = $tmpShopDir;
        $this->tmpOutputDir = $tmpOutputDir;
        $this->xmlOutputFile = $xmlOutputFile;

        $this->xmlOutputDir = $xmlOutputDir;

        $this->logger = LogService::getInstance();
        $this->alertService = AlertService::getInstance();
    }

    /**
     * @throws CronRunningException
     */
    public function generateXmlAjaxBase(bool $force = false, bool $firstRun = false): array
    {
        if ($firstRun) {
            $this->deleteLoweredProductsPerStep();
        }

        if ($firstRun && $this->hasFeedFailed()) {
            $this->setLowerProductsPerStep($this->getDefaultProductsPerStep());
        }

        $result = $this->generateXML($force);
        $percentage = $this->getFeedPercentage();

        // Save lowered value as main if cron is ok without internal error
        if ($this->getLoweredProductsPerStep() !== 0) {
            $this->setLoweredProductsPerStepAsMain();
        }

        $this->alertService->setErrorInactive($this->name, AlertService::ALERT_NAMES['ERROR_DURING_GENERATION']);

        return ['result' => $result, 'percentage' => $percentage];
    }

    /*******************************************************************************************************************
     * FEED FOLDER MANIPULATION
     *******************************************************************************************************************/

    protected function deleteTemporaryFilesByDirAndFeedType(string $tmpDir, string $feedPrefix = ''): void
    {
        $dirs = glob($tmpDir . $feedPrefix . '*');

        foreach ($dirs as $dir) {
            DirectoryManager::removeFilesInDirectory($dir);
        }
    }

    /**
     * Reset feed and delete all TMP files
     */
    protected function resetFeedGenerating(): void
    {
        $this->deleteTemporaryFilesByDirAndFeedType($this->tmpOutputDir);
    }

    /**
     * Check and create necessary directories for this cron
     */
    public function createNecessaryDirs(): void
    {
        DirectoryManager::createDIR(
            [
                $this->tmpDir,
                $this->tmpShopDir,
                $this->tmpOutputDir,
                $this->xmlOutputDir
            ]
        );
    }

    public function deleteTemporaryFiles(): void
    {
        DirectoryManager::removeFilesInDirectory($this->tmpOutputDir);

        $this->logger->info('Temporary files in directory deleted - ' . $this->tmpOutputDir);
    }

    public function deleteXml(): void
    {
        DirectoryManager::removeFile($this->xmlOutputFile);

        $this->logger->info('XML file deleted - ' . $this->xmlOutputFile);
    }

    /*******************************************************************************************************************
     * FEED LINKS - must be static methods because of multi currency and multilanguage shops
     *******************************************************************************************************************/

    public function getFeedUrl(): string
    {
        return UrlHelper::getShopModuleUrl() . '/xml/' . $this->shopID . '/' . $this->nameWithToken . '.xml';
    }

    public function getCronUrl(): string
    {
        return UrlHelper::getShopModuleUrl() . '/cron.php?feed=' . $this->name .
            '&token=' . Tools::substr(Tools::encrypt('mergado/cron'), 0, 10);
    }

    /*******************************************************************************************************************
     * FIELD COUNT
     *******************************************************************************************************************/

    public function getCurrentTempFilesCount(): int
    {
        if (glob($this->tmpOutputDir . '*.xml')) {
            return count(glob($this->tmpOutputDir . '*.xml'));
        }

        return 0;
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    public static function isPartial($stepProducts, $productList): bool
    {
        return $stepProducts !== 0 && $productList !== [];
    }

    public static function isNormal($stepProducts, $productList): bool
    {
        return $stepProducts === 0 || $stepProducts === false || ($productList && ($stepProducts >= count($productList)));
    }

    /*******************************************************************************************************************
     * FEED
     *******************************************************************************************************************/

    public function getStart(int $currentFilesCount, int $stepProducts)
    {
        return $currentFilesCount === 0 ? 0 : ($currentFilesCount * $stepProducts);
    }

    public function isFeedExist(): bool
    {
        return file_exists($this->xmlOutputFile);
    }

    public function getFeedPercentage(): int
    {
        $productsPerRun = $this->getProductsPerStep();

        $currentNumberOfFiles = $this->getCurrentTempFilesCount();
        $totalFiles = $this->getTotalFiles($productsPerRun);

        if ($totalFiles === 0) {
            return 0;
        }

        return (int)round(($currentNumberOfFiles / ($totalFiles)) * 100);
    }


    private function getTotalFiles($productsPerRun): int
    {
        if ($productsPerRun === 0) {
            $totalFiles = 0;
        } else {
            $totalFiles = DatabaseManager::getSettingsFromCache($this->feedCountVariableName, $this->shopID);
        }

        // Just magic for loading
        if ($totalFiles === false) {
            $totalFiles = 100;
        }

        return (int)$totalFiles;
    }

    public function updateFeedCount(): bool
    {
        return DatabaseManager::saveSetting($this->feedCountVariableName, $this->getCurrentTempFilesCount(), $this->shopID);
    }

    public function getLastFeedChangeTimestamp()
    {
        $path = $this->xmlOutputFile;

        if (file_exists($path)) {
            $lastUpdate = filemtime($path);
        } else {
            $lastUpdate = false;
        }

        if ($lastUpdate) {
            return $lastUpdate;
        }

        return false;
    }

    public function getLastFeedChange()
    {
        $path = $this->xmlOutputFile;

        $lastUpdate = $this->getLastFeedChangeTimestamp();

        $dateFormat = Translate::getModuleTranslation('mergado', 'Y-m-d H:i', 'AdminMergadoController');

        if ($lastUpdate) {
            $lastUpdate = date($dateFormat, filemtime($path));
        } else {
            $lastUpdate = false;
        }

        return $lastUpdate;
    }

    /*******************************************************************************************************************
     * PRODUCTS PER STEP
     ******************************************************************************************************************/

    public function getDefaultProductsPerStep(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this->defaultCountVariableName);
    }

    public function lowerProductsPerStep()
    {
        $productsPerStep = $this->getProductsPerStep();

        $loweredValue = round($productsPerStep / 2);

        if ($loweredValue < 10 && $loweredValue != 0) {
            $response = false;
        } else if ($this->setLoweredProductsPerStep($this->loweredCountVariableName, $loweredValue, $this->shopID)) {
            $response = $loweredValue;
        } else {
            $response = false;
        }

        if ($response === false) {
            $this->deleteLoweredProductsPerStep();
        }

        return $response;
    }

    public function setLoweredProductsPerStep($loweredProductPerStepName, $value, $shopId): bool
    {
        return DatabaseManager::saveSetting($loweredProductPerStepName, $value, $shopId);
    }

    public function getProductsPerStep(): int
    {
        $loweredProductsPerStep = $this->getLoweredProductsPerStep();

        return self::getItemsPerStep($this->optimizationVariableName, $loweredProductsPerStep, $this->shopID);
    }

    public static function getItemsPerStep($feedName, $loweredProductsPerStep): int
    {
        if ($loweredProductsPerStep !== 0 && $loweredProductsPerStep !== '') {
            return (int)$loweredProductsPerStep;
        }

        return (int)DatabaseManager::getSettingsFromCache($feedName);
    }

    /**
     * Return value of lowered product step (repetitive call if 500 error timeout)
     */
    public function getLoweredProductsPerStep(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this->loweredCountVariableName);
    }

    public function deleteLoweredProductsPerStep(): bool
    {
        return $this->setLoweredProductsPerStep($this->loweredCountVariableName, 0, $this->shopID);
    }

    public function setLowerProductsPerStep($value): bool
    {
        return $this->setLoweredProductsPerStep($this->loweredCountVariableName, $value, $this->shopID);
    }

    public function setLoweredProductsPerStepAsMain(): void
    {
        $productsPerStep = $this->getLoweredProductsPerStep();
        $this->setProductsPerStep($productsPerStep);
        $this->deleteLoweredProductsPerStep();
    }

    public function setProductsPerStep($value): bool
    {
        return DatabaseManager::saveSetting($this->optimizationVariableName, $value, $this->shopID);
    }

    /*******************************************************************************************************************
     * FEED ERRORS
     ******************************************************************************************************************/

    public function hasFeedFailed(): bool
    {
        $errors = $this->alertService->getFeedErrors($this->name);
        return in_array(AlertService::ALERT_NAMES['ERROR_DURING_GENERATION'], $errors, true);
    }

    /*******************************************************************************************************************
     * FEED LOCKING
     *******************************************************************************************************************/

    protected function isFeedLocked($now): bool
    {
        $lock = DatabaseManager::getSettingsFromCache('feed_lock_' . $this->name);

        return $lock && $lock !== 0 && $lock >= $now->getTimestamp();
    }

    protected function setFeedLocked($now): void
    {
        DatabaseManager::saveSetting('feed_lock_' . $this->name, $now->modify("+1 minute")->getTimestamp(), $this->shopID);
        $this->logger->info($this->name . ' - locking');
    }

    protected function unlockFeed(): void
    {
        DatabaseManager::saveSetting('feed_lock_' . $this->name, 0, $this->shopID);
        $this->logger->info($this->name . ' - unlocking');
    }

    public static function deleteTmpFiles($prefix, $shopId): void
    {
        $folders = glob(self::TMP_DIR . 'xml/' . $shopId . '/' . $prefix . '*');

        foreach ($folders as $folder) {
            DirectoryManager::removeFilesInDirectory($folder);
        }
    }

    protected function showCongratulations(): bool
    {
        $output = isset($_GET['mmp-alert-congratulations']) && $_GET['mmp-alert-congratulations'] === $this->name;

        if ($output) {
            unset($_GET['mmp-alert-congratulations']);
        }

        return $output;
    }

    protected static function isWizardFinishedBase($wizard): bool
    {
        return (bool)DatabaseManager::getSettingsFromCache($wizard);
    }

    protected static function isFeedType(string $feedName, string $prefix): bool
    {
        return strpos($feedName, $prefix) === 0;
    }

    protected function getDataForTemplatesBase(): array
    {
        $feedUrl = $this->getFeedUrl();

        $errors = $this->alertService->getFeedErrors($this->name);

        $feedExist = $this->isFeedExist();
        $percentage = $this->getFeedPercentage();

        if (!$feedExist && !$percentage) {
            $feedStatus = 'danger';
        } else if ($feedExist) {
            $feedStatus = 'success';
        } else {
            $feedStatus = 'warning';
        }

        if (in_array(AlertService::ALERT_NAMES['ERROR_DURING_GENERATION'], $errors, true)) {
            $feedStatusClass = 'mmp_feedBox__feedStatus--danger';
        } else {
            $feedStatusClass = 'mmp_feedBox__feedStatus--' . $feedStatus;
        }

        return [
            'createExportInMergadoUrl' => false,
            'cronGenerateUrl' => $this->getCronUrl(),
            'downloadUrl' => $feedUrl,
            'errorDuringGeneration' => in_array(AlertService::ALERT_NAMES['ERROR_DURING_GENERATION'], $errors, true),
            'feedExist' => $feedExist,
            'feedBoxData' => [
                'statusClass' => $feedStatusClass,
                'errors' => $errors,
                'errorCount' => count($errors),
                'errorDuringGeneration' => in_array(AlertService::ALERT_NAMES['ERROR_DURING_GENERATION'], $errors, true),
                'errorNotUpdated' => in_array(AlertService::ALERT_NAMES['NO_FEED_UPDATE'], $errors, true)
            ],
            'feedUrl' => $feedUrl,
            'feedStatus' => $feedStatus,
            'lastUpdate' => $this->getLastFeedChange(),
            'percentageStep' => $percentage,
            'showCongratulations' => $this->showCongratulations(),
        ];
    }

    protected function getWizardDataBase(): array
    {
        return [
            'ajaxGenerateAction' => 'generate_xml',
            'cronAction' => 'generate_xml',
            'cronUrl' => $this->getCronUrl(),
            'feedUrl' => $this->getFeedUrl(),
            'frontendData' => [
                'productsPerStep' => $this->getProductsPerStep(),
                'feedRunning' => false,
                'feedFinished' => false,
            ],
            'percentage' => $this->getFeedPercentage(),
            'productsPerStep' => $this->getProductsPerStep(),
        ];
    }
}
