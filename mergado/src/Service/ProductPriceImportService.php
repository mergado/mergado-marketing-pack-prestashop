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


namespace Mergado\Service;

use Exception;
use Mergado;
use Mergado\Exception\MissingUrlException;
use Mergado\Helper\ShopHelper;
use Mergado\Helper\UrlHelper;
use Mergado\Manager\DatabaseManager;
use Mergado\Manager\DirectoryManager;
use Mergado\Service\News\NewsService;
use Shop;
use Product;
use Tools;
use Combination;
use SimpleXMLElement;

class ProductPriceImportService extends AbstractBaseService
{
    public const USER_ITEM_COUNT_PER_STEP_DB_NAME = 'import-form-products';
    public const LOWERED_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-form-import-user';
    public const WIZARD_FINISHED_DB_NAME = 'mmp-wizard-finished-import';
    public const DEFAULT_ITEM_COUNT_PER_STEP_DB_NAME = 'mergado-feed-import-default-step';

    protected const DIR_FOLDER = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/tmp/importPrices/';
    protected const FILE_NAMES = [
        'MAIN' => 'pricesImport.xml',
        'PROGRESS' => 'progressFile.xml',
    ];

    protected const IMPORT_URL = 'import_product_prices_url';

    protected $shopID;

    public function __construct()
    {
        $this->shopID = ShopHelper::getId();

        parent::__construct();
    }


    /**
     *  Download or get data, update product info and save progress XML. Delete or change name of progress XML if not empty.
     */
    public function importPrices()
    {
        $this->logger->info('-- Mergado import prices started --');
        $result = '';

        try {
            $importURL = DatabaseManager::getSettingsFromCache(SettingsService::IMPORT['URL']);

            if(trim($importURL) !== '') {
                if($data = $this->downloadPrices($importURL)) {
                    $loop = 1;

                    $itemsToImport = (int) DatabaseManager::getSettingsFromCache(ProductPriceImportService::USER_ITEM_COUNT_PER_STEP_DB_NAME);

                    while((array) $data->ITEM !== []) {
                        if ($loop <= $itemsToImport || $itemsToImport === 0) {
                            $this->updateProduct($data->ITEM);
                            unset($data->ITEM[0]);
                            $this->saveProgressFile($data);
                            $loop++;
                        } else {
                            $result = 'hitTheLimit';
                            break;
                        }
                    }

                    $this->logger->info('Products imported successfully');

                    unlink(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN']);

                    if((array) $data->ITEM !== []) {
                        rename(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['PROGRESS'], self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN']);
                    } else {
                        unlink(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['PROGRESS']);
                    }
                }

                $this->logger->info('-- Mergado import prices ended --');

            } else {
                $this->logger->error('Import of product prices failed. Feed url is missing!');
                throw new MissingUrlException('Missing import prices feed URL');
            }
        } catch (MissingUrlException $e) {
            return false;
        } catch (Exception $e) {
            $this->logger->error('Can\'t import new product prices from Mergado feed', ['exception' => $e]);
            return false;
        }

        if ($result === 'hitTheLimit') {
            return 'stepGenerated';
        }

        return 'finished';
    }


    /**
     * Download Prices or retrieve file from tmp folder
     *
     * @throws MissingUrlException
     * @throws Exception
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
        }

        // File exists
        if (!$importFinished) {
            $tempFile = $this->getTempFile();
            return new SimpleXMLElement($tempFile);
        }

        return false;
    }


    /**
     * Set date of last downlaoded and saved XML
     *
     * @param $date
     * @throws Exception
     */
    public function setLastImportDate($date): void
    {
        try {
            $date = new \DateTime($date);
            DatabaseManager::saveSetting(SettingsService::IMPORT['LAST_UPDATE'], $date->format(NewsService::DATE_FORMAT), $this->shopID);
        } catch (Exception $e) {
            throw new Exception('Feed contains incorrect Date format! Import failed.');
        }
    }


    /**
     * Save downloaded Mergado XML
     *
     * @param $data
     * @throws Exception
     */
    public function saveTemporaryFile($data): void
    {
        $dirFolder = self::DIR_FOLDER;
        $dirShop = $dirFolder . $this->shopID;
        $filename = $dirShop . '/' . self::FILE_NAMES['MAIN'];

        DirectoryManager::createDIR([$dirFolder, $dirShop]);

        if ($this->lastImportFinished()) {
            file_put_contents($filename, $data->asXml());
        } else {
            throw new Exception('Previous import not finished! File exists.');
        }
    }


    /**
     * Save xml with progress data
     *
     * @param $data
     */
    public function saveProgressFile($data): void
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
     * @throws Exception
     */
    public function isNewPriceFile($date): bool
    {
        try {
            $date = new \DateTime($date);
            $dbDate = new \DateTime(DatabaseManager::getSettingsFromCache(SettingsService::IMPORT['LAST_UPDATE']), new \DateTimeZone('+00:00'));

            return $date != $dbDate;

        } catch (Exception $e) {
            $this->logger->error("Mergado DateTime error in isNewPriceFile function", ['exception' => $e]);
            return false;
        }
    }


    /**
     * Returns if last import is finished
     */
    public function lastImportFinished(): bool
    {
        $dir = self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN'];

        return !file_exists($dir);
    }


    /**
     * Get temporary file
     *
     * @return false|string
     * @throws Exception
     */
    public function getTempFile()
    {
        try {
            return file_get_contents(self::DIR_FOLDER . $this->shopID . '/' . self::FILE_NAMES['MAIN']);
        } catch (Exception $ex) {
            $this->logger->info('XML File deleted.');
            throw new Exception('XML File deleted.');
        }
    }


    /**
     * Update product properties by XML data
     */
    private function updateProduct($item): void
    {
        $exploded = explode('-', $item->ITEM_ID->__toString());

        $itemID = $exploded[0];
        $combID = isset($exploded[1]) ? $exploded[1] : null;


        try {
            if($combID !== null) {
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
            $this->logger->error('Database error during product import', ['exception' => $e]);
        } catch (\PrestaShopException $e) {
            $this->logger->error('Prestashop error during product import', ['exception' => $e]);
        }
    }

    /**
     * Return value of lowered product step (repetitive call if 500 error timeout)
     */
    public function getLoweredProductsPerStep(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::LOWERED_ITEM_COUNT_PER_STEP_DB_NAME);
    }

    public function setLoweredProductsPerStepAsMain(): void
    {
        $productsPerStep = $this->getLoweredProductsPerStep();
        $this->setProductsPerStep($productsPerStep);
        $this->deleteLoweredProductsPerStep();
    }

    /**
     * Return value of product per step
     */
    public function getProductsPerStep(): int
    {
        $loweredProductsPerStep = $this->getLoweredProductsPerStep();

        if ($loweredProductsPerStep !== 0) {
            return $loweredProductsPerStep;
        }

        return (int)DatabaseManager::getSettingsFromCache(self::USER_ITEM_COUNT_PER_STEP_DB_NAME);
    }

    public function setProductsPerStep($value): bool
    {
        return DatabaseManager::saveSetting(self::USER_ITEM_COUNT_PER_STEP_DB_NAME, $value, $this->shopID);
    }

    public function deleteLoweredProductsPerStep(): bool
    {
        return DatabaseManager::saveSetting(self::LOWERED_ITEM_COUNT_PER_STEP_DB_NAME, 0, $this->shopID);
    }

    public function lowerProductsPerStep()
    {
        $productsPerStep = $this->getProductsPerStep();

        $loweredValue = round($productsPerStep / 2);

        if ($loweredValue < 10 && $loweredValue !== 0) {
            $response = false;
        } else if ($this->setLoweredProductsPerStep(self::LOWERED_ITEM_COUNT_PER_STEP_DB_NAME, $loweredValue, $this->shopID)) {
            $response = $loweredValue;
        } else {
            $response = false;
        }

        if ($response === false) {
            $this->deleteLoweredProductsPerStep();
        }

        return $response;
    }

    public function setLoweredProductsPerStep($loweredProductPerStepName, $value, $shopId): bool
    {
        return DatabaseManager::saveSetting($loweredProductPerStepName, $value, $shopId);
    }

    public function getWizardData(): array
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
        return DatabaseManager::getSettingsFromCache(self::IMPORT_URL);
    }

    public function setImportUrl($url): bool
    {
        if ($this->getImportUrl() === $url) {
            return true;
        }

        return DatabaseManager::saveSetting(self::IMPORT_URL, $url, $this->shopID);
    }

    public function getCronUrl(): string
    {
        if (Shop::isFeatureActive()) {
            return UrlHelper::getShopModuleUrl() . '/importPrices.php?' .
                '&token=' . Tools::substr(Tools::encrypt('mergado/importPrices'), 0, 10);
        }

        return UrlHelper::getShopModuleUrl() . '/importPrices.php?' .
            '&token=' . Tools::substr(Tools::encrypt('mergado/importPrices'), 0, 10);
    }
}
