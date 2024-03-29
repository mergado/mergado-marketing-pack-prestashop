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

use CombinationCore as Combination;
use Exception;
use ProductCore as Product;
use Mergado;
use SimpleXMLElement;
use Symfony\Bundle\SecurityBundle\Tests\DependencyInjection\XmlCompleteConfigurationTest;


class ImportPricesClass
{
    const DIR_FOLDER = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/tmp/importPrices/';
    const FILE_NAMES = [
        'MAIN' => 'pricesImport.xml',
        'PROGRESS' => 'progressFile.xml',
    ];

    const IMPORT_URL = 'import_product_prices_url';

    protected $shopID;

    public function __construct()
    {
        $this->shopID = Mergado::getShopId();
    }


    /**
     *  Download or get data, update product info and save progress XML. Delete or change name of progress XML if not empty.
     */
    public function importPrices()
    {
        LogClass::log('-- Mergado import prices started --');
        $result = '';

        try {
            $importURL = SettingsClass::getSettings(SettingsClass::IMPORT['URL'], $this->shopID);

            if(trim($importURL) != '') {
                if($data = $this->downloadPrices($importURL)) {
                    $loop = 1;

                    $itemsToImport = (int) SettingsClass::getSettings(XMLClass::OPTIMIZATION['IMPORT_FEED'], $this->shopID);

                    while((array) $data->ITEM != []) {
                        if ($loop <= $itemsToImport || $itemsToImport == 0) {
                            $this->updateProduct($data->ITEM);
                            unset($data->ITEM[0]);
                            $this->saveProgressFile($data);
                            $loop++;
                        } else {
                            $result = 'hitTheLimit';
                            break;
                        }
                    }

                    LogClass::log('Products imported succesfully');

                    if((array) $data->ITEM != []) {
                        unlink(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN']);
                        rename(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['PROGRESS'], self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN']);
                    } else {
                        unlink(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN']);
                        unlink(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['PROGRESS']);
                    }
                }

                LogClass::log('-- Mergado import prices ended --');

            } else {
                LogClass::log('Error importing product prices. Missing feed url!');
                throw new MissingUrlException('Missing import prices feed URL');
            }
        } catch (MissingUrlException $ex) {
            return false;
        } catch (\Exception $ex) {
            LogClass::log('Error importing new product prices from Mergado feed.' . $ex->getMessage());
            return false;
        }

        if ($result === 'hitTheLimit') {
            return 'stepGenerated';
        } else {
            return 'finished';
        }
    }


    /**
     * Download Prices or retrieve file from tmp folder
     *
     * @throws MissingUrlException
     * @throws \Exception
     */
    public function downloadPrices($url)
    {
        $agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $agent); //make it act decent
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //set this flag for results to the variable
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //This is required for HTTPS certs if
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //you don't have some key/password action
        $feed = curl_exec($ch);
        curl_close($ch);

        $x = new SimpleXMLElement($feed);

        $importFinished = $this->lastImportFinished();

        // File not exists && build dates in files are not same
        if ($importFinished && $this->isNewPriceFile($x->LAST_BUILD_DATE)) {
            $this->saveTemporaryFile($x);
            $this->setLastImportDate($x->LAST_BUILD_DATE);

            return $x;
        // File exists
        } elseif (!$importFinished) {
            $tempFile = $this->getTempFile();
            $x = new SimpleXMLElement($tempFile);

            return $x;
        }

        return false;
    }


    /**
     * Set date of last downlaoded and saved XML
     *
     * @param $date
     * @throws \Exception
     */
    public function setLastImportDate($date)
    {
        try {
            $date = new \DateTime($date);
            SettingsClass::saveSetting(SettingsClass::IMPORT['LAST_UPDATE'], $date->format(NewsClass::DATE_FORMAT), $this->shopID);
        } catch (\Exception $e) {
            throw new \Exception('Feed contains incorrect Date format! Import failed.');
        }
    }


    /**
     * Save downloaded Mergado XML
     *
     * @param $data
     * @throws \Exception
     */
    public function saveTemporaryFile($data)
    {
        $dirFolder = self::DIR_FOLDER;
        $dirShop = $dirFolder . $this->shopID;
        $filename = $dirShop . '/' . self::FILE_NAMES['MAIN'];

        DirectoryManager::createDIR([$dirFolder, $dirShop]);

        if ($this->lastImportFinished()) {
            file_put_contents($filename, $data->asXml());
        } else {
            throw new \Exception('Previous import not finished! File exists.');
        }
    }


    /**
     * Save xml with progress data
     *
     * @param $data
     */
    public function saveProgressFile($data)
    {
        $dirFolder = self::DIR_FOLDER;
        $dirShop = $dirFolder . $this->shopID;
        $filename = $dirShop . '/' . self::FILE_NAMES['PROGRESS'];

        DirectoryManager::createDIR([$dirFolder, $dirShop]);

        file_put_contents($filename, $data->asXml());
    }


    /**
     * Return if price file is updated or already imported before
     *
     * @param $date
     * @return bool
     * @throws \Exception
     */
    public function isNewPriceFile($date)
    {
        try {
            $date = new \DateTime($date);
            $dbDate = new \DateTime(SettingsClass::getSettings(SettingsClass::IMPORT['LAST_UPDATE'], $this->shopID), new \DateTimeZone('+00:00'));

            if ($date == $dbDate) {
                return false;
            } else {
                return true;
            }

        } catch (\Exception $ex) {
            LogClass::log("Mergado DateTime error in isNewPriceFile function.\n" . $ex->getMessage());
            return false;
        }
    }


    /**
     * Returns if last import is finished
     *
     * @return bool
     */
    public function lastImportFinished()
    {
        $dir = self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN'];

        return !file_exists($dir);
    }


    /**
     * Get temporary file
     *
     * @return false|string
     * @throws \Exception
     */
    public function getTempFile()
    {
        try {
            $file = file_get_contents(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN']);
            return $file;
        } catch (\Exception $ex) {
            LogClass::log('XML File deleted.');
            throw new \Exception('XML File deleted.');
        }
    }


    /**
     * Update product properties by XML data
     *
     * @param $item
     */
    private function updateProduct($item)
    {
        $exploded = explode('-', $item->ITEM_ID->__toString());

        $itemID = $exploded[0];
        $combID = isset($exploded[1]) ? $exploded[1] : null;             
        
        
        try {
            if($combID != null) {
                // Part with combinations
                $product = new Product($itemID);
                $combination = new Combination($combID);

                if($item->PRICE->__toString() !== "") {
                    $price = (float)$item->PRICE->__toString() - (float)$product->price;
                    
                    $combination->price = $price;
                    $combination->save();
                }
            } else {
                // Part with products
                $product = new Product($itemID);

                // Correct
                if($item->PRICE->__toString() !== "") {
                    $product->price = (string) $item->PRICE->__toString();
                }

                $product->save();
            }
        } catch (\PrestaShopDatabaseException $e) {
            LogClass::log('Error importing product' . $e);
        } catch (\PrestaShopException $e) {
            LogClass::log('Error importing product' . $e);
        }
    }

    /**
     * Return value of lowered product step (repetetive call if 500 error timeout)
     */
    public function getLoweredProductsPerStep()
    {
        return (int)SettingsClass::getSettings(XMLClass::FEED_PRODUCTS_USER['IMPORT'], $this->shopID);
    }

    public function setLoweredProductsPerStepAsMain()
    {
        $productsPerStep = $this->getLoweredProductsPerStep();
        $this->setProductsPerStep($productsPerStep);
        $this->deleteLoweredProductsPerStep();
    }

    /**
     * Return value of product per step
     * @return int
     */
    public function getProductsPerStep()
    {
        $loweredProductsPerStep = $this->getLoweredProductsPerStep();

        if ($loweredProductsPerStep != 0 && $loweredProductsPerStep !== '') {
            return $loweredProductsPerStep;
        } else {
            return (int)SettingsClass::getSettings(XMLClass::OPTIMIZATION['IMPORT_FEED'], $this->shopID);
        }
    }

    public function setProductsPerStep($value)
    {
        return SettingsClass::saveSetting(XMLClass::OPTIMIZATION['IMPORT_FEED'], $value, $this->shopID);
    }

    public function deleteLoweredProductsPerStep()
    {
        return SettingsClass::saveSetting(XMLClass::FEED_PRODUCTS_USER['IMPORT'], 0, $this->shopID);
    }

    public function lowerProductsPerStep()
    {
        $productsPerStep = $this->getProductsPerStep();

        $loweredValue = round($productsPerStep / 2);

        if ($loweredValue < 10 && $loweredValue != 0) {
            $response = false;
        } else if ($this->setLoweredProductsPerStep(XMLClass::FEED_PRODUCTS_USER['IMPORT'], $loweredValue, $this->shopID)) {
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

    public function getWizardData()
    {
        return [
            'feedName' => 'import',
            'cronAction' => 'importPrices',
            'ajaxGenerateAction' => 'ajax_generate_feed',
            'cronUrl' =>  $this->getCronUrl(),
            'importUrl' => $this->getImportUrl(),
            'productsPerStep' => $this->getProductsPerStep(),
        ];
    }

    public function getImportUrl()
    {
        return SettingsClass::getSettings(self::IMPORT_URL, $this->shopID);
    }

    public function setImportUrl($url)
    {
        if ($this->getImportUrl() === $url) {
            return true;
        } else {
            return SettingsClass::saveSetting(self::IMPORT_URL, $url, $this->shopID);
        }
    }

    public function getCronUrl()
    {
        return UrlManager::getImportCronUrl($this->shopID);
    }
}

/**
 * Thrown when an service returns an exception
 */
class MissingUrlException extends Exception
{
};