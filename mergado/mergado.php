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

// Do not use USE statements because of PS 1.6.1.12 - error during installation

define('__MERGADO_DIR__', _PS_MODULE_DIR_ . 'mergado');
define('__MERGADO_FORMS_DIR__', _PS_MODULE_DIR_ . 'mergado/controllers/admin/forms/');
define('__MERGADO_ALERT_DIR__', _PS_MODULE_DIR_ . 'mergado/views/templates/admin/mergado/pages/partials/components/alerts/');

include_once _PS_MODULE_DIR_ . 'mergado/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('MERGADO_DEBUG')) {
    define('MERGADO_DEBUG', false);
}

class Mergado extends Module
{
    protected $controllerClass;

    // Languages
    public const LANG_CS = 'cs';
    public const LANG_SK = 'sk';
    public const LANG_EN = 'en';
    public const LANG_PL = 'pl';

    public const LANG_AVAILABLE = array(
        self::LANG_EN,
        self::LANG_CS,
        self::LANG_SK,
        self::LANG_PL,
    );

    // Mergado
    public const MERGADO = [
        'MODULE_NAME' => 'mergado',
        'TABLE_NAME' => 'mergado',
        'TABLE_NEWS_NAME' => 'mergado_news',
        'TABLE_ORDERS_NAME' => 'mergado_orders',
        'VERSION' => '4.1.0',
        'PHP_MIN_VERSION' => 7.1
    ];

    public $googleAdsService;
    public $googleUniversalAnalyticsService;
    public $googleAnalytics4Service;
    public $cookieService;

    public $bianoStarServiceIntegration;
    public $arukeresoServiceIntegration;
    public $compariServiceIntegration;
    public $pazaruvajServiceIntegration;
    public $googleAdsServiceIntegration;
    public $googleUniversalAnalyticsServiceIntegration;
    public $googleAnalytics4ServiceIntegration;
    public $googleTagManagerServiceIntegration;
    public $kelkooServiceIntegration;

    public $gtagIntegrationHelper;

    /**
     * @var Mergado\Helper\ControllerHelper
     */
    public $controllerHelper;

    /**
     * @var Mergado\Service\LogService
     */
    private $logger;

    /**
     * @var Mergado\Service\External\Heureka\HeurekaServiceIntegration
     */
    private $heurekaServiceIntegration;

    /**
     * @var Mergado\Service\Data\OrderCompletionDataService
     */
    private $orderCompletionService;

    /**
     * @var Mergado\Service\Data\ProductDataService
     */
    private $productService;

    /**
     * @var Mergado\Service\Data\CartDataService
     */
    private $cartDataService;

    /**
     * @var Mergado\Service\Data\OrderConfirmationDataService
     */
    private $orderConfirmationDataService;

    /**
     * @var Mergado\Service\External\Google\GaRefundService
     */
    private $gaRefundService;

    /**
     * @var Mergado\Service\External\Pricemania\PricemaniaServiceIntegration
     */
    private $pricemaniaServiceIntegration;

    /**
     * @var Mergado\Service\External\Glami\GlamiServiceIntegration
     */
    private $glamiServiceIntegration;
    /**
     * @var Mergado\Service\External\Biano\Biano\BianoServiceIntegration
     */
    private $bianoServiceIntegration;
    /**
     * @var Mergado\Service\External\Etarget\EtargetServiceIntegration
     */
    private $etargetServiceIntegration;

    /**
     * @var Mergado\Service\External\Facebook\FacebookServiceIntegration
     */
    private $facebookServiceIntegration;

    /**
     * @var Mergado\Service\External\Google\GoogleReviews\GoogleReviewsServiceIntegration
     */
    private $googleReviewsServiceIntegration;

    /**
     * @var Mergado\Service\External\NajNakup\NajNakupServiceIntegration
     */
    private $najNakupServiceIntegration;

    /**
     * @var Mergado\Service\External\Sklik\SklikServiceIntegration
     */
    private $sklikServiceIntegration;

    /**
     * @var Mergado\Service\External\Zbozi\ZboziServiceIntegration
     */
    private $zboziServiceIntegration;

    public function __construct()
    {
        $this->name = self::MERGADO['MODULE_NAME'];
        $this->tab = 'export';
        $this->version = self::MERGADO['VERSION'];
        $this->author = 'www.mergado.cz';
        $this->need_instance = 0;
        $this->module_key = '12cdb75588bb090637655d626c01c351';
        $this->controllerClass = 'AdminMergado';

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.7)
         */
        if (Mergado\Helper\PrestashopVersionHelper::is16()) {
            $this->bootstrap = false;
        } else {
            $this->bootstrap = true;
        }

        parent::__construct();

        $this->displayName = $this->l('Mergado marketing pack');
        $this->description = $this->l('Mergado marketing pack module helps you to export your products information to Mergado services.');

        $this->confirmUninstall = $this->l('Are you sure to uninstall Mergado marketing pack module?');

        $this->ps_versions_compliancy = array('min' => Mergado\Helper\PrestashopVersionHelper::VERSION_16, 'max' => '8.9.99');

        try {
            $this->controllerHelper = Mergado\Helper\ControllerHelper::getInstance();
            $this->logger = Mergado\Service\LogService::getInstance();
            $this->orderCompletionService = Mergado\Service\Data\OrderCompletionDataService::getInstance();

            $this->googleUniversalAnalyticsService = Mergado\Service\External\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService::getInstance();
            $this->googleAdsService = Mergado\Service\External\Google\GoogleAds\GoogleAdsService::getInstance();
            $this->googleAnalytics4Service = Mergado\Service\External\Google\GoogleAnalytics4\GoogleAnalytics4Service::getInstance();

            $this->productService = Mergado\Service\Data\ProductDataService::getInstance();
            $this->heurekaServiceIntegration = Mergado\Service\External\Heureka\HeurekaServiceIntegration::getInstance();

            $this->gaRefundService = Mergado\Service\External\Google\GaRefundService::getInstance();

            $this->orderConfirmationDataService = Mergado\Service\Data\OrderConfirmationDataService::getInstance();
            $this->cartDataService = Mergado\Service\Data\CartDataService::getInstance();
            $this->cookieService = Mergado\Service\CookieService::getInstance();

            $this->bianoStarServiceIntegration = Mergado\Service\External\Biano\BianoStar\BianoStarServiceIntegration::getInstance();
            $this->arukeresoServiceIntegration = Mergado\Service\External\ArukeresoFamily\Arukereso\ArukeresoServiceIntegration::getInstance();
            $this->compariServiceIntegration = Mergado\Service\External\ArukeresoFamily\Compari\CompariServiceIntegration::getInstance();
            $this->pazaruvajServiceIntegration = Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajServiceIntegration::getInstance();
            $this->googleAdsServiceIntegration = Mergado\Service\External\Google\GoogleAds\GoogleAdsServiceIntegration::getInstance();
            $this->googleUniversalAnalyticsServiceIntegration = Mergado\Service\External\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsServiceIntegration::getInstance();
            $this->googleAnalytics4ServiceIntegration = Mergado\Service\External\Google\GoogleAnalytics4\GoogleAnalytics4ServiceIntegration::getInstance();
            $this->googleTagManagerServiceIntegration = Mergado\Service\External\Google\GoogleTagManager\GoogleTagManagerServiceIntegration::getInstance();
            $this->kelkooServiceIntegration = Mergado\Service\External\Kelkoo\KelkooServiceIntegration::getInstance();
            $this->pricemaniaServiceIntegration = Mergado\Service\External\Pricemania\PricemaniaServiceIntegration::getInstance();
            $this->glamiServiceIntegration = Mergado\Service\External\Glami\GlamiServiceIntegration::getInstance();
            $this->bianoServiceIntegration = Mergado\Service\External\Biano\Biano\BianoServiceIntegration::getInstance();
            $this->etargetServiceIntegration = Mergado\Service\External\Etarget\EtargetServiceIntegration::getInstance();
            $this->facebookServiceIntegration = Mergado\Service\External\Facebook\FacebookServiceIntegration::getInstance();
            $this->googleReviewsServiceIntegration = Mergado\Service\External\Google\GoogleReviews\GoogleReviewsServiceIntegration::getInstance();
            $this->najNakupServiceIntegration = Mergado\Service\External\NajNakup\NajNakupServiceIntegration::getInstance();
            $this->sklikServiceIntegration = Mergado\Service\External\Sklik\SklikServiceIntegration::getInstance();
            $this->zboziServiceIntegration = Mergado\Service\External\Zbozi\ZboziServiceIntegration::getInstance();

            $this->gtagIntegrationHelper = Mergado\Service\External\Google\Gtag\GtagIntegrationHelper::getInstance();
        } catch (Throwable $e) {
            $this->logger->error('Error in mergado.php constructor', ['exception' => $e]);
        }
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     * @throws PrestaShopException
     */
    public function install(): bool
    {
        include __DIR__ . '/sql/install.php';

        $this->addTab();

        return parent::install()
            && $this->installUpdates()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('orderConfirmation')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->registerHook('displayAfterBodyOpeningTag')
            && $this->registerHook('actionOrderStatusUpdate')
            && $this->registerHook('actionAdminShopControllerSaveAfter')
            && $this->registerHook('extraCarrier')
            && $this->registerHook('displayBeforeCarrier')
            && Mergado\Helper\ShopModuleEnablerHelper::enableInAllShops($this->id, true);
    }

    /**
     * @throws PrestaShopException
     */
    public function uninstall(): bool
    {
        include __DIR__ . '/sql/uninstall.php';

        $this->removeTab();

        return parent::uninstall();
    }

    public function installUpdates(): bool
    {
        include __DIR__ . "/sql/update-1.2.2.php";
        include __DIR__ . "/sql/update-1.6.5.php";
        include __DIR__ . "/sql/update-2.0.0.php"; // 2.0.1 not added (because of version missmatch fix)
        include __DIR__ . "/sql/update-2.3.0.php";
        include __DIR__ . "/sql/update-3.0.0.php";
        include __DIR__ . "/sql/update-3.1.0.php";
        include __DIR__ . "/sql/update-3.4.1.php";

        return true;
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm(): void
    {
        $id = Tab::getIdFromClassName($this->controllerClass);
        $token = Tools::getAdminToken($this->controllerClass . $id . (int)$this->context->employee->id);
        Tools::redirectAdmin('index.php?controller=' . $this->controllerClass . '&token=' . $token);
        die;
    }

    /**
     * Add item into menu.
     * @throws PrestaShopException
     */
    protected function addTab(): void
    {
        $id_parent = Tab::getIdFromClassName('AdminCatalog');
        if (!$id_parent) {
            throw new RuntimeException(
                $this->l('Failed to add the module into the main BO menu.') . ' : '
                . Db::getInstance()->getMsgError()
            );
        }

        $tabNames = array();
        foreach (Language::getLanguages(false) as $lang) {
            $tabNames[$lang['id_lang']] = $this->displayName;
        }

        $tab = new Tab();
        $tab->class_name = $this->controllerClass;
        $tab->name = $tabNames;
        $tab->module = $this->name;
        $tab->id_parent = $id_parent;

        if (!$tab->save()) {
            throw new RuntimeException($this->l('Failed to add the module into the main BO menu.'));
        }
    }

    /**
     * @throws PrestaShopException
     */
    protected function removeTab(): void
    {
        if (!Tab::getInstanceFromClassName($this->controllerClass)->delete()) {
            throw new RuntimeException($this->l('Failed to remove the module from the main BO menu.'));
        }
    }


    public function hookDisplayAfterBodyOpeningTag(): string
    {
        try {
            return $this->googleTagManagerServiceIntegration->insertDefaultBodyCode();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function hookDisplayProductAdditionalInfo($product): void
    {
        try {
            echo $this->glamiServiceIntegration->addToCart($this);
            echo $this->facebookServiceIntegration->addToCart();

            /**
             * Only for PS 1.7
             */
            // Inserts data to product modal and detail
            echo $this->productService->insertProductData($product, 'mergado-product-data');
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function hookDisplayBackOfficeHeader(): string
    {
        try {
            if (!Module::isEnabled($this->name)) {
                return '';
            }

            if (Tools::getValue('controller') === $this->controllerClass) {
                $this->context->controller->addJquery();
                $this->context->controller->addJS($this->_path . 'views/js/admin/back.js?v=' . MERGADO::MERGADO['VERSION'], false);
                $this->context->controller->addJS($this->_path . 'views/vendors/yesno/src/index.js?v=' . MERGADO::MERGADO['VERSION'], false);
                $this->context->controller->addJS($this->_path . 'views/js/admin/wizard.js?v=' . MERGADO::MERGADO['VERSION'], false);
                $this->context->controller->addJS($this->_path . 'views/js/admin/alerts.js?v=' . MERGADO::MERGADO['VERSION'], false);
                $this->context->controller->addJS($this->_path . 'views/js/admin/import.js?v=' . MERGADO::MERGADO['VERSION'], false);
                $this->context->controller->addJS($this->_path . 'views/vendors/iframe-resizer/js/iframeResizer.min.js?v=' . MERGADO::MERGADO['VERSION'], false);
                $this->context->controller->addJS($this->_path . 'views/js/admin/iframe-resizer.js?v=' . MERGADO::MERGADO['VERSION'], false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-base.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-tabs.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-news.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-news-header.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-wizard.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-settings.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-import.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/vendors/yesno/src/index.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-wizard-dialog.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-wizard-radio.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-alert.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            } else {
                $this->context->controller->addJquery();
            }

            $this->context->controller->addJS($this->_path . 'views/js/admin/notifications.js?v=' . MERGADO::MERGADO['VERSION'], false);

            if (Mergado\Helper\PrestashopVersionHelper::is16AndLower()) {
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-notifications-16.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            } else {
                $this->context->controller->addCSS($this->_path . 'views/css/mmp-notifications-17.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            }

            $lang = Mergado\Helper\LanguageHelper::getLang();
            $this->smarty->assign(array(
                'langCode' => $lang,
            ));

            $logoPath = '"' . __PS_BASE_URI__ . "modules/" . self::MERGADO["MODULE_NAME"] . '/logo.gif"';

            return '<script>
                    var admin_mergado_ajax_url = ' . (string)json_encode($this->context->link->getAdminLink('AdminMergado')) . ';
                    var admin_mergado_show_more_message = "' . $this->l('Show all messages') . '";
                    var admin_mergado_read_more = "' . $this->l('Read more') . '";
                    var admin_mergado_show_messages = "' . $this->l('Mergado messages') . '";
                    var admin_mergado_news = "' . $this->l('NEWS') . '";
                    var admin_mergado_no_new = "' . $this->l('No new messages.') . '";
                    var admin_mergado_all_messages_url = ' . (string)json_encode($this->context->link->getAdminLink('AdminMergado')) . ';
                    var admin_mergado_all_messages_id_tab = "news";
                    
                    var admin_mergado_prices_imported = "' . $this->l('Prices successfully imported.') . '";
                    var admin_mergado_back_running = "' . $this->l('Error generate XML. Selected cron already running.') . '";
                    var admin_mergado_back_merged = "' . $this->l('File merged and ready for review in XML feeds section!') . '";
                    var admin_mergado_back_success = "' . $this->l('File successfully generated.') . '";
                    var admin_mergado_back_error = "' . $this->l('Mergado feed generate ERROR. Try to change number of temporary files and repeat the process.') . '";
                    var admin_mergado_back_process = "' . $this->l('Generating') . '";
                    
                    var psv_new = ' . (int)Mergado\Helper\PrestashopVersionHelper::is17AndHigher() . ';
                    var m_logoPath = ' . $logoPath . ';
                </script>';
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function hookDisplayFooterProduct($params) : string
    {
        try {

            /**
             * PS 1.6
             */
            echo $this->productService->insertProductData($params, 'mergado-product-data');

            $display = "";

            // Biano
            $display .= $this->bianoServiceIntegration->viewProductDetail($params['product'], $this, $this->smarty);

            $display .= $this->arukeresoServiceIntegration->productDetailView($this, $this->smarty);
            $display .= $this->compariServiceIntegration->productDetailView($this, $this->smarty);
            $display .= $this->pazaruvajServiceIntegration->productDetailView($this, $this->smarty);
            $display .= $this->heurekaServiceIntegration->productDetailView($this, $this->smarty);

            return $display;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function hookDisplayProductPriceBlock($params): void
    {
        try {
            if ($params['type'] === 'before_price' && Mergado\Helper\PrestashopVersionHelper::is16AndHigher()) {
                echo $this->productService->insertProductData($params, 'mergado-product-list-item-data');
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function hookActionValidateOrder($params): void
    {
        try {
            $this->heurekaServiceIntegration->submitVerify($this->context, $params);

            $zboziSent = $this->zboziServiceIntegration->backendConversion($params, $this->context);

            // NajNakup
            $najNakupSent = $this->najNakupServiceIntegration->sendNajnakupValuation($params, self::LANG_SK);

            // Pricemania
            $pricemaniaSent = $this->pricemaniaServiceIntegration->send($params, self::LANG_SK);

            $this->logger->info("Validate order:\n" . json_encode(array('conversionSent_Zbozi' => $zboziSent, 'conversionSent_NajNakup' => $najNakupSent, 'conversionSent_Pricemania' => $pricemaniaSent)) . "\n");
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function hookDisplayFooter($params): string
    {
        try {
            global $cookie;

            $display = "";

            $display .= $this->heurekaServiceIntegration->renderWidget($this, $this->smarty);

            //Sklik retargeting - GDPR inside code
            $display .= $this->sklikServiceIntegration->retargeting($this, $this->context, $this->smarty);

            //Etarget
            $display .= $this->etargetServiceIntegration->etargetRetarget($this, $this->smarty);

            $currency = new Currency($cookie->id_currency);

            $display .= Mergado\Utility\SmartyTemplateLoader::render($this,
                'views/templates/front/footer/base.tpl',
                $this->smarty,
                [
                    'currencySign' => $currency->sign,
                ]
            );

            $display .= $this->cartDataService->getCartDataPs16($this, $this->smarty, $params['cart']);

            //BIANO
            $display .= $this->bianoServiceIntegration->viewPage($this, $this->context, $this->_path);

            $display .= $this->arukeresoServiceIntegration->addWidget($this, $this->smarty);
            $display .= $this->compariServiceIntegration->addWidget($this, $this->smarty);
            $display .= $this->pazaruvajServiceIntegration->addWidget($this, $this->smarty);

            // Google reviews
            $display .= $this->googleReviewsServiceIntegration->addBadge($this, $this->smarty);

            // GTM
            $display .= $this->googleTagManagerServiceIntegration->insertDefaultBodyCode();

            return $display;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function hookDisplayHeader($params): string
    {
        try {
            $display = "";

            if ($this->controllerHelper->isCart() || $this->controllerHelper->isCheckout() || $this->controllerHelper->isOnePageCheckout()) {
                $this->cartDataService->insertCartData($params['cart']);
                $this->cartDataService->insertCartAjaxHelpers();
            }

            $this->createJsVariables();

            $categoryId = Tools::getValue('id_category', null);
            $productId = Tools::getValue('id_product', null);

            $display .= $this->cartDataService->getCartDataPs17($this, $this->smarty,$params['cart']);

            if ($this->cookieService->isCookieBlockingEnabled()) {
                $this->context->controller->addJS($this->_path . 'views/js/front/cookies.js'); // This script needs to be first
            }

            if ($productId) {
                $this->smarty->assign([
                    'productId' => $productId
                ]);
            }

            //Glami
            $lang = Mergado\Helper\LanguageHelper::getLang();
            $display .= $this->glamiServiceIntegration->init($this, $this->smarty, $this->context, $this->_path, $lang);
            $display .= $this->glamiServiceIntegration->viewContent($this, $this->smarty, $lang, $categoryId, $productId);

            //KELKOO
            $display .= $this->kelkooServiceIntegration->insertKelkooHeader($this);

            //GTAG
            // GoogleUniversalAnalytics
            $display .= $this->gtagIntegrationHelper->insertHeader($this, $this->smarty, $this->context, $this->_path);

            $this->googleAdsServiceIntegration->insertScripts($this->context, $this->_path);
            $this->googleUniversalAnalyticsServiceIntegration->insertScripts($this->context, $this->_path);

            $this->googleUniversalAnalyticsServiceIntegration->userClickedProduct($this->context, $this->_path);

            //GoogleAnalytics4
            $this->googleAnalytics4ServiceIntegration->insertDefaultHelpers($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->addToCart($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->addProductDetailView($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->viewItemList($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->search($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->viewCart($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->beginCheckout($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->addPaymentInfo($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->addShippingInfo($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->removeFromCart($this->context, $this->_path);
            $this->googleAnalytics4ServiceIntegration->cartEvents($this->context, $this->_path);

            //Google Tag Manager
            $display .= $this->googleTagManagerServiceIntegration->insertDefaultCode($this, $this->smarty);
            $this->googleTagManagerServiceIntegration->insertDefaultHelpers($this->context, $this->_path);
            $this->googleTagManagerServiceIntegration->userClickedProduct($this->context, $this->_path);

            $display .= $this->googleTagManagerServiceIntegration->orderConfirmation($this, $this->smarty, $this->context);
            $display .= $this->bianoServiceIntegration->header($this, $this->context, $this->smarty, $this->_path);
            $display .= $this->facebookServiceIntegration->search($this, $this->smarty, $this->context, $this->_path);
            $this->heurekaServiceIntegration->addVerifiedCheckboxForPs17($this->context, $this->_path);
            $this->zboziServiceIntegration->addCheckboxVerifyForPs17($this->context, $this->_path);
            $this->arukeresoServiceIntegration->addCheckboxForPs17($this->context, $this->_path);
            $this->compariServiceIntegration->addCheckboxForPs17($this->context, $this->_path);
            $this->pazaruvajServiceIntegration->addCheckboxForPs17($this->context, $this->_path);
            $this->bianoStarServiceIntegration->addCheckboxForPS17($this->context, $this->_path);

            return $display;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function createJsVariables(): void
    {
        try {
            Media::addJsDef(['mmp' => ['cookies' => $this->cookieService->createJsVariables()]]);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function hookDisplayBeforeCarrier(): void
    {
        try {
            $this->cartDataService->insertShippingInfo();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function hookExtraCarrier(): string
    {
        try {
            $display = '';

            $display .= $this->heurekaServiceIntegration->addVerifiedCheckboxForPs16($this, $this->smarty, $this->context, $this->_path);
            $display .= $this->zboziServiceIntegration->addCheckboxVerifyForPs16($this, $this->smarty, $this->context, $this->_path);
            $display .= $this->arukeresoServiceIntegration->addCheckboxForPs16($this, $this->smarty, $this->context, $this->_path);
            $display .= $this->compariServiceIntegration->addCheckboxForPs16($this, $this->smarty, $this->context, $this->_path);
            $display .= $this->pazaruvajServiceIntegration->addCheckboxForPs16($this, $this->smarty, $this->context, $this->_path);
            $display .= $this->bianoStarServiceIntegration->addCheckboxForPS16($this, $this->smarty, $this->context, $this->_path);

            return $display;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    /**
     * Refund order in analytics if status changed to selected one
     *
     * Status update is triggered before cancelProduct
     * (ex. if only one product exist in order and is canceled, then order status is changed to "canceled" ..
     * but it won't send id_order_state so we won't get in)
     */
    public function hookActionOrderStatusUpdate($params): void
    {
        try {
            $order = new Order(Tools::getValue('id_order'));
            $orderId = $order->id;
            $orderStateId = Tools::getValue('id_order_state');

            if (!$orderStateId) {
                $orderStateId = $params['newOrderStatus']->id;
            }

            if (!$orderId) {
                $orderId = $params['id_order'];
            }

            if ($this->googleUniversalAnalyticsService->isActiveEcommerce()) {
                if ($this->gaRefundService->isStatusActive($orderStateId)) {

                    // Check if order has full refund status already .. and don't send it again
                    $orderHistory = $order->getHistory($this->context->language->id);
                    $hasRefundedStatus = false;

                    foreach ($orderHistory as $history) {
                        if ($this->gaRefundService->isStatusActive($history)) {
                            $hasRefundedStatus = true;
                        }
                    }

                    if (!$hasRefundedStatus) {
                        $this->gaRefundService->sendRefundCode(array(), $orderId, false);
                    }
                }
            }

            $this->googleAnalytics4ServiceIntegration->sendRefundOrderFull($this->context, $orderId, $orderStateId);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function hookDisplayOrderConfirmation($params): string
    {
        try {
            $display = "";

            $context = Context::getContext();

            $orderId = Mergado\Helper\OrderHelper::getOrderId($params);
            $orderCartId = Mergado\Helper\OrderHelper::getOrderCartId($params);

            $order = new Order($orderId);
            $orderCurrency = Currency::getCurrency($order->id_currency);
            $orderProducts = $order->getProducts();
            $orderCustomer = $order->getCustomer(); // new Customer($order->id_customer);

            $this->orderConfirmationDataService->insertOrderData($orderId, $orderCurrency, $order, $orderProducts, $orderCustomer);

            $lang = Mergado\Helper\LanguageHelper::getLang();

            $display .= $this->glamiServiceIntegration->purchase($this, $this->smarty, $orderProducts, $orderId, $params, $lang);
            $display .= $this->glamiServiceIntegration->topPurchase($this, $this->smarty, $orderCustomer->email, $orderProducts, $orderId, $params);

            // Heureka conversions
            $cart = new Cart($orderCartId);

            $display .= $this->heurekaServiceIntegration->conversionLegacy($orderCartId, $this, $this->smarty);

            $display .= $this->arukeresoServiceIntegration->conversion($orderCartId, $this, $this->smarty, $order);
            $display .= $this->pazaruvajServiceIntegration->conversion($orderCartId, $this, $this->smarty, $order);
            $display .= $this->compariServiceIntegration->conversion($orderCartId, $this, $this->smarty, $order);
            $display .= $this->heurekaServiceIntegration->conversion($orderCartId, $this, $this->smarty, $order);

            // Facebook
            $display .= $this->facebookServiceIntegration->purchase($this, $this->smarty, $params, $cart->getProducts());

            //Sklik
            $display .= $this->sklikServiceIntegration->conversion($this, $this->smarty, $context, $order);

            //Kelkoo
            $display .= $this->kelkooServiceIntegration->orderConfirmation($this, $this->smarty, $orderId, $order, $orderProducts);

            //GoogleAds, Google universal analytics - Remove when universal analytics die and move to Google ADS
            if (!$this->orderCompletionService->isOrderCompleted($orderId, Mergado\Helper\ShopHelper::getId())) {

                // GAds
                $display .= $this->googleAdsServiceIntegration->conversion($orderId, $orderCurrency['iso_code'], $this, $this->smarty);
                $display .= $this->googleAdsServiceIntegration->purchase($orderId, $order, $orderProducts, $this,$this->context, $this->smarty);

                // GUA
                $display .= $this->googleUniversalAnalyticsServiceIntegration->purchase($orderId, $order, $orderProducts, $this, $this->context, $this->smarty);

                // GA4
                $this->googleAnalytics4ServiceIntegration->purchase($this->context, $this->_path);

                $this->orderCompletionService->setOrderCompleted($orderId, Mergado\Helper\ShopHelper::getId());
            }

            //Biano
            $display .= $this->bianoServiceIntegration->purchase($orderId, $order, $orderCustomer, $orderProducts, $this->context, $this->smarty, $this);

            //Arukereso, Compari, Pazaruvaj
            $this->arukeresoServiceIntegration->orderConfirmation($orderProducts, $orderCustomer, $this->context->cookie);
            $this->compariServiceIntegration->orderConfirmation($orderProducts, $orderCustomer, $this->context->cookie);
            $this->pazaruvajServiceIntegration->orderConfirmation($orderProducts, $orderCustomer, $this->context->cookie);

            // Google reviews
            $display .= $this->googleReviewsServiceIntegration->addOptIn($this, $this->smarty, $this->context, $params, $orderProducts);

            // Zbozi
            $display .= $this->zboziServiceIntegration->frontendConversion($orderId, $this, $this->smarty, $context->language->language_code);

            return $display;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function hookDisplayShoppingCart($params): string
    {
        try {
            //For checkout in ps 1.6
            if (Mergado\Helper\PrestashopVersionHelper::is16AndLower()) {
                $cart = $params['cart'];
                $cartProducts = $cart->getProducts(true);

                $productData = $this->cartDataService->getOldCartProductData($cartProducts);

                $discounts = [];

                foreach ($cart->getDiscounts() as $item) {
                    $discounts[] = $item['name'];
                }

                return Mergado\Utility\SmartyTemplateLoader::render($this,
                    'views/templates/front/shoppingCart/cart_data.tpl',
                    $this->smarty,
                    [
                        'data' => htmlspecialchars(json_encode($productData['default'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                        'dataWithVat' => htmlspecialchars(json_encode($productData['withVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                        'dataWithoutVat' => htmlspecialchars(json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                        'cart_id' => $cart->id,
                        'coupons' => join(', ', $discounts),
                        'orderUrl' =>  '', // Not needed for ps 1.6
                    ]
                );
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function hookActionAdminShopControllerSaveAfter(): void
    {
        try {
            if (Tools::getValue('submitAddshop') !== false && Tools::getValue('submitAddshop') === '1') {
                $shopId = Tools::getValue('id_shop');
                Mergado\Manager\DatabaseManager::saveSetting(\Mergado\Service\SettingsService::EXPORT['BOTH'], 'on', $shopId);
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @param $addons
     * @return mixed
     * @version 3.0.0
     * @date 19.11.2021
     *
     * !!! NEVER DELETE THIS FUNCTION !!!
     *
     * uninstallOverrides and installOverride do not work for TOOLS.php override,
     * This function is still used and called in every UPDATE in older installations of Mergado Pack!!!
     * Deleting it will break the plugin.
     *
     * Explanation: Older version had CUSTOM updates that stopped to work one day...
     * After deletion, this remained as a fix ...
     *
     */
    public function updateVersionXml($addons)
    {
        return $addons;
    }
}
