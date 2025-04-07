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


namespace Mergado\Endpoint;

use Mergado\Helper\UrlHelper;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Traits\SingletonTrait;

class AdminFeedDeletionEndpoint implements EndpointInterface
{
    use SingletonTrait;

    protected function deleteFeed(): void
    {
        if (isset($_GET['action']) && $_GET['action'] === 'mmp-delete-feed') {
            $feed = $_GET['feed'] ?? '';

            // Stock - no language and currency
            if (StockFeed::isStockFeed($feed)) {
                $xmlStockFeed = new StockFeed();
                $xmlStockFeed->deleteTemporaryFiles();
                $xmlStockFeed->deleteXml();

                header('Location: ' . UrlHelper::getAdminControllerUrl() . '&page=feeds-other&mmp-tab=stock');
                exit;
            }

            // Static - no language and currency
            if (StaticFeed::isStaticFeed($feed)){
                $xmlStaticFeed = new StaticFeed();
                $xmlStaticFeed->deleteTemporaryFiles();
                $xmlStaticFeed->deleteXml();

                header('Location: ' . UrlHelper::getAdminControllerUrl() . '&page=feeds-other&mmp-tab=static');
                exit;
            }

            // Category - lang and currency
            if (CategoryFeed::isCategoryFeed($feed)) {
                $xmlCategoryFeed = new CategoryFeed($feed);
                $xmlCategoryFeed->deleteTemporaryFiles();
                $xmlCategoryFeed->deleteXml();

                header('Location: ' . UrlHelper::getAdminControllerUrl() . '&page=feeds-other&mmp-tab=category');
                exit;
            }

            // Product - lang and currency
            $xmlProductFeed = new ProductFeed($feed);
            $xmlProductFeed->deleteTemporaryFiles();
            $xmlProductFeed->deleteXml();

            header('Location: ' . UrlHelper::getAdminControllerUrl() . '&page=feeds-product&mmp-tab=product');
            exit;
        }
    }

    public function initEndpoints(): void
    {
        $this->deleteFeed();
    }
}
