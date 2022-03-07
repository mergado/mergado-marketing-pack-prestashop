<?php

namespace Mergado\Tools;

use LanguageCore as Language;
use CurrencyCore as Currency;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLStaticFeed;
use Mergado\Tools\XML\XMLStockFeed;

class FeedQuery
{
    protected $language;
    protected $currency;

    public function __construct()
    {
        $this->language = new Language();
        $this->currency = new Currency();
    }

    public function getProductFeedsData()
    {
        $output = [];
        $output['isAlreadyFinished'] = XMLProductFeed::isWizardFinished(\Mergado::getShopId());

        foreach ($this->language->getLanguages(true) as $lang) {
            foreach ($this->currency->getCurrencies(false, true, true) as $currency) {
                $name = XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];
                $xmlProductFeed = new XMLProductFeed($name);
                $wizardData = $xmlProductFeed->getWizardData();
                $templateData = $xmlProductFeed->getDataForTemplates();

                $output = $this->getOutput($templateData, $wizardData, $output, $name);
            }
        }

        $output['feeds']['allJson'] = json_encode($output['feeds']['all']);

        return $output;
    }

    public function getCategoryFeedsData()
    {
        $output = [];
        $output['isAlreadyFinished'] = XMLCategoryFeed::isWizardFinished(\Mergado::getShopId());

        foreach ($this->language->getLanguages(true) as $lang) {
            foreach ($this->currency->getCurrencies(false, true, true) as $currency) {
                $name = XMLCategoryFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];
                $xmlCategoryFeed = new XMLCategoryFeed($name);
                $wizardData = $xmlCategoryFeed->getWizardData();
                $templateData = $xmlCategoryFeed->getDataForTemplates();

                $output = $this->getOutput($templateData, $wizardData, $output, $name);
            }
        }

        $output['feeds']['allJson'] = json_encode($output['feeds']['all']);

        return $output;
    }

    public function getStaticFeedData()
    {
        $output = [];
        $output['isAlreadyFinished'] = XMLStaticFeed::isWizardFinished(\Mergado::getShopId());

        $xmlStaticFeed = new XMLStaticFeed();
        $wizardData = $xmlStaticFeed->getWizardData();
        $templateData = $xmlStaticFeed->getDataForTemplates();

        $output['wizardData'] = ['static' => $wizardData];
        $output['wizardDataJson'] = json_encode(['static' => $wizardData]);
        $output['templateData'] = $templateData;

        return $output;
    }

    public function getStockFeedData()
    {
        $output = [];
        $output['isAlreadyFinished'] = XMLStockFeed::isWizardFinished(\Mergado::getShopId());

        $xmlStockFeed = new XMLStockFeed();
        $wizardData = $xmlStockFeed->getWizardData();
        $templateData = $xmlStockFeed->getDataForTemplates();

        $output['wizardData'] = ['stock' => $wizardData];
        $output['wizardDataJson'] = json_encode(['stock' => $wizardData]);
        $output['templateData'] = $templateData;

        return $output;
    }

    /**
     * @param array $templateData
     * @param array $wizardData
     * @param array $output
     * @param string $name
     * @return array
     */
    public function getOutput(array $templateData, array $wizardData, array $output, string $name): array
    {
        if ($templateData['feedExist'] || $templateData['percentageStep'] > 0) {
            $output['feeds']['active'][$name]['wizardData'] = $wizardData;
            $output['feeds']['active'][$name]['templateData'] = $templateData;
        } else {
            $output['feeds']['inactive'][$name]['wizardData'] = $wizardData;
            $output['feeds']['inactive'][$name]['templateData'] = $templateData;
        }

        $output['feeds']['all'][$name] = $wizardData;
        return $output;
    }
}