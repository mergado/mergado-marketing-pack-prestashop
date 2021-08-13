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

require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Arukereso/ArukeresoClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Biano/BianoClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Google/GoogleClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Google/GoogleTagManagerClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Google/GoogleAdsClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Google/GaRefundClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Kelkoo/KelkooClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Glami/GlamiClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Facebook/FacebookClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Heureka/HeurekaClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Etarget/EtargetClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Zbozi/ZboziClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Zbozi/Zbozi.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/NajNakup/NajNakupClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Pricemania/PricemaniaClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Sklik/SklikClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Google/GoogleReviews/GoogleReviewsClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/RssClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/ImportPricesClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/HelperClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/SettingsClass.php';

if (!defined('_PS_VERSION_')) {
    exit;
}


class Mergado extends Module
{
    protected $controllerClass;
    public $shopId;

    const MERGADO_LATEST_RELEASE = "https://api.github.com/repos/mergado/mergado-marketing-pack-prestashop/releases/latest";
    const MERGADO_UPDATE = 'https://raw.githubusercontent.com/mergado/mergado-marketing-pack-prestashop/master/mergado/config/mergado_update.xml';
    const MERGADO_UPDATE_CACHE_ID = 'mergado_remote_version';

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
        'VERSION' => '2.6.0',
        'PHP_MIN_VERSION' => 7.1
    ];

    public $GoogleAdsClass;
    public $GoogleTagManagerClass;

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

        $this->ps_versions_compliancy = array('min' => self::PS_V_16, 'max' => '1.7.9.99');

        try {
            $cronRss = new Mergado\Tools\RssClass();
            $cronRss->getFeed();
        } catch (Exception $ex) {
            // Error during installation
        }

        $this->GoogleAdsClass = new Mergado\Google\GoogleAdsClass($this->shopID);
        $this->GoogleTagManagerClass = new Mergado\Google\GoogleTagManagerClass($this->shopID);
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
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('orderConfirmation')
            && $this->registerHook('displayFooter')
//            && $this->registerHook('displayProductFooter') // Probably not used
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->registerHook('displayAfterBodyOpeningTag') // only for PS 1.7
            && $this->registerHook('displayBeforeBodyClosingTag') // only for PS 1.7
            && $this->registerHook('actionOrderStatusUpdate') // For google refund
//            && $this->registerHook('actionProductCancel') // For google refund
            && $this->registerHook('extraCarrier')
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

        return true;
    }

    public static function getRepo()
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_URL => self::MERGADO_LATEST_RELEASE,
        ));
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        return $response;
    }

    /**
     * @param $url
     * @param $zipPath
     * @return bool
     */
    public function getZipFile($url, $zipPath)
    {

        mkdir($zipPath);
        $zipFile = $zipPath . 'update.zip'; // Local Zip File Path
        $zipResource = fopen($zipFile, "w+");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FILE, $zipResource);
        $page = curl_exec($ch);

        if (!$page) {
            echo "Error :- " . curl_error($ch);
        }
        curl_close($ch);

        $zip = new ZipArchive;
        $extractPath = $zipPath;
        if (!$zip->open($zipFile)) {
            echo "Error :- Unable to open the Zip File";
        }

        $result = $zip->extractTo($extractPath);
        $zip->close();

        return $result;
    }

    public static function checkUpdate()
    {
        $response = self::getRepo();
        $version = $response->tag_name;

        if ($version > self::MERGADO['VERSION']) {
            Mergado\Tools\SettingsClass::saveSetting(Mergado\Tools\SettingsClass::NEW_MODULE_VERSION_AVAILABLE, $version, 0);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array|bool
     */
    public function updateModule()
    {
        $response = $this->getRepo();
        $version = $response->tag_name;
        $zipUrl = $response->zipball_url;
        $zipPath = _PS_MODULE_DIR_ . $this->name . '/upgrade/tmp/';

        if ($version > $this->version) {
            if ($this->getZipFile($zipUrl, $zipPath)) {
                $dirname = '';
                foreach (glob($zipPath . '*', GLOB_ONLYDIR) as $dir) {
                    $dirname = basename($dir);
                    break;
                }

                if ($dirname !== '') {
                    $from = $zipPath . $dirname . '/' . $this->name;
                    $to = _PS_MODULE_DIR_ . $this->name;

                    AdminController::mergadoCopyFiles($from, $to);

                    return true;
                }
            }
        } else {
            Mergado\Tools\SettingsClass::saveSetting(Mergado\Tools\SettingsClass::NEW_MODULE_VERSION_AVAILABLE, $version, 0);
            return false;
        }

        return false;
    }

    /**
     * @param null $addons
     * @return string
     */
    public function updateVersionXml($addons = null)
    {
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $addons = Tools::addonsRequest('must-have');
        }

        $mergadoXml = Tools::file_get_contents(self::MERGADO_UPDATE);

        try {
            if ($addons && $mergadoXml) {
                $psXml = new \SimpleXMLElement($addons);
                $mXml = new \SimpleXMLElement($mergadoXml);

                $doc = new DOMDocument();
                $doc->loadXML($psXml->asXml());

                $mDoc = new DOMDocument();
                $mDoc->loadXML($mXml->asXml());

                $node = $doc->importNode($mDoc->documentElement, true);
                $doc->documentElement->appendChild($node);

                $updateXml = $doc->saveXml();

                if (_PS_VERSION_ >= Mergado::PS_V_17) {
                    return $updateXml;
                }
                //            @file_put_contents(_PS_ROOT_DIR_ . ModuleCore::CACHE_FILE_MUST_HAVE_MODULES_LIST, $updateXml);
            }
        } catch(Exception $e) {
            //xml in presta addons not correct or xml in mergado not correct
        }
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
        if(_PS_VERSION_ >= self::PS_V_17) { // Just check cause of custom hook in ps16
            if($this->GoogleTagManagerClass->isActive()):
                $code = $this->GoogleTagManagerClass->getCode();
                ?>
                <!-- Google Tag Manager (noscript) -->
                    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?= $code ?>"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
                <!-- End Google Tag Manager (noscript) -->
            <?php
            endif;
        }
    }

    public function hookDisplayProductAdditionalInfo($product) {
        //Modal first
        $this->addToCart();
    }

    public function addToCart() {
        $lang = Mergado\Tools\SettingsClass::getLangIso();

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
                            currency: $_currency
                        });
                    });
                }
            </script>
            <?php
        }

        $facebookClass = new \Mergado\Facebook\FacebookClass();

        if($facebookClass->isActive($this->shopID)) {
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
                        });
                    });
                }
            </script>
            <?php
        }
    }


    /**
     * HOOK - BACKOFFICE HEADER
     */

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $this->shopId = self::getShopId();

        if (Tools::getValue('controller') == $this->controllerClass) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addJS($this->_path . 'views/vendors/iframe-resizer/js/iframeResizer.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/iframe-resizer.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        } else {
            $this->context->controller->addJquery();
        }

        $this->context->controller->addJS($this->_path . 'views/js/notifications.js');

        if(_PS_VERSION_ < Mergado::PS_V_17) {
            $this->context->controller->addCSS($this->_path . 'views/css/notifications16.css');
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/notifications17.css');
        }

        $lang = Mergado\Tools\SettingsClass::getLangIso();
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
                var admin_mergado_all_messages_id_tab = 7;
                
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
        $display = "";
        $this->shopId = self::getShopId();

        // Biano
        $bianoClass = new \Mergado\Biano\BianoClass();

        if($bianoClass->isActive($this->shopId)) {
            $langCode = Mergado\Tools\SettingsClass::getLangIso(strtoupper($this->context->language->iso_code));

            if($bianoClass->isLanguageActive($langCode, $this->shopId)) {
                $this->smarty->assign(array(
                        'productId' => \Mergado\Tools\HelperClass::getProductId($params['product']),
                ));

                $display .= $this->display(__FILE__, 'views/templates/front/productDetail/biano/bianoViewProductDetail.tpl');
            }
        }

        return $display;
    }

//    /**
//     * HOOK - DISPLAY SHOPPING CART FOOTER
//     * @param $params
//     * @return bool
//     */

//    public function hookDisplayBeforeBodyClosingTag($params)
//    {
//        return $this->cartDataPs17($params);
//    }


    /**
     * HOOK - ACTION VALIDATE ORDER
     */

    /**
     * Verified by users.
     * @param $params
     * @throws Exception
     */
    public function hookActionValidateOrder($params)
    {
        $this->shopId = Mergado::getShopId();

        $verifiedCz = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CZ'], $this->shopID);
        $verifiedSk = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_SK'], $this->shopID);

        //If user don't want the heureka document ... dont send data
        if ($this->context->cookie->mergado_heureka_consent != '1') {

            /* Heureka verified by users */
            if ($verifiedCz && $verifiedCz === Mergado\Tools\SettingsClass::ENABLED) {
                $verifiedCzCode = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CODE_CZ'], $this->shopID);

                if ($verifiedCzCode && $verifiedCzCode !== '') {
                    Mergado\Heureka\HeurekaClass::heurekaVerify($verifiedCzCode, $params, self::LANG_CS);
                }
            }

            if ($verifiedSk && $verifiedSk === Mergado\Tools\SettingsClass::ENABLED) {
                $verifiedCzCode = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CODE_SK'], $this->shopID);

                if ($verifiedCzCode && $verifiedCzCode !== '') {
                    Mergado\Heureka\HeurekaClass::heurekaVerify($verifiedCzCode, $params, self::LANG_SK);
                }
            }
        }

        // Unset cookie becuase of next buy
        $this->context->cookie->mergado_heureka_consent = '0';


        $zboziSent = false;
        if ($this->context->cookie->mergado_zbozi_consent == '1') {
            $zboziSent = Mergado\Zbozi\Zbozi::sendZbozi($params, $this->shopId);
        }

        // Unset cookie because of next buy
        $this->context->cookie->mergado_zbozi_consent = '0';

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
        $langIso = \Mergado\Tools\SettingsClass::getLangIso($iso_code); // for heureka CZ/SK etc..

        $sklikRetargeting = Mergado\Tools\SettingsClass::getSettings(\Mergado\Sklik\SklikClass::RETARGETING_ACTIVE, $this->shopID);

        $display = "";

        //Heureka Widget
        $widgetActive = isset(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_' . $langIso]) ? Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_' . $langIso], $this->shopID) : false;

        if ($widgetActive === Mergado\Tools\SettingsClass::ENABLED) {
            $widgetId = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_ID_' . $langIso], $this->shopID);

            if ($widgetId !== '') {
                $position = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_POSITION_' . $langIso], $this->shopID);
                $minWidth = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_SCREEN_WIDTH_' . $langIso], $this->shopID);
                $showMobile = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_MOBILE_' . $langIso], $this->shopID);
                $marginTop = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::HEUREKA['WIDGET_TOP_MARGIN_' . $langIso], $this->shopID);

                $this->smarty->assign(array(
                    'widgetId' => $widgetId,
                    'marginTop' => $marginTop,
                    'minWidth' => $minWidth,
                    'position' => $position,
                    'showMobile' => $showMobile,
                    'langIso' => strtolower($langIso),
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/heureka_widget.tpl');
            }
        }

        //Sklik retargeting
        $sklikClass = new \Mergado\Sklik\SklikClass();
        if ($sklikClass->isRetargetingActive($this->shopID)) {
            $this->smarty->assign(array(
                'seznam_retargeting_id' => $sklikClass->getRetargetingId($this->shopID),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/sklik.tpl');
        }


        //Etarget
        $etargetClass = new \Mergado\Etarget\EtargetClass();
        if ($etargetClass->isActive($this->shopID)) {
            $this->smarty->assign(array(
                'etargetData' => $etargetClass->getData($this->shopID),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/etarget.tpl');
        }

        $currency = new CurrencyCore($cookie->id_currency);
        $this->smarty->assign(array(
            'currencySign' => $currency->sign,
        ));

        $display .= $this->display(__FILE__, '/views/templates/front/footer/base.tpl');

        $display .= $this->cartDataPs16($params);

        //BIANO
        $bianoClass = new \Mergado\Biano\BianoClass();
        if ($bianoClass->isActive($this->shopId)) {
            $display .= $this->display(__FILE__, 'views/templates/front/header/biano/bianoView.tpl');
            $this->context->controller->addJS($this->_path . 'views/js/biano.js');
        }

        // Arukereso
        $arukeresoClass = new Mergado\Arukereso\ArukeresoClass(self::getShopId());

        if ($arukeresoClass->isWidgetActive()) {
            $this->smarty->assign(
                array(
                    'arukeresoWidget' => $arukeresoClass->getWidgetSmartyVariables(),
                )
            );

            $display .= $this->display(__FILE__, $arukeresoClass->getWidgetTemplatePath());
        }

        // Google reviews
        $GoogleReviewsClass = new Mergado\Google\GoogleReviewsClass(self::getShopId());
        if ($GoogleReviewsClass->isBadgeActive()) {
            $this->smarty->assign(
                array(
                    'googleBadge' => $GoogleReviewsClass->getBadgeSmartyVariables(),
                )
            );

            $display .= $this->display(__FILE__, $GoogleReviewsClass->getBadgeTemplatePath());
        }

        if(_PS_VERSION_ < self::PS_V_17) { // 16
            if($this->GoogleTagManagerClass->isActive()):
                $this->smarty->assign(
                    array(
                        'googleTagManagerCode' => $this->GoogleTagManagerClass->getCode(),
                    )
                );
                ?>
            <?php

            $display .= $this->display(__FILE__, '/views/templates/front/footer/googleTagManager.tpl');
            endif;
        }

        return $display;
    }

    public function cartDataPs17($params) {
        //Data for checkout in ps 1.7 ..

        if(_PS_VERSION_ > self::PS_V_16) {
            $langId = (int)ContextCore::getContext()->language->id;

            $cart = $params['cart'];
            $cartProducts = $cart->getProducts(true);

            $exportProducts = array();

            foreach ($cartProducts as $i => $product) {
                $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
                $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
                $variant = Mergado\Tools\HelperClass::getProductAttributeName($product['id_product_attribute'], (int)$langId);

                $exportProducts[] = array(
                    "id" => \Mergado\Tools\HelperClass::getProductId($product),
                    "name" => $product['name'],
                    "brand" => $manufacturer->name,
                    "category" => $category->name,
                    "variant" => $variant,
                    "list_position" => $i,
                    "quantity" => $product['cart_quantity'],
                    "price" => $product['total_wt'] / $product['cart_quantity'],
                );
            }

            if (_PS_VERSION_ < self::PS_V_17) {
                $this->smarty->assign(array(
                    'data' => htmlspecialchars(json_encode($exportProducts), ENT_QUOTES, 'UTF-8'),
                    'cart_id' => $cart->id,
                ));
            } else {
                $this->smarty->assign(array(
                    'data' => json_encode($exportProducts),
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

            $exportProducts = array();

            foreach ($cartProducts as $i => $product) {
                $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
                $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
                $variant = Mergado\Tools\HelperClass::getProductAttributeName($product['id_product_attribute'], (int)$langId);

                $exportProducts[] = array(
                    "id" => \Mergado\Tools\HelperClass::getProductId($product),
                    "name" => $product['name'],
                    "brand" => $manufacturer->name,
                    "category" => $category->name,
                    "variant" => $variant,
                    "list_position" => $i,
                    "quantity" => $product['cart_quantity'],
                    "price" => $product['total_wt'] / $product['cart_quantity'],
                );
            }

            if (_PS_VERSION_ < self::PS_V_17) {
                $this->smarty->assign(array(
                    'data' => htmlspecialchars(json_encode($exportProducts), ENT_QUOTES, 'UTF-8'),
                    'cart_id' => $cart->id,
                ));
            } else {
                $this->smarty->assign(array(
                    'data' => json_encode($exportProducts),
                    'cart_id' => $cart->id,
                ));
            }

            $discounts = [];

            foreach ($cart->getDiscounts() as $item) {
                $discounts[] = $item['name'];
            }

            global $smarty;
            $url = array_key_exists('urls', $smarty->tpl_vars) ? $smarty->tpl_vars['urls']->value['pages']['order'] : null;

            $this->smarty->assign(array(
                'orderUrl' => $url,
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
        $lang = Mergado\Tools\SettingsClass::getLangIso();

        $this->shopId = self::getShopId();

        $display = "";
        $glami = Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI['ACTIVE'], self::getShopId());
        $glamiLangActive = isset(Mergado\Tools\SettingsClass::GLAMI_LANGUAGES[$lang]) ? Mergado\Tools\SettingsClass::getSettings(Mergado\Tools\SettingsClass::GLAMI_LANGUAGES[$lang], self::getShopId()) : false;
        $categoryId = Tools::getValue('id_category');
        $productId = Tools::getValue('id_product');

        $display .= $this->cartDataPs17($params);

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
                    'glami_lang' => strtolower($lang),
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/header/glami.tpl');
            }
        }

        //KELKOO
        $kelkooActive = Mergado\Tools\SettingsClass::getSettings(\Mergado\Kelkoo\KelkooClass::ACTIVE, self::getShopId());
        if ($kelkooActive === Mergado\Tools\SettingsClass::ENABLED) {
            $display .= $this->display(__FILE__, '/views/templates/front/header/kelkoo.tpl');
        }

        //GTAG
        if (Mergado\Google\GoogleClass::isGtagjsActive($this->shopId) || $this->GoogleAdsClass->isConversionsActive() || $this->GoogleAdsClass->isRemarketingActive()) {
            $smartyParams = array();
            $gtagMainCode = '';

            //Add google analytics code
            if (Mergado\Google\GoogleClass::isGtagjsActive($this->shopId)) {
                $googleAnalyticsCode = Mergado\Google\GoogleClass::getGoogleAnalyticsCode(self::getShopId());
                $smartyParams['googleAnalyticsCode'] = $googleAnalyticsCode;
                $gtagMainCode = $smartyParams['googleAnalyticsCode'];
            }

            // Add conversion code
            if ($this->GoogleAdsClass->isConversionsActive() || $this->GoogleAdsClass->isRemarketingActive()) {
                $gAdsConversionCode = $this->GoogleAdsClass->getConversionsCode();
                $smartyParams['gAdsConversionCode'] = $gAdsConversionCode;

                if ($gtagMainCode == '') {
                    $gtagMainCode = $smartyParams['gAdsConversionCode'];
                }
            }

            //Does remarketing code exist ??
            $smartyParams['gAdsRemarketingActive'] = $this->GoogleAdsClass->isRemarketingActive();

            //Add main code to template
            $smartyParams['gtagMainCode'] = $gtagMainCode;

            $this->smarty->assign($smartyParams);

            $display .= $this->display(__FILE__, '/views/templates/front/header/gtagjs.tpl');
        }

        //GTAG - ecommerce enhanced
        if(Mergado\Google\GoogleClass::isGtagjsEcommerceEnhancedActive($this->shopId) || $this->GoogleAdsClass->isRemarketingActive()) {
            if (\Mergado\Google\GoogleClass::isGtagjsEcommerceEnhancedActive($this->shopId) && $this->GoogleAdsClass->isRemarketingActive()) {
                $send_to = implode(',', [\Mergado\Google\GoogleClass::getGoogleAnalyticsCode($this->shopId), $this->GoogleAdsClass->getConversionsCode()]);
            } else if (\Mergado\Google\GoogleClass::isGtagjsEcommerceEnhancedActive($this->shopId)) {
                $send_to = \Mergado\Google\GoogleClass::getGoogleAnalyticsCode($this->shopId);
            } else {
                $send_to = $this->GoogleAdsClass->getConversionsCode();
            }

            Media::addJsDef(
                array('mergado' =>
                    array (
                        'GoogleAds' => array(
                            'remarketingActive' => $this->GoogleAdsClass->isRemarketingActive(),
                        ),
                        'Gtag' => array(
                            'enhancedActive' => Mergado\Google\GoogleClass::isGtagjsEcommerceEnhancedActive($this->shopId),
                        ),
                        'GtagAndGads' => array (
                            'send_to' => $send_to,
                        )
                    )
                )
            );

            $this->context->controller->addJS($this->_path . 'views/js/gtag.js');
        }

        //GTAG + Google Tag Manager
        //If user come from my url === clicked on product url
        if(isset($_SERVER["HTTP_REFERER"])) {
            if($_SERVER["HTTP_REFERER"]) {
                global $smarty;

                if(_PS_VERSION_ < self::PS_V_17) {
                    $shopUrl = $smarty->tpl_vars['base_dir']->value;
                } else {
                    $shopUrl = $smarty->tpl_vars['urls']->value['shop_domain_url'];
                }

                if(strpos($_SERVER["HTTP_REFERER"], $shopUrl) !== false) {
                    if(Mergado\Google\GoogleClass::isGtagjsEcommerceEnhancedActive($this->shopId)) {
                        $this->context->controller->addJS($this->_path . 'views/js/gtagProductClick.js');
                    }

                    if ($this->GoogleTagManagerClass->isEnhancedEcommerceActive()) {
                        $this->context->controller->addJS($this->_path . 'views/js/gtmProductClick.js');
                    }
                }
            }
        }

        //Google Tag Manager
        if ($this->GoogleTagManagerClass->isActive()) {
            if (Tools::getValue('controller') === 'orderconfirmation') {
                $orderId = Tools::getValue('id_order');
                $order = new Order($orderId);
                $currency = new CurrencyCore($order->id_currency);

                $this->smarty->assign(array(
                    'gtm_ecommerceEnhanced' => $this->GoogleTagManagerClass->isEnhancedEcommerceActive(),
                    'gtm_purchase_data' => $this->GoogleTagManagerClass->getPurchaseData($orderId, $order, (int) $this->context->language->id),
                    'gtm_transaction_data' => $this->GoogleTagManagerClass->getTransactionData($orderId, $order, (int) $this->context->language->id),
                    'gtm_currencyCode' => $currency->iso_code,
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/gtm.tpl');
            }

            //Google Tag Manager - ecommerce enhanced
            if($this->GoogleTagManagerClass->isEnhancedEcommerceActive()) {
                Media::addJsDef(
                    array('mergado' =>
                        array (
                            'GoogleTagManager' => array('maxViewListItems' => $this->GoogleTagManagerClass->getViewListItemsCount()),
                        )
                    )
                );

                $this->context->controller->addJS($this->_path . 'views/js/gtm.js');
            }

            $this->smarty->assign(array(
                'gtm_analytics_id' => $this->GoogleTagManagerClass->getCode(),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/header/gtm.tpl');
        }

        //BIANO
        $bianoClass = new \Mergado\Biano\BianoClass();
        if ($bianoClass->isActive($this->shopId)) {
            $langCode = Mergado\Tools\SettingsClass::getLangIso(strtoupper($this->context->language->iso_code));

            if($bianoClass->isLanguageActive($langCode, $this->shopId)) {
                $this->smarty->assign(array(
                    'merchantId' => $bianoClass->getMerchantId($langCode, $this->shopId),
                    'langCode' => $langCode,
                    'bianoLangOptions' => \Mergado\Biano\BianoClass::LANG_OPTIONS,
                ));

                $display .= $this->display(__FILE__, 'views/templates/front/header/biano/biano.tpl');
            } else {
                $this->smarty->assign(array(
                    'langCode' => $langCode,
                ));

                $display .= $this->display(__FILE__, 'views/templates/front/header/biano/bianoDefault.tpl');
            }

            $this->context->controller->addJS($this->_path . 'views/js/biano.js');
        }

        //FB PIXEL
        $facebookClass = new \Mergado\Facebook\FacebookClass();

        if ($facebookClass->isActive($this->shopID)) {
            $this->smarty->assign(array(
                'fbPixelCode' => $facebookClass->getCode($this->shopID),
                'searchQuery' => Tools::getValue('search_query'),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/fbpixel.tpl');
        }

        $this->context->controller->addJS($this->_path . 'views/js/glami.js');
        $this->context->controller->addJS($this->_path . 'views/js/fbpixel.js');


        //Add checkbox for heureka
        if (_PS_VERSION_ >= self::PS_V_17) {
            $lang = Mergado\Tools\SettingsClass::getLangIso();

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
                        "checkboxChecked" => $this->context->cookie->mergado_heureka_consent,
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
            $lang = Mergado\Tools\SettingsClass::getLangIso();

            if ($ZboziClass->isActive()) {
                $textInLanguage = $ZboziClass->getOptOut($lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = 'If you check this, we will send the content of your order together with your e-mail address to Zboží.cz.';
                }

                $link = new Link;
                $parameters = array("action" => "setZboziOpc");
                $ajax_link = $link->getModuleLink('mergado','ajax', $parameters);

                Media::addJsDef(array(
                    "mmp_zbozi" => array(
                        "ajaxLink" => $ajax_link,
                        "optText" => $textInLanguage,
                        "checkboxChecked" => $this->context->cookie->mergado_zbozi_consent,
                    )
                ));

                // Create a link with ajax path
                $this->context->controller->addJS($this->_path . 'views/js/order17/zbozi.js');
            }
        }

        //Add checkbox for arukereso
        if (_PS_VERSION_ >= self::PS_V_17) {
            $lang = Mergado\Tools\SettingsClass::getLangIso();
            $this->shopId = self::getShopId();
            $arukeresoClass = new Mergado\Arukereso\ArukeresoClass($this->shopID);

            if ($arukeresoClass->isActive()) {
                $textInLanguage = $arukeresoClass->getOptOut($lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Trusted Shop program.';
                }

                $link = new Link;
                $parameters = array("action" => "setArukeresoOpc");
                $ajax_link = $link->getModuleLink('mergado','ajax', $parameters);

                Media::addJsDef(
                    array(
                        "mmp_arukereso" => array (
                            "ajaxLink" => $ajax_link,
                            "optText" => $textInLanguage,
                            "checkboxChecked" => $this->context->cookie->mergado_arukereso_consent
                        )
                    )
                );

                // Create a link with ajax path
                $this->context->controller->addJS($this->_path . 'views/js/order17/arukereso.js');
            }
        }

        return $display;
    }

    /**
     * HOOK - TOP PAYMENT
     */

    function hookExtraCarrier ()
    {
        $display = '';

        //Works just for ps 1.6
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $lang = Mergado\Tools\SettingsClass::getLangIso();

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
                    'heureka_checkboxChecked' => $this->context->cookie->mergado_heureka_consent,
                ));

                $this->context->controller->addJS($this->_path . 'views/js/orderOPC/heureka.js');

                $display .= $this->display(__FILE__, '/views/templates/front/orderCarrier/heureka.tpl');
            }
        }

        //Works just for ps 1.6
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $lang = Mergado\Tools\SettingsClass::getLangIso();

            $ZboziClass = new Mergado\Zbozi\ZboziClass($this->shopId);

            if ($ZboziClass->isActive()) {
                $textInLanguage = $ZboziClass->getOptOut($lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0) ) {
                    $textInLanguage = 'If you check this, we will send the content of your order together with your e-mail address to Zboží.cz.';
                }

                $this->smarty->assign(array(
                    'zbozi_consentText' => $textInLanguage,
                    'zbozi_checkboxChecked' => $this->context->cookie->mergado_zbozi_consent,
                ));

                $this->context->controller->addJS($this->_path . 'views/js/orderOPC/zbozi.js');

                $display .= $this->display(__FILE__, '/views/templates/front/orderCarrier/zbozi.tpl');
            }
        }

        //Works just for ps 1.6

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $lang = Mergado\Tools\SettingsClass::getLangIso();
            $this->shopId = self::getShopId();
            $arukeresoClass = new Mergado\Arukereso\ArukeresoClass($this->shopID);

            if ($arukeresoClass->isActive()) {
                $textInLanguage = $arukeresoClass->getOptOut($lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0) ) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Trusted Shop program.';
                }

                $this->smarty->assign(array(
                    'arukereso_consentText' => $textInLanguage,
                    'arukereso_checkboxChecked' => $this->context->cookie->mergado_arukereso_consent,
                ));

                $this->context->controller->addJS($this->_path . 'views/js/orderOPC/arukereso.js');

                $display .= $this->display(__FILE__, '/views/templates/front/orderCarrier/arukereso.tpl');
            }
        }

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
        $GaRefundClass = new \Mergado\Google\GaRefundClass();

        if ($GaRefundClass->isActive($this->shopId)) {
            if ($GaRefundClass->isStatusActive(Tools::getValue('id_order_state'), $this->shopId)) {
                $order = new Order(Tools::getValue('id_order'));

                // Check if order has full refund status already .. and don't send it again
                $orderHistory = $order->getHistory($this->context->language->id);
                $hasRefundedStatus = false;

                foreach($orderHistory as $history) {
                    if ($GaRefundClass->isStatusActive($history, $this->shopId)) {
                        $hasRefundedStatus = true;
                    }
                }

                if (!$hasRefundedStatus) {
    //                $products = $order->getProducts();
    //
    //                $returnProducts = [];
    //                foreach ($products as $product) {
    //                    $productId = Mergado\Tools\HelperClass::getProductId($product);
    //
    //                    $returnProducts[] = array(
    //                        'id' => $productId,
    //                        'quantity' => ((int)$product['product_quantity'] - (int)$product['product_quantity_refunded']),
    //                    );
    //                }
    //
    //                $GaRefundClass->sendRefundCode($returnProducts, $order->id, Mergado::getShopId(), true);


                    $GaRefundClass->sendRefundCode(array(), $order->id, Mergado::getShopId(), false);
                }
            }
        }
    }

    // 1.7.6

    /**
     * Disabled for now / partial refunds not working
     *
     * @param $param
     */
    public function hookActionProductCancel($param)
    {
        $GaRefundClass = new \Mergado\Google\GaRefundClass();

        if ($GaRefundClass->isActive($this->shopId)) {
//            $logger = new FileLogger(0);
//            $logger->setFilename(_PS_ROOT_DIR_.'/var/logs/pouzi-mergado.log');
//            if ($param['order']->hasBeenShipped()) {
                // When delivered and returned by 'Vrátit produkty'
//                $logger->logDebug('return product');
//            } else if ($param['order']->hasBeenPaid()) {
                // When paid nad returned by 'Storno'
//                $logger->logDebug('standard cancel');
//            } else {
                // When not paid or shipped and canceled by stornovat
//                $logger->logDebug('cancel product');
//            }

            $order = new Order(Tools::getValue('id_order'));
            $products = array();

            foreach($_REQUEST['id_order_detail'] as $id) {
                $productId = Mergado\Tools\HelperClass::getProductId($order->getProducts()[$id]);
                $products[] = array('id' => $productId, 'quantity' => $_REQUEST['cancelQuantity'][$id]);
            }

            $GaRefundClass->sendRefundCode($products, $_REQUEST['id_order'], $this->shopId, true);
        }
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
//        $this->context->controller->addCSS($this->_path . 'views/css/popup.css');
        $this->shopId = self::getShopId();

        $options = $this->getOrderConfirmationOptions();
        $context = ContextCore::getContext();

        $orderId = \Mergado\Tools\HelperClass::getOrderId($params);
        $orderCartId = \Mergado\Tools\HelperClass::getOrderCartId($params);

        $this->smarty->assign(array(
            'useSandbox' => Mergado\Zbozi\Zbozi::ZBOZI_SANDBOX === true ? 1 : 0,
            'lang' => strtolower(substr($context->language->language_code, strpos($context->language->language_code, "-") + 1)), // CZ/SK
            'langIsoCode' => $context->language->iso_code, // CS,SK
        ));

        $order = new OrderCore($orderId);
        $products_tmp = $order->getProducts();

        $customer = new Customer($order->id_customer);

        //Glami top/glami normal TODO: add if
        $glamiProducts = \Mergado\Glami\GlamiClass::prepareProductData($products_tmp);

        // Glami TODO: add if
        $this->smarty->assign(array(
            'glamiData' => \Mergado\Glami\GlamiClass::getGlamiOrderData($orderId, $params, $glamiProducts, $customer->email, $this->shopId),
        ));

        // Glami TOP TODO: add if
        $this->smarty->assign(array(
            'glamiTopData' => \Mergado\Glami\GlamiClass::getGlamiTOPOrderData($orderId, $glamiProducts, $customer->email, $this->shopID),
        ));


        // Heureka conversions
        $cart = new CartCore($orderCartId);
        $cartCz = new CartCore($orderCartId, LanguageCore::getIdByIso(self::LANG_CS));
        $cartSk = new CartCore($orderCartId, LanguageCore::getIdByIso(self::LANG_SK));

        if ($cartCz && $options['heurekaCzActive']) {
            $heurekaCzProducts = $this->getOrderConfirmationHeurekaProducts($cartCz->getProducts(), \Mergado\Tools\SettingsClass::getLangIso(strtoupper(self::LANG_CS)));
        } else {
            $heurekaCzProducts = array();
        }

        if ($cartSk && $options['heurekaSkActive']) {
            $heurekaSkProducts = $this->getOrderConfirmationHeurekaProducts($cartSk->getProducts(), \Mergado\Tools\SettingsClass::getLangIso(strtoupper(self::LANG_SK)));
        } else {
            $heurekaSkProducts = array();
        }

        $baseData = $this->getOrderConfirmationBaseData($options, $params, $context, $heurekaSkProducts, $heurekaCzProducts);

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $specialData = array(
                'conversionOrderId' => $orderId,
                'total' => $params['objOrder']->total_products,
                'currency' => $params['currencyObj'],
                'totalWithoutShippingAndVat' => $params['objOrder']->total_products,
            );

        } else {
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
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/sklik.tpl');
        }

        //Kelkoo
        $kelkooClass = new \Mergado\Kelkoo\KelkooClass();
        if($kelkooClass->isActive($this->shopId)) {
            $this->smarty->assign(array(
                'kelkooData' => $kelkooClass->getOrderData($orderId, $order, $products_tmp, $this->shopId),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/kelkoo.tpl');
        }

        //Gtag.js - Google analytics
        if(Mergado\Google\GoogleClass::isGtagjsEcommerceActive(self::getShopId())) {
            $this->smarty->assign(array(
                'gtag_purchase_data' => Mergado\Google\GoogleClass::getGtagjsPurchaseData($orderId, $order, $products_tmp, (int) $context->language->id, $this->shopID),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/gtagjs.tpl');
        }

        //Biano
        $bianoClass = new \Mergado\Biano\BianoClass();
        if($bianoClass->isActive($this->shopID)) {
            $this->smarty->assign(array(
                'bianoPurchaseData' => $bianoClass->getPurchaseData($orderId, $order, $products_tmp, $this->shopID),
            ));

            $display .= $this->display(__FILE__, '/views/templates/front/orderConfirmation/partials/biano.tpl');
        }

        //Arukereso

        $arukeresoClass = new \Mergado\Arukereso\ArukeresoClass($this->shopID);
        $test = $arukeresoClass->orderConfirmation($products_tmp, $customer, $this->context->cookie->mergado_arukereso_consent);

        // Null it for next order
        $this->context->cookie->mergado_arukereso_consent = '0';

        $display .= $test;

        // Heureka
        $heurekaCZactive = Mergado\Tools\SettingsClass::getSettings(\Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_CZ'], $this->shopID);
        $heurekaSKactive = Mergado\Tools\SettingsClass::getSettings(\Mergado\Tools\SettingsClass::HEUREKA['VERIFIED_SK'], $this->shopID);

        if ($heurekaCZactive || $heurekaSKactive) {
            $this->context->controller->addJS($this->_path . 'views/js/heureka.js');
        }


        // Google reviews
        $GoogleReviewsClass = new Mergado\Google\GoogleReviewsClass(self::getShopId());
        if ($GoogleReviewsClass->isOptInActive()) {
            $this->smarty->assign(
                array(
                    'googleReviewsOptIn' => $GoogleReviewsClass->getOptInSmartyVariables($params, $products_tmp, $this->context->cart),
                )
            );

            $display .= $this->display(__FILE__, $GoogleReviewsClass->getOptInTemplatePath());
        }


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
        //For checkout in ps 1.6
        if(_PS_VERSION_ < self::PS_V_17) {
            $display = "";
            $langId = (int)ContextCore::getContext()->language->id;

            $cart = $params['cart'];
            $cartProducts = $cart->getProducts(true);

            $exportProducts = array();

            foreach ($cartProducts as $i => $product) {
                $category = new CategoryCore((int)$product['id_category_default'], (int)$langId);
                $manufacturer = new ManufacturerCore($product['id_manufacturer'], (int)$langId);
                $variant = Mergado\Tools\HelperClass::getProductAttributeName($product['id_product_attribute'], (int)$langId);

                $exportProducts[] = array(
                    "id" => \Mergado\Tools\HelperClass::getProductId($product),
                    "name" => $product['name'],
                    "brand" => $manufacturer->name,
                    "category" => $category->name,
                    "variant" => $variant,
                    "list_position" => $i,
                    "quantity" => $product['cart_quantity'],
                    "price" => $product['total_wt'] / $product['cart_quantity'],
                );
            }

            if (_PS_VERSION_ < self::PS_V_17) {
                $this->smarty->assign(array(
                    'data' => htmlspecialchars(json_encode($exportProducts), ENT_QUOTES, 'UTF-8'),
                    'cart_id' => $cart->id,
                ));
            } else {
                $this->smarty->assign(array(
                    'data' => json_encode($exportProducts),
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

        // @TODO Rewrite if only php 7+ support - like: isset($settings[Mergado\Tools\SettingsClass::ZBOZI['CONVERSION']]) ?? '',

        $sorted = array();

        foreach($settings as $item) {
            $sorted[$item['key']] = $item['value'];
        }

        return array(
            'zboziActive' => isset($sorted[Mergado\Zbozi\ZboziClass::ACTIVE]) ? $sorted[Mergado\Zbozi\ZboziClass::ACTIVE] : '',
            'zboziAdvancedActive' => isset($sorted[Mergado\Zbozi\ZboziClass::ADVANCED_ACTIVE]) ? $sorted[Mergado\Zbozi\ZboziClass::ADVANCED_ACTIVE] : '',
            'zboziId' => isset($sorted[Mergado\Zbozi\ZboziClass::SHOP_ID]) ? $sorted[Mergado\Zbozi\ZboziClass::SHOP_ID] : '',
            'heurekaCzActive' => isset($sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CZ']]) ? $sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CZ']] : '',
            'heurekaCzCode' => isset($sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ']]) ? $sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ']] : '',
            'heurekaSkActive' => isset($sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_SK']]) ? $sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_SK']] : '',
            'heurekaSkCode' => isset($sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CODE_SK']]) ? $sorted[Mergado\Tools\SettingsClass::HEUREKA['CONVERSIONS_CODE_SK']] : '',
            'googleAds' => isset($sorted[$this->GoogleAdsClass::CONVERSIONS_ACTIVE]) ? $sorted[$this->GoogleAdsClass::CONVERSIONS_ACTIVE] : '',
            'googleAdsCode' => $this->GoogleAdsClass->getConversionsCode(),
            'googleAdsLabel' => isset($sorted[$this->GoogleAdsClass::CONVERSIONS_LABEL]) ? $sorted[$this->GoogleAdsClass::CONVERSIONS_LABEL] : '',
        );
    }

    /**
     * @param array $options
     * @param array $params
     * @param $context
     * @param array $heurekaSkProducts
     * @param array $heurekaCzProducts
     * @return array
     */
    public function getOrderConfirmationBaseData(array $options, array $params, $context, array $heurekaSkProducts, array $heurekaCzProducts)
    {
        return array(
            'conversionZboziShopId' => $options['zboziId'],
            'conversionZboziActive' => $options['zboziActive'],
            'conversionZboziAdvancedActive' => $options['zboziAdvancedActive'],
            'heurekaCzActive' => $options['heurekaCzActive'],
            'heurekaCzCode' => $options['heurekaCzCode'],
            'heurekaSkActive' => $options['heurekaSkActive'],
            'heurekaSkCode' => $options['heurekaSkCode'],
            'heurekaCzProducts' => $heurekaCzProducts,
            'heurekaSkProducts' => $heurekaSkProducts,
            'googleAds' => $options['googleAds'],
            'googleAdsCode' => $options['googleAdsCode'],
            'googleAdsLabel' => $options['googleAdsLabel'],
            'languageCode' => str_replace('-', '_', $context->language->language_code),
        );
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
        }

        return true;
    }
}
