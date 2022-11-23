<?php

namespace Mergado\Tools\XML;

use Mergado;
use Mergado\Tools\LogClass;
use Mergado\Tools\UrlManager;
use Mergado\Tools\XMLClass;
use Tools;
use XMLWriter;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

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

    public function __construct($name, $nameWithToken, $feedCountVariableName, $optimizationVariableName, $loweredCountVariableName, $defaultCountVariableName) {
        $this->nameWithToken = $nameWithToken;
        $this->shopID = Mergado::getShopId();

        $this->xmlOutputDir = XMLClass::XML_DIR . $this->shopID . '/';
        $this->tmpDir = XMLClass::TMP_DIR . 'xml/';
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

    /**
     * Merge xml files to final file
     *
     * @return bool
     */
    protected function mergeTemporaryFilesBase()
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

        LogClass::log('Feed merged. XML created.');

        $this->deleteTemporaryFiles();

        return true;
    }

    /**
     * @param string $feedDisplayName
     * @param string $feedName
     * @param string $alertSection
     * @param string $page
     * @param bool $wizardCompleted
     * @return array
     */
    protected function getDataForTemplatesBaseSimple(string $feedDisplayName, string $feedName, string $alertSection, string $page, bool $wizardCompleted): array
    {
        $data = [
            'alertSection' => $alertSection,
            'cronSetUpUrl' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection .'&mmp-wizard=' . $alertSection . '&step=4&force=true&mmp-wizard-feed=' . $alertSection,
            'deleteUrl' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection .'&action=mmp-delete-feed&feed=' . $alertSection,
            'feedFullName' => $feedDisplayName,
            'feedName' => $feedName,
            'generateUrl' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection .'&mmp-wizard=' . $alertSection . '&step=3&force=true&mmp-wizard-feed=' . $alertSection,
            'wizardCompleted' => $wizardCompleted,
            'wizardUrl' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $alertSection .'&mmp-wizard=' . $alertSection . '&mmp-wizard-feed=' . $alertSection,
        ];

        return array_replace(parent::getDataForTemplatesBase(), $data);
    }

    /**
     * @param string $feedName
     * @param string $feedFullName
     * @param string $alertSection
     * @param string $page
     * @return array
     */
    protected function getWizardDataBaseSimple(string $feedName, string $feedFullName, string $alertSection, string $page): array {
        $data = [
            'alertSection' => $alertSection,
            'feedFullName' => $feedFullName,
            'feedListLink' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $feedName,
            'feedListLinkWithCongratulations' => UrlManager::getAdminControllerUrl() . '&page=' . $page . '&mmp-tab=' . $feedName . '&mmp-alert-congratulations=' . $this->name,
            'feedName' => $feedName,
        ];

        return array_replace(parent::getWizardDataBase(), $data);
    }
}
