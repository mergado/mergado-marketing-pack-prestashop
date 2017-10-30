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
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   LICENSE.txt
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mergado/classes/MergadoClass.php';

class Mergado extends Module {

    protected $controllerClass;
    private $gitLatestRelease = "https://api.github.com/repos/mergado/mergado-marketing-pack-prestashop/releases/latest";

    public function __construct() {
        $this->name = 'mergado';
        $this->tab = 'export';
        $this->version = '1.5.5';
        $this->author = 'www.mergado.cz';
        $this->need_instance = 0;
        $this->module_key = '12cdb75588bb090637655d626c01c351';
        $this->controllerClass = 'AdminMergado';

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        if (_PS_VERSION_ >= 1.5 && _PS_VERSION_ < 1.6) {
            $this->bootstrap = false;
        } else {
            $this->bootstrap = true;
        }

        parent::__construct();

        $this->displayName = $this->l('Mergado marketing pack');
        $this->description = $this->l('Mergado marketing pack module helps you to export your products information to Mergado services.');

        $this->confirmUninstall = $this->l('Are you sure to uninstall Mergado marketing pack module?');

        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);

        $this->_clearCache('*');
    }

    public function getRepo() {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_URL => $this->gitLatestRelease,
        ));
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        return $response;
    }

    public function getZipFile($url, $zipPath) {

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

    public function updateModule() {

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

                if ($dirname != '') {
                    return array(
                        'from' => $zipPath . $dirname . '/' . $this->name,
                        'to' => _PS_MODULE_DIR_ . $this->name,
                        'delete' => $zipPath
                    );
                }
            }
        }

        return false;
    }

    public function updateVersionXml() {
        $mustHave = Tools::addonsRequest('must-have');
        $mergadoXml = file_get_contents('https://raw.githubusercontent.com/mergado/mergado-marketing-pack-prestashop/master/mergado/config/mergado_update.xml');
        $mergadoXml = file_get_contents(_PS_MODULE_DIR_ . '/mergado/config/mergado_update.xml');

        $psXml = new \SimpleXMLElement($mustHave);
        $mXml = new \SimpleXMLElement($mergadoXml);

        $doc = new DOMDocument();
        $doc->loadXML($psXml->asXml());

        $mDoc = new DOMDocument();
        $mDoc->loadXML($mXml->asXml());

        $node = $doc->importNode($mDoc->documentElement, true);
        $doc->documentElement->appendChild($node);

        $updateXml = $doc->saveXml();

        @file_put_contents(_PS_ROOT_DIR_ . Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, $updateXml);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     */
    public function install() {
        include dirname(__FILE__) . '/sql/install.php';

        $this->addTab();

        return parent::install() && $this->installUpdates() && $this->registerHook('backOfficeHeader') && $this->registerHook('actionValidateOrder') && $this->registerHook('orderConfirmation') && $this->registerHook('displayFooter') && $this->registerHook('displayProductFooter') && $this->registerHook('displayShoppingCartFooter');
    }

    public function uninstall() {
        include dirname(__FILE__) . '/sql/uninstall.php';

        $this->removeTab();

        return parent::uninstall();
    }

    public function installUpdates() {
        include __DIR__ . "/sql/update-1.2.2.php";

        return true;
    }

    /**
     * Load the configuration form.
     */
    public function getContent() {
        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm() {
        $id = Tab::getIdFromClassName($this->controllerClass);
        $token = Tools::getAdminToken($this->controllerClass . $id . (int) $this->context->employee->id);
        Tools::redirectAdmin('index.php?controller=' . $this->controllerClass . '&token=' . $token);
        die;
    }

    /**
     * Add item into menu.
     */
    protected function addTab() {
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

    protected function removeTab() {
        if (!Tab::getInstanceFromClassName($this->controllerClass)->delete()) {
            throw new RuntimeException($this->l('Failed to remove the module from the main BO menu.'));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader() {
        if (Tools::getValue('controller') == $this->controllerClass) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/back.js');

            if (_PS_VERSION_ >= 1.5 && _PS_VERSION_ < 1.6) {
                $this->context->controller->addCSS($this->_path . 'views/css/back15.css');
            } else {
                $this->context->controller->addCSS($this->_path . 'views/css/back.css');
            }
        }
    }

    public function hookDisplayFooterProduct($params) {
        $adWordsRemarketing = MergadoClass::getSettings('adwords_remarketing');

        if ($adWordsRemarketing == '1') {
            $adWordsRemarketingId = MergadoClass::getSettings('adwords_remarketing_id');

            if ($adWordsRemarketingId != '') {
                $this->smarty->assign(array(
                    'adwords_remarketing_id' => $adWordsRemarketingId,
                    'page_type' => 'product',
                    'prodid' => $params['product']->id
                ));

                return $this->display(__FILE__, '/views/templates/front/remarketingtag.tpl');
            }
        }
    }

    public function hookDisplayShoppingCartFooter($params) {
        $adWordsRemarketing = MergadoClass::getSettings('adwords_remarketing');

        $prodid = "";
        foreach ($params['products'] as $product) {
            $prodid .= "'" . $product['id_product'];

            if(isset($product['id_product_attribute']) && $product['id_product_attribute'] != "") {
                $prodid .= '-';
                $prodid .= $product['id_product_attribute'];
            }
            
            $prodid .= "',";
        }
        
        $prodid = "[" . substr($prodid, 0, -1) . "]";
        
        if ($adWordsRemarketing == '1') {
            $adWordsRemarketingId = MergadoClass::getSettings('adwords_remarketing_id');

            if ($adWordsRemarketingId != '') {
                $this->smarty->assign(array(
                    'adwords_remarketing_id' => $adWordsRemarketingId,
                    'page_type' => 'cart',
                    'prodid' => $prodid
                ));

                return $this->display(__FILE__, '/views/templates/front/remarketingtag.tpl');
            }
        }
    }

    /**
     * Verified by users.
     */
    public function hookActionValidateOrder($params) {
        $verifiedCz = MergadoClass::getSettings('mergado_heureka_overeno_zakazniky_cz');
        $verifiedSk = MergadoClass::getSettings('mergado_heureka_overeno_zakazniky_sk');
        $mergado = new MergadoClass();

        /* Heureka verified by users */
        if ($verifiedCz && $verifiedCz === '1') {
            $verifiedCzCode = MergadoClass::getSettings('mergado_heureka_overeno_zakazniky_kod_cz');

            if ($verifiedCzCode && $verifiedCzCode != '') {
                $mergado->heurekaVerify($verifiedCzCode, $params, 'cs');
            }
        }

        if ($verifiedSk && $verifiedSk === '1') {
            $verifiedCzCode = MergadoClass::getSettings('mergado_heureka_overeno_zakazniky_kod_sk');

            if ($verifiedCzCode && $verifiedCzCode != '') {
                $mergado->heurekaVerify($verifiedCzCode, $params, 'sk');
            }
        }

        $zboziSent = $mergado->sendZboziKonverze($params, 'cs');
        $pricemaniaSent = $mergado->sendPricemaniaOverenyObchod($params, 'sk');
        $najNakupSent = $mergado->sendNajnakupValuation($params, 'sk');
        MergadoClass::log("Validate order:\n" . json_encode(array('verifiedCz' => $verifiedCz, 'verifiedSk' => $verifiedSk, 'conversionSent_Zbozi' => $zboziSent, 'conversionSent_NajNakup' => $najNakupSent, 'conversionSent_Pricemania' => $pricemaniaSent)) . "\n");
    }

    public function hookOrderConfirmation($params) {
        $zboziActive = MergadoClass::getSettings('mergado_zbozi_konverze');
        $zboziId = MergadoClass::getSettings('mergado_zbozi_shop_id');
        $heurekaCzActive = MergadoClass::getSettings('mergado_heureka_konverze_cz');
        $heurekaCzCode = MergadoClass::getSettings('mergado_heureka_konverze_cz_kod');
        $heurekaSkActive = MergadoClass::getSettings('mergado_heureka_konverze_sk');
        $heurekaSkCode = MergadoClass::getSettings('mergado_heureka_konverze_sk');
        $sklik = MergadoClass::getSettings('mergado_sklik_konverze');
        $sklikCode = MergadoClass::getSettings('mergado_sklik_konverze_kod');
        $sklikValue = MergadoClass::getSettings('mergado_sklik_konverze_hodnota');
        $adwords = MergadoClass::getSettings('mergado_adwords_conversion');
        $adwordsCode = MergadoClass::getSettings('mergado_adwords_conversion_code');
        $adwordsLabel = MergadoClass::getSettings('mergado_adwords_conversion_label');
        $cart = new CartCore($params['objOrder']->id_cart);
        $cartCz = new CartCore($params['objOrder']->id_cart, LanguageCore::getIdByIso('cs'));
        $cartSk = new CartCore($params['objOrder']->id_cart, LanguageCore::getIdByIso('sk'));
        $heurekaCzProducts = array();
        $heurekaSkProducts = array();

        if (!$sklikValue) {
            $sklikValue = 0;
        }

        if ($cartCz && $heurekaCzActive) {
            foreach ($cartCz->getProducts() as $product) {
                $exactName = $product['name'];

                if (array_key_exists('attributes_small', $product) && $product['attributes_small'] != '') {
                    $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                    $exactName .= ': ' . implode(' ', $tmpName);
                }

                $heurekaCzProducts[] = array(
                    'name' => $exactName,
                    'qty' => $product['quantity'],
                    'unitPrice' => Tools::ps_round(
                            $product['price_wt'], Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                    ),
                );
            }
        }

        if ($cartSk && $heurekaSkActive) {
            foreach ($cartSk->getProducts() as $product) {
                $exactName = $product['name'];

                if (array_key_exists('attributes_small', $product) && $product['attributes_small'] != '') {
                    $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                    $exactName .= ': ' . implode(' ', $tmpName);
                }

                $heurekaSkProducts[] = array(
                    'name' => $exactName,
                    'qty' => $product['quantity'],
                    'unitPrice' => Tools::ps_round(
                            $product['price_wt'], Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                    ),
                );
            }
        }

        $fbPixel = MergadoClass::getSettings('fb_pixel');
        $fbProducts = array();
        if ($fbPixel) {
            foreach ($cart->getProducts() as $product) {
                $fbProducts[] = $product['id_product'];
            }
        }

        $context = Context::getContext();

        $data = array(
            'conversionZboziShopId' => $zboziId,
            'conversionZboziActive' => $zboziActive,
            'conversionZboziTotal' => number_format(
                    $params['objOrder']->total_paid, Configuration::get('PS_PRICE_DISPLAY_PRECISION')
            ),
            'conversionOrderId' => $params['objOrder']->id,
            'heurekaCzActive' => $heurekaCzActive,
            'heurekaCzCode' => $heurekaCzCode,
            'heurekaSkActive' => $heurekaSkActive,
            'heurekaSkCode' => $heurekaSkCode,
            'heurekaCzProducts' => $heurekaCzProducts,
            'heurekaSkProducts' => $heurekaSkProducts,
            'sklik' => $sklik,
            'sklikCode' => $sklikCode,
            'sklikValue' => $sklikValue,
            'adwords' => $adwords,
            'adwordsCode' => $adwordsCode,
            'adwordsLabel' => $adwordsLabel,
            'total' => $params['total_to_pay'],
            'currency' => $params['currencyObj'],
            'languageCode' => str_replace('-', '_', $context->language->language_code),
            'fbPixel' => $fbPixel,
            'fbPixelProducts' => $fbProducts,
        );

        $this->smarty->assign($data);

        MergadoClass::log("Order confirmation:\n" . json_encode($data) . "\n");
        return $this->display(__FILE__, '/views/templates/front/tracking.tpl');
    }

    public function hookDisplayFooter($params) {

        global $cookie;
        $iso_code = Language::getIsoById((int) $cookie->id_lang);
        $codeCz = MergadoClass::getSettings('mergado_heureka_widget_cz');
        $codeSk = MergadoClass::getSettings('mergado_heureka_widget_sk');
        $fbPixel = MergadoClass::getSettings('fb_pixel');
        $adWordsRemarketing = MergadoClass::getSettings('adwords_remarketing');
        $sklikRetargeting = MergadoClass::getSettings('seznam_retargeting');
        $etarget = MergadoClass::getSettings('etarget');

        $display = "";

        if ($iso_code == 'cs' && $codeCz == '1') {
            $conversioncode = MergadoClass::getSettings('mergado_heureka_konverze_cz_kod');
            if ($conversioncode != '') {

                $this->smarty->assign(array(
                    'conversionKey' => $conversioncode
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/heureka_widget_cz.tpl');
            }
        }

        if ($iso_code == 'sk' && $codeSk == '1') {
            $conversioncode = MergadoClass::getSettings('mergado_heureka_konverze_sk_kod');
            if ($conversioncode != '') {
                $this->smarty->assign(array(
                    'conversionKey' => $conversioncode
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/heureka_widget_sk.tpl');
            }
        }

        if ($fbPixel == '1') {
            $fbPixelCode = MergadoClass::getSettings('fb_pixel_code');

            if ($fbPixelCode != '') {
                $this->smarty->assign(array(
                    'fbPixelCode' => $fbPixelCode,
                    'searchQuery' => Tools::getValue('search_query'),
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/fbpixel.tpl');
            }
        }

        if ($adWordsRemarketing == '1') {
            $adWordsRemarketingId = MergadoClass::getSettings('adwords_remarketing_id');

            if ($adWordsRemarketingId != '') {
                $this->smarty->assign(array(
                    'adwords_remarketing_id' => $adWordsRemarketingId,
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/adwords.tpl');
            }
        }

        if ($sklikRetargeting == '1') {
            $sklikRetargetingId = MergadoClass::getSettings('seznam_retargeting_id');

            if ($sklikRetargetingId != '') {
                $this->smarty->assign(array(
                    'seznam_retargeting_id' => $sklikRetargetingId,
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/sklik.tpl');
            }
        }

        if ($etarget == '1') {
            $etargetId = MergadoClass::getSettings('etarget_id');
            $etargetHash = MergadoClass::getSettings('etarget_hash');

            if ($etargetId != '') {
                $this->smarty->assign(array(
                    'etarget_id' => $etargetId,
                    'etarget_hash' => $etargetHash,
                ));

                $display .= $this->display(__FILE__, '/views/templates/front/etarget.tpl');
            }
        }

        return $display;
    }

}
