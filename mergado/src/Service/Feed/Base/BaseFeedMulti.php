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

use Configuration;
use Currency;
use Language;
use Mergado\Helper\ShopHelper;
use Mergado\Helper\UrlHelper;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\LogService;
use Tools;
use XMLWriter;

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

    /**
     * @var LogService
     */
    private $logger;

    public function __construct($name, $feedPrefix, $feedCountVariableName, $optimizationVariableName, $loweredCountVariableName, $defaultCountVariableName)
    {
        $this->logger = LogService::getInstance();

        $this->name = $name;
        $this->nameWithToken = $this->getOutputXmlNameWithLangAndCurrency($this->name);

        $this->language = $this->getLanguageFromFeedName($this->name);
        $this->currency = $this->getCurrencyFromFeedName($this->name);
        $this->langIso = $this->getLanguageIsoFromFeedName($this->name);
        $this->currencyIso = $this->getCurrencyIsoFromFeedName($this->name);

        $this->shopID = ShopHelper::getId();
        $this->langAndIsoName = $this->langIso . '-' . $this->currencyIso;

        $this->xmlOutputDir = self::XML_DIR . $this->shopID . '/';
        $this->tmpDir = self::TMP_DIR . 'xml/';
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

        $this->logger->info('Feed merged. XML created.');

        $this->deleteTemporaryFiles();

        return true;
    }

    /*******************************************************************************************************************
     * FEED FOLDER MANIPULATION
     *******************************************************************************************************************/

    protected function resetFeedGenerating(): void
    {
        $this->deleteTemporaryFilesByDirAndFeedType($this->tmpOutputDir, $this->feedPrefix);
    }

    /**
     * For category and product feed only!!
     */
    protected function getOutputXmlNameWithLangAndCurrency(string $feedName): string
    {
        return $feedName . '_' . Tools::substr(hash('md5', $this->getLanguageIsoFromFeedName($feedName) . '-' . $this->getCurrencyIsoFromFeedName($feedName) . Configuration::get('PS_SHOP_NAME')), 1, 11);
    }

    protected function getLanguageFromFeedName(string $feedName)
    {
        return Language::getLanguageByIETFCode(Language::getLanguageCodeByIso($this->getLanguageIsoFromFeedName($feedName)));
    }

    protected function getCurrencyFromFeedName(string $feedName): Currency
    {
        return new Currency(Currency::getIdByIsoCode($this->getCurrencyIsoFromFeedName($feedName)));
    }

    protected function getLanguageIsoFromFeedName(string $feedName): string
    {
        $feedName = str_replace([CategoryFeed::FEED_PREFIX, ProductFeed::FEED_PREFIX], '', $feedName);
        $base = explode('-', $feedName);

        return $base[0];
    }

    protected function getCurrencyIsoFromFeedName(string $name): string
    {
        $name = str_replace([CategoryFeed::FEED_PREFIX, ProductFeed::FEED_PREFIX], '', $name);
        $base = explode('-', $name);

        return $base[1];
    }

    protected function getDataForTemplatesBaseMulti(string $alertSection, string $page, bool $wizardFinished): array
    {
        $data = [
            'alertSection' => $alertSection,
            'createExportInMergadoUrl' => 'https://app.mergado.com/new-project/prefill/?url=' . $this->getFeedUrl() . '&inputFormat=mergado.cz',
            'cronSetUpUrl' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&step=4&force=true&mmp-wizard-feed=' . $this->name,
            'deleteUrl' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&action=mmp-delete-feed&feed=' . $this->name,
            'feedFullName' => $this->language->name . ' - ' . $this->currencyIso,
            'feedName' => $this->name,
            'generateUrl' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&step=3&force=true&mmp-wizard-feed=' . $this->name,
            'wizardCompleted' => $wizardFinished,
            'wizardUrl' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&mmp-wizard-feed=' . $this->name,
        ];

        return array_replace($this->getDataForTemplatesBase(), $data);
    }

    protected function getWizardDataBaseMulti(string $alertSection, string $page): array
    {
        $data = [
            'alertSection' => $alertSection,
            'feedFullName' => $this->language->name . ' - ' . $this->currencyIso,
            'feedListLink' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection,
            'feedListLinkWithCongratulations' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-alert-congratulations=' . $this->name,
            'feedName' => $this->name,
        ];

        return array_replace($this->getWizardDataBase(), $data);
    }
}
