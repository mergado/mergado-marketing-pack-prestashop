<?php

use Mergado\Tools\UrlManager;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLStaticFeed;
use Mergado\Tools\XML\XMLStockFeed;

if ($_GET['action'] === 'mmp-delete-feed') {
    $feed = $_GET['feed'];

    // Stock - no language and currency
    if (XMLStockFeed::isStockFeed($feed)) {
        $xmlStockFeed = new XMLStockFeed();
        $xmlStockFeed->deleteTemporaryFiles();
        $xmlStockFeed->deleteXml();

        header('Location: ' . UrlManager::getAdminControllerUrl() . '&page=feeds-other&mmp-tab=stock');
        exit;

        // Static - no language and currency
    } else if (XMLStaticFeed::isStaticFeed($feed)){
        $xmlStaticFeed = new XMLStaticFeed();
        $xmlStaticFeed->deleteTemporaryFiles();
        $xmlStaticFeed->deleteXml();

        header('Location: ' . UrlManager::getAdminControllerUrl() . '&page=feeds-other&mmp-tab=static');
        exit;

        // Category - lang and currency
    } else if (XMLCategoryFeed::isCategoryFeed($feed)) {
        $xmlCategoryFeed = new XMLCategoryFeed($feed);
        $xmlCategoryFeed->deleteTemporaryFiles();
        $xmlCategoryFeed->deleteXml();

        header('Location: ' . UrlManager::getAdminControllerUrl() . '&page=feeds-other&mmp-tab=category');
        exit;

        // Product - lang and currency
    } else {
        $xmlProductFeed = new XMLProductFeed($feed);
        $xmlProductFeed->deleteTemporaryFiles();
        $xmlProductFeed->deleteXml();

        header('Location: ' . UrlManager::getAdminControllerUrl() . '&page=feeds-product&mmp-tab=product');
        exit;
    }
}