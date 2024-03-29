<?php

use Mergado\includes\services\Biano\Biano\BianoClass;
use Mergado\Etarget\EtargetClass;
use Mergado\Facebook\FacebookClass;
//use Mergado\Glami\GlamiPixelClass;
//use Mergado\Glami\GlamiTopClass;
use Mergado\Google\GaRefundClass;
use Mergado\includes\services\Google\GoogleAds\GoogleAdsService;
use Mergado\Google\GoogleReviewsClass;
//use Mergado\Tools\Crons;
use Mergado\includes\services\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\includes\services\ArukeresoFamily\Compari\CompariService;
use Mergado\includes\services\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\includes\services\Google\GoogleTagManager\GoogleTagManagerService;
use Mergado\includes\services\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService;
use Mergado\includes\services\Kelkoo\KelkooService;
use Mergado\NajNakup\NajNakupClass;
use Mergado\Sklik\SklikClass;
use Mergado\Tools\ImportPricesClass;
//use Mergado\Tools\Settings;
use Mergado\Tools\SettingsClass;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLStaticFeed;
use Mergado\Tools\XML\XMLStockFeed;
use Mergado\Zbozi\ZboziClass;

class SupportClass {
    public static function getInformationsForSupport($shopId, $module)
    {
        $sklikClass = new SklikClass();
        $etargetClass = new EtargetClass();
        $najnakupClass = new NajNakupClass();
        $bianoClass = new BianoClass();
        $kelkoService = KelkooService::getInstance();
        $googleAds = GoogleAdsService::getInstance();
        $googleTagManager = GoogleTagManagerService::getInstance();
        $googleReviewsClass = GoogleReviewsClass::getInstance();
        $arukeresoService = new ArukeresoService();
        $compariService = new CompariService();
        $pazaruvajService = new PazaruvajService();
        $zboziClass = new ZboziClass($shopId);
        $facebookClass = new FacebookClass();

        $base = [
            'web_url' => [
                'name' => $module->l('Web URL', 'support'),
                'value' => \Mergado\Tools\UrlManager::getBaseUrl(),
            ],
            'ps_version' => [
                'name' => $module->l('PS version', 'support'),
                'value' => _PS_VERSION_,
            ],
            'php' => [
                'name' => $module->l('PHP', 'support'),
                'value' => phpversion(),
            ],
        ];

        $base = array_merge($base, self::getFormattedFeedsToSupport($module)[0]);

        $adsystems = [
            'googleAds' => self::boolToActive($googleAds->getConversionsActive()),
            'googleAdsRemarketing' => self::boolToActive($googleAds->getRemarketingActive()),
            'googleAnalytics' => self::boolToActive(SettingsClass::getSettings(GoogleUniversalAnalyticsService::ACTIVE, $shopId)),
            'googleTagManager' => self::boolToActive($googleTagManager->getActive()),
            'googleTagManagerEcommerce' => self::boolToActive($googleTagManager->getEcommerceActive()),
            'googleTagManagerEnhancedEcommerce' => self::boolToActive($googleTagManager->getEnhancedEcommerceActive()),
            'googleCustomerReviews' => self::boolToActive($googleReviewsClass->getOptInActive()),
            'googleCustomerReviewsBadge' => self::boolToActive($googleReviewsClass->getBadgeActive()),
            'facebookPixel' => self::boolToActive($facebookClass->getActive($shopId)),
            'heurekaVerify' => self::boolToActive(SettingsClass::getSettings(SettingsClass::HEUREKA['VERIFIED_CZ'], $shopId)),
            'heurekaVerifyWidget' => self::boolToActive(SettingsClass::getSettings(SettingsClass::HEUREKA['WIDGET_CZ'], $shopId)),
            'heurekaConversions' => self::boolToActive(SettingsClass::getSettings(SettingsClass::HEUREKA['CONVERSIONS_CZ'], $shopId)),
            'heurekaVerifySk' => self::boolToActive(SettingsClass::getSettings(SettingsClass::HEUREKA['VERIFIED_SK'], $shopId)),
            'heurekaVerifySkWidget' => self::boolToActive(SettingsClass::getSettings(SettingsClass::HEUREKA['WIDGET_SK'], $shopId)),
            'heurekaConversionsSk' => self::boolToActive(SettingsClass::getSettings(SettingsClass::HEUREKA['CONVERSIONS_SK'], $shopId)),
            'glamiPixel' => self::boolToActive(SettingsClass::getSettings(SettingsClass::GLAMI['ACTIVE'], $shopId)),
            'glamiTop' => self::boolToActive(SettingsClass::getSettings(SettingsClass::GLAMI['ACTIVE_TOP'], $shopId)),
            'sklik' => self::boolToActive($sklikClass->getConversionsActive($shopId)),
            'sklikRetargeting' => self::boolToActive($sklikClass->getRetargetingActive($shopId)),
            'zbozi' => self::boolToActive($zboziClass->getActive()),
            'etarget' => self::boolToActive($etargetClass->getActive($shopId)),
            'najnakup' => self::boolToActive($najnakupClass->getActive($shopId)),
            'pricemania' => self::boolToActive(SettingsClass::getSettings(SettingsClass::PRICEMANIA['VERIFIED'], $shopId)),
            'kelkoo' => self::boolToActive($kelkoService->getActive($shopId)),
            'biano' => self::boolToActive($bianoClass->getActive($shopId)),
            'arukereso' => self::boolToActive($arukeresoService->getActive()),
            'arukeresoWidget' => self::boolToActive($arukeresoService->getWidgetActive()),
            'compari' => self::boolToActive($compariService->getActive()),
            'compariWidget' => self::boolToActive($compariService->getWidgetActive()),
            'pazaruvaj' => self::boolToActive($pazaruvajService->getActive()),
            'pazaruvajWidget' => self::boolToActive($pazaruvajService->getWidgetActive()),
        ];


        return [
            'base' => $base,
            'adsystems' => $adsystems
        ];
    }

    public static function getFormattedFeedsToSupport($module)
    {
        $productFeeds = [];
        $categoryFeeds = [];

        foreach (LanguageCore::getLanguages(true) as $lang) {
            foreach (CurrencyCore::getCurrencies(false, true, true) as $currency) {
                //Product
                $name = XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];
                $xmlProductFeed = new XMLProductFeed($name);

                $productFeeds['product_feed_url_' . $name] = [
                    'name' => $module->l('Product feed URL - ' . $lang['iso_code'] . '-' . $currency['iso_code'], 'support'),
                    'value' => $xmlProductFeed->getFeedUrl()
                ];

                $productFeeds['product_cron_url_' . $name] = [
                    'name' => $module->l('Product cron URL - ' . $lang['iso_code'] . '-' . $currency['iso_code'], 'support'),
                    'value' => $xmlProductFeed->getCronUrl()
                ];

                //Category
                $name = XMLCategoryFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];
                $xmlCategoryFeed = new XMLCategoryFeed($name);

                $categoryFeeds['category_feed_url_' . $name] = [
                    'name' => $module->l('Category feed URL - ' . $lang['iso_code'] . '-' . $currency['iso_code'], 'support'),
                    'value' => $xmlCategoryFeed->getFeedUrl()
                ];

                $categoryFeeds['category_cron_url_' . $name] = [
                    'name' => $module->l('Category cron URL - ' . $lang['iso_code'] . '-' . $currency['iso_code'], 'support'),
                    'value' => $xmlCategoryFeed->getCronUrl()
                ];
            }
        }

        $xmlStockFeed = new XMLStockFeed();

        $stockFeeds = [
            'stock_feed_url' => [
                'name' => $module->l('Stock feed URL'),
                'value' => $xmlStockFeed->getFeedUrl(),
            ],
            'stock_cron_url' => [
                'name' => $module->l('Stock feed URL'),
                'value' => $xmlStockFeed->getCronUrl(),
            ],
        ];

        $xmlStaticFeed = new XMLStaticFeed();

        $staticFeeds = [
            'static_feed_url' => [
                'name' => $module->l('Static feed URL'),
                'value' => $xmlStaticFeed->getFeedUrl(),
            ],
            'static_cron_url' => [
                'name' => $module->l('Static cron URL'),
                'value' => $xmlStaticFeed->getCronUrl(),
            ],
        ];

        $importPrices = new ImportPricesClass();

        $importFeeds = [
            'import_feed_url' => [
                'name' => $module->l('Import prices feed URL', 'support'),
                'value' => $importPrices->getImportUrl(),
            ],
            'import_cron_url' => [
                'name' => $module->l('Import prices cron URL', 'support'),
                'value' => $importPrices->getCronUrl(),
            ],
        ];

        return array_merge([$productFeeds, $categoryFeeds, $stockFeeds, $staticFeeds, $importFeeds]);
    }

    public static function boolToActive($bool)
    {
        if ($bool) {
            return 'active';
        } else {
            return 'inactive';
        }
    }
}
