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
use Mergado;
use Mergado\Manager\DatabaseManager;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\Service\External\ArukeresoFamily\Compari\CompariService;
use Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\Service\External\Biano\Biano\BianoService;
use Mergado\Service\External\Etarget\EtargetService;
use Mergado\Service\External\Facebook\FacebookService;
use Mergado\Service\External\Google\GoogleAds\GoogleAdsService;
use Mergado\Service\External\Google\GoogleReviews\GoogleReviewsService;
use Mergado\Service\External\Google\GoogleTagManager\GoogleTagManagerService;
use Mergado\Service\External\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService;
use Mergado\Service\External\Heureka\HeurekaCZService;
use Mergado\Service\External\Heureka\HeurekaSKService;
use Mergado\Service\External\Kelkoo\KelkooService;
use Mergado\Service\External\NajNakup\NajNakupService;
use Mergado\Service\External\Pricemania\PricemaniaService;
use Mergado\Service\External\Sklik\SklikService;
use Mergado\Service\External\Zbozi\ZboziService;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Service\ProductPriceImportService;

class SupportPageQuery extends AbstractBaseService
{
    public static function getInformationForSupport($module): array
    {
        $sklikService = SklikService::getInstance();
        $etargetService = EtargetService::getInstance();
        $najNakupService = NajNakupService::getInstance();
        $bianoService = BianoService::getInstance();
        $kelkoService = KelkooService::getInstance();
        $googleAds = GoogleAdsService::getInstance();
        $googleTagManager = GoogleTagManagerService::getInstance();
        $googleReviewsService = GoogleReviewsService::getInstance();
        $arukeresoService = ArukeresoService::getInstance();
        $compariService = CompariService::getInstance();
        $pazaruvajService = PazaruvajService::getInstance();
        $zboziService = ZboziService::getInstance();
        $facebookService = FacebookService::getInstance();
        $pricemaniaService = PricemaniaService::getInstance();
        $glamiService = Mergado\Service\External\Glami\GlamiService::getInstance();

        $base = [
            'web_url' => [
                'name' => $module->l('Web URL', 'support'),
                'value' => Mergado\Helper\UrlHelper::getShopUrl(),
            ],
            'ps_version' => [
                'name' => $module->l('PS version', 'support'),
                'value' => _PS_VERSION_,
            ],
            'php' => [
                'name' => $module->l('PHP', 'support'),
                'value' => PHP_VERSION,
            ],
        ];

        $base = array_merge($base, self::getFormattedFeedsToSupport($module)[0]);

        $adsystems = [
            'googleAds' => self::boolToActive($googleAds->getConversionsActive()),
            'googleAdsRemarketing' => self::boolToActive($googleAds->getRemarketingActive()),
            'googleAnalytics' => self::boolToActive(DatabaseManager::getSettingsFromCache(GoogleUniversalAnalyticsService::FIELD_ACTIVE)),
            'googleTagManager' => self::boolToActive($googleTagManager->getActive()),
            'googleTagManagerEcommerce' => self::boolToActive($googleTagManager->getEcommerceActive()),
            'googleTagManagerEnhancedEcommerce' => self::boolToActive($googleTagManager->getEnhancedEcommerceActive()),
            'googleCustomerReviews' => self::boolToActive($googleReviewsService->getOptInActive()),
            'googleCustomerReviewsBadge' => self::boolToActive($googleReviewsService->getBadgeActive()),
            'facebookPixel' => self::boolToActive($facebookService->getActive()),
            'heurekaVerify' => self::boolToActive(DatabaseManager::getSettingsFromCache(HeurekaCZService::FIELD_VERIFIED)),
            'heurekaVerifyWidget' => self::boolToActive(DatabaseManager::getSettingsFromCache(HeurekaCZService::FIELD_WIDGET)),
            'heurekaConversions' => self::boolToActive(DatabaseManager::getSettingsFromCache(HeurekaCZService::FIELD_LEGACY_CONVERSIONS)),
            'heurekaVerifySk' => self::boolToActive(DatabaseManager::getSettingsFromCache(HeurekaSKService::FIELD_VERIFIED)),
            'heurekaVerifySkWidget' => self::boolToActive(DatabaseManager::getSettingsFromCache(HeurekaSKService::FIELD_WIDGET)),
            'heurekaConversionsSk' => self::boolToActive(DatabaseManager::getSettingsFromCache(HeurekaSKService::FIELD_LEGACY_CONVERSIONS)),
            'glamiPixel' => self::boolToActive($glamiService->getPixelActive()),
            'glamiTop' => self::boolToActive($glamiService->getTopActive()),
            'sklik' => self::boolToActive($sklikService->getConversionsActive()),
            'sklikRetargeting' => self::boolToActive($sklikService->getRetargetingActive()),
            'zbozi' => self::boolToActive($zboziService->getActive()),
            'etarget' => self::boolToActive($etargetService->getActive()),
            'najnakup' => self::boolToActive($najNakupService->getActive()),
            'pricemania' => self::boolToActive($pricemaniaService->getActive()),
            'kelkoo' => self::boolToActive($kelkoService->getActive()),
            'biano' => self::boolToActive($bianoService->getActive()),
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

    public static function getFormattedFeedsToSupport($module): array
    {
        $productFeeds = [];
        $categoryFeeds = [];

        foreach (Language::getLanguages(true) as $lang) {
            foreach (Currency::getCurrencies(false, true, true) as $currency) {
                //Product
                $name = ProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];
                $xmlProductFeed = new ProductFeed($name);

                $productFeeds['product_feed_url_' . $name] = [
                    'name' => $module->l('Product feed URL - ' . $lang['iso_code'] . '-' . $currency['iso_code'], 'support'),
                    'value' => $xmlProductFeed->getFeedUrl()
                ];

                $productFeeds['product_cron_url_' . $name] = [
                    'name' => $module->l('Product cron URL - ' . $lang['iso_code'] . '-' . $currency['iso_code'], 'support'),
                    'value' => $xmlProductFeed->getCronUrl()
                ];

                //Category
                $name = CategoryFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];
                $xmlCategoryFeed = new CategoryFeed($name);

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

        $xmlStockFeed = new StockFeed();

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

        $xmlStaticFeed = new StaticFeed();

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

        $productPriceImportService = ProductPriceImportService::getInstance();

        $importFeeds = [
            'import_feed_url' => [
                'name' => $module->l('Import prices feed URL', 'support'),
                'value' => $productPriceImportService->getImportUrl(),
            ],
            'import_cron_url' => [
                'name' => $module->l('Import prices cron URL', 'support'),
                'value' => $productPriceImportService->getCronUrl(),
            ],
        ];

        return array_merge([$productFeeds, $categoryFeeds, $stockFeeds, $staticFeeds, $importFeeds]);
    }

    private static function boolToActive($bool): string
    {
        if ($bool) {
            return 'active';
        }

        return 'inactive';
    }
}
