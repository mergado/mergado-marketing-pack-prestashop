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

use Mergado\Heureka\HeurekaClass;
use Mergado\Tools\LogClass;
use Mergado\NajNakup\NajNakupClass;
use Mergado\Pricemania\PricemaniaClass;
use Mergado\Tools\SettingsClass;
use Mergado\Tools\RssClass;
use Mergado\Zbozi\ZboziClass;
use ContextCore as Context;
use LanguageCore as Language;
use ModuleCore as Module;
use TabCore as Tab;
use ConfigurationCore as Configuration;
use ShopCore as Shop;

require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Heureka/HeurekaClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Zbozi/ZboziClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/NajNakup/NajNakupClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Pricemania/PricemaniaClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/RssClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/ImportPricesClass.php';

if (!defined('_PS_VERSION_')) {
    exit;
}


class Mergado extends \Module
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

    const LANG_AVAILABLE = array(
        self::LANG_EN,
        self::LANG_CS,
        self::LANG_SK,
    );

    // Prestashop versions
    const PS_V_16 = 1.6;
    const PS_V_17 = 1.7;

    // Mergado
    const MERGADO = [
        'MODULE_NAME' => 'mergado',
        'TABLE_NAME' => 'mergado',
        'TABLE_NEWS_NAME' => 'mergado_news',
        'VERSION' => '2.0.0',
    ];

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

        $this->_clearCache('*');

        $cronRss = new RssClass();
        $cronRss->getFeed($this->context->language->iso_code);
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
            SettingsClass::saveSetting(SettingsClass::NEW_MODULE_VERSION_AVAILABLE, $version, 0);
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
            SettingsClass::saveSetting(SettingsClass::NEW_MODULE_VERSION_AVAILABLE, $version, 0);
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

            @file_put_contents(_PS_ROOT_DIR_ . Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, $updateXml);
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
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('orderConfirmation')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayProductFooter')
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayOrderConfirmation')
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
        include __DIR__ . "/sql/update-2.0.0.php";

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
    protected function renderForm()
    {
        $id = Tab::getIdFromClassName($this->controllerClass);
        $token = Tools::getAdminToken($this->controllerClass . $id . (int)$this->context->employee->id);
        Tools::redirectAdmin('index.php?controller=' . $this->controllerClass . '&token=' . $token);
        die;
    }

    /**
     * Add item into menu.
     */
    protected function addTab()
    {
        $id_parent = Tab::getIdFromClassName('AdminCatalog');
        if (!$id_parent) {
            throw new RuntimeException(
                sprintf($this->l('Failed to add the module into the main BO menu.')) . ' : '
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

    protected function removeTab()
    {
        if (!Tab::getInstanceFromClassName($this->controllerClass)->delete()) {
            throw new RuntimeException($this->l('Failed to remove the module from the main BO menu.'));
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


        if (!Module::isEnabled($this->name)) {
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
        $this->shopId = self::getShopId();

        $googleAdsRemarketing = SettingsClass::getSettings(SettingsClass::GOOGLE_ADS['REMARKETING'], $this->shopID);

        if ($googleAdsRemarketing === SettingsClass::ENABLED) {
            $googleAdsRemarketingId = SettingsClass::getSettings(SettingsClass::GOOGLE_ADS['REMARKETING_ID'], $this->shopID);

            if ($googleAdsRemarketingId !== '') {
                $this->smarty->assign(array(
                    'googleAds_remarketing_id' => $googleAdsRemarketingId,
                    'page_type' => 'product',
                    'prodid' => $params['product']->id
                ));

                return $this->display(__FILE__, '/views/templates/front/remarketingtag.tpl');
            }
        }

        return false;
    }

    /**
     * HOOK - DISPLAY SHOPPING CART FOOTER
     */

    /**
     * @param array $params
     * @return mixed
     */
    public function hookDisplayShoppingCartFooter($params)
    {
        $this->shopId = self::getShopId();

        $googleAdsRemarketing = SettingsClass::getSettings(SettingsClass::GOOGLE_ADS['REMARKETING'], $this->shopId);
        $prodid = "";

        foreach ($params['cart']->getProducts() as $product) {
            $prodid .= "'" . $product['id_product'];

            if (isset($product['id_product_attribute']) && $product['id_product_attribute'] !== "") {
                $prodid .= '-';
                $prodid .= $product['id_product_attribute'];
            }

            $prodid .= "',";
        }

        $prodid = "[" . substr($prodid, 0, -1) . "]";

        if ($googleAdsRemarketing === SettingsClass::ENABLED) {
            $googleAdsRemarketingId = SettingsClass::getSettings(SettingsClass::GOOGLE_ADS['REMARKETING_ID'], $this->shopId);

            if ($googleAdsRemarketingId !== '') {
                $this->smarty->assign(array(
                    'googleAds_remarketing_id' => $googleAdsRemarketingId,
                    'page_type' => 'cart',
                    'prodid' => $prodid
                ));

                return $this->display(__FILE__, '/views/templates/front/remarketingtag.tpl');
            }
        }

        return false;
    }


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

        $verifiedCz = SettingsClass::getSettings(SettingsClass::HEUREKA['VERIFIED_CZ'], $this->shopID);
        $verifiedSk = SettingsClass::getSettings(SettingsClass::HEUREKA['VERIFIED_SK'], $this->shopID);

        /* Heureka verified by users */
        if ($verifiedCz && $verifiedCz === SettingsClass::ENABLED) {
            $verifiedCzCode = SettingsClass::getSettings(SettingsClass::HEUREKA['VERIFIED_CODE_CZ'], $this->shopID);

            if ($verifiedCzCode && $verifiedCzCode !== '') {
                HeurekaClass::heurekaVerify($verifiedCzCode, $params, self::LANG_CS);
            }
        }

        if ($verifiedSk && $verifiedSk === SettingsClass::ENABLED) {
            $verifiedCzCode = SettingsClass::getSettings(SettingsClass::HEUREKA['VERIFIED_SK'], $this->shopID);

            if ($verifiedCzCode && $verifiedCzCode !== '') {
                HeurekaClass::heurekaVerify($verifiedCzCode, $params, self::LANG_SK);
            }
        }

        $zboziSent = ZboziClass::sendZbozi($params, $this->shopId);
        $najNakupSent = NajnakupClass::sendNajnakupValuation($params, self::LANG_SK, $this->shopId);

        try {
            $pricemaniaSent = PricemaniaClass::sendPricemaniaOverenyObchod($params, self::LANG_SK, $this->shopId);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

        LogClass::log("Validate order:\n" . json_encode(array('verifiedCz' => $verifiedCz, 'verifiedSk' => $verifiedSk, 'conversionSent_Zbozi' => $zboziSent, 'conversionSent_NajNakup' => $najNakupSent, 'conversionSent_Pricemania' => $pricemaniaSent)) . "\n");
    }


    /**
     * HOOK - DISPLAY FOOTER
     */

    /**
     * @return string
     */
    public function hookDisplayFooter()
    {
        global $cookie;

        $this->shopId = self::getShopId();

        $iso_code = Language::getIsoById((int)$cookie->id_lang);
        $codeCz = SettingsClass::getSettings(SettingsClass::HEUREKA['WIDGET_CZ'], $this->shopID);
        $codeSk = SettingsClass::getSettings(SettingsClass::HEUREKA['WIDGET_SK'], $this->shopID);
        $fbPixel = SettingsClass::getSettings('fb_pixel', $this->shopID);
        $googleAdsRemarketing = SettingsClass::getSettings(SettingsClass::GOOGLE_ADS['REMARKETING'], $this->shopID);
        $sklikRetargeting = SettingsClass::getSettings(SettingsClass::SKLIK['RETARGETING'], $this->shopID);
        $etarget = SettingsClass::getSettings(SettingsClass::ETARGET['ACTIVE'], $this->shopID);

        $display = "";

        if ($iso_code === self::LANG_CS && $codeCz === SettingsClass::ENABLED) {
            $conversioncode = SettingsClass::getSettings(SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ'], $this->shopID);
            if ($conversioncode !== '') {

                $this->smarty->assign(array(
                    'conversionKey' => $conversioncode
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/heureka_widget_cz.tpl');
            }
        }

        if ($iso_code === self::LANG_SK && $codeSk === SettingsClass::ENABLED) {
            $conversioncode = SettingsClass::getSettings(SettingsClass::HEUREKA['CONVERSIONS_CODE_SK'], $this->shopID);
            if ($conversioncode !== '') {
                $this->smarty->assign(array(
                    'conversionKey' => $conversioncode
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/heureka_widget_sk.tpl');
            }
        }

        if ($fbPixel === SettingsClass::ENABLED) {
            $fbPixelCode = SettingsClass::getSettings(SettingsClass::FB_PIXEL['CODE'], $this->shopID);

            if ($fbPixelCode !== '') {
                $this->smarty->assign(array(
                    'fbPixelCode' => $fbPixelCode,
                    'searchQuery' => Tools::getValue('search_query'),
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/fbpixel.tpl');
            }
        }


        if ($googleAdsRemarketing === SettingsClass::ENABLED) {
            $googleAdsRemarketingId = SettingsClass::getSettings(SettingsClass::GOOGLE_ADS['REMARKETING_ID'], $this->shopID);

            if ($googleAdsRemarketingId !== '') {
                $this->smarty->assign(array(
                    'googleAds_remarketing_id' => $googleAdsRemarketingId,
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/googleAds.tpl');
            }
        }

        if ($sklikRetargeting === SettingsClass::ENABLED) {
            $sklikRetargetingId = SettingsClass::getSettings(SettingsClass::SKLIK['RETARGETING_ID'], $this->shopID);

            if ($sklikRetargetingId !== '') {
                $this->smarty->assign(array(
                    'seznam_retargeting_id' => $sklikRetargetingId,
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/sklik.tpl');
            }
        }

        if ($etarget === SettingsClass::ENABLED) {
            $etargetId = SettingsClass::getSettings(SettingsClass::ETARGET['ID'], $this->shopID);
            $etargetHash = SettingsClass::getSettings(SettingsClass::ETARGET['HASH'], $this->shopID);

            if ($etargetId !== '') {
                $this->smarty->assign(array(
                    'etarget_id' => $etargetId,
                    'etarget_hash' => $etargetHash,
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/footer/partials/etarget.tpl');
            }
        }

        $currency = new CurrencyCore($cookie->id_currency);
        $this->smarty->assign(array(
            'currencySign' => $currency->sign,
        ));

        $display .= $this->display(__FILE__, '/views/templates/front/footer/base.tpl');

        return $display;
    }


    /**
     * HOOK - DISPLAY HEADER
     */

    /**
     * @return string
     */
    public function hookDisplayHeader()
    {
        $lang = SettingsClass::getLangIso();

        $this->shopId = self::getShopId();

        $display = "";
        $glami = SettingsClass::getSettings(SettingsClass::GLAMI['ACTIVE'], self::getShopId());
        $glamiLangActive = SettingsClass::getSettings(SettingsClass::GLAMI_LANGUAGES[$lang], self::getShopId());
        $categoryId = Tools::getValue('id_category');
        $productId = Tools::getValue('id_product');

        if ($categoryId) {
            $category = new CategoryCore($categoryId, (int)ContextCore::getContext()->language->id);
            $nb = 10;
            $products_tmp = $category->getProducts((int)Context::getContext()->language->id, 1, ($nb ? $nb : 10));
            $products = array();
            foreach ($products_tmp as $product) {
                $products['ids'][] = $product['id_product'] . '-' . $product['id_product_attribute'];
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
                'glami_pixel_product' => $product
            ));
        }

        if ($glami === SettingsClass::ENABLED && $glamiLangActive === SettingsClass::ENABLED) {
            $glamiPixel = SettingsClass::getSettings(SettingsClass::GLAMI['CODE'] . '-' . $lang, $this->shopID);

            if ($glamiPixel !== '') {
                $this->smarty->assign(array(
                    'glami_pixel_code' => $glamiPixel,
                    'glami_lang' => strtolower($lang),
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/header/glami.tpl');
            }
        }

        $this->context->controller->addJS($this->_path . 'views/js/glami.js');

        return $display;
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
        $this->shopId = self::getShopId();

        $heurekaCzProducts = array();
        $heurekaSkProducts = array();

        $options = $this->getOrderConfirmationOptions();
        $context = Context::getContext();

        $this->smarty->assign(array(
            'useSandbox' => ZboziClass::ZBOZI_SANDBOX === true ? 1 : 0,
        ));

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $order = new OrderCore($params['objOrder']->id);
        } else {
            $order = new OrderCore($params['order']->id);
        }

        $products_tmp = $order->getProducts();

        $products = array();
        foreach ($products_tmp as $product) {
            $products['ids'][] = $product['product_id'] . '-' . $product['product_attribute_id'];
            $products['name'][] = $product['product_name'];
        }

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $this->assignGlami($params['objOrder']->id,
                $params['total_to_pay'],
                $params['currencyObj']->iso_code,
                $products['ids'],
                $products['name']
            );
        } else {
            $this->assignGlami(
                $params['order']->id_cart,
                $params['order']->total_paid,
                CurrencyCore::getCurrency($params['order']->id_currency),
                $products['ids'],
                $products['name']
            );
        }

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $cart = new CartCore($params['objOrder']->id_cart);
            $cartCz = new CartCore($params['objOrder']->id_cart, LanguageCore::getIdByIso(self::LANG_CS));
            $cartSk = new CartCore($params['objOrder']->id_cart, LanguageCore::getIdByIso(self::LANG_SK));
        } else {
            $cart = new CartCore($params['order']->id_cart);
            $cartCz = new CartCore($params['order']->id_cart, LanguageCore::getIdByIso(self::LANG_CS));
            $cartSk = new CartCore($params['order']->id_cart, LanguageCore::getIdByIso(self::LANG_SK));
        }

        if (!$options['sklikValue']) {
            $sklikValue = 0;
        } else {
            $sklikValue = $options['sklikValue'];
        }

        if ($cartCz && $options['heurekaCzActive']) {
            $heurekaCzProducts = $this->getOrderConfirmationHeurekaProducts($cartCz->getProducts());
        }

        if ($cartSk && $options['heurekaSkActive']) {
            $heurekaSkProducts = $this->getOrderConfirmationHeurekaProducts($cartSk->getProducts());
        }

        $fbProducts = array();

        if ($options['fbPixel']) {
            foreach ($cart->getProducts() as $product) {
                $fbProducts[] = $product['id_product'];
            }
        }

        $baseData = $this->getOrderConfirmationBaseData($options, $params, $context, $heurekaSkProducts, $heurekaCzProducts, $fbProducts);

        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $specialData = array(
                'sklikValue' => $sklikValue,
                'conversionOrderId' => $params['objOrder']->id,
                'total' => $params['total_to_pay'],
                'currency' => $params['currencyObj'],
                'totalWithoutShippingAndVat' => $params['order']->total_products,
            );
        } else {
            $specialData = array(
                'sklikValue' => $sklikValue,
                'conversionOrderId' => $params['order']->id,
                'total' => $params['order']->total_paid,
                'currency' => CurrencyCore::getCurrency($params['order']->id_currency),
                'totalWithoutShippingAndVat' => $params['order']->total_products,
            );
        }

        $data = array_merge($baseData + $specialData);

        $this->smarty->assign($data);

        LogClass::log("Order confirmation:\n" . json_encode($data) . "\n");
        return $this->display(__FILE__, '/views/templates/front/orderConfirmation/base.tpl');
    }

    /**
     * @param $orderId
     * @param $value
     * @param $currency
     * @param $productIds
     * @param $productNames
     */
    public function assignGlami($orderId, $value, $currency, $productIds, $productNames)
    {
        $this->smarty->assign(array(
            'glami_pixel_orderId' => $orderId,
            'glami_pixel_value' => $value,
            'glami_pixel_currency' => $currency,
            'glami_pixel_productIds' => json_encode($productIds),
            'glami_pixel_productNames' => json_encode($productNames)
        ));
    }

    /**
     * @return array
     */
    public function getOrderConfirmationOptions()
    {
        $shopId = self::getShopId();
        $settings = SettingsClass::getWholeSettings($shopId);

        // @TODO Rewrite if only php 7+ support - like: isset($settings[SettingsClass::ZBOZI['CONVERSION']]) ?? '',

        $sorted = array();

        foreach($settings as $item) {
            $sorted[$item['key']] = $item['value'];
        }

        return array(
            'zboziActive' => isset($sorted[SettingsClass::ZBOZI['CONVERSIONS']]) ? $sorted[SettingsClass::ZBOZI['CONVERSIONS']] : '',
            'zboziAdvancedActive' => isset($sorted[SettingsClass::ZBOZI['CONVERSIONS_ADVANCED']]) ? $sorted[SettingsClass::ZBOZI['CONVERSIONS_ADVANCED']] : '',
            'zboziId' => isset($sorted[SettingsClass::ZBOZI['SHOP_ID']]) ? $sorted[SettingsClass::ZBOZI['SHOP_ID']] : '',
            'heurekaCzActive' => isset($sorted[SettingsClass::HEUREKA['CONVERSIONS_CZ']]) ? $sorted[SettingsClass::HEUREKA['CONVERSIONS_CZ']] : '',
            'heurekaCzCode' => isset($sorted[SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ']]) ? $sorted[SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ']] : '',
            'heurekaSkActive' => isset($sorted[SettingsClass::HEUREKA['CONVERSIONS_SK']]) ? $sorted[SettingsClass::HEUREKA['CONVERSIONS_SK']] : '',
            'heurekaSkCode' => isset($sorted[SettingsClass::HEUREKA['CONVERSIONS_SK']]) ? $sorted[SettingsClass::HEUREKA['CONVERSIONS_SK']] : '',
            'sklik' => isset($sorted[SettingsClass::SKLIK['CONVERSIONS']]) ? $sorted[SettingsClass::SKLIK['CONVERSIONS']] : '',
            'sklikCode' => isset($sorted[SettingsClass::SKLIK['CONVERSIONS_CODE']]) ? $sorted[SettingsClass::SKLIK['CONVERSIONS_CODE']] : '',
            'sklikValue' => isset($sorted[SettingsClass::SKLIK['CONVERSIONS_VALUE']]) ? $sorted[SettingsClass::SKLIK['CONVERSIONS_VALUE']] : '',
            'googleAds' => isset($sorted[SettingsClass::GOOGLE_ADS['CONVERSIONS']]) ? $sorted[SettingsClass::GOOGLE_ADS['CONVERSIONS']] : '',
            'googleAdsCode' => isset($sorted[SettingsClass::GOOGLE_ADS['CONVERSIONS_CODE']]) ? $sorted[SettingsClass::GOOGLE_ADS['CONVERSIONS_CODE']] : '',
            'googleAdsLabel' => isset($sorted[SettingsClass::GOOGLE_ADS['CONVERSIONS_LABEL']]) ? $sorted[SettingsClass::GOOGLE_ADS['CONVERSIONS_LABEL']] : '',
            'fbPixel' => isset($sorted[SettingsClass::FB_PIXEL['ACTIVE']]) ? $sorted[SettingsClass::FB_PIXEL['ACTIVE']] : '',
        );
    }

    /**
     * @param array $options
     * @param array $params
     * @param $context
     * @param array $heurekaSkProducts
     * @param array $heurekaCzProducts
     * @param array $fbProducts
     * @return array
     */
    public function getOrderConfirmationBaseData(array $options, array $params, $context, array $heurekaSkProducts, array $heurekaCzProducts, array $fbProducts)
    {
        return array(
            'conversionZboziShopId' => $options['zboziId'],
            'conversionZboziActive' => $options['zboziActive'],
            'conversionZboziAdvancedActive' => $options['zboziAdvancedActive'],
            'conversionZboziTotal' => number_format(
                $params['order']->total_paid, Configuration::get('PS_PRICE_DISPLAY_PRECISION')
            ),
            'heurekaCzActive' => $options['heurekaCzActive'],
            'heurekaCzCode' => $options['heurekaCzCode'],
            'heurekaSkActive' => $options['heurekaSkActive'],
            'heurekaSkCode' => $options['heurekaSkCode'],
            'heurekaCzProducts' => $heurekaCzProducts,
            'heurekaSkProducts' => $heurekaSkProducts,
            'sklik' => $options['sklik'],
            'sklikCode' => $options['sklikCode'],
            'googleAds' => $options['googleAds'],
            'googleAdsCode' => $options['googleAdsCode'],
            'googleAdsLabel' => $options['googleAdsLabel'],
            'languageCode' => str_replace('-', '_', $context->language->language_code),
            'fbPixel' => $options['fbPixel'],
            'fbPixelProducts' => $fbProducts,
        );
    }

    /**
     * @param array $products
     * @return array
     */
    public function getOrderConfirmationHeurekaProducts(array $products)
    {
        $query = [];

        foreach ($products as $product) {
            $exactName = $product['name'];

            if (array_key_exists('attributes_small', $product) && $product['attributes_small'] !== '') {
                $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                $exactName .= ': ' . implode(' ', $tmpName);
            }

            $query[] = array(
                'name' => $exactName,
                'qty' => $product['quantity'],
                'unitPrice' => Tools::ps_round(
                    $product['price_wt'], Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                ),
            );
        }

        return $query;
    }

    public static function getShopId()
    {
        if (Shop::isFeatureActive()) {
            $shopID = Context::getContext()->shop->id;
        } else {
            $shopID = 0;
        }

        return $shopID;
    }

    public function mergadoEnableAll($force_all = false)
    {
        // Retrieve all shops where the module is enabled
        $list = Shop::getShops(true, null, true);
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
