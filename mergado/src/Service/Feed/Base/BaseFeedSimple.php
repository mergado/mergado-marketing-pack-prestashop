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

use Mergado\Helper\ShopHelper;
use Mergado\Helper\UrlHelper;
use Mergado\Service\LogService;
use Tools;
use XMLWriter;

class BaseFeedSimple extends BaseFeed
{
    protected $language;
    protected $shopID;
    protected $name;
    protected $nameWithToken;
    protected $currency;

    protected $feedCountVariableName;

    protected $tmpDir;
    protected $tmpShopDir;
    protected $tmpOutputDir;
    protected $xmlOutputFile;
    protected $xmlOutputDir;

    /**
     * @var LogService
     */
    private $logger;

    public function __construct($name, $nameWithToken, $feedCountVariableName, $optimizationVariableName, $loweredCountVariableName, $defaultCountVariableName)
    {
        $this->logger = LogService::getInstance();

        $this->nameWithToken = $nameWithToken;
        $this->shopID = ShopHelper::getId();

        $this->xmlOutputDir = self::XML_DIR . $this->shopID . '/';
        $this->tmpDir = self::TMP_DIR . 'xml/';
        $this->tmpShopDir = $this->tmpDir . $this->shopID . '/';
        $this->tmpOutputDir = $this->tmpShopDir . $this->name . '/';

        $this->xmlOutputFile = $this->xmlOutputDir . $this->nameWithToken . '.xml';

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

    protected function mergeTemporaryFilesBase(): bool
    {
        $xmlstr = '<CHANNEL xmlns="http://www.mergado.com/ns/1.10">';

        foreach (glob($this->tmpOutputDir . '*.xml') as $file) {
            $xml = Tools::simplexml_load_file($file);

            foreach ($xml as $item) {
                $xmlstr .= $item->asXml();
            }
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

    protected function getDataForTemplatesBaseSimple(string $feedDisplayName, string $feedName, string $alertSection, string $page, bool $wizardCompleted): array
    {
        $data = [
            'alertSection' => $alertSection,
            'cronSetUpUrl' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&step=4&force=true&mmp-wizard-feed=' . $alertSection,
            'deleteUrl' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&action=mmp-delete-feed&feed=' . $alertSection,
            'feedFullName' => $feedDisplayName,
            'feedName' => $feedName,
            'generateUrl' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&step=3&force=true&mmp-wizard-feed=' . $alertSection,
            'wizardCompleted' => $wizardCompleted,
            'wizardUrl' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection . '&mmp-wizard=' . $alertSection . '&mmp-wizard-feed=' . $alertSection,
        ];

        return array_replace($this->getDataForTemplatesBase(), $data);
    }

    protected function getWizardDataBaseSimple(string $feedName, string $feedFullName, string $alertSection, string $page): array
    {
        $data = [
            'alertSection' => $alertSection,
            'feedFullName' => $feedFullName,
            'feedListLink' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $feedName,
            'feedListLinkWithCongratulations' => UrlHelper::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $feedName . '&mmp-alert-congratulations=' . $this->name,
            'feedName' => $feedName,
        ];

        return array_replace($this->getWizardDataBase(), $data);
    }
}
