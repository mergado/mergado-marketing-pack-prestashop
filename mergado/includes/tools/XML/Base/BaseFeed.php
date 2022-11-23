<?php

namespace Mergado\Tools\XML;

use AlertClass;
use Mergado;
use Mergado\Tools\CronRunningException;
use Mergado\Tools\DirectoryManager;
use Mergado\Tools\LogClass;
use Mergado\Tools\SettingsClass;
use Mergado\Tools\UrlManager;
use Mergado\Tools\XMLClass;
use ObjectModel;
use Tools;
use Translate;
use TranslateCore;
use XMLWriter;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class BaseFeed extends ObjectModel
{
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
        $this->shopID = Mergado::getShopId();

        $this->optimizationVariableName = $optimizationVariableName;
        $this->loweredCountVariableName = $loweredCountVariableName;
        $this->defaultCountVariableName = $defaultCountVariableName;

        $this->feedCountVariableName= $feedCountVariableName;

        $this->tmpDir = $tmpDir;
        $this->tmpShopDir = $tmpShopDir;
        $this->tmpOutputDir = $tmpOutputDir;
        $this->xmlOutputFile = $xmlOutputFile;

        $this->xmlOutputDir = $xmlOutputDir;
    }

    /**
     * @param false $force
     * @param false $firstRun
     * @throws CronRunningException
     */
    public function generateXmlAjax($force = false, $firstRun = false)
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

        $alertClass = new AlertClass();
        $alertClass->setErrorInactive($this->name, AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION']);

        return ['result' => $result, 'percentage' => $percentage];
    }

    /*******************************************************************************************************************
     * FEED FOLDER MANIPULATION
     *******************************************************************************************************************/

    /**
     * Reset feed and delete all TMP files
     */
    protected function resetFeedGenerating()
    {
        XMLClass::deleteTemporaryFiles($this->tmpOutputDir);
    }

    /**
     * Check and create necessary directories for this cron
     */
    public function createNecessaryDirs()
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

    public function deleteTemporaryFiles()
    {
        DirectoryManager::removeFilesInDirectory($this->tmpOutputDir);

        LogClass::log('Temporary files in directory deleted - ' . $this->tmpOutputDir);
    }


    /**
     * @return void
     */
    public function deleteXml()
    {
        DirectoryManager::removeFile($this->xmlOutputFile);

        LogClass::log('XML file deleted - ' . $this->xmlOutputFile);
    }

    /*******************************************************************************************************************
     * FEED LINKS - must be static methods because of multicurrency and multilanguage shops
     *******************************************************************************************************************/

    public function getFeedUrl()
    {
        return UrlManager::getFeedUrl($this->nameWithToken,  $this->shopID);
    }

    public function getCronUrl()
    {
        return UrlManager::getCronUrl($this->name,  $this->shopID);
    }

    public static function getFeedUrlFromFilename($filename, $shopId)
    {
        return UrlManager::getFeedUrl($filename, $shopId);
    }

    public static function getCronUrlFromFilename($filename, $shopId)
    {
        return UrlManager::getCronUrl($filename, $shopId);
    }

    /*******************************************************************************************************************
     * FIELD COUNT
     *******************************************************************************************************************/

    public function getCurrentTempFilesCount()
    {
        if (glob($this->tmpOutputDir . '*.xml') != false) {
            return count(glob($this->tmpOutputDir . '*.xml'));
        } else {
            return 0;
        }
    }

    /*******************************************************************************************************************
     * FEED TYPE
     *******************************************************************************************************************/

    public static function isPartial($stepProducts, $productList)
    {
        return $stepProducts !== 0 && $productList !== [];
    }

    public static function isNormal($stepProducts, $productList)
    {
        return $stepProducts === 0 || $stepProducts === false || ($productList && ($stepProducts >= count($productList)));
    }

    /*******************************************************************************************************************
     * FEED
     *******************************************************************************************************************/

    /**
     * @param $currentFilesCount
     * @param $stepProducts
     * @return int
     */
    public function getStart($currentFilesCount, $stepProducts)
    {
        return $currentFilesCount === 0 ? 0 : ($currentFilesCount * $stepProducts); // Original stock working
//        return $currentFilesCount === 0 ? 0 : $currentFilesCount + 1; // From WP
    }

    public function isFeedExist()
    {
        return file_exists($this->xmlOutputFile);
    }

    public function getFeedPercentage()
    {
        $productsPerRun = $this->getProductsPerStep();

        $currentNumberOfFiles = $this->getCurrentTempFilesCount();
        $totalFiles = $this->getTotalFiles($productsPerRun);

        if ($totalFiles === 0) {
            return 0;
        }

        return intval(round(($currentNumberOfFiles / ($totalFiles)) * 100));
    }


    private function getTotalFiles($productsPerRun)
    {
        if ($productsPerRun === 0) {
            $totalFiles = 0;
        } else {
            $totalFiles = SettingsClass::getSettings($this->feedCountVariableName, $this->shopID);
        }

        // Just magic for loading
        if ($totalFiles === false) {
            $totalFiles = 100;
        }

        return $totalFiles;
    }

    public function updateFeedCount()
    {
        return SettingsClass::saveSetting($this->feedCountVariableName, $this->getCurrentTempFilesCount(), $this->shopID);
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
        } else {
            return false;
        }
    }

    public function getLastFeedChange()
    {
        $path = $this->xmlOutputFile;

        $lastUpdate = $this->getLastFeedChangeTimestamp();

        $dateFormat = TranslateCore::getModuleTranslation('mergado', 'Y-m-d H:i', 'AdminMergadoController');

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

    public function getDefaultProductsPerStep()
    {
        return (int)SettingsClass::getSettings($this->defaultCountVariableName, $this->shopID);
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

    public function setLoweredProductsPerStep($loweredProductPerStepName, $value, $shopId)
    {
        return SettingsClass::saveSetting($loweredProductPerStepName, $value, $shopId);
    }

    /**
     * Return value of product per step
     * @return int
     */
    public function getProductsPerStep()
    {
        $loweredProductsPerStep = $this->getLoweredProductsPerStep();

        return $this->getItemsPerStep($this->optimizationVariableName, $loweredProductsPerStep, $this->shopID);
    }

    /**
     * @param $feedName
     * @return int
     */
    public static function getItemsPerStep($feedName, $loweredProductsPerStep, $shopId)
    {
        if ($loweredProductsPerStep != 0 && $loweredProductsPerStep !== '') {
            return $loweredProductsPerStep;
        } else {
            return (int)SettingsClass::getSettings($feedName, $shopId);
        }
    }

    /**
     * Return value of lowered product step (repetetive call if 500 error timeout)
     */
    public function getLoweredProductsPerStep()
    {
        return (int)SettingsClass::getSettings($this->loweredCountVariableName, $this->shopID);
    }

    public function deleteLoweredProductsPerStep()
    {
        return $this->setLoweredProductsPerStep($this->loweredCountVariableName, 0, $this->shopID);
    }

    public function setLowerProductsPerStep($value)
    {
        return $this->setLoweredProductsPerStep($this->loweredCountVariableName, $value, $this->shopID);
    }

    public function setLoweredProductsPerStepAsMain()
    {
        $productsPerStep = $this->getLoweredProductsPerStep();
        $this->setProductsPerStep($productsPerStep);
        $this->deleteLoweredProductsPerStep();
    }

    public function setProductsPerStep($value)
    {
        return SettingsClass::saveSetting($this->optimizationVariableName, $value, $this->shopID);
    }

    /*******************************************************************************************************************
     * FEED ERRORS
     ******************************************************************************************************************/

    public function hasFeedFailed()
    {
        $alertClass = new AlertClass();
        $errors = $alertClass->getFeedErrors($this->name);
        return in_array(AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION'], $errors);
    }

    /*******************************************************************************************************************
     * FEED LOCKING
     *******************************************************************************************************************/

    /**
     * Return if feed is currently locked
     *
     * @param $now
     * @return bool
     */
    protected function isFeedLocked($now)
    {
        $lock = SettingsClass::getSettings('feed_lock_' . $this->name, $this->shopID);

        if ($lock && $lock !== 0 && $lock >= $now->getTimestamp()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Lock feed
     * @param $now
     */
    protected function setFeedLocked($now)
    {
        SettingsClass::saveSetting('feed_lock_' . $this->name, $now->modify("+1 minute")->getTimestamp(), $this->shopID);
        LogClass::log($this->name . ' - locking');
    }

    /**
     * Unlock feed
     */
    protected function unlockFeed()
    {
        SettingsClass::saveSetting('feed_lock_' . $this->name, 0, $this->shopID);
        LogClass::log($this->name . ' - unlocking');
    }

    public static function deleteTmpFiles($prefix, $shopId)
    {
        $folders = glob(XMLClass::TMP_DIR . 'xml/' . $shopId . '/' . $prefix . '*');

        foreach($folders as $folder) {
            DirectoryManager::removeFilesInDirectory($folder);
        }
    }

    /**
     * Should i show congratulations box on page?
     * @return bool
     */
    protected function showCongratulations() {
        $output = isset($_GET['mmp-alert-congratulations']) && $_GET['mmp-alert-congratulations'] === $this->name;

        if ($output) {
            unset($_GET['mmp-alert-congratulations']);
        }

        return $output;
    }

    /**
     * @param $wizard
     * @param $shopId
     * @return bool
     */
    protected static function isWizardFinishedBase($wizard, $shopId) : bool
    {
        return (bool)SettingsClass::getSettings($wizard, $shopId);
    }

     /**
     * @param string $feedName
     * @param string $prefix
     * @return bool
     */
    protected static function isFeedType(string $feedName, string $prefix) {
        return strpos( $feedName, $prefix ) === 0;
    }

    /**
     * @return array
     */
    protected function getDataForTemplatesBase(): array
    {
        $feedUrl = $this->getFeedUrl();

        $alertClass = new AlertClass();
        $errors = $alertClass->getFeedErrors($this->name);

        $feedExist = $this->isFeedExist();
        $percentage = $this->getFeedPercentage();

        if (!$feedExist && !$percentage) {
            $feedStatus = 'danger';
        } else if ($feedExist) {
            $feedStatus = 'success';
        } else {
            $feedStatus = 'warning';
        }

        if (in_array(AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION'], $errors)) {
            $feedStatusClass = 'mmp_feedBox__feedStatus--danger';
        } else {
            $feedStatusClass = 'mmp_feedBox__feedStatus--' . $feedStatus;
        }

        return [
            'createExportInMergadoUrl' => false,
            'cronGenerateUrl' =>  $this->getCronUrl(),
            'downloadUrl' => $feedUrl,
            'errorDuringGeneration' => in_array(AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION'], $errors),
            'feedExist' => $feedExist,
            'feedBoxData' => [
                    'statusClass' => $feedStatusClass,
                    'errors' => $errors,
                    'errorCount' => count($errors),
                    'errorDuringGeneration' => in_array(AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION'], $errors),
                    'errorNotUpdated' => in_array(AlertClass::ALERT_NAMES['NO_FEED_UPDATE'], $errors)
            ],
            'feedUrl' => $feedUrl,
            'feedStatus' => $feedStatus,
            'lastUpdate' => $this->getLastFeedChange(),
            'percentageStep' => $percentage,
            'showCongratulations' => $this->showCongratulations(),
        ];
    }

    /**
     * @return array
     */
    protected function getWizardDataBase(): array
    {
        return [
            'ajaxGenerateAction' => 'generate_xml',
            'cronAction' => 'generate_xml',
            'cronUrl' =>  $this->getCronUrl(),
            'feedUrl' =>  $this->getFeedUrl(),
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
