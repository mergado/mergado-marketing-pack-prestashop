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
use ToolsCore as Tools;
use Exception;
use ObjectModel;
use Mergado;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class XMLClass extends ObjectModel
{
    public static $feedPrefix = 'mergado_feed_';
    public static $feedCategoryPrefix = 'category_mergado_feed_';
    public static $feedStockPrefix = 'stock_mergado_feed_';

    protected $language;
    protected $currency;
    protected $shopID;

    // XML/TMP DIR
    const TMP_DIR = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/tmp/';
    const XML_DIR = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/xml/';

    public static $definition = array(
        'table' => Mergado::MERGADO['TABLE_NAME'],
        'primary' => 'id',
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->language = new Language();
        $this->currency = new Currency();
        $this->shopID = Mergado::getShopId();

        parent::__construct($id, $id_lang, $id_shop);
    }

    /**
     * @param $feedBase
     * @return bool
     */
    public function generateMergadoFeed($feedBase)
    {
        // Create TMP and XML dir if not exist
        self::createDIR(array(self::TMP_DIR, self::XML_DIR));
        $now = new DateTime();

        try {
            $isCategory = substr($feedBase, 0, 8) === "category";
            $isStock = substr($feedBase, 0, 5) === "stock";

            if ($isStock) {
                $name = $feedBase;
                $feedBase = $feedBase . '_' . Tools::substr(hash('md5', 'stock' . Configuration::get('PS_SHOP_NAME')), 1, 11);

                $lock = SettingsClass::getSettings('feed_lock_' . $feedBase, $this->shopID);

                if($lock && $lock !== 0 && $lock >= $now->getTimestamp()) {
                    return 'running';
                } else {
                    $xmlStockFeed = new XMLStockFeed($this->shopID, $feedBase, $name);
                    $xml = $xmlStockFeed->generateXML();
                    LogClass::log("Stock feed generated\n");

                    return $xml;
                }
            } elseif ($isCategory) {
                $name = $feedBase;
                $base = explode('-', str_replace(self::$feedCategoryPrefix, '', $feedBase));
                $feedBase = $feedBase . '_' .
                    Tools::substr(hash('md5', $base[0] . '-' . $base[1] . Configuration::get('PS_SHOP_NAME')), 1, 11);

                $lock = SettingsClass::getSettings('feed_lock_' . $feedBase, $this->shopID);

                if($lock && $lock !== 0 && $lock >= $now->getTimestamp()) {
                    return 'running';
                } else {
                    $this->language = $this->language->getLanguageByIETFCode($this->language->getLanguageCodeByIso($base[0]));
                    $this->currency = new Currency($this->currency->getIdByIsoCode($base[1]));

                    $xmlCategoryFeed = new XMLCategoryFeed($this->shopID, $feedBase, $name, $this->language);
                    $xml = $xmlCategoryFeed->generateXML();

                    LogClass::log("Mergado category feed generated:\n" . $feedBase);

                    return $xml;
                }
            } else {
                $name = $feedBase;
                $base = explode('-', str_replace(self::$feedPrefix, '', $feedBase));

                $lock = SettingsClass::getSettings('feed_lock_' . $name, $this->shopID);

                if($lock && $lock !== 0 && $lock >= $now->getTimestamp()) {
                    return 'running';
                } else {
                    SettingsClass::saveSetting('feed_lock_' . $name, $now->modify("+1 minute")->getTimestamp(), $this->shopID);
                    $feedBase = $feedBase . '_' .
                        Tools::substr(hash('md5', $base[0] . '-' . $base[1] . Configuration::get('PS_SHOP_NAME')), 1, 11);

                    $this->language = $this->language->getLanguageByIETFCode($this->language->getLanguageCodeByIso($base[0]));
                    $this->currency = new Currency($this->currency->getIdByIsoCode($base[1]));

                    $xmlProductFeed = new XMLProductFeed($this->shopID, $name, $feedBase, $this->language, $this->currency);
                    $xml = $xmlProductFeed->generateXML();
                    LogClass::log("Mergado feed generated:\n" . $feedBase);

                    if (SettingsClass::getSettings(SettingsClass::FEED['STATIC'], $this->shopID) === "1") {
                        $feedBaseStatic = Tools::getAdminTokenLite('AdminModules');
                        $xmlQuery = new XMLQuery();
                        $export_out_of_stock_other = SettingsClass::getSettings(SettingsClass::EXPORT['DENIED_PRODUCTS_OTHER'], $this->shopID);
                        $staticProducts = $xmlQuery->productsToFlat(0, 0, intval(Configuration::get('PS_LANG_DEFAULT')), $export_out_of_stock_other);
                        $xmlStaticFeed = new XMLStaticFeed();
                        $xml = $xmlStaticFeed->generateXML($feedBaseStatic, $staticProducts, $this->shopID);
                        LogClass::log("Mergado static feed generated");
                    }

                    SettingsClass::saveSetting('feed_lock_' . $name, 0, $this->shopID);

                    return $xml;
                }
            }
        } catch (Exception $e) {
            LogClass::log("Mergado feed generate ERROR:\n" . $e->getMessage());
            return false;
        }
    }

    /*******************************************************************************************************************
     * MANAGE DIRECTORIES
     *******************************************************************************************************************/

    /**
     * Create directory for xml generator if not exist
     *
     * @param array $dirPaths
     */
    public static function createDIR(array $dirPaths)
    {
        foreach ($dirPaths as $item) {
            if (!file_exists($item)) {
                mkdir($item);
            }
        }
    }

    /**
     * Remove all files in directory
     *
     * @param $dir
     */

    public static function removeFilesInDirectory($dir)
    {
        $files = glob($dir . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                array_map('unlink', glob("$file/*.*"));
            }
        }
    }

    /*******************************************************************************************************************
     * GENERATED FILES COUNT
     *******************************************************************************************************************/

    /**
     * Return number of generated fiels in foldertab
     *
     * @param $dir
     * @return int
     */
    public static function getTempNumber($dir)
    {
        if (glob($dir . '*.xml') != false) {
            return count(glob($dir . '*.xml'));
        } else {
            return 0;
        }
    }

    /**
     * Return expected number of files in folder before merge feed
     *
     * @param $shopID
     * @return false|int|string|null
     */
    public static function getTotalFilesCount($feed, $shopID)
    {
        if ($s = SettingsClass::getSettings($feed, $shopID)) {
            return $s;
        } else {
            return 0;
        }
    }
}
