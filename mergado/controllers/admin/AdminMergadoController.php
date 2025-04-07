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

use Mergado\Form\AdSystems\ArukeresoForm;
use Mergado\Form\AdSystems\BianoForm;
use Mergado\Form\AdSystems\CompariForm;
use Mergado\Form\AdSystems\CookieActivationPartForm;
use Mergado\Form\AdSystems\CookieInputPartForm;
use Mergado\Form\AdSystems\EtargetForm;
use Mergado\Form\AdSystems\FacebookForm;
use Mergado\Form\AdSystems\GlamiForm;
use Mergado\Form\AdSystems\GoogleForm;
use Mergado\Form\AdSystems\HeurekaForm;
use Mergado\Form\AdSystems\KelkooForm;
use Mergado\Form\AdSystems\NajNakupSkForm;
use Mergado\Form\AdSystems\PazaruvajForm;
use Mergado\Form\AdSystems\PricemaniaForm;
use Mergado\Form\AdSystems\SeznamForm;
use Mergado\Form\OtherFeedsSettingsForm;
use Mergado\Form\ProductFeedSettingsForm;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Helper\ShopHelper;
use Mergado\Service\Feed\Base\BaseFeed;
use Mergado\Form\SupportForm;
use Mergado\Helper\LanguageHelper;
use Mergado\Helper\UrlHelper;
use Mergado\Manager\DatabaseManager;
use Mergado\Query\FeedTemplateQuery;
use Mergado\Query\SupportPageQuery;
use Mergado\Service\AccessService;
use Mergado\Service\AlertService;
use Mergado\Service\ApiService;
use Mergado\Service\External\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\Service\External\ArukeresoFamily\Compari\CompariService;
use Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\Service\External\Biano\Biano\BianoService;
use Mergado\Service\External\Biano\BianoStar\BianoStarService;
use Mergado\Service\External\Etarget\EtargetService;
use Mergado\Service\External\Facebook\FacebookService;
use Mergado\Service\External\Glami\GlamiService;
use Mergado\Service\External\Google\GoogleAds\GoogleAdsService;
use Mergado\Service\External\Google\GoogleAnalytics4\GoogleAnalytics4Service;
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
use Mergado\Service\LogService;
use Mergado\Service\News\NewsBannerService;
use Mergado\Service\News\NewsFeedService;
use Mergado\Service\News\NewsService;
use Mergado\Service\ProductPriceImportService;
use Mergado\Service\SettingsService;
use Mergado\Service\TabService;
use Mergado\Utility\TemplateLoader;

include_once _PS_MODULE_DIR_ . 'mergado/vendor/autoload.php';
include_once _PS_MODULE_DIR_ . 'mergado/mergado.php';

class AdminMergadoController extends \ModuleAdminController
{
    protected $languages;
    protected $currencies;
    protected $modulePath;
    protected $settingsValues;
    protected $defaultLang;
    public $name;

    protected $shopID;
    protected $multistoreShopSelected = true;
    protected $moduleEnabled = true;
    protected $supportForm;
    protected $tabService;

    /**
     * @var LogService
     */
    private $logger;

    /**
     * @var NewsBannerService
     */
    private $newsBannerService;

    /**
     * @var NewsService
     */
    private $newsService;

    /**
     * @var NewsFeedService
     */
    private $newsFeedService;

    /**
     * @var AccessService
     */
    private $accessService;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var AlertService
     */
    private $alertService;

    /**
     * @var ProductPriceImportService
     */
    private $productPriceImportService;

    /**
     * @var FeedTemplateQuery
     */
    private $feedTemplateQuery;

    public function __construct()
    {
        $this->className = 'AdminMergado';
        $this->table = Mergado::MERGADO['TABLE_NAME'];
        $this->name = Mergado::MERGADO['MODULE_NAME'];
        $this->bootstrap = true;
        $this->languages = new Language();
        $this->currencies = new Currency();
        $this->modulePath = __MERGADO_DIR__ . '/';
        $this->defaultLang = Configuration::get('PS_LANG_DEFAULT');

        if (Shop::isFeatureActive()) {
            // Updating context in admin are ( to change shopID properly)
            $AdminController = new AdminController();
            $AdminController->initShopContext();

            // If not single shop selected => disable module settings
            if (substr(Context::getContext()->cookie->shopContext, 0, 1) !== 's') {
                $this->multistoreShopSelected = false;
            }

            if (!Module::isEnabled($this->name)) {
                $this->moduleEnabled = false;
            }
        }

        $this->shopID = ShopHelper::getId();

        $settingsTable = DatabaseManager::getWholeSettings();
        foreach ($settingsTable as $s) {
            $this->settingsValues[$s['key']] = $s['value'];
        }

        parent::__construct();

        $this->urlHelper = UrlHelper::getInstance();
        $this->supportForm = new SupportForm();
        $this->tabService = TabService::getInstance();
        $this->logger = LogService::getInstance();
        $this->newsBannerService = NewsBannerService::getInstance();
        $this->newsService = NewsService::getInstance();
        $this->newsFeedService = NewsFeedService::getInstance();
        $this->accessService = AccessService::getInstance();
        $this->alertService = AlertService::getInstance();
        $this->productPriceImportService = ProductPriceImportService::getInstance();
        $this->feedTemplateQuery = FeedTemplateQuery::getInstance();

        try {
            $this->newsFeedService->downloadNews();
        } catch (Exception $ex) {
            // Error during installation  ()
        }
    }

    /**
     *  Prepare content for admin section
     */
    public function initContent(): void
    {
        // Check if all feed was updated
        $this->alertService->checkIfErrorsShouldBeActive();

        // Check if form is submitted
        $supportFormSubmitted = $this->supportForm->sendEmailIfSubmitted($this->module);

        $moduleUrl = UrlHelper::getShopModuleUrl();

        $mergadoModule = new Mergado();

        if(isset($_GET['page']) && $_GET['page'] === 'news') {
            $this->newsService->markArticlesShownByLanguage($this->context->language->iso_code);
        }

        $supportData = SupportPageQuery::getInformationForSupport($this->module);

        $templateData = [
            'mmp' => [
                'base' => [
                    'lang' => LanguageHelper::getLang(),
                    'multistoreShopSelected' => $this->multistoreShopSelected,
                    'moduleEnabled' => $this->moduleEnabled,
                ],
                'dirs' => [
                    'alertDir' => __MERGADO_ALERT_DIR__,
                ],
                'version' => [
                    'module' => $mergadoModule->version,
                    'phpMin' => Mergado::MERGADO['PHP_MIN_VERSION']
                ],
                'url' => [
                    'module' => $moduleUrl,
//                    'importCron' => UrlManager::getImportCronUrl($this->shopID),
                ],
                'formatting' => [
                    'date' => NewsService::DATE_OUTPUT_FORMAT, // Because of ps1.6 smarty,
                ],
                'domains' => [
                    'pack' => LanguageHelper::getPackDomain(), //header
                    'mergado' => LanguageHelper::getMergadoDomain() //header
                ],
                // SVG IMAGES
                'images' => [
                    'baseImageUrl' => $moduleUrl . '/views/img/icons.svg#',
                    'baseMmpImageUrl' => $moduleUrl . '/views/img/mmp_icons.svg#',
                ],
                // MAIN MENU
                'menu' => [
                    'left' => [
                        'feeds-product' => ['text' => $this->l('Product feeds'), 'icon' => 'product', 'page' => 'feeds-product', 'link' => $this->urlHelper->getPageLink('feeds-product')],
                        'feeds-other' => ['text' => $this->l('Other feeds'), 'icon' => 'other_feeds', 'page' => 'feeds-other', 'link' => $this->urlHelper->getPageLink('feeds-other')],
                        'adsys' => ['text' => $this->l('Ad Systems'), 'icon' => 'elements', 'page' => 'adsys', 'link' => $this->urlHelper->getPageLink('adsys')],
                    ],
                    'right' => [
                        'cookies' => ['text' => $this->l('Cookies'), 'file' => 'baseMmpImageUrl', 'icon' => 'cookies', 'page' => 'cookies', 'link' => $this->urlHelper->getPageLinkWithTab('cookies', 'cookies')],
                        'news' => ['text' => $this->l('News'), 'icon' => 'notification', 'page' => 'news', 'link' => $this->urlHelper->getPageLink('news')],
                        'support' => ['text' => $this->l('Support'), 'icon' => 'help', 'page' => 'support', 'link' => $this->urlHelper->getPageLink('support')],
                        'licence' => ['text' => $this->l('Licence'), 'icon' => 'info', 'page' => 'licence', 'link' => $this->urlHelper->getPageLink('licence')],
                    ]
                ],
                // TABS (FEEDS-OTHER, feeds-PRODUCT)
                'tabs' => $this->tabService->getTabs($this->module),
                'pageContent' => [
                    // SUPPORT PAGE
                    'support' => [
                        'form' => [
                            'submitted' => $supportFormSubmitted,
                        ],
                        'data' => [
                            'default' => $supportData,
                            'json' => json_encode($supportData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        ],
                    ],
                    'adsys' => [
                        'cookies' => ['title' => $this->l('Cookies'), 'form' => $this->getPageCookies(), 'icon' => 'cookies'],
                        'google' => ['title' => $this->l('Google'), 'form' => GoogleForm::getInstance()->render($this, $this->name, $this->defaultLang,  $this->translationFunction())],
                        'facebook' => ['title' => $this->l('Facebook'), 'form' => FacebookForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'heureka' => ['title' => $this->l('Heureka'),'form' => HeurekaForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'glami' => ['title' => $this->l('GLAMI'),'form' => GlamiForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'seznam' => ['title' => $this->l('Seznam'),'form' => SeznamForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'etarget' => ['title' => $this->l('Etarget'),'form' => EtargetForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'najnakupsk' => ['title' => $this->l('Najnakup.sk'), 'form' => NajNakupSkForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'pricemania' => ['title' => $this->l('Pricemania'), 'form' => PricemaniaForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'kelkoo' => ['title' => $this->l('Kelkoo'),'form' => KelkooForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'biano' => ['title' => $this->l('Biano'),'form' => BianoForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'arukereso' => ['title' => $this->l('Árukereső'),'form' => ArukeresoForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'compari' => ['title' => $this->l('Compari'),'form' => CompariForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                        'pazaruvaj' => ['title' => $this->l('Pazaruvaj'),'form' =>  PazaruvajForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction())],
                    ],
                    'feeds-product' => [
                        'product' => $this->feedTemplateQuery->getProductFeedsData(),
                        'settings' => ProductFeedSettingsForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction()),
                    ],
                    'feeds-other' => [
                        'category' => $this->feedTemplateQuery->getCategoryFeedsData(),
                        'static' => $this->feedTemplateQuery->getStaticFeedData(),
                        'stock' => $this->feedTemplateQuery->getStockFeedData(),
                        'import' => [
                            'data' => $this->productPriceImportService->getWizardData()
                        ],
                        'settings' => OtherFeedsSettingsForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction()),
                    ],
                    'ads' => [
                        'side' => @file_get_contents('https://platforms.mergado.com/prestashop/sidebar'),
                        'wide' => @file_get_contents('https://platforms.mergado.com/prestashop/wide'),
                    ],
                    'news' => [
                        'news' => $this->newsService->getNewsWithFormattedDate(15),
                    ]
                ],
                'hideNews' => $this->newsBannerService->isBannerVisible(),
                'toggleFieldsJSON' => $this->toggleFieldsJSON(),
                'news' => [
                    'unreadNews' => $this->newsService->getNewsByStatusAndCategory(false, 'news', 1, true, 'DESC'),
                    'unreadUpdates' => $this->newsService->getNewsByStatusAndCategory(false, 'update', 1, true, 'DESC'),
                    'unreadTopNews' => $this->newsService->getNewsByStatusAndCategory(false, 'TOP'),
                ]
            ],
            'alertService' => AlertService::getInstance(),
            'prestashopVersionHelper' => PrestashopVersionHelper::class
        ];

        if (isset($_GET['mmp-tab']) && isset($templateData['mmp']['pageContent']['adsys'][$_GET['mmp-tab']])) {
            $templateData['mmp']['pageContent']['adsys'][$_GET['mmp-tab']]['active'] = true;
        } else {
            $templateData['mmp']['pageContent']['adsys']['google']['active'] = true;
        }

        $this->context->smarty->assign($templateData);

        parent::initContent();
    }


    /*******************************************************************************************************************
     * TOGGLE FIELDS
     *******************************************************************************************************************/

    /**
     * EXAMPLE:
     *  'name' => [
     *     'fields' => [
     *        'name', 'name', 'name'
     *     ],
     *    'sub-check' => [
     *      'name' => [
     *         'fields' => [
     *           'name', 'name', 'name'
     *         ]
     *       ]
     *    ],
     *    'sub-check-two' => [
     *      'name' => [
     *         'fields' => [
     *           'name', 'name', 'name'
     *         ]
     *       ]
     *    ]
     *  ],
     *
     * @return false|string
     */
    public function toggleFieldsJSON() {
        $languages = $this->languages->getLanguages(true);

        // Extracting merge logic for better reuse and readability
        $fieldsByService = [
            'facebook' => FacebookService::getToggleFields(),
            'seznam' => array_merge(SklikService::getToggleFields(), ZboziService::getToggleFields($languages)),
            'kelkoo' => KelkooService::getToggleFields(),
            'biano' => array_merge(BianoService::getToggleFields($languages), BianoStarService::getToggleFields($languages)),
            'najnakupsk' => NajNakupService::getToggleFields(),
            'etarget' => EtargetService::getToggleFields(),
            'arukereso' => ArukeresoService::getToggleFields($languages),
            'compari' => CompariService::getToggleFields($languages),
            'pazaruvaj' => PazaruvajService::getToggleFields($languages),
            'pricemania' => PricemaniaService::getToggleFields(),
            'heureka' => array_merge(HeurekaCZService::getToggleFields(), HeurekaSKService::getToggleFields()),
            'glami' => GlamiService::getToggleFields(),
            'google' => array_merge(
                GoogleReviewsService::getToggleFields(),
                GoogleAdsService::getToggleFields(),
                GoogleTagManagerService::getToggleFields(),
                GoogleUniversalAnalyticsService::getToggleFields(),
                GoogleAnalytics4Service::getToggleFields()
            )
        ];

        return json_encode($fieldsByService, JSON_FORCE_OBJECT);
    }

    /*******************************************************************************************************************
     * POST / AJAX PROCESS
     *******************************************************************************************************************/

    /**
     *  Processing data after submitting forms in admin section
     */
    public function postProcess() : void
    {
        if ($this->accessService->employeeHasAccessToModify($this->context)) {
            // PS FORM SUBMIT
            if (Tools::isSubmit('submit' . $this->name)) {
                unset($_POST['submit' . $this->name]);

                $this->logger->info("Settings saved:\n" . json_encode($_POST) . "\n");

                // Delete checkbox values manually, because $_POST does not contain empty checkboxes
                if (isset($_POST['clrCheckboxesProduct'])) {
                    DatabaseManager::deleteShopItemsByPattern(SettingsService::EXPORT['BOTH'], $this->shopID);
                    DatabaseManager::deleteShopItemsByPattern(SettingsService::EXPORT['NONE'], $this->shopID);
                    DatabaseManager::deleteShopItemsByPattern(SettingsService::EXPORT['DENIED_PRODUCTS'], $this->shopID);
                    DatabaseManager::deleteShopItemsByPattern(SettingsService::EXPORT['CATALOG'], $this->shopID);
                    DatabaseManager::deleteShopItemsByPattern(SettingsService::EXPORT['SEARCH'], $this->shopID);
                    DatabaseManager::deleteShopItemsByPattern(SettingsService::EXPORT['COST'], $this->shopID);
                }

                // Delete checkbox values manually, because $_POST does not contain empty checkboxes
                if (isset($_POST['clrCheckboxesOther'])) {
                    DatabaseManager::deleteShopItemsByPattern(SettingsService::EXPORT['DENIED_PRODUCTS_OTHER'], $this->shopID, true);
                }

                // Remove temporary files when changed the ITEMS PER STEP FIELDS
                if (isset($_POST[ProductFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME]) && $_POST[ProductFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME] !== DatabaseManager::getSettingsFromCache(ProductFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME, $_POST['id_shop'])) {
                    ProductFeed::deleteTmpFiles('mergado_feed_', $_POST['id_shop']);
                }

                if (isset($_POST[CategoryFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME]) && $_POST[CategoryFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME] !== DatabaseManager::getSettingsFromCache(CategoryFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME, $_POST['id_shop'])) {
                    CategoryFeed::deleteTmpFiles('category_mergado_feed_',$_POST['id_shop']);
                }

                if (isset($_POST[StockFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME]) && $_POST[StockFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME] !== DatabaseManager::getSettingsFromCache(StockFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME, $_POST['id_shop'])) {
                    StockFeed::deleteTmpFiles('stock',$_POST['id_shop']);
                }

                if (isset($_POST[StaticFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME]) && $_POST[StaticFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME] !== DatabaseManager::getSettingsFromCache(StaticFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME, $_POST['id_shop'])) {
                    StaticFeed::deleteTmpFiles('static_feed', $_POST['id_shop']);
                }


                if(isset($_POST['id_shop'])) {
                    $shopID = $_POST['id_shop'];
                    unset($_POST['id_shop']);

                    $changed = true;
                    foreach ($_POST as $key => $value) {
                        $settingValue = DatabaseManager::getSettingsWithoutCache($key);

                        if($settingValue !== $value) {

                            // Html inputs that should be saved as html not escaped text ..
                            if (strpos($key, 'opt_out_text-') === false) {
                                DatabaseManager::saveSetting($key, $value, $shopID);
                            } else {
                                DatabaseManager::saveSetting($key, $value, $shopID, true);
                            }

                            $this->logger->info('Settings value edited: ' . $key . ' => ' . $value);
                            $changed = false;
                        }
                    }

                    if($changed) {
                        $this->logger->info('No value changed during save');
                    }
                }

                $this->redirect_after = self::$currentIndex . '&token=' . $this->token . (Tools::isSubmit('submitFilter' . $this->list_id) ? '&submitFilter' . $this->list_id . '=' . (int)Tools::getValue('submitFilter' . $this->list_id) : '') . '&page=' . $_POST['page'];
                if (isset($_POST['mmp-tab']) && $_POST['mmp-tab']) {
                    $this->redirect_after = $this->redirect_after . '&mmp-tab=' . $_POST['mmp-tab'];
                }
            }

            if (Tools::isSubmit('submit' . $this->name . 'delete')) {
                if (isset($_POST['delete_url'])) {
                    if (file_exists(BaseFeed::XML_DIR . $_POST['delete_url'])) {
                        unlink(BaseFeed::XML_DIR . $_POST['delete_url']);
                    }
                }
            }

            //TODO: check delete feed (WAS $_GET without $_POST checks) and other endpoints if working (and translations was $this->trans and now $context->trans)
            ApiService::getInstance()->initAdminEndpoints($this, $this->context);
        }
    }

    /**
     * Define function alternative introduced in prestashop 1.7
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        if (method_exists(parent::class, 'trans')) {
            return parent::trans($id, $parameters, $domain, $locale);
        }

        // Compatibility fro PS1.6
        return $id;
    }

    /*******************************************************************************************************************
     * FORMS
     *******************************************************************************************************************/

    public function formOtherSettings()
    {
        /**
         * @var $helper
         * @var $fields_form
         */
        include_once __DIR__ . '/forms/settings/feeds-other.php';
        return @$helper->generateForm($fields_form);
    }

    public function formProductSettings()
    {
        /**
         * @var $helper
         * @var $fields_form
         */
        include_once __DIR__ . '/forms/settings/feeds-product.php';
        return @$helper->generateForm($fields_form);
    }

    /*******************************************************************************************************************
     * FORMS - ADSYS
     *******************************************************************************************************************/

    public function getPageCookies() {
        return TemplateLoader::getTemplate(__MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/adsys/cookies.php', [
            'module' => $this->module,
            'translateFunction' => $this->translationFunction(),
            'activationForm' => CookieActivationPartForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction()),
            'inputForm' => CookieInputPartForm::getInstance()->render($this, $this->name, $this->defaultLang, $this->translationFunction()),
        ]);
    }

    public function translationFunction(): Closure
    {
        return function($string, $specific = false) {
            return $this->module->l($string, $specific);
        };
    }
}
