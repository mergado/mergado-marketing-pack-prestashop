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
 * @author    www.mergado.cz
 * @copyright 2016 Mergado technologies, s. r. o.
 * @license   LICENSE.txt
 */

namespace Mergado\Manager;

use Exception;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Exception\CronRunningException;
use ObjectModel;

class FeedGeneratorManager extends ObjectModel
{
    /**
     * @throws CronRunningException - will be thrown only when not AJAX ( ajax catches that and return json response )
     * @throws Exception
     */
    public static function generateFeed($name, bool $manualGenerating = false, bool $force = false, bool $firstRun = false) {
        DirectoryManager::checkAndCreateTmpDataDir();

        if (StockFeed::isStockFeed($name)) {
            // Stock - no language and currency
            $feedClass = new StockFeed();
        } else if (StaticFeed::isStaticFeed($name)){
            // Stock - no language and currency
            $feedClass = new StaticFeed();
        } else if (CategoryFeed::isCategoryFeed($name)) {
            // Category - lang and currency
            $feedClass = new CategoryFeed($name);
        } else {
            // Product - lang and currency
            $feedClass = new ProductFeed($name);
        }

        if ($manualGenerating) {
            /**
             * @var $feedClass ProductFeed|StockFeed|CategoryFeed|StaticFeed
             */
            $feedClass->generateXMLAjax($force, $firstRun);
        } else {
            return $feedClass->generateXML();
        }

        return false;
    }
}

