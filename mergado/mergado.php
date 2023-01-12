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

define( '__MERGADO_DIR__', _PS_MODULE_DIR_ . 'mergado' );
define( '__MERGADO_FORMS_DIR__', _PS_MODULE_DIR_ . 'mergado/controllers/admin/forms/');
define( '__MERGADO_ALERT_DIR__', _PS_MODULE_DIR_ . 'mergado/views/templates/admin/mergado/pages/partials/components/alerts/');

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined( 'MERGADO_DEBUG' )) {
    define('MERGADO_DEBUG', false);
}


class Mergado extends Module
{
    protected $controllerClass;
    public $shopId;

    // Languages
    const LANG_CS = 'cs';
    const LANG_SK = 'sk';
    const LANG_EN = 'en';
    const LANG_PL = 'pl';

    const LANG_AVAILABLE = array(
        self::LANG_EN,
        self::LANG_CS,
        self::LANG_SK,
        self::LANG_PL,
    );

    // Prestashop versions
    const PS_V_16 = 1.6;
    const PS_V_17 = 1.7;

    // Mergado
    const MERGADO = [
        'MODULE_NAME' => 'mergado',
        'TABLE_NAME' => 'mergado',
        'TABLE_NEWS_NAME' => 'mergado_news',
        'TABLE_ORDERS_NAME' => 'mergado_orders',
        'VERSION' => '3.4.1',
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
        if (_PS_VERSION_ >= self::PS_V_16 && _PS_VERSION_ < self::PS_V_17) {
            $this->bootstrap = false;
        } else {
            $this->bootstrap = true;
        }

        $this->shopID = self::getShopId();

        parent::__construct();

        $this->displayName = $this->l('Mergado marketing pack');
        $this->description = $this->l('Mergado marketing pack module helps you to export your products information to Mergado services.');

        $this->confirmUninstall = $this->l('Are you sure to uninstall Mergado marketing pack module?');

        $this->ps_versions_compliancy = array('min' => self::PS_V_16, 'max' => '8.9.99');

        try {
            $this->googleUniversalAnalyticsService = Mergado\includes\services\Google\GoogleUniversalAnalytics\googleUniversalAnalyticsService::getInstance();
            $this->googleAdsService = Mergado\includes\services\Google\GoogleAds\GoogleAdsService::getInstance();
            $this->googleAnalytics4Service = Mergado\includes\services\Google\GoogleAnalytics4\GoogleAnalytics4Service::getInstance();

            $this->cookieService = \Mergado\includes\tools\CookieService::getInstance();

            $this->bianoStarServiceIntegration = new \Mergado\includes\services\Biano\BianoStar\BianoStarServiceIntegration();
            $this->arukeresoServiceIntegration = new \Mergado\includes\services\ArukeresoFamily\Arukereso\ArukeresoServiceIntegration();
            $this->compariServiceIntegration = new \Mergado\includes\services\ArukeresoFamily\Compari\CompariServiceIntegration();
            $this->pazaruvajServiceIntegration = new \Mergado\includes\services\ArukeresoFamily\Pazaruvaj\PazaruvajServiceIntegration();
            $this->googleAdsServiceIntegration = \Mergado\includes\services\Google\GoogleAds\GoogleAdsServiceIntegration::getInstance();
            $this->googleUniversalAnalyticsServiceIntegration = \Mergado\includes\services\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsServiceIntegration::getInstance();
            $this->googleAnalytics4ServiceIntegration = \Mergado\includes\services\Google\GoogleAnalytics4\GoogleAnalytics4ServiceIntegration::getInstance();
            $this->googleTagManagerServiceIntegration = \Mergado\includes\services\Google\GoogleTagManager\GoogleTagManagerServiceIntegration::getInstance();
            $this->kelkooServiceIntegration = \Mergado\includes\services\Kelkoo\KelkooServiceIntegration::getInstance();

            $this->gtagIntegrationHelper = \Mergado\includes\services\Google\Gtag\GtagIntegrationHelper::getInstance();
        } catch (Exception $e) {
            Mergado\Tools\LogClass::log('Mergado log: Error in mergado.php constructor ' . $e->getMessage());
        }
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     */
    public function install()
    {
        include dirname(__FILE__) . '/sql/install.php';

        $this->addTab();

        return parent::install()
            && $this->installUpdates()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('orderConfirmation')
            && $this->registerHook('displayFooter')
//            && $this->registerHook('displayProductFooter') // Probably not used
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayShoppingCart')
//            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->registerHook('displayAfterBodyOpeningTag') // only for PS 1.7
//            && $this->registerHook('displayBeforeBodyClosingTag') // only for PS 1.7
            && $this->registerHook('actionOrderStatusUpdate') // For google refund
            && $this->registerHook('actionAdminShopControllerSaveAfter')
//            && $this->registerHook('actionProductCancel') // For google refund
            && $this->registerHook('extraCarrier')
            && $this->registerHook('displayBeforeCarrier')
            && $this->mergadoEnableAll(true);
    }

    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';

        $this->removeTab();

        return parent::uninstall();
    }

    public function installUpdates()
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
     * @version 3.0.0
     * @date 19.11.2021
     *
     * !!! NEVER DELETE THIS FUNCTION !!!
     *
     * uninstallOverrides and installOverride do not work for TOOLS.php override,
     * This function is still used and called in every UPDATE in older instalations of Mergado Pack!!!
     * Deleting it will breaks the plugin.
     *
     * Explanation: Older version had CUSTOM updates that stopped to work one day..
     * After deletion, this remained as a fix ...
     *
     * @param $addons
     * @return mixed
     */
    public function updateVersionXml($addons) {
        return $addons;
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
    protected function renderForm()
    {
        $id = TabCore::getIdFromClassName($this->controllerClass);
        $token = Tools::getAdminToken($this->controllerClass . $id . (int)$this->context->employee->id);
        Tools::redirectAdmin('index.php?controller=' . $this->controllerClass . '&token=' . $token);
        die;
    }

    /**
     * Add item into menu.
     */
    protected function addTab()
    {
        $id_parent = TabCore::getIdFromClassName('AdminCatalog');
        if (!$id_parent) {
            throw new RuntimeException(
                sprintf($this->l('Failed to add the module into the main BO menu.')) . ' : '
                . Db::getInstance()->getMsgError()
            );
        }

        $tabNames = array();
        foreach (LanguageCore::getLanguages(false) as $lang) {
            $tabNames[$lang['id_lang']] = $this->displayName;
        }

        $tab = new TabCore();
        $tab->class_name = $this->controllerClass;
        $tab->name = $tabNames;
        $tab->module = $this->name;
        $tab->id_parent = $id_parent;

        if (!$tab->save()) {
            throw new RuntimeException($this->l('Failed to add the module into the main BO menu.'));
        }
    }

    protected function removeTab()
    {
        if (!TabCore::getInstanceFromClassName($this->controllerClass)->delete()) {
            throw new RuntimeException($this->l('Failed to remove the module from the main BO menu.'));
        }
    }


    public function hookDisplayAfterBodyOpeningTag() {
        $display = "";

        $display .= $this->googleTagManagerServiceIntegration->insertDefaultBodyCode();

        return $display;
    }

    public function hookDisplayProductAdditionalInfo($product) {
        $lang = Mergado\Tools\LanguagesClass::getLangIso();
        $this->shopId = self::getShopId();

        $glami = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['ACTIVE'], self::getShopId());
        $glamiLangActive = isset(Mergado\Tools\SettingsClass::GLAMI_LANGUAGES[$lang]) ? Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI_LANGUAGES[$lang], self::getShopId()) : false;

        if($glami === Mergado\Tools\SettingsClass::ENABLED && $glamiLangActive === Mergado\Tools\SettingsClass::ENABLED) {
            ?>
            <script>
                if(typeof $ !== 'undefined') {
                    $('.add-to-cart').on('click', function () {
                        var $_currency = $('.product-price').find('[itemprop="priceCurrency"]').attr('content');
                        var $_id = $(this).closest('form').find('#product_page_product_id').val();
                        var $_name = $('h1[itemprop="name"]').text();
                        var $_price = $('.product-price').find('[itemprop="price"]').attr('content');

                        if ($_name === '') {
                            $_name = $('.modal-body h1').text();
                        }

                        if ($(this).closest('form').find('#idCombination').length > 0) {
                            $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
                        }

                        glami('track', 'AddToCart', {
                            item_ids: [$_id],
                            product_names: [$_name],
                            value: $_price,
                            currency: $_currency,
                            consent: window.mmp.cookies.sections.advertisement.onloadStatus,
                        });
                    });
                }
            </script>
            <?php
        }

        $facebookClass = new \Mergado\Facebook\FacebookClass();

        // There is an ajax call when user changes variant .. jQuery parser cant handle that
        if($facebookClass->isActive($this->shopID) && !(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] === '1')) {
            ?>
            <script>
                // In product detail and modal in PS1.7
                if(typeof $ !== 'undefined') {
                    $('.add-to-cart').on('click', function () {
                        var $_defaultInitialId = <?= Tools::getValue('id_product_attribute') ?>;

                    var $_currency = $('.product-price').find('[itemprop="priceCurrency"]').attr('content');
                    var $_id = $(this).closest('form').find('#product_page_product_id').val();
                    var $_name = $('h1[itemprop="name"]').text();
                    var $_price = $('.product-price').find('[itemprop="price"]').attr('content');
                    var $_quantity = $(this).closest('form').find('#quantity_wanted').val();

                    if($_name === '') {
                        $_name = $('.modal-body h1').text();
                    }

                    if($(this).closest('form').find('#idCombination').length > 0) {
                        $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
                    } else if ($_defaultInitialId && $_defaultInitialId !== '') {
                        $_id = $_id + '-' + $_defaultInitialId;
                    }

                    fbq('track', 'AddToCart', {
                        content_name: $_name,
                        content_ids: [$_id],
                        contents: [{'id': $_id, 'quantity': $_quantity}],
                        content_type: 'product',
                        value: $_price,
                        currency: $_currency,
                        consent: window.mmp.cookies.sections.advertisement.onloadStatus,
                    });
                });
            }
        </script>
        <?php
        }

        /**
         * Only for PS 1.7
         */
        // Inserts data to product modal and detail
        echo \Mergado\includes\helpers\ProductHelper::insertProductData($product, 'mergado-product-data');
    }


    /**
     * HOOK - BACKOFFICE HEADER
     */

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->shopId = self::getShopId();

        if (Tools::getValue('controller') == $this->controllerClass) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/back.js?v=' . MERGADO::MERGADO['VERSION'], false);
            $this->context->controller->addJS($this->_path . 'views/vendors/yesno/src/index.js?v=' . MERGADO::MERGADO['VERSION'], false);
            $this->context->controller->addJS($this->_path . 'views/js/wizard.js?v=' . MERGADO::MERGADO['VERSION'], false);
            $this->context->controller->addJS($this->_path . 'views/js/alerts.js?v=' . MERGADO::MERGADO['VERSION'], false);
            $this->context->controller->addJS($this->_path . 'views/js/import.js?v=' . MERGADO::MERGADO['VERSION'], false);
            $this->context->controller->addJS($this->_path . 'views/vendors/iframe-resizer/js/iframeResizer.min.js?v=' . MERGADO::MERGADO['VERSION'], false);
            $this->context->controller->addJS($this->_path . 'views/js/iframe-resizer.js?v=' . MERGADO::MERGADO['VERSION'], false);
            $this->context->controller->addCSS($this->_path . 'views/css/back.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/mmp-tabs.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/backNews.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/backNewsHeader.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/backWizard.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/backSettings.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/backImport.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/vendors/yesno/src/index.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/backWizardDialog.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/backWizardRadio.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
            $this->context->controller->addCSS($this->_path . 'views/css/backAlert.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
        } else {
            $this->context->controller->addJquery();
        }

        $this->context->controller->addJS($this->_path . 'views/js/notifications.js?v=' . MERGADO::MERGADO['VERSION'], false);

        if(_PS_VERSION_ < Mergado::PS_V_17) {
            $this->context->controller->addCSS($this->_path . 'views/css/notifications16.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/notifications17.css?v=' . MERGADO::MERGADO['VERSION'], 'all', null, false);
        }

        $lang = Mergado\Tools\LanguagesClass::getLangIso();
        $this->smarty->assign(array(
            'langCode' => $lang,
        ));


        if (!ModuleCore::isEnabled($this->name)) {
            return false;
        }

        if(_PS_VERSION_ == self::PS_V_17) {
            $psv_new = 1;
        } else {
            $psv_new = 0;
        }

        $logoPath = '"' . __PS_BASE_URI__ . "modules/" . self::MERGADO["MODULE_NAME"] . '/logo.gif"';

        return '<script>
                var admin_mergado_ajax_url = ' . (string) json_encode($this->context->link->getAdminLink('AdminMergado')) . ';
                var admin_mergado_show_more_message = "' . $this->l('Show all messages') . '";
                var admin_mergado_read_more = "' . $this->l('Read more') . '";
                var admin_mergado_show_messages = "' . $this->l('Mergado messages') . '";
                var admin_mergado_news = "' . $this->l('NEWS') . '";
                var admin_mergado_no_new = "' . $this->l('No new messages.') . '";
                var admin_mergado_all_messages_url = ' . (string) json_encode($this->context->link->getAdminLink('AdminMergado')) . ';
                var admin_mergado_all_messages_id_tab = "news";
                
                var admin_mergado_prices_imported = "' . $this->l('Prices successfully imported.') . '";
                var admin_mergado_back_running = "' . $this->l('Error generate XML. Selected cron already running.') . '";
                var admin_mergado_back_merged = "' . $this->l('File merged and ready for review in XML feeds section!') . '";
                var admin_mergado_back_success = "' . $this->l('File successfully generated.') . '";
                var admin_mergado_back_error = "' . $this->l('Mergado feed generate ERROR. Try to change number of temporary files and repeat the process.') . '";
                var admin_mergado_back_process = "' . $this->l('Generating') . '";
                
                var psv_new = ' . $psv_new . ';
                var m_logoPath = ' . $logoPath . ';
            </script>';
    }


    /**
     * HOOK - DISPLAY FOOTER PRODUCT
     */

    /**
     * @param array $params
     * @return mixed
     */
    public function hookDisplayFooterProduct($params)
    {
        /**
         * PS 1.6
         */
        echo \Mergado\includes\helpers\ProductHelper::insertProductData($params, 'mergado-product-data');

        $display = "";
        $this->shopId = self::getShopId();

        // Biano
        $bianoClass = new \Mergado\includes\services\Biano\Biano\BianoClass();

            if ($bianoClass->isActive($this->shopId)) {
                $langCode = Mergado\Tools\LanguagesClass::getLangIso(strtoupper($this->context->language->iso_code));

            if ($bianoClass->isLanguageActive($langCode, $this->shopId)) {
                $this->smarty->assign(array(
                    'productId' => \Mergado\Tools\HelperClass::getProductId($params['product']),
                ));

                $display .= $this->display(__FILE__, 'views/templates/front/productDetail/biano/bianoViewProductDetail.tpl');
            }
        }

        return $display;
    }

    /**
     * Product list data
     */
    public function hookDisplayProductPriceBlock($params)
    {
        /**
         * Only for PS 1.7
         */
        if ($params['type'] === 'before_price' && _PS_VERSION_ > Mergado::PS_V_16) {
            echo \Mergado\includes\helpers\ProductHelper::insertProductData($params, 'mergado-product-list-item-data');
        }

//        return $params;
    }

    /**
     * HOOK - ACTION VALIDATE ORDER
     */

    /**
     * Verified by users.
     * @param $params
     */
    public function hookActionValidateOrder($params)
    {
        $this->shopId = Mergado::getShopId();

        $verifiedCz = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CZ'], $this->shopID);
        $verifiedSk = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_SK'], $this->shopID);

        //If user don't want the heureka document ... dont send data
        if ($this->context->cookie->__get(\Mergado\Heureka\HeurekaClass::CONSENT_NAME) != '1') {

            /* Heureka verified by users */
            if ($verifiedCz && $verifiedCz === Mergado\Tools\SettingsClass::ENABLED) {
                $verifiedCzCode = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CODE_CZ'], $this->shopID);

                if ($verifiedCzCode && $verifiedCzCode !== '') {
                    $sendWithItems = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_WITH_ITEMS_CZ'], $this->shopID);

                    // Default true
                    if ($sendWithItems === false) {
                        $sendWithItems = true;
                    }

                    Mergado\Heureka\HeurekaClass::heurekaVerify($verifiedCzCode, $params, self::LANG_CS, $sendWithItems);
                }
            }

            if ($verifiedSk && $verifiedSk === Mergado\Tools\SettingsClass::ENABLED) {
                $verifiedCzCode = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CODE_SK'], $this->shopID);

                if ($verifiedCzCode && $verifiedCzCode !== '') {
                    $sendWithItems = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_WITH_ITEMS_SK'], $this->shopID);

                    // Default true
                    if ($sendWithItems === false) {
                        $sendWithItems = true;
                    }

                    Mergado\Heureka\HeurekaClass::heurekaVerify($verifiedCzCode, $params, self::LANG_SK, $sendWithItems);
                }
            }
        }

        // Unset cookie becuase of next buy
        $this->context->cookie->__set(\Mergado\Heureka\HeurekaClass::CONSENT_NAME, '0');

        $zboziSent = false;

        if ($this->context->cookie->__get(\Mergado\Zbozi\ZboziClass::CONSENT_NAME) !== '1') {
            $zboziSent = Mergado\Zbozi\Zbozi::sendZbozi($params, $this->shopId);
        }

        // Unset cookie because of next buy
        $this->context->cookie->__set(\Mergado\Zbozi\ZboziClass::CONSENT_NAME, '0');

        // NajNakup
        $najNakupClass = new \Mergado\NajNakup\NajNakupClass();
        $najNakupSent = $najNakupClass->sendNajnakupValuation($params, self::LANG_SK, $this->shopId);


        try {
            $pricemaniaSent = Mergado\Pricemania\PricemaniaClass::sendPricemaniaOverenyObchod($params, self::LANG_SK, $this->shopId);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

        Mergado\Tools\LogClass::log("Validate order:\n" . json_encode(array('verifiedCz' => $verifiedCz, 'verifiedSk' => $verifiedSk, 'conversionSent_Zbozi' => $zboziSent, 'conversionSent_NajNakup' => $najNakupSent, 'conversionSent_Pricemania' => $pricemaniaSent)) . "\n");
    }


    /**
     * HOOK - DISPLAY FOOTER
     */

    /**
     * @return string
     */
    public function hookDisplayFooter($params)
    {
        global $cookie;

        $this->shopId = self::getShopId();

        $iso_code = LanguageCore::getIsoById((int)$cookie->id_lang);
        $langIso = Mergado\Tools\LanguagesClass::getLangIso($iso_code); // for heureka CZ/SK etc..

        $display = "";

        //Heureka Widget
        $widgetActive = isset(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_' . $langIso]) ? Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_' . $langIso], $this->shopID) : false;

        if ($widgetActive === Mergado\Tools\SettingsClass::ENABLED) {
            $widgetId = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_ID_' . $langIso], $this->shopID);

            if ($widgetId !== '') {
                $position = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_POSITION_' . $langIso], $this->shopID);
//                $minWidth = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_SCREEN_WIDTH_' . $langIso], $this->shopID);
//                $showMobile = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_MOBILE_' . $langIso], $this->shopID);
                $marginTop = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_TOP_MARGIN_' . $langIso], $this->shopID);

                $this->smarty->assign(array(
                    'widgetId' => $widgetId,
                    'marginTop' => $marginTop,
//                    'minWidth' => $minWidth,
//                    'showMobile' => $showMobile,
                    'position' => $position,
                    'langIso' => strtolower($langIso),
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/heureka_widget.tpl');
            }
        }

        //Sklik retargeting - GDPR inside code
        $sklikClass = new \Mergado\Sklik\SklikClass();
        if ($sklikClass->isRetargetingActive($this->shopID)) {
            $this->smarty->assign(array(
                'seznam_retargeting_id' => $sklikClass->getRetargetingId($this->shopID),
                'seznam_consent_advertisement' => (int) $this->cookieService->advertismentEnabled()
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/sklik.tpl');
        }


        //Etarget
        if($this->cookieService->advertismentEnabled()) {
            $etargetClass = new \Mergado\Etarget\EtargetClass();
            if ($etargetClass->isActive($this->shopID)) {
                $this->smarty->assign(array(
                    'etargetData' => $etargetClass->getData($this->shopID),
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/etarget.tpl');
            }
        }

        $currency = new CurrencyCore($cookie->id_currency);
        $this->smarty->assign(array(
            'currencySign' => $currency->sign,
        ));

        $display .= $this->display(__FILE__, '/views/templates/front/footer/base.tpl');

        $display .= $this->cartDataPs16($params);

        //BIANO
        $bianoClass = new \Mergado\includes\services\Biano\Biano\BianoClass();
        if ($bianoClass->isActive($this->shopId)) {
            $display .= $this->display(__FILE__, 'views/templates/front/header/biano/bianoView.tpl');
            $this->context->controller->addJS($this->_path . 'views/js/biano.js');
        }

        $display .= $this->arukeresoServiceIntegration->addWidget($this, $this->smarty, $this->_path);
        $display .= $this->compariServiceIntegration->addWidget($this, $this->smarty, $this->_path);
        $display .= $this->pazaruvajServiceIntegration->addWidget($this, $this->smarty, $this->_path);

        // Google reviews
        $GoogleReviewsClass = Mergado\Google\GoogleReviewsClass::getInstance();
        if ($GoogleReviewsClass->isBadgeActive()) {
            $this->smarty->assign(
                array(
                    'googleBadge' => $GoogleReviewsClass->getBadgeSmartyVariables(),
                )
            );

            $display .= $this->display(__FILE__, $GoogleReviewsClass->getBadgeTemplatePath());
        }

        // GTM
        $display .= $this->googleTagManagerServiceIntegration->insertDefaultBodyCode();

        return $display;
    }

    public function cartDataPs17($params) {
        //Data for checkout in ps 1.7 ..

        if(_PS_VERSION_ > self::PS_V_16) {
            $cart = $params['cart'];
            $cartProducts = $cart->getProducts(true);

            $productData = \Mergado\includes\helpers\CartHelper::getOldCartProductData($cartProducts);

            if (_PS_VERSION_ < self::PS_V_17) {
                $this->smarty->assign(array(
                    'data' => htmlspecialchars(json_encode($productData['default'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithVat' => htmlspecialchars(json_encode($productData['withVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithoutVat' => htmlspecialchars(json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'cart_id' => $cart->id,
                ));
            } else {
                $this->smarty->assign(array(
                    'data' => json_encode($productData['default'], JSON_NUMERIC_CHECK),
                    'dataWithVat' => json_encode($productData['withVat'], JSON_NUMERIC_CHECK),
                    'dataWithoutVat' => json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK),
                    'cart_id' => $cart->id,
                ));
            }

            $discounts = [];

            foreach ($cart->getDiscounts() as $item) {
                $discounts[] = $item['name'];
            }

            global $smarty;
            $url = $smarty->tpl_vars['urls']->value['pages']['order'];

            $this->smarty->assign(array(
                'orderUrl' => $url,
                'coupons' => join(', ', $discounts),
            ));

            return $this->display(__FILE__, '/views/templates/front/shoppingCart/cart_data.tpl');
        }

        return false;
    }

    public function cartDataPs16($params) {
        //For checkout in ps 1.6
        if(_PS_VERSION_ < self::PS_V_17) {
            $langId = (int)ContextCore::getContext()->language->id;

            $cart = $params['cart'];
            $cartProducts = $cart->getProducts(true);

            $productData = \Mergado\includes\helpers\CartHelper::getOldCartProductData($cartProducts);

            if (_PS_VERSION_ < self::PS_V_17) {
                $this->smarty->assign(array(
                    'data' => htmlspecialchars(json_encode($productData['default'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithVat' => htmlspecialchars(json_encode($productData['withVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithoutVat' => htmlspecialchars(json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'cart_id' => $cart->id,
                ));
            } else {
                $this->smarty->assign(array(
                    'data' => json_encode($productData['default'], JSON_NUMERIC_CHECK),
                    'dataWithVat' => json_encode($productData['withVat'], JSON_NUMERIC_CHECK),
                    'dataWithoutVat' => json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK),
                    'cart_id' => $cart->id,
                ));
            }

            $discounts = [];

            foreach ($cart->getDiscounts() as $item) {
                $discounts[] = $item['name'];
            }

            $this->smarty->assign(array(
                'coupons' => join(', ', $discounts),
            ));

            return $this->display(__FILE__, '/views/templates/front/shoppingCart/cart_data.tpl');
        }
    }

    /**
     * HOOK - DISPLAY HEADER
     */

    /**
     * @return string
     */
    public function hookDisplayHeader($params)
    {
        $display = "";

        // Add product list data to page
//        if (\Mergado\includes\helpers\ControllerHelper::isCategory()) {
//            \Mergado\includes\helpers\ProductHelper::insertProductListData();
//        }

        if (\Mergado\includes\helpers\ControllerHelper::isCart() || \Mergado\includes\helpers\ControllerHelper::isCheckout() || \Mergado\includes\helpers\ControllerHelper::isOnePageCheckout()) {
            \Mergado\includes\helpers\CartHelper::insertCartData($params['cart']);
            \Mergado\includes\helpers\CartHelper::insertCartAjaxHelpers();
        }

        $lang = Mergado\Tools\LanguagesClass::getLangIso();

        $this->shopId = self::getShopId();


        $glami = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['ACTIVE'], self::getShopId());
        $glamiLangActive = isset(Mergado\Tools\SettingsClass::GLAMI_LANGUAGES[$lang]) ? Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI_LANGUAGES[$lang], self::getShopId()) : false;
        $categoryId = Tools::getValue('id_category');
        $productId = Tools::getValue('id_product');

        $display .= $this->createJsVariables();

        $display .= $this->cartDataPs17($params);


        $jsDef = [];

        if ($this->cookieService->isCookieBlockingEnabled()) {
            $this->context->controller->addJS($this->_path . 'views/js/cookies.js');
        }

        //TODO: REFACTOR TO product
        if ($categoryId) {
            $category = new CategoryCore($categoryId, (int)ContextCore::getContext()->language->id);
            $nb = 10;
            $products_tmp = $category->getProducts((int)ContextCore::getContext()->language->id, 1, ($nb ? $nb : 10));
            $products = array();

            foreach ($products_tmp as $product) {
                if(isset($product['id_product_attribute']) && $product['id_product_attribute'] !== '' && $product['id_product_attribute'] != 0) {
                    $products['ids'][] = $product['id_product'] . '-' . $product['id_product_attribute'];
                } else {
                    $products['ids'][] = $product['id_product'];
                }

                $products['name'][] = $product['name'];
            }

            $this->smarty->assign(array(
                'glami_pixel_category' => $category,
                'glami_pixel_productIds' => json_encode($products['ids']),
                'glami_pixel_productNames' => json_encode($products['name'])
            ));
        }

        if ($productId) {
            $product = new ProductCore($productId, false, (int)ContextCore::getContext()->language->id);

            $this->smarty->assign(array(
                'glami_pixel_product' => $product,
                'productId' => $productId
            ));
        }

        //GLAMI
        if ($glami === Mergado\Tools\SettingsClass::ENABLED && $glamiLangActive === Mergado\Tools\SettingsClass::ENABLED) {
            $glamiPixel = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['CODE'] . '-' . $lang, $this->shopID);

            if ($glamiPixel !== '') {
                $this->smarty->assign(array(
                    'glami_pixel_code' => $glamiPixel,
                    'glami_lang' => strtolower($lang)
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/header/glami.tpl');
            }
        }

        //KELKOO
        $display .= $this->kelkooServiceIntegration->insertKelkooHeader($this, $this->_path);

        //GTAG
        // GoogleUniversalAnalytics
        $display .= $this->gtagIntegrationHelper->insertHeader($this, $this->smarty, $this->context, $this->_path);
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
        $display .= $this->googleTagManagerServiceIntegration->insertDefaultCode($this, $this->smarty, $this->context, $this->_path);
        $this->googleTagManagerServiceIntegration->insertDefaultHelpers($this->context, $this->_path);
        $this->googleTagManagerServiceIntegration->userClickedProduct($this->context, $this->_path);

        $display .= $this->googleTagManagerServiceIntegration->orderConfirmation($this, $this->smarty,$this->context, $this->_path);

        //BIANO
        $bianoClass = new \Mergado\includes\services\Biano\Biano\BianoClass();
        if ($bianoClass->isActive($this->shopId)) {
            $langCode = Mergado\Tools\LanguagesClass::getLangIso(strtoupper($this->context->language->iso_code));

            if ($bianoClass->isLanguageActive($langCode, $this->shopId)) {
                $this->smarty->assign(array(
                    'langCode' => $langCode,
                ));

                $display .= $this->display(__FILE__, 'views/templates/front/header/biano/biano.tpl');
            } else {
                $display .= $this->display(__FILE__, 'views/templates/front/header/biano/bianoDefault.tpl');
            }

            $this->smarty->assign(array(
                'merchantId' => $bianoClass->getMerchantId($langCode, $this->shopId),
            ));

            $display .= $this->display(__FILE__, 'views/templates/front/header/biano/bianoInit.tpl');

            $this->context->controller->addJS($this->_path . 'views/js/biano.js');
        }


        //FB PIXEL
        $facebookClass = new \Mergado\Facebook\FacebookClass();

        if ($facebookClass->isActive($this->shopID)) {
            $this->smarty->assign(array(
                'fbPixelCode' => $facebookClass->getCode($this->shopID),
                'searchQuery' => Tools::getValue('search_query'),
                'fbPixel_advertisement_consent' => $this->cookieService->advertismentEnabled()
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/fbpixel.tpl');
        }

        $this->context->controller->addJS($this->_path . 'views/js/glami.js');
        $this->context->controller->addJS($this->_path . 'views/js/fbpixel.js');

        //Add checkbox for heureka
        if (_PS_VERSION_ >= self::PS_V_17) {
            $lang = Mergado\Tools\LanguagesClass::getLangIso();

            $CZactive = Mergado\Tools\SettingsClass::getSettings(\Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CZ'], self::getShopId());
            $SKactive = Mergado\Tools\SettingsClass::getSettings(\Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_SK'], self::getShopId());

            $this->shopId = self::getShopId();

            if ($CZactive === Mergado\Tools\SettingsClass::ENABLED || $SKactive === Mergado\Tools\SettingsClass::ENABLED) {
                $textInLanguage = Mergado\Tools\SettingsClass::getSettings('mergado_heureka_opt_out_text-' . $lang, self::getShopId());

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Verified by Customer program.';
                }

                $link = new Link;
                $parameters = array("action" => "setHeurekaOpc");
                $ajax_link = $link->getModuleLink('mergado','ajax', $parameters);

                Media::addJsDef(array(
                    "mmp_heureka" => array(
                        "ajaxLink" => $ajax_link,
                        "optText" => $textInLanguage,
                        "checkboxChecked" => $this->context->cookie->__get(\Mergado\Heureka\HeurekaClass::CONSENT_NAME),
                    )
            ));

            // Create a link with ajax path
            $this->context->controller->addJS($this->_path . 'views/js/order17/heureka.js');
            }
        }

        //Add checkbox for zbozi
        if (_PS_VERSION_ >= self::PS_V_17) {
            $this->shopId = self::getShopId();
            $ZboziClass = new \Mergado\Zbozi\ZboziClass($this->shopId);
            $lang = Mergado\Tools\LanguagesClass::getLangIso();

            if ($ZboziClass->isActive()) {
                $defaultText = $ZboziClass->getOptOut('en_US');
                $checkboxText = $ZboziClass->getOptOut($lang);

                if (!$checkboxText || ($checkboxText === '') || ($checkboxText === 0)) {
                    $checkboxText = $defaultText;
                }

                if (!$checkboxText || ($checkboxText === '') || ($checkboxText === 0)) {
                    $checkboxText = \Mergado\Zbozi\ZboziClass::DEFAULT_OPT;
                }

                $link = new Link;
                $parameters = array("action" => "setZboziOpc");
                $ajax_link = $link->getModuleLink('mergado','ajax', $parameters);

                Media::addJsDef(array(
                    "mmp_zbozi" => array(
                        "ajaxLink" => $ajax_link,
                        "optText" => $checkboxText,
                        "checkboxChecked" => $this->context->cookie->__get(\Mergado\Zbozi\ZboziClass::CONSENT_NAME),
                    )
                ));

                // Create a link with ajax path
                $this->context->controller->addJS($this->_path . 'views/js/order17/zbozi.js');
            }
        }

        $this->arukeresoServiceIntegration->addCheckboxForPs17($this->context, $this->_path);
        $this->compariServiceIntegration->addCheckboxForPs17($this->context, $this->_path);
        $this->pazaruvajServiceIntegration->addCheckboxForPs17($this->context, $this->_path);
        $this->bianoStarServiceIntegration->addCheckboxForPS17($this->context, $this->_path);

        Media::addJsDef(
            array('mergado' => $jsDef)
        );

        return $display;
    }

    public function createJsVariables()
    {
        // Basic wrapper
        ob_start();
        ?>
        <script type="text/javascript">
          window.mmp = {};
        </script>
        <?php

        $this->cookieService->createJsVariables();

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    function hookDisplayBeforeCarrier($params) {
        \Mergado\includes\helpers\CartHelper::insertShippingInfo($params);
    }

    /**
     * HOOK - TOP PAYMENT
     */

    function hookExtraCarrier ($params)
    {
        $display = '';

        //Works just for ps 1.6
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $lang = Mergado\Tools\LanguagesClass::getLangIso();

            $CZactive = Mergado\Tools\SettingsClass::getSettings(\Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CZ'], self::getShopId());
            $SKactive = Mergado\Tools\SettingsClass::getSettings(\Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_SK'], self::getShopId());

            $this->shopId = self::getShopId();

            if ($CZactive === Mergado\Tools\SettingsClass::ENABLED || $SKactive === Mergado\Tools\SettingsClass::ENABLED) {
                $textInLanguage = Mergado\Tools\SettingsClass::getSettings('mergado_heureka_opt_out_text-' . $lang, self::getShopId());

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0) ) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Verified by Customer program.';
                }

                $this->smarty->assign(array(
                    'heureka_consentText' => $textInLanguage,
                    'heureka_checkboxChecked' => $this->context->cookie->__get(\Mergado\Heureka\HeurekaClass::CONSENT_NAME),
                ));

                $this->context->controller->addJS($this->_path . 'views/js/orderOPC/heureka.js');

                $display .= $this->display(__FILE__, '/views/templates/front/orderCarrier/heureka.tpl');
            }
        }

        //Works just for ps 1.6
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $lang = Mergado\Tools\LanguagesClass::getLangIso();

            $ZboziClass = new Mergado\Zbozi\ZboziClass($this->shopId);

            if ($ZboziClass->isActive()) {
                $textInLanguage = $ZboziClass->getOptOut($lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0) ) {
                    $textInLanguage = 'If you check this, we will send the content of your order together with your e-mail address to Zboží.cz.';
                }

                $this->smarty->assign(array(
                    'zbozi_consentText' => $textInLanguage,
                    'zbozi_checkboxChecked' => $this->context->cookie->__get(\Mergado\Zbozi\ZboziClass::CONSENT_NAME),
                ));

                $this->context->controller->addJS($this->_path . 'views/js/orderOPC/zbozi.js');

                $display .= $this->display(__FILE__, '/views/templates/front/orderCarrier/zbozi.tpl');
            }
        }



        $display .= $this->arukeresoServiceIntegration->addCheckboxForPs16($this, $this->smarty, $this->context, $this->_path);
        $display .= $this->compariServiceIntegration->addCheckboxForPs16($this, $this->smarty, $this->context, $this->_path);
        $display .= $this->pazaruvajServiceIntegration->addCheckboxForPs16($this, $this->smarty, $this->context, $this->_path);
        $display .= $this->bianoStarServiceIntegration->addCheckboxForPS16($this, $this->smarty, $this->context, $this->_path);

        return $display;
    }

    /**
     * Refund order in analytics if status changed to selected one
     *
     * Status update is triggered before cancelProduct
     * (ex. if only one product exist in order and is canceled, then order status is changed to "canceled" ..
     * but it won't send id_order_state so we won't get in)
     *
     * @param $params
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $this->shopId = self::getShopId(); // Not set somehow

        $GaRefundClass = \Mergado\Google\GaRefundClass::getInstance();

        $order = new OrderCore(ToolsCore::getValue('id_order'));
        $orderId = $order->id;
        $orderStateId = Tools::getValue('id_order_state');

        if (!$orderStateId) {
            $orderStateId = $params['newOrderStatus']->id;
        }

        if (!$orderId) {
            $orderId = $params['id_order'];
        }

        if ($this->googleUniversalAnalyticsService->isActiveEcommerce()) {
            if ($GaRefundClass->isStatusActive($orderStateId, $this->shopId)) {

                // Check if order has full refund status already .. and don't send it again
                $orderHistory = $order->getHistory($this->context->language->id);
                $hasRefundedStatus = false;

                foreach($orderHistory as $history) {
                    if ($GaRefundClass->isStatusActive($history, $this->shopId)) {
                        $hasRefundedStatus = true;
                    }
                }

                if (!$hasRefundedStatus) {
                    $GaRefundClass->sendRefundCode(array(), $orderId, Mergado::getShopId(), false);
                }
            }
        }

        $this->googleAnalytics4ServiceIntegration->sendRefundOrderFull($this->context, $orderId, $orderStateId);
    }

    // 1.7.6

    /**
     * Disabled for now / partial refunds not working
     *
     * @param $param
     */
    public function hookActionProductCancel($param)
    {
        $GaRefundClass = \Mergado\Google\GaRefundClass::getInstance();

        $order = new OrderCore(ToolsCore::getValue('id_order'));
        $orderId = $order->id;
        $orderStateId = Tools::getValue('id_order_state');
        $orderProducts = ToolsCore::getValue('id_order_detail');
        $orderCancelQuantity = ToolsCore::getValue('cancelQuantity');

        if ($this->googleUniversalAnalyticsService->isActiveEcommerce()) {
            $products = array();

            foreach($orderProducts as $id) {
                $productId = Mergado\Tools\HelperClass::getProductId($order->getProducts()[$id]);
                $products[] = array('id' => $productId, 'quantity' => $orderCancelQuantity[$id]);
            }

            $GaRefundClass->sendRefundCode($products, $orderId, $this->shopId, true);
        }

        $this->googleAnalytics4ServiceIntegration->sendRefundOrderPartial($this->context, $orderProducts, $orderId, $orderStateId, $orderCancelQuantity);
    }

    /**
     * HOOK - DISPLAY ORDER CONFIRMATION
     */

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayOrderConfirmation($params)
    {
        $display = "";

        $this->shopId = self::getShopId();

        $options = $this->getOrderConfirmationOptions();
        $context = ContextCore::getContext();

        $orderId = \Mergado\Tools\HelperClass::getOrderId($params);
        $order = new OrderCore($orderId);
        $orderCartId = \Mergado\Tools\HelperClass::getOrderCartId($params);
        $orderCurrency = CurrencyCore::getCurrency($order->id_currency);
        $orderProducts = $order->getProducts();
        $orderCustomer = $order->getCustomer(); // new Customer($order->id_customer);

        \Mergado\includes\helpers\OrderConfirmationHelper::insertOrderData($orderId, $orderCurrency, $order, $orderProducts, $orderCustomer);


        $this->smarty->assign(array(
            'useSandbox' => Mergado\Zbozi\Zbozi::ZBOZI_SANDBOX === true ? 1 : 0,
            'lang' => strtolower(substr($context->language->language_code, strpos($context->language->language_code, "-") + 1)), // CZ/SK
            'langIsoCode' => $context->language->iso_code, // CS,SK
        ));

        //Glami top/glami normal TODO: add if
        $glamiProducts = \Mergado\Glami\GlamiClass::prepareProductData($orderProducts);

        // Glami TODO: add if
        $this->smarty->assign(array(
            'glamiData' => \Mergado\Glami\GlamiClass::getGlamiOrderData($orderId, $params, $glamiProducts, $orderCustomer->email, $this->shopId),
        ));

        // Glami TOP TODO: add if
        $this->smarty->assign(array(
            'glamiTopData' => \Mergado\Glami\GlamiClass::getGlamiTOPOrderData($orderId, $glamiProducts, $orderCustomer->email, $this->shopID),
        ));

        // Heureka conversions
        $cart = new CartCore($orderCartId);
        $cartCz = new CartCore($orderCartId, LanguageCore::getIdByIso(self::LANG_CS));
        $cartSk = new CartCore($orderCartId, LanguageCore::getIdByIso(self::LANG_SK));

        if ($cartCz && $options['heurekaCzActive']) {
            $heurekaCzProducts = $this->getOrderConfirmationHeurekaProducts($cartCz->getProducts(), Mergado\Tools\LanguagesClass::getLangIso(strtoupper(self::LANG_CS)));
        } else {
            $heurekaCzProducts = array();
        }

        if ($cartSk && $options['heurekaSkActive']) {
            $heurekaSkProducts = $this->getOrderConfirmationHeurekaProducts($cartSk->getProducts(), Mergado\Tools\LanguagesClass::getLangIso(strtoupper(self::LANG_SK)));
        } else {
            $heurekaSkProducts = array();
        }

        $baseData = $this->getOrderConfirmationBaseData($options, $params, $context, $heurekaSkProducts, $heurekaCzProducts);

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $orderTotal = $params['objOrder']->total_products;

            $specialData = array(
                'conversionOrderId' => $orderId,
                'total' => $params['objOrder']->total_products,
                'currency' => $params['currencyObj'],
                'totalWithoutShippingAndVat' => $params['objOrder']->total_products,
            );

        } else {
            $orderTotal = $params['order']->total_products;

            $specialData = array(
                'conversionOrderId' => $orderId,
                'total' => $params['order']->total_products,
                'currency' => CurrencyCore::getCurrency($params['order']->id_currency),
                'totalWithoutShippingAndVat' => $params['order']->total_products,
            );
        }

        // Facebook
        $facebookClass = new \Mergado\Facebook\FacebookClass();
        if ($facebookClass->isActive($this->shopID)) {
            $this->smarty->assign(array(
                'fbPixelData' => $facebookClass->getFbPixelData($params, $cart->getProducts(), $this->shopID),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/fbpixel.tpl');
        }

        //Sklik
        $sklikClass = new \Mergado\Sklik\SklikClass();
        if ($sklikClass->isConversionsActive($this->shopID)) {
            $this->smarty->assign(array(
                'sklikData' => $sklikClass->getConversionsData($order, $this->shopId),
                'sklikConsent' => (bool) $this->cookieService->advertismentEnabled()
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/sklik.tpl');
        }

        //Kelkoo
        $display .= $this->kelkooServiceIntegration->orderConfirmation($this, $this->smarty, $this->_path, $orderId, $order, $orderProducts);

        //GoogleAds, Google universal analytics - Remove when universal analytics die and move to Google ADS
        if (!OrderClass::isOrderCompleted($orderId, $this->shopId)) {
            if ($this->googleAdsService->isRemarketingActive() || $this->googleUniversalAnalyticsService->isActiveEcommerce()) {
                $this->smarty->assign(array(
                    'gtag_purchase_data' => $this->googleUniversalAnalyticsServiceIntegration->getPurchaseData($orderId, $order, $orderProducts, (int)$context->language->id, $this->shopID)
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/gtagjs.tpl');
            }

            OrderClass::setOrderCompleted($orderId, $this->shopId);
        }

        //Biano
        if ($this->cookieService->advertismentEnabled()) {
            $bianoClass = new \Mergado\includes\services\Biano\Biano\BianoClass();
            if ($bianoClass->isActive($this->shopID)) {
                $this->smarty->assign(array(
                    'bianoPurchaseData' => $bianoClass->getPurchaseData($orderId, $order, $orderCustomer->email, $orderProducts, $this->shopID, $this->context->cookie->__get(\Mergado\includes\services\Biano\BianoStar\BianoStarService::CONSENT_NAME)),
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/biano.tpl');
            }
        }

        //Arukereso, Compari, Pazaruvaj
        $this->arukeresoServiceIntegration->orderConfirmation($orderProducts, $orderCustomer, $this->context->cookie);
        $this->compariServiceIntegration->orderConfirmation($orderProducts, $orderCustomer, $this->context->cookie);
        $this->pazaruvajServiceIntegration->orderConfirmation($orderProducts, $orderCustomer, $this->context->cookie);

        // Google reviews
        $GoogleReviewsClass = Mergado\Google\GoogleReviewsClass::getInstance();
        if ($GoogleReviewsClass->isOptInActive()) {
            $this->smarty->assign(
                array(
                    'googleReviewsOptIn' => $GoogleReviewsClass->getOptInSmartyVariables($params, $orderProducts, $this->context->cart),
                    'googleReviewsFunctionalCookies' => $this->cookieService->functionalEnabled()
                )
            );

            $display .= $this->display(__FILE__, $GoogleReviewsClass->getOptInTemplatePath());
        }

        // GoogleAds
        $display .= $this->googleAdsServiceIntegration->conversion($orderId, $orderTotal, $orderCurrency['iso_code'], $this, $this->smarty, $this->_path);

        // GA4
        $this->googleAnalytics4ServiceIntegration->purchase($this->context, $this->_path);

        // All smarty assign merged and assigned
        $data = array_merge($baseData + $specialData + array('PS_VERSION' => _PS_VERSION_));
        $this->smarty->assign($data);

        Mergado\Tools\LogClass::log("Order confirmation:\n" . json_encode($data) . "\n");

        $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/base.tpl');
        return $display;
    }

    /**
     * HOOK - DISPLAY SHOPING CART
     */

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayShoppingCart($params)
    {
        $display = "";

        //For checkout in ps 1.6
        if(_PS_VERSION_ < self::PS_V_17) {
            $cart = $params['cart'];
            $cartProducts = $cart->getProducts(true);

            $productData = \Mergado\includes\helpers\CartHelper::getOldCartProductData($cartProducts);

            if (_PS_VERSION_ < self::PS_V_17) {
                $this->smarty->assign(array(
                    'data' => htmlspecialchars(json_encode($productData['default'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithVat' => htmlspecialchars(json_encode($productData['withVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'dataWithoutVat' => htmlspecialchars(json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK), ENT_QUOTES, 'UTF-8'),
                    'cart_id' => $cart->id,
                ));
            } else {
                $this->smarty->assign(array(
                    'data' => json_encode($productData['default'], JSON_NUMERIC_CHECK),
                    'dataWithVat' => json_encode($productData['withVat'], JSON_NUMERIC_CHECK),
                    'dataWithoutVat' => json_encode($productData['withoutVat'], JSON_NUMERIC_CHECK),
                    'cart_id' => $cart->id,
                ));
            }

            $discounts = [];

            foreach ($cart->getDiscounts() as $item) {
                $discounts[] = $item['name'];
            }

            $this->smarty->assign(array(
                'coupons' => join(', ', $discounts),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/shoppingCart/cart_data.tpl');

            return $display;
        }
    }

    /**
     * @return array
     */
    public function getOrderConfirmationOptions()
    {
        $shopId = self::getShopId();
        $settings = Mergado\Tools\SettingsClass::getWholeSettings($shopId);

        $sorted = [];

        foreach($settings as $item) {
            $sorted[$item['key']] = $item['value'];
        }

        return [
            'zboziActive' => $sorted[Mergado\Zbozi\ZboziClass::ACTIVE] ?? '',
            'zboziAdvancedActive' => $sorted[Mergado\Zbozi\ZboziClass::ADVANCED_ACTIVE] ?? '',
            'zboziId' => $sorted[Mergado\Zbozi\ZboziClass::SHOP_ID] ?? '',
            'heurekaCzActive' => $sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CZ']] ?? '',
            'heurekaCzCode' => $sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ']] ?? '',
            'heurekaSkActive' => $sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_SK']] ?? '',
            'heurekaSkCode' => $sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CODE_SK']] ?? '',
        ];
    }

    /**
     * @param array $options
     * @param array $params
     * @param $context
     * @param array $heurekaSkProducts
     * @param array $heurekaCzProducts
     * @return array
     */
    public function getOrderConfirmationBaseData(array $options, array $params, $context, array $heurekaSkProducts, array $heurekaCzProducts): array
    {
        return [
            'advertisementCookieConsent' => (int) $this->cookieService->advertismentEnabled(),
            'conversionZboziShopId' => $options['zboziId'],
            'conversionZboziActive' => $options['zboziActive'],
            'conversionZboziAdvancedActive' => $options['zboziAdvancedActive'],
            'heurekaCzActive' => $options['heurekaCzActive'],
            'heurekaCzCode' => $options['heurekaCzCode'],
            'heurekaSkActive' => $options['heurekaSkActive'],
            'heurekaSkCode' => $options['heurekaSkCode'],
            'heurekaCzProducts' => $heurekaCzProducts,
            'heurekaSkProducts' => $heurekaSkProducts,
            'languageCode' => str_replace('-', '_', $context->language->language_code),
        ];
    }

    /**
     * @param array $products
     * @param $lang
     * @return array
     */
    public function getOrderConfirmationHeurekaProducts(array $products, $lang)
    {
        $withVat = \Mergado\Tools\SettingsClass::getSettings(\Mergado\Tools\SettingsClass::HEUREKA['CONVERSION_VAT_INCL_' . $lang], $this->shopId);

        // Default is with vat
        if ($withVat === false) {
            $withVat = true;
        }

        $query = [];

        foreach ($products as $product) {
            $exactName = $product['name'];

            if (array_key_exists('attributes_small', $product) && $product['attributes_small'] !== '') {
                $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                $exactName .= ': ' . implode(' ', $tmpName);
            }

            $id = '';

            if ($product['id_product_attribute'] === \Mergado\Tools\SettingsClass::DISABLED) {
                $id .= urlencode($product['id_product']);
            } else {
                $id .= urlencode($product['id_product'] . '-' . $product['id_product_attribute']);
            }

            $item = array(
                'id' => $id,
                'name' => $exactName,
                'qty' => $product['quantity'],
            );

            if ($withVat) {
                $item['unitPrice'] = Tools::ps_round($product['price_wt'], ConfigurationCore::get('PS_PRICE_DISPLAY_PRECISION'));
            } else {
                $item['unitPrice'] = Tools::ps_round($product['price'], ConfigurationCore::get('PS_PRICE_DISPLAY_PRECISION'));
            }

            $query[] = $item;
        }


        return $query;
    }

    public static function getShopId()
    {
        if (ShopCore::isFeatureActive()) {
            $shopID = ContextCore::getContext()->shop->id;
        } else {
            $shopID = 0;
        }

        return $shopID;
    }

    public function mergadoEnableAll($force_all = false)
    {
        // Retrieve all shops where the module is enabled
        $list = ShopCore::getShops(true, null, true);
        if (!$this->id || !is_array($list)) {
            return false;
        }
        $sql = 'SELECT `id_shop` FROM `' . _DB_PREFIX_ . 'module_shop`
                WHERE `id_module` = ' . (int) $this->id .
            ((!$force_all) ? ' AND `id_shop` IN(' . implode(', ', $list) . ')' : '');

        // Store the results in an array
        $items = array();
        if ($results = Db::getInstance($sql)->executeS($sql)) {
            foreach ($results as $row) {
                $items[] = $row['id_shop'];
            }
        }

        // Enable module in the shop where it is not enabled yet
        foreach ($list as $id) {
            if (!in_array($id, $items)) {
                Db::getInstance()->insert('module_shop', array(
                    'id_module' => $this->id,
                    'id_shop' => $id,
                ));
            }

            // Set export to both enabled for all shops
            Mergado\Tools\SettingsClass::saveSetting(\Mergado\Tools\SettingsClass::EXPORT['BOTH'], 'on', $id);
        }

        return true;
    }

    /**
     * Form add new mutlistore shop saved
     *
     * @param $data
     * @return void
     */
    public function hookActionAdminShopControllerSaveAfter($data)
    {
        if(Tools::getValue('submitAddshop') !== false && Tools::getValue('submitAddshop') === '1') {
            $shopId = Tools::getValue('id_shop');
            Mergado\Tools\SettingsClass::saveSetting(\Mergado\Tools\SettingsClass::EXPORT['BOTH'], 'on', $shopId);
        }
    }
}
