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


namespace Mergado\Query;

use Currency;
use Language;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Traits\SingletonTrait;

class FeedTemplateQuery
{
    use SingletonTrait;

    protected $language;
    protected $currency;

    public function __construct()
    {
        $this->language = new Language();
        $this->currency = new Currency();
    }

    public function getProductFeedsData(): array
    {
        $output = [];
        $output['isAlreadyFinished'] = ProductFeed::isWizardFinished();

        foreach ($this->language->getLanguages(true) as $lang) {
            foreach ($this->currency->getCurrencies(false, true, true) as $currency) {
                $name = ProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];
                $xmlProductFeed = new ProductFeed($name);
                $wizardData = $xmlProductFeed->getWizardData();
                $templateData = $xmlProductFeed->getDataForTemplates();

                $output = $this->getOutput($templateData, $wizardData, $output, $name);
            }
        }

        $output['feeds']['allJson'] = json_encode($output['feeds']['all']);

        return $output;
    }

    public function getCategoryFeedsData(): array
    {
        $output = [];
        $output['isAlreadyFinished'] = CategoryFeed::isWizardFinished();

        foreach ($this->language->getLanguages(true) as $lang) {
            foreach ($this->currency->getCurrencies(false, true, true) as $currency) {
                $name = CategoryFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];
                $xmlCategoryFeed = new CategoryFeed($name);
                $wizardData = $xmlCategoryFeed->getWizardData();
                $templateData = $xmlCategoryFeed->getDataForTemplates();

                $output = $this->getOutput($templateData, $wizardData, $output, $name);
            }
        }

        $output['feeds']['allJson'] = json_encode($output['feeds']['all']);

        return $output;
    }

    public function getStaticFeedData(): array
    {
        $output = [];
        $output['isAlreadyFinished'] = StaticFeed::isWizardFinished();

        $xmlStaticFeed = new StaticFeed();
        $wizardData = $xmlStaticFeed->getWizardData();
        $templateData = $xmlStaticFeed->getDataForTemplates();

        $output['wizardData'] = ['static' => $wizardData];
        $output['wizardDataJson'] = json_encode(['static' => $wizardData]);
        $output['templateData'] = $templateData;

        return $output;
    }

    public function getStockFeedData(): array
    {
        $output = [];
        $output['isAlreadyFinished'] = StockFeed::isWizardFinished();

        $xmlStockFeed = new StockFeed();
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
