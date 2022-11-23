<?php

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

namespace Mergado\Tools;

use DateTime;
use LanguageCore as Language;
use ConfigurationCore as Configuration;
use CurrencyCore as Currency;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLQuery;
use Mergado\Tools\XML\XMLStaticFeed;
use Mergado\Tools\XML\XMLStockFeed;
use CategoryCore as Category;
use PrestaShop\PrestaShop\Adapter\Entity\Translate;
use ShopUrlCore;
use ToolsCore as Tools;
use Exception;
use ObjectModel;
use Mergado;
use ShopCore as Shop;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class XMLClass extends ObjectModel
{
    const FEED_COUNT = [
        'PRODUCT' => 'feed-last-cron-product-count',
        'CATEGORY' => 'feed-last-cron-category-run-count',
        'STOCK' => 'feed-last-cron-stock-count',
        'STATIC' => 'feed-last-cron-static-run-count',
    ];

    // Input user - items per step
    const OPTIMIZATION = [
        'PRODUCT_FEED' => 'feed-form-products',
        'STOCK_FEED' => 'feed-form-stock',
        'STATIC_FEED' => 'feed-form-static',
        'CATEGORY_FEED' => 'feed-form-category',
        'IMPORT_FEED' => 'import-form-products',
    ];

    // Current lowered - items per step value
    const FEED_PRODUCTS_USER = [
        'PRODUCT' => 'mergado-feed-form-products-user',
        'STOCK' => 'mergado-feed-form-stock-user',
        'STATIC' => 'mergado-feed-form-static-user',
        'CATEGORY' => 'mergado-feed-form-category-user',
        'IMPORT' => 'mergado-feed-form-import-user',
    ];

    // Wizard finished
    const WIZARD = [
        'FINISHED_PRODUCT' => 'mmp-wizard-finished-product',
        'FINISHED_STOCK' => 'mmp-wizard-finished-stock',
        'FINISHED_STATIC' => 'mmp-wizard-finished-static',
        'FINISHED_CATEGORY' => 'mmp-wizard-finished-category',
        'FINISHED_IMPORT' => 'mmp-wizard-finished-import',
    ];

    // Default value of wizard - items per step
    const DEFAULT_ITEMS_STEP = [
        'PRODUCT_FEED' => 'mergado-feed-products-default-step',
        'STOCK_FEED' => 'mergado-feed-stock-default-step',
        'STATIC_FEED' => 'mergado-feed-static-default-step',
        'CATEGORY_FEED' => 'mergado-feed-category-default-step',
        'IMPORT_FEED' => 'mergado-feed-import-default-step',
    ];

    protected $language;
    protected $currency;
    protected $shopID;

    // XML/TMP DIR
    const TMP_DIR = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/tmp/';
    const XML_DIR = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/xml/';

    public static $definition = [
        'table' => Mergado::MERGADO['TABLE_NAME'],
        'primary' => 'id',
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->language = new Language();
        $this->currency = new Currency();
        $this->shopID = Mergado::getShopId();

        parent::__construct($id, $id_lang, $id_shop);
    }

    /**
     * DEVEL METHOD
     * @param $name
     * @param bool $manualGenerating
     * @param bool $force
     * @param bool $firstRun
     * @return bool|string
     * @throws CronRunningException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function generateFeed($name, $manualGenerating = false, $force = false, $firstRun = false) {
        DirectoryManager::checkAndCreateTmpDataDir(); // Create TMP and XML dir if not exist

        // Stock - no language and currency
        if (XMLStockFeed::isStockFeed($name)) {
            $xmlStockFeed = new XMLStockFeed($name);

            if ($manualGenerating) {
                $xmlStockFeed->generateXmlAjax($force, $firstRun); // RETURNS: JSON + EXIT, CONTAINS: LOWER PRODUCTS LOGIC etc.. generateXML is hidden inside of it
            } else {
                return $xmlStockFeed->generateXML(); // RETURNS: MESSAGE AND STATUS .. or THROW EXCEPTION?!
            }

        // Stock - no language and currency
        } else if (XMLStaticFeed::isStaticFeed($name)){
            $xmlStaticFeed = new XMLStaticFeed();

            if ($manualGenerating) {
                $xmlStaticFeed->generateXMLAjax($force, $firstRun);
            } else {
                return $xmlStaticFeed->generateXML();
            }

        // Category - lang and currency
        } else if (XMLCategoryFeed::isCategoryFeed($name)) {
            $xmlCategoryFeed = new XMLCategoryFeed($name);

            if ($manualGenerating) {
                $xmlCategoryFeed->generateXMLAjax($force, $firstRun);
            } else {
                return $xmlCategoryFeed->generateXML();
            }

        // Product - lang and currency
        } else {
            $xmlProductFeed = new XMLProductFeed($name);

            if ($manualGenerating) {
                $xmlProductFeed->generateXMLAjax($force, $firstRun);
            } else {
                return $xmlProductFeed->generateXML();
            }
        }

        return false;
    }

    /*******************************************************************************************************************
     * FILE MANIPULATION
     ******************************************************************************************************************/

    /**
     * Delete all files from TMP folder
     * @param $tmpDir
     */
    public static function deleteTemporaryFiles($tmpDir)
    {
        $files = glob($tmpDir . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public static function deleteTemporaryFilesByDirAndFeedType($tmpDir, $feedPrefix)
    {
        $dirs = glob($tmpDir . $feedPrefix . '*');

        foreach ($dirs as $dir) {
            self::deleteTemporaryFiles($dir);
        }
    }


    public static function getAllFeedLangCurrencyCombinations()
    {
        $combinations = [];

        foreach (Language::getLanguages(true) as $lang) {
            foreach (Currency::getCurrencies(false, true, true) as $currency) {
                $combinations[] = $lang['iso_code'] . '-' . $currency['iso_code'];
            }
        }

        return $combinations;
    }
}

/**
 * Thrown when an service returns an exception
 */
class CronRunningException extends Exception
{

};
