<?php
// Abstract
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/AbstractArukeresoFamilyService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/AbstractArukeresoFamilyServiceIntegration.php';

// Trait
include_once _PS_MODULE_DIR_ . 'mergado/includes/traits/SingletonTrait.php';

// Ga4 Objects
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleAnalytics4/objects/base/BaseGoogleAnalytics4EventObject.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleAnalytics4/objects/base/BaseGoogleAnalytics4ItemEventObject.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleAnalytics4/objects/base/BaseGoogleAnalytics4ItemsEventObject.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleAnalytics4/objects/GoogleAnalytics4RefundEventObject.php';

// Services
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/Arukereso/ArukeresoService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/Compari/CompariService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/Pazaruvaj/PazaruvajService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/TrustedShop.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Biano/Biano/BianoClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Biano/BianoStar/BianoStarService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Etarget/EtargetClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Facebook/FacebookClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Glami/GlamiClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GaRefundClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleAds/GoogleAdsService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleUniversalAnalytics/GoogleUniversalAnalyticsService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleAnalytics4/GoogleAnalytics4Service.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleReviews/GoogleReviewsClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleTagManager/GoogleTagManagerService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Heureka/HeurekaClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Kelkoo/KelkooService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/NajNakup/NajNakupClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Pricemania/PricemaniaClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Sklik/SklikClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Zbozi/DeliveryType.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Zbozi/Zbozi.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Zbozi/ZboziCartItem.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Zbozi/ZboziClass.php';

// Service integration
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/Arukereso/ArukeresoServiceIntegration.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/Compari/CompariServiceIntegration.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/ArukeresoFamily/Pazaruvaj/PazaruvajServiceIntegration.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Biano/BianoStar/BianoStarServiceIntegration.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleAds/GoogleAdsServiceIntegration.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleUniversalAnalytics/GoogleUniversalAnalyticsServiceIntegration.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleAnalytics4/GoogleAnalytics4ServiceIntegration.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Kelkoo/KelkooServiceIntegration.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/GoogleTagManager/GoogleTagManagerServiceIntegration.php';

include_once _PS_MODULE_DIR_ . 'mergado/includes/services/Google/Gtag/GtagIntegrationHelper.php';

// Tools
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/HelperClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/ImportPricesClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/LogClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/NewsClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/RssClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/SettingsClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XML/Base/BaseFeed.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XML/Base/BaseFeedSimple.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XML/Base/BaseFeedMulti.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XML/Helpers/XMLQuery.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XML/XMLCategoryFeed.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XML/XMLProductFeed.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XML/XMLStaticFeed.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XML/XMLStockFeed.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/XMLClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/CookieService.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/NavigationClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/LanguagesClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/SupportClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/TabsClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/DirectoryManager.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/UrlManager.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/FeedQuery.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/AlertClass.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/JsonResponse.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/tools/OrderClass.php';

/**
 * Helpers
 */
include_once _PS_MODULE_DIR_ . 'mergado/includes/helpers/CartHelper.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/helpers/ProductHelper.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/helpers/ControllerHelper.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/helpers/OrderConfirmationHelper.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/helpers/DebugHelper.php';
include_once _PS_MODULE_DIR_ . 'mergado/includes/helpers/RefererHelper.php';

// Forms
include_once _PS_MODULE_DIR_ . 'mergado/views/templates/admin/mergado/pages/partials/support/form/SupportForm.php';

// Main
include_once _PS_MODULE_DIR_ . 'mergado/mergado.php';

