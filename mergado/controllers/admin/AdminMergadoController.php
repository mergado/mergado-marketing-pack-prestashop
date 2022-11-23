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

use Mergado\includes\services\Biano\Biano\BianoClass;
use Mergado\includes\services\Biano\BianoStar\BianoStarService;
use Mergado\Etarget\EtargetClass;
use Mergado\Facebook\FacebookClass;
use Mergado\Forms\SupportForm;
use Mergado\Google\GaRefundClass;
use Mergado\Google\GoogleReviewsClass;
use Mergado\includes\services\Google\GoogleAds\GoogleAdsService;
use Mergado\includes\services\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\includes\services\ArukeresoFamily\Compari\CompariService;
use Mergado\includes\services\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\includes\services\Google\GoogleAnalytics4\GoogleAnalytics4Service;
use Mergado\includes\services\Google\GoogleTagManager\GoogleTagManagerService;
use Mergado\includes\services\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService;
use Mergado\includes\services\Kelkoo\KelkooService;
use Mergado\NajNakup\NajNakupClass;
use Mergado\Sklik\SklikClass;
use Mergado\Tools\FeedQuery;
use Mergado\Tools\ImportPricesClass;
use Mergado\Tools\LanguagesClass;
use Mergado\Tools\LogClass;
use Mergado\Tools\NavigationClass;
use Mergado\Tools\NewsClass;
use Mergado\Tools\TabsClass;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLStaticFeed;
use Mergado\Tools\XML\XMLStockFeed;
use Mergado\Tools\XMLClass;
use Mergado\Tools\SettingsClass;
use Mergado\Tools\UrlManager;
use Mergado\Zbozi\ZboziClass;
use ShopCore as Shop;
use ConfigurationCore as Configuration;
use ContextCore as Context;
use CurrencyCore as Currency;
use LanguageCore as Language;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

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

    protected $navigationClass;
    protected $supportForm;
    protected $tabsClass;
    protected $xmlClass;
    protected $feedQuery;
    protected $importPricesClass;

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

        $this->shopID = Mergado::getShopId();

        $settingsTable = SettingsClass::getWholeSettings($this->shopID);
        foreach ($settingsTable as $s) {
            $this->settingsValues[$s['key']] = $s['value'];
        }

        parent::__construct();

        if (!LogClass::getLogToken()) {
            LogClass::setLogToken();
        }

        $this->navigationClass = new NavigationClass($this->context);
        $this->supportForm = new SupportForm();
        $this->tabsClass = new TabsClass($this->module);
        $this->xmlClass = new XmlClass();
        $this->feedQuery = new FeedQuery();
        $this->importPricesClass = new ImportPricesClass();

        try {
            $cronRss = new Mergado\Tools\RssClass();
            $cronRss->getFeed();
        } catch (Exception $ex) {
            // Error during installation  ()
        }
    }

    /**
     *  Prepare content for admin section
     */
    public function initContent()
    {
        // Check if all feed was updated
        $alertClass = new AlertClass();
        $alertClass->checkIfErrorsShouldBeActive();

        // Check if form is subbmited
        $supportFormSubmitted = $this->supportForm->sendEmailIfSubmited($this->shopID, $this->module);

        $moduleUrl = UrlManager::getModuleUrl();

        $mergadoModule = new Mergado();

        if(isset($_GET['page']) && $_GET['page'] == 'news') {
            NewsClass::setArticlesShownByLanguage($this->context->language->iso_code);
        }

        $supportData = SupportClass::getInformationsForSupport($this->shopID, $this->module);

        $templateData = [
            'mmp' => [
                'base' => [
                    'lang' => LanguagesClass::getLangIso(),
                    'multistoreShopSelected' => $this->multistoreShopSelected,
                    'moduleEnabled' => $this->moduleEnabled,
                ],
                'dirs' => [
                    'alertDir' => __MERGADO_ALERT_DIR__,
                ],
                'version' => [
                    'module' => $mergadoModule->version,
                    'remote' => SettingsClass::getSettings(SettingsClass::NEW_MODULE_VERSION_AVAILABLE, 0), // probably not needed anymore
                    'phpMin' => Mergado::MERGADO['PHP_MIN_VERSION']
                ],
                'url' => [
                    'module' => $moduleUrl,
                    'importCron' => UrlManager::getImportCronUrl($this->shopID),
                ],
                'formatting' => [
                    'date' => NewsClass::DATE_OUTPUT_FORMAT, // Because of ps1.6 smarty,
                ],
                'domains' => [
                    'pack' => LanguagesClass::getPackDomain(), //header
                    'mergado' => LanguagesClass::getMergadoDomain() //header
                ],
                // SVG IMAGES
                'images' => [
                    'baseImageUrl' => $moduleUrl . 'views/img/icons.svg#',
                    'baseMmpImageUrl' => $moduleUrl . 'views/img/mmp_icons.svg#',
                ],
                // MAIN MENU
                'menu' => [
                    'left' => [
                        'feeds-product' => ['text' => $this->l('Product feeds'), 'icon' => 'product', 'page' => 'feeds-product', 'link' => $this->navigationClass->getPageLink('feeds-product')],
                        'feeds-other' => ['text' => $this->l('Other feeds'), 'icon' => 'other_feeds', 'page' => 'feeds-other', 'link' => $this->navigationClass->getPageLink('feeds-other')],
                        'adsys' => ['text' => $this->l('Ad Systems'), 'icon' => 'elements', 'page' => 'adsys', 'link' => $this->navigationClass->getPageLink('adsys')],
                    ],
                    'right' => [
                        'cookies' => ['text' => $this->l('Cookies'), 'file' => 'baseMmpImageUrl', 'icon' => 'cookies', 'page' => 'cookies', 'link' => $this->navigationClass->getPageLinkWithTab('cookies', 'cookies')],
                        'news' => ['text' => $this->l('News'), 'icon' => 'notification', 'page' => 'news', 'link' => $this->navigationClass->getPageLink('news')],
                        'support' => ['text' => $this->l('Support'), 'icon' => 'help', 'page' => 'support', 'link' => $this->navigationClass->getPageLink('support')],
                        'licence' => ['text' => $this->l('Licence'), 'icon' => 'info', 'page' => 'licence', 'link' => $this->navigationClass->getPageLink('licence')],
                    ]
                ],
                // TABS (FEEDS-OTHER, feeds-PRODUCT)
                'tabs' => $this->tabsClass->getTabs(),
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
                        'cookies' => ['title' => $this->l('Cookies'), 'form' => $this->pageCookies(), 'icon' => 'cookies'],
                        'google' => ['title' => $this->l('Google'), 'form' => $this->formAdSys_google()],
                        'facebook' => ['title' => $this->l('Facebook'), 'form' => $this->formAdSys_facebook()],
                        'heureka' => ['title' => $this->l('Heureka'),'form' => $this->formAdSys_heureka()],
                        'glami' => ['title' => $this->l('GLAMI'),'form' => $this->formAdSys_glami()],
                        'seznam' => ['title' => $this->l('Seznam'),'form' => $this->formAdSys_seznam()],
                        'etarget' => ['title' => $this->l('Etarget'),'form' => $this->formAdSys_etarget()],
                        'najnakupsk' => ['title' => $this->l('Najnakup.sk'), 'form' => $this->formAdSys_najnakupsk()],
                        'pricemania' => ['title' => $this->l('Pricemania'), 'form' => $this->formAdSys_pricemania()],
                        'kelkoo' => ['title' => $this->l('Kelkoo'),'form' => $this->formAdSys_kelkoo()],
                        'biano' => ['title' => $this->l('Biano'),'form' => $this->formAdSys_biano()],
                        'arukereso' => ['title' => $this->l('Árukereső'),'form' => $this->formAdSys_arukereso()],
                        'compari' => ['title' => $this->l('Compari'),'form' => $this->formAdSys_compari()],
                        'pazaruvaj' => ['title' => $this->l('Pazaruvaj'),'form' => $this->formAdSys_pazaruvaj()],
                    ],
                    'feeds-product' => [
                        'product' => $this->feedQuery->getProductFeedsData(),
                        'settings' => $this->formProductSettings(), // maybe will be array/different
                    ],
                    'feeds-other' => [
                        'category' => $this->feedQuery->getCategoryFeedsData(),
                        'static' => $this->feedQuery->getStaticFeedData(),
                        'stock' => $this->feedQuery->getStockFeedData(),
                        'import' => [
                            'data' => $this->importPricesClass->getWizardData()
                        ],
                        'settings' => $this->formOtherSettings(), // maybe will be array/different
                    ],
                    'ads' => [
                        'side' => @file_get_contents('https://platforms.mergado.com/prestashop/sidebar'),
                        'wide' => @file_get_contents('https://platforms.mergado.com/prestashop/wide'),
                    ],
                    'news' => [
                        'news' => NewsClass::getNewsWithFormatedDate($this->context->language->iso_code, 15),
                    ]
                ],
                'hideNews' => NewsClass::areNewsHidden(),
                'toggleFieldsJSON' => $this->toggleFieldsJSON(),
                'news' => [
                    'unreadedNews' => NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code, 'news', 1, true, 'DESC'),
                    'unreadedUpdates' => NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code, 'update', 1, true, 'DESC'),
                    'unreadedTopNews' => NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code, 'TOP'),
                ]
            ],
            'alertClass' => new AlertClass()
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

    //TODO: SPLIT IN SMALLER FILES - MOVE EVERY SINGLE ONE TO SINGLE CLASS AND IMCLUDE IT HERE
    public function toggleFieldsJSON() {
        //GLAMI
        $glamiFields = [];
        $glamiMainFields = array_values(SettingsClass::GLAMI_LANGUAGES);

        foreach(SettingsClass::GLAMI_LANGUAGES as $key => $values) {
            $glamiFields[$values]['fields'] = [SettingsClass::GLAMI['CODE'] . '-' . $key];
        }
        $glamiMainFields[] = SettingsClass::GLAMI['CONVERSION_VAT_INCL'];

        $oldFields = [
            // Google analytics - GTAGJS
            GoogleUniversalAnalyticsService::ACTIVE => [
                'fields' => [
                    GoogleUniversalAnalyticsService::CODE,
                    GoogleUniversalAnalyticsService::ECOMMERCE,
                    GoogleUniversalAnalyticsService::ECOMMERCE_ENHANCED,
                    GoogleUniversalAnalyticsService::CONVERSION_VAT_INCL,
                ],
                'sub-check' => [
                    GoogleUniversalAnalyticsService::ECOMMERCE => [
                        'fields' => [
                            GoogleUniversalAnalyticsService::ECOMMERCE_ENHANCED,
                        ],
                    ],
                ]
            ],

            // TODO: Heureka optOut toggle
            // Heureka
            SettingsClass::HEUREKA['VERIFIED_CZ'] => [
                'fields' => [
                    SettingsClass::HEUREKA['VERIFIED_CODE_CZ'],
                ],
            ],
            SettingsClass::HEUREKA['WIDGET_CZ'] => [
                'fields' => [
                    SettingsClass::HEUREKA['WIDGET_ID_CZ'],
                    SettingsClass::HEUREKA['WIDGET_POSITION_CZ'],
                    SettingsClass::HEUREKA['WIDGET_TOP_MARGIN_CZ'],
                ],
            ],

            SettingsClass::HEUREKA['VERIFIED_SK'] => [
                'fields' => [
                    SettingsClass::HEUREKA['VERIFIED_CODE_SK'],
                ],
            ],
            SettingsClass::HEUREKA['WIDGET_SK'] => [
                'fields' => [
                    SettingsClass::HEUREKA['WIDGET_ID_SK'],
                    SettingsClass::HEUREKA['WIDGET_POSITION_SK'],
                    SettingsClass::HEUREKA['WIDGET_TOP_MARGIN_SK'],
                ],
            ],


            SettingsClass::HEUREKA['CONVERSIONS_CZ'] => [
                'fields' => [
                    SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ'],
                    SettingsClass::HEUREKA['CONVERSION_VAT_INCL_CZ'],
                ],
            ],
            SettingsClass::HEUREKA['CONVERSIONS_SK'] => [
                'fields' => [
                    SettingsClass::HEUREKA['CONVERSIONS_CODE_SK'],
                    SettingsClass::HEUREKA['CONVERSION_VAT_INCL_SK'],
                ],
            ],

            // GLAMI
            SettingsClass::GLAMI['ACTIVE'] => [
                'fields' => $glamiMainFields,
                'sub-check' => $glamiFields,
            ],
            SettingsClass::GLAMI['ACTIVE_TOP'] => [
                'fields' => [
                    SettingsClass::GLAMI['SELECTION_TOP'],
                    SettingsClass::GLAMI['CODE_TOP'],
                ],
            ],

            // Pricemania
            SettingsClass::PRICEMANIA['VERIFIED'] => [
                'fields' => [
                    SettingsClass::PRICEMANIA['SHOP_ID'],
                ]
            ],
        ];

        $jsonMap = array_merge(
            $oldFields,
            FacebookClass::getToggleFields(),
            SklikClass::getToggleFields(),
            KelkooService::getToggleFields(),
            BianoClass::getToggleFields($this->languages->getLanguages(true)),
            BianoStarService::getToggleFields($this->languages->getLanguages(true)),
            NajNakupClass::getToggleFields(),
            EtargetClass::getToggleFields(),
            GoogleReviewsClass::getToggleFields(),
            ZboziClass::getToggleFields($this->languages->getLanguages(true)),
            ArukeresoService::getToggleFields($this->languages->getLanguages(true)),
            CompariService::getToggleFields($this->languages->getLanguages(true)),
            PazaruvajService::getToggleFields($this->languages->getLanguages(true)),
            GoogleAdsService::getToggleFields(),
            GoogleTagManagerService::getToggleFields(),
            GoogleUniversalAnalyticsService::getToggleFields(),
            GoogleAnalytics4Service::getToggleFields()
        );

        return json_encode($jsonMap, JSON_FORCE_OBJECT);
    }


    /*******************************************************************************************************************
     * POST / AJAX PROCESS
     *******************************************************************************************************************/

    /**
     *  Processing data after submitting forms in admin section
     */
    public function postProcess()
    {
        // PS FORM SUBMIT
        if (Tools::isSubmit('submit' . $this->name)) {
            unset($_POST['submit' . $this->name]);

            LogClass::log("Settings saved:\n" . json_encode($_POST) . "\n");

            // Delete checkbox values manually, because $_POST does not contain empty checkboxes
            if (isset($_POST['clrCheckboxesProduct'])) {
                SettingsClass::clearSettings(SettingsClass::EXPORT['BOTH'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['NONE'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['DENIED_PRODUCTS'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['CATALOG'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['SEARCH'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['COST'], $this->shopID);
            }

            // Delete checkbox values manually, because $_POST does not contain empty checkboxes
            if (isset($_POST['clrCheckboxesOther'])) {
                SettingsClass::clearSettings(SettingsClass::EXPORT['DENIED_PRODUCTS_OTHER'], $this->shopID);
            }

            // Not used anymore
            if (isset($_POST['mergado_del_log']) && $_POST['mergado_del_log'] === SettingsClass::ENABLED) {
                LogClass::deleteLog();
            }

            // Remove temporary files when changed the ITEMS PER STEP FIELDS
            if (isset($_POST[XMLClass::OPTIMIZATION['PRODUCT_FEED']]) && $_POST[XMLClass::OPTIMIZATION['PRODUCT_FEED']] !== SettingsClass::getSettings(XMLClass::OPTIMIZATION['PRODUCT_FEED'], $_POST['id_shop'])) {
                XMLProductFeed::deleteTmpFiles('mergado_feed_', $_POST['id_shop']);
            }

            if (isset($_POST[XMLClass::OPTIMIZATION['CATEGORY_FEED']]) && $_POST[XMLClass::OPTIMIZATION['CATEGORY_FEED']] !== SettingsClass::getSettings(XMLClass::OPTIMIZATION['CATEGORY_FEED'], $_POST['id_shop'])) {
                XMLCategoryFeed::deleteTmpFiles('category_mergado_feed_',$_POST['id_shop']);
            }

            if (isset($_POST[XMLClass::OPTIMIZATION['STOCK_FEED']]) && $_POST[XMLClass::OPTIMIZATION['STOCK_FEED']] !== SettingsClass::getSettings(XMLClass::OPTIMIZATION['STOCK_FEED'], $_POST['id_shop'])) {
                XMLStockFeed::deleteTmpFiles('stock',$_POST['id_shop']);
            }

            if (isset($_POST[XMLClass::OPTIMIZATION['STATIC_FEED']]) && $_POST[XMLClass::OPTIMIZATION['STATIC_FEED']] !== SettingsClass::getSettings(XMLClass::OPTIMIZATION['STATIC_FEED'], $_POST['id_shop'])) {
                XMLStaticFeed::deleteTmpFiles('static_feed', $_POST['id_shop']);
            }

            if(isset($_POST['id_shop'])) {
                $shopID = $_POST['id_shop'];
                unset($_POST['id_shop']);

                $changed = true;
                foreach ($_POST as $key => $value) {
                    $settingValue = SettingsClass::getSettings($key, $shopID);

                    if($settingValue !== $value) {

                        // Html inputs that should be saved as html not escaped text ..
                        if (strpos($key, 'opt_out_text-') === false) {
                            SettingsClass::saveSetting($key, $value, $shopID);
                        } else {
                            SettingsClass::saveSetting($key, $value, $shopID, true);
                        }

                        LogClass::log('Settings value edited: ' . $key . ' => ' . $value);
                        $changed = false;
                    }
                }

                if($changed) {
                    LogClass::log('No value changed during save');
                }
            }

            $this->redirect_after = self::$currentIndex . '&token=' . $this->token . (Tools::isSubmit('submitFilter' . $this->list_id) ? '&submitFilter' . $this->list_id . '=' . (int)Tools::getValue('submitFilter' . $this->list_id) : '') . '&page=' . $_POST['page'];
            if (isset($_POST['mmp-tab']) && $_POST['mmp-tab']) {
                $this->redirect_after = $this->redirect_after . '&mmp-tab=' . $_POST['mmp-tab'];
            }
        }

        if (Tools::isSubmit('submit' . $this->name . 'delete')) {
            if (isset($_POST['delete_url'])) {
                if (file_exists(XMLClass::XML_DIR . $_POST['delete_url'])) {
                    unlink(XMLClass::XML_DIR . $_POST['delete_url']);
                }
            }
        }

        if (isset($_GET['action'])) {
            include_once __MERGADO_DIR__ . '/includes/api/DeleteFeed.php';
        }

        if(isset($_POST['controller']) && $_POST['controller'] === 'AdminMergado') {
            // Feed generation
            include_once __MERGADO_DIR__ . '/includes/api/FeedGeneration.php';

            // Wizard
            include_once __MERGADO_DIR__ . '/includes/api/Wizard.php';

            // Import prices
            include_once __MERGADO_DIR__ . '/includes/api/ImportPrices.php';

            // News
            include_once __MERGADO_DIR__ . '/includes/api/News.php';

            // Cookie - for news banner and other services
            include_once __MERGADO_DIR__ . '/includes/api/Cookies.php';

            // Alerts
            include_once __MERGADO_DIR__ . '/includes/api/Alerts.php';
        }
    }

    /*******************************************************************************************************************
     * FORMS
     *******************************************************************************************************************/

    public function formOtherSettings()
    {
        include_once __DIR__ . '/forms/settings/feeds-other.php';
        return @$helper->generateForm($fields_form);
    }

    public function formProductSettings()
    {
        include_once __DIR__ . '/forms/settings/feeds-product.php';
        return @$helper->generateForm($fields_form);
    }

//    /**
//     * Forms in Dev Tab in admin section
//     *
//     * @return mixed
//     */
//    public function formDevelopers()
//    {
//        $fields_form[0]['form'] = array(
//            'legend' => array(
//                'title' => $this->l('Help'),
//                'icon' => 'icon-bug'
//            ),
//            'input' => array(
//                array(
//                    'type' => 'hidden',
//                    'name' => 'page'
//                ),
//                array(
//                    'type' => 'hidden',
//                    'name' => 'id_shop'
//                ),
//                array(
//                    'name' => 'mergado_dev_log',
//                    'label' => $this->l('Enable log'),
//                    'validation' => 'isBool',
//                    'cast' => 'intval',
//                    'class' => 'switch15',
//                    'desc' => $this->l('Send this to support:') . " " . LogClass::getLogLite(),
//                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
//                    'values' => array(
//                        array(
//                            'id' => 'mergado_dev_log_on',
//                            'value' => 1,
//                            'label' => $this->l('Yes')
//                        ),
//                        array(
//                            'id' => 'mergado_dev_log_off',
//                            'value' => 0,
//                            'label' => $this->l('No')
//                        )
//                    ),
//                    'visibility' => Shop::CONTEXT_ALL
//                ),
//                array(
//                    'label' => $this->l('Delete log file when saving'),
//                    'name' => 'mergado_del_log',
//                    'validation' => 'isBool',
//                    'cast' => 'intval',
//                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
//                    'class' => 'switch15',
//                    'values' => array(
//                        array(
//                            'id' => 'mergado_del_log_on',
//                            'value' => 1,
//                            'label' => $this->l('Yes')
//                        ),
//                        array(
//                            'id' => 'mergado_del_log_off',
//                            'value' => 0,
//                            'label' => $this->l('No')
//                        )
//                    ),
//                ),
//            ),
//            'submit' => array(
//                'title' => $this->l('Save'),
//                'name' => 'submit' . $this->name
//            )
//        );
//
//        $fields_value = array(
//            'mergado_dev_log' => isset($this->settingsValues['mergado_dev_log']) ? $this->settingsValues['mergado_dev_log'] : 0,
//            'mergado_del_log' => 0,
//            'page' => 4,
//            'id_shop' => $this->shopID,
//        );
//
//        //Fill in empty fields
//        include __MERGADO_FORMS_DIR__ . '/helpers/helperFormEmptyFieldsFiller.php';
//
//        $this->show_toolbar = true;
//        $this->show_form_cancel_button = false;
//
//        $helper = new HelperForm();
//
//        $helper->module = $this;
//        $helper->name_controller = $this->name;
//
//        $helper->tpl_vars = array('fields_value' => $fields_value);
//        $helper->default_form_language = $this->defaultLang;
//        $helper->allow_employee_form_lang = $this->defaultLang;
//
//        if (isset($this->displayName)) {
//            $helper->title = $this->displayName;
//        }
//
//        $helper->show_toolbar = true;
//        $helper->toolbar_scroll = true;
//        $helper->submit_action = 'submit' . $this->name;
//
//        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {
//            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
//            $helper->submit_action = 'save' . $this->name;
//            $helper->token = Tools::getValue('token');
//        }
//
//        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {
//
//        } else {
//            $helper->toolbar_btn = array(
//                'save' =>
//                    array(
//                        'desc' => $this->l('Save'),
//                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
//                            '&token=' . Tools::getAdminTokenLite('AdminModules'),
//                    ),
//                'back' => array(
//                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
//                    'desc' => $this->l('Back to list')
//                )
//            );
//        }
//
//        return @$helper->generateForm($fields_form);
//    }

    /*******************************************************************************************************************
     * FORMS - ADSYS
     *******************************************************************************************************************/

    public function pageCookies() {
        ob_start();
        include_once __DIR__ . '/adsys/cookies.php';
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function formAdSys_google() {
        include_once __DIR__ . '/forms/adsys/google.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_facebook() {
        include_once __DIR__ . '/forms/adsys/facebook.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_heureka() {
        include_once __DIR__ . '/forms/adsys/heureka.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_etarget() {
        include_once __DIR__ . '/forms/adsys/etarget.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_najnakupsk() {
        include_once __DIR__ . '/forms/adsys/najnakupsk.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_seznam() {
        include_once __DIR__ . '/forms/adsys/seznam.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_glami() {
        include_once __DIR__ . '/forms/adsys/glami.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_kelkoo() {
        include_once __DIR__ . '/forms/adsys/kelkoo.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_pricemania() {
        include_once __DIR__ . '/forms/adsys/pricemania.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_biano() {
        include_once __DIR__ . '/forms/adsys/biano.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_arukereso() {
        include_once __DIR__ . '/forms/adsys/arukereso.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_compari() {
        include_once __DIR__ . '/forms/adsys/compari.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_pazaruvaj() {
        include_once __DIR__ . '/forms/adsys/pazaruvaj.php';
        return @$helper->generateForm($fields_form);
    }
}
