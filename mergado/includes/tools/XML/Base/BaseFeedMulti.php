<?php

namespace Mergado\Tools\XML;

use Configuration;
use Currency;
use Language;
use Mergado;
use Mergado\Tools\LogClass;
use Mergado\Tools\UrlManager;
use Mergado\Tools\XMLClass;
use Tools;
use XMLWriter;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class BaseFeedMulti extends BaseFeed
{
    protected $feedPrefix;

    protected $currencyIso;
    protected $langIso;

    protected $language;
    protected $shopID;
    protected $name;
    protected $nameWithToken;
    protected $currency;
    protected $langAndIsoName;

    protected $feedCountVariableName;

    protected $tmpDir;
    protected $tmpShopDir;
    protected $tmpOutputDir;
    protected $xmlOutputDir;
    protected $xmlOutputFile;

    public function __construct($name, $feedPrefix, $feedCountVariableName, $optimizationVariableName, $loweredCountVariableName, $defaultCountVariableName) {

        $this->name = $name;
        $this->nameWithToken = $this->getOutputXmlNameWithLangAndCurrency($this->name);

        $this->language = $this->getLanguageFromFeedName($this->name);
        $this->currency = $this->getCurrencyFromFeedName($this->name);
        $this->langIso = $this->getLanguageIsoFromFeedName($this->name);
        $this->currencyIso = $this->getCurrencyIsoFromFeedName($this->name);

        $this->shopID = Mergado::getShopId();
        $this->langAndIsoName = $this->langIso . '-' . $this->currencyIso;

        $this->xmlOutputDir = XMLClass::XML_DIR . $this->shopID . '/';
        $this->tmpDir = XMLClass::TMP_DIR . 'xml/';
        $this->tmpShopDir = $this->tmpDir . $this->shopID . '/';
        $this->tmpOutputDir = $this->tmpShopDir . $this->name . '/';

        $this->xmlOutputFile = $this->xmlOutputDir . $this->nameWithToken . '.xml';

        $this->feedPrefix = $feedPrefix;

        $this->feedCountVariableName = $feedCountVariableName;

        $this->optimizationVariableName = $optimizationVariableName;
        $this->loweredCountVariableName = $loweredCountVariableName;
        $this->defaultCountVariableName = $defaultCountVariableName;

        parent::__construct(
            $name,
            $this->nameWithToken,
            $this->optimizationVariableName,
            $this->loweredCountVariableName,
            $this->defaultCountVariableName,
            $this->tmpDir,
            $this->tmpShopDir,
            $this->tmpOutputDir,
            $this->xmlOutputFile,
            $this->xmlOutputDir,
            $this->feedCountVariableName
        );
    }

    /*******************************************************************************************************************
     * MERGE XML
     *******************************************************************************************************************/

    /**
     * Merge xml files to final file
     *
     * @param string $feedVersion
     * @return bool
     */
    protected function mergeTemporaryFilesBase(string $feedVersion): bool
    {
        $loop = 0;
        $xmlstr = '<CHANNEL xmlns="' . $feedVersion . '">';

        foreach (glob($this->tmpOutputDir . '*.xml') as $file) {
            $xml = Tools::simplexml_load_file($file);

            $innerLoop = 0;
            foreach ($xml as $item) {
                if ($loop != 0 && (preg_match('/^mergado.prestashop/', $item[0]) || ($innerLoop == 0 || $innerLoop == 1))) {
                    $innerLoop++;
                } else {
                    $innerLoop++;
                    $xmlstr .= $item->asXml();
                }
            }

            $loop++;
        }

        $xmlstr .= '</CHANNEL>';

        $xml_new = new XMLWriter();

        $xml_new->openURI($this->xmlOutputFile);
        $xml_new->startDocument('1.0', 'UTF-8');
        $xml_new->writeRaw($xmlstr);
        $xml_new->endDocument();

        LogClass::log('Feed merged. XML created.');

        $this->deleteTemporaryFiles();

        return true;
    }

    /*******************************************************************************************************************
     * FEED FOLDER MANIPULATION
     *******************************************************************************************************************/

    /**
     * Reset feed and delete all TMP files
     */
    protected function resetFeedGenerating()
    {
        XMLClass::deleteTemporaryFilesByDirAndFeedType($this->tmpOutputDir, $this->feedPrefix);
    }

    /**
     * For category and product feed only!!
     *
     * @param $feedName
     * @return string
     */
    protected function getOutputXmlNameWithLangAndCurrency($feedName)
    {
        return $feedName . '_' . Tools::substr(hash('md5', $this->getLanguageIsoFromFeedName($feedName) . '-' . $this->getCurrencyIsoFromFeedName($feedName) . Configuration::get('PS_SHOP_NAME')), 1, 11);
    }

    protected function getLanguageFromFeedName($feedName)
    {
        return Language::getLanguageByIETFCode(Language::getLanguageCodeByIso($this->getLanguageIsoFromFeedName($feedName)));
    }

    protected function getCurrencyFromFeedName($feedName)
    {
        return new Currency(Currency::getIdByIsoCode($this->getCurrencyIsoFromFeedName($feedName)));
    }

    protected function getLanguageIsoFromFeedName($feedName)
    {
        $feedName = str_replace([XMLCategoryFeed::FEED_PREFIX, XMLProductFeed::FEED_PREFIX], '', $feedName);
        $base = explode('-', $feedName);

        return $base[0];
    }

    protected function getCurrencyIsoFromFeedName($name)
    {
        $name = str_replace([XMLCategoryFeed::FEED_PREFIX, XMLProductFeed::FEED_PREFIX], '', $name);
        $base = explode('-', $name);

        return $base[1];
    }

    /**
     * @param string $page
     * @param string $alertSection
     * @param bool $wizardFinished
     * @return array
     */
    protected function getDataForTemplatesBaseMulti(string $alertSection, string $page,  bool $wizardFinished): array {
        $data = [
            'alertSection' => $alertSection,
            'createExportInMergadoUrl' => 'https://app.mergado.com/new-project/prefill/?url=' . $this->getFeedUrl() . '&inputFormat=mergado.cz',
            'cronSetUpUrl' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&step=4&force=true&mmp-wizard-feed=' . $this->name,
            'deleteUrl' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&action=mmp-delete-feed&feed=' . $this->name,
            'feedFullName' => $this->language->name . ' - ' . $this->currencyIso,
            'feedName' => $this->name,
            'generateUrl' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&step=3&force=true&mmp-wizard-feed=' . $this->name,
            'wizardCompleted' => $wizardFinished,
            'wizardUrl' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&mmp-wizard-feed=' . $this->name,
        ];

        return array_replace(parent::getDataForTemplatesBase(), $data);
    }

    /**
     *
     * @param string $page
     * @param string $alertSection
     * @return array
     */
    protected function getWizardDataBaseMulti(string $alertSection, string $page): array {
        $data = [
            'alertSection' => $alertSection,
            'feedFullName' => $this->language->name . ' - ' . $this->currencyIso,
            'feedListLink' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection,
            'feedListLinkWithCongratulations' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-alert-congratulations=' . $this->name,
            'feedName' => $this->name,
        ];

        return array_replace(parent::getWizardDataBase(), $data);
    }
}
