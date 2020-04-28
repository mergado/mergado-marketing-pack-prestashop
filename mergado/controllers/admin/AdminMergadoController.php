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

use Mergado\Tools\ImportPricesClass;
use Mergado\Tools\LogClass;
use Mergado\Tools\NewsClass;
use Mergado\Tools\XMLClass;
use Mergado\Tools\SettingsClass;
use ShopCore as Shop;
use ConfigurationCore as Configuration;
use ContextCore as Context;
use CurrencyCore as Currency;
use LanguageCore as Language;

require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/XMLClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/SettingsClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/NewsClass.php';

class AdminMergadoController extends \ModuleAdminController
{
    protected $languages;
    protected $currencies;
    protected $modulePath;
    protected $settingsValues;
    protected $defaultLang;

    protected $shopID;
    protected $disableFeatures = false;
    protected $disablePlugin = false;


    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'AdminMergado';
        $this->table = Mergado::MERGADO['TABLE_NAME'];
        $this->name = Mergado::MERGADO['MODULE_NAME'];
        $this->bootstrap = true;
        $this->languages = new Language();
        $this->currencies = new Currency();
        $this->modulePath = _PS_MODULE_DIR_ . 'mergado/';
        $this->defaultLang = Configuration::get('PS_LANG_DEFAULT');

        if (Shop::isFeatureActive()) {
            // Updating context in admin are ( to change shopID properly)
            $AdminController = new AdminController();
            $AdminController->initShopContext();

            // If not single shop selected => disable module settings
            if (substr(Context::getContext()->cookie->shopContext, 0, 1) !== 's') {
                $this->disableFeatures = true;
            }

            if (!Module::isEnabled($this->name)) {
                $this->disablePlugin = true;
            }
        }

        $this->shopID = Mergado::getShopId();

        $settingsTable = SettingsClass::getWholeSettings($this->shopID);
        foreach ($settingsTable as $s) {
            $this->settingsValues[$s['key']] = $s['value'];
        }

        parent::__construct();

        if (!Configuration::get('MERGADO_LOG_TOKEN')) {
            Configuration::updateValue('MERGADO_LOG_TOKEN', Tools::getAdminTokenLite('AdminMergadoLog'));
        }
    }

    /*******************************************************************************************************************
     * FORMS
     *******************************************************************************************************************/

    /**
     * Forms in Dev Tab in admin section
     *
     * @return mixed
     */
    public function formImportPrices()
    {
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Import cen'),
                'icon' => 'icon-flag',
            ),
            'description' => $this->l('Import cen z Mergado XML feedu.'),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'page'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_shop'
                ),
                array(
                    'label' => $this->l('Import prices feed URL'),
                    'name' => SettingsClass::IMPORT['URL'],
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Insert URL of import prices feed from Mergado webpage.'),
                    'visibility' => Shop::CONTEXT_ALL
                ),
                array(
                    'label' => $this->l('Number of products imported in one cron run'),
                    'name' => SettingsClass::IMPORT['COUNT'],
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Leave blank or 0 if you don\'t have problem with importing product prices.'),
                    'visibility' => Shop::CONTEXT_ALL
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_value = array(
//            SettingsClass::IMPORT['ENABLED'] => isset($this->settingsValues[SettingsClass::IMPORT['ENABLED']]) ? $this->settingsValues[SettingsClass::IMPORT['ENABLED']] : false,
            SettingsClass::IMPORT['COUNT'] => isset($this->settingsValues[SettingsClass::IMPORT['COUNT']]) ? $this->settingsValues[SettingsClass::IMPORT['COUNT']] : false,
            SettingsClass::IMPORT['URL'] => isset($this->settingsValues[SettingsClass::IMPORT['URL']]) ? $this->settingsValues[SettingsClass::IMPORT['URL']] : false,
            'page' => 1,
            'id_shop' => $this->shopID,
        );

        $this->show_toolbar = true;
        $this->show_form_cancel_button = false;

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;

        $helper->tpl_vars = array('fields_value' => $fields_value);
        $helper->default_form_language = $this->defaultLang;
        $helper->allow_employee_form_lang = $this->defaultLang;

        if (isset($this->displayName)) {
            $helper->title = $this->displayName;
        }

        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->submit_action = 'save' . $this->name;
            $helper->token = Tools::getValue('token');
        }

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {

        } else {
            $helper->toolbar_btn = array(
                'save' =>
                    array(
                        'desc' => $this->l('Save'),
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                            '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    ),
                'back' => array(
                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
                )
            );
        }

        return $helper->generateForm($fields_form);
    }

    /**
     * Forms in Dev Tab in admin section
     *
     * @return mixed
     */
    public function formDevelopers()
    {
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Help'),
                'icon' => 'icon-bug'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'page'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_shop'
                ),
                array(
                    'name' => 'mergado_dev_log',
                    'label' => $this->l('Enable log'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'class' => 'switch15',
                    'desc' => $this->l('Send this to support:') . " " . LogClass::getLogLite(),
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'values' => array(
                        array(
                            'id' => 'mergado_dev_log_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_dev_log_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL
                ),
                array(
                    'label' => $this->l('Delete log file when saving'),
                    'name' => 'mergado_del_log',
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_del_log_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_del_log_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_value = array(
            'mergado_dev_log' => isset($this->settingsValues['mergado_dev_log']) ? $this->settingsValues['mergado_dev_log'] : 0,
            'mergado_del_log' => 0,
            'page' => 4,
            'id_shop' => $this->shopID,
        );


        $this->show_toolbar = true;
        $this->show_form_cancel_button = false;

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;

        $helper->tpl_vars = array('fields_value' => $fields_value);
        $helper->default_form_language = $this->defaultLang;
        $helper->allow_employee_form_lang = $this->defaultLang;

        if (isset($this->displayName)) {
            $helper->title = $this->displayName;
        }

        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->submit_action = 'save' . $this->name;
            $helper->token = Tools::getValue('token');
        }

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {

        } else {
            $helper->toolbar_btn = array(
                'save' =>
                    array(
                        'desc' => $this->l('Save'),
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                            '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    ),
                'back' => array(
                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
                )
            );
        }

        return $helper->generateForm($fields_form);
    }

    /**
     * Forms in Export Tab in admin section
     *
     * @return mixed
     */
    public function formExportProducts()
    {

        $options = array(
            array(
                'id_option' => 'both',
                'name' => $this->l('Everywhere')
            ),
            array(
                'id_option' => 'catalog',
                'name' => $this->l('Catalog')
            ),
            array(
                'id_option' => 'search',
                'name' => $this->l('Search')
            )
        );

        $feedLang = array();
        $defaultValues = array();

        foreach ($this->languages->getLanguages(true) as $lang) {
            foreach ($this->currencies->getCurrencies(false, true, true) as $currency) {

                $feedLang = array_merge($feedLang, array(
                    array(
                        'label' => $lang['name'] . ' - ' . $currency['iso_code'],
                        'hint' => $this->l('Export to this language?'),
                        'name' => XMLClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'],
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                        'class' => 'switch15',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => XMLClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'] . '_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => XMLClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'] . '_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'visibility' => Shop::CONTEXT_ALL
                    ),
                ));

                if (isset($this->settingsValues[XMLClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code']])) {
                    $defaultValues = array_merge($defaultValues, array(
                        XMLClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'] => $this->settingsValues[XMLClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code']]
                    ));
                }
            }
        }

        $fields_value = $defaultValues;

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Language & currency settings'),
                'icon' => 'icon-flag'
            ),
            'description' => $this->l('Vyberte, které kombinace chcete aktivovat pro vytváření exportů.'),
            'input' => $feedLang,
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Generování exportu v dávkách'),
                'icon' => 'icon-bug'
            ),
            'description' => $this->l('Nastavte po kolika produktech se budou generovat jednotlivé dávky exportu. Ponechte prázdné pro vygenerování celého XML feedu najednou.'),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'page'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_shop'
                ),
                array(
                    'label' => $this->l('Number of products'),
                    'name' => SettingsClass::FEED['MAX_SIZE'],
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Leave blank or 0 if you don\'t have problem with generating mergado feed. Use lower number of products in one cron run if your feed is still too big and server cant generate it. Changing this value will delete all current temporary files!!!'),
                    'visibility' => Shop::CONTEXT_ALL
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Doplňková nastavení'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'clrCheckboxes'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'page'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_shop'
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Export visible in'),
                    'name' => 'what_to_export',
                    'values' => array(
                        'query' => $options,
                        'id' => 'id_option',
                        'name' => 'name'),
                    'hint' => $this->l('Choose which products will be exported by visibility.')
                ),
                array(
                    'label' => $this->l('Delivery days'),
                    'type' => 'text',
                    'name' => 'delivery_days',
                    'hint' => $this->l('In how many days can you delivery the product when it is out of stock'),
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Produkty, které nejsou skladem a zároveň je můžete objednat, budou mít v elementu "DELIVERY_DAYS" tuto hodnotu.'),
                    'visibility' => Shop::CONTEXT_ALL
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $optionsArray = array();
        foreach ($options as $option) {
            if (isset($this->settingsValues['what_to_export_' . $option['id_option']])) {
                $optionsArray = array_merge(
                    $optionsArray, array(
                        'what_to_export_' . $option['id_option'] => $this->settingsValues['what_to_export_' . $option['id_option']]
                    )
                );
            }
        }

        $fields_value = array(
            SettingsClass::FEED['MAX_SIZE'] => isset($this->settingsValues['partial_feeds_size']) ? $this->settingsValues['partial_feeds_size'] : false,
            'delivery_days' => isset($this->settingsValues['delivery_days']) ? $this->settingsValues['delivery_days'] : null,
            'clrCheckboxes' => 1,
            'page' => 1,
            'id_shop' => $this->shopID,
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;

        $helper->tpl_vars = array('fields_value' => array_merge($fields_value, $optionsArray, $defaultValues));

        if (isset($this->defaultLang)) {
            $helper->default_form_language = $this->defaultLang;
            $helper->allow_employee_form_lang = $this->defaultLang;
        }

        if (isset($this->displayName)) {
            $helper->title = $this->displayName;
        }
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->submit_action = 'save' . $this->name;
            $helper->token = Tools::getValue('token');
        }

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {

        } else {
            $helper->toolbar_btn = array(
                'save' =>
                    array(
                        'desc' => $this->l('Save'),
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                            '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    ),
                'back' => array(
                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
                )
            );
        }

        return $helper->generateForm($fields_form);
    }

    /**
     * Forms in Export Tab in admin section
     *
     * @return mixed
     */
    public function formExportStatic()
    {

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Mergado analytický feed'),
                'icon' => 'icon-flag'
            ),
            'description' => $this->l('Zapnutí exportu Mergado Analytického XML.'),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'page'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_shop'
                ),
                array(
                    'label' => $this->l('Export Mergado\'s anayltic feed?'),
                    'name' => SettingsClass::FEED['STATIC'],
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'static_feed_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'static_feed_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_value = array(
            'static_feed' => isset($this->settingsValues[SettingsClass::FEED['STATIC']]) ? $this->settingsValues[SettingsClass::FEED['STATIC']] : null,
            'clrCheckboxes' => 1,
            'page' => 1,
            'id_shop' => $this->shopID,
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;

        $helper->tpl_vars = array('fields_value' => $fields_value);

        if (isset($this->defaultLang)) {
            $helper->default_form_language = $this->defaultLang;
            $helper->allow_employee_form_lang = $this->defaultLang;
        }

        if (isset($this->displayName)) {
            $helper->title = $this->displayName;
        }
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->submit_action = 'save' . $this->name;
            $helper->token = Tools::getValue('token');
        }

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {

        } else {
            $helper->toolbar_btn = array(
                'save' =>
                    array(
                        'desc' => $this->l('Save'),
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                            '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    ),
                'back' => array(
                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
                )
            );
        }

        return $helper->generateForm($fields_form);
    }

    /**
     * Forms in Export Tab in admin section
     *
     * @return mixed
     */
    public function formExportCategory()
    {
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Mergado\'s category feed'),
                'icon' => 'icon-flag'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'page'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_shop'
                ),
                array(
                    'label' => $this->l('Export Mergado\'s category feed?'),
                    'name' => SettingsClass::FEED['CATEGORY'],
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'category_feed_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'category_feed_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_value = array(
            'category_feed' => isset($this->settingsValues[SettingsClass::FEED['CATEGORY']]) ? $this->settingsValues[SettingsClass::FEED['CATEGORY']] : null,
            'clrCheckboxes' => 1,
            'page' => 1,
            'id_shop' => $this->shopID,
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;

        $helper->tpl_vars = array('fields_value' => $fields_value);

        if (isset($this->defaultLang)) {
            $helper->default_form_language = $this->defaultLang;
            $helper->allow_employee_form_lang = $this->defaultLang;
        }

        if (isset($this->displayName)) {
            $helper->title = $this->displayName;
        }
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->submit_action = 'save' . $this->name;
            $helper->token = Tools::getValue('token');
        }

        if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {

        } else {
            $helper->toolbar_btn = array(
                'save' =>
                    array(
                        'desc' => $this->l('Save'),
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                            '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    ),
                'back' => array(
                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
                )
            );
        }

        return $helper->generateForm($fields_form);
    }

    /*******************************************************************************************************************
     * FORMS - ADSYS
     *******************************************************************************************************************/

    public function formAdSys_google() {
        include_once __DIR__ . '/adsys/google.php';
        return $helper->generateForm($fields_form);
    }

    public function formAdSys_facebook() {
        include_once __DIR__ . '/adsys/facebook.php';
        return $helper->generateForm($fields_form);
    }

    public function formAdSys_heureka() {
        include_once __DIR__ . '/adsys/heureka.php';
        return $helper->generateForm($fields_form);
    }

    public function formAdSys_etarget() {
        include_once __DIR__ . '/adsys/etarget.php';
        return $helper->generateForm($fields_form);
    }

    public function formAdSys_najnakupsk() {
        include_once __DIR__ . '/adsys/najnakupsk.php';
        return $helper->generateForm($fields_form);
    }

    public function formAdSys_seznam() {
        include_once __DIR__ . '/adsys/seznam.php';
        return $helper->generateForm($fields_form);
    }

    public function formAdSys_glami() {
        include_once __DIR__ . '/adsys/glami.php';
        return $helper->generateForm($fields_form);
    }

    public function formAdSys_pricemania() {
        include_once __DIR__ . '/adsys/pricemania.php';
        return $helper->generateForm($fields_form);
    }


    /**
     *  Prepare content for admin section
     */
    public function initContent()
    {
        $sql = 'SELECT `key` FROM `' . _DB_PREFIX_ . $this->table . '` WHERE `key` LIKE "';
        $sql .= pSQL(XMLClass::$feedPrefix) . '%" AND `value` = 1 AND `id_shop` = ' . $this->shopID;
        $feeds = Db::getInstance()->executeS($sql);

        $mergadoModule = new Mergado();
        $version = $mergadoModule->version;
        $remoteVersion = SettingsClass::getSettings(SettingsClass::NEW_MODULE_VERSION_AVAILABLE, 0);

        if(isset($_GET['mergadoTab']) && $_GET['mergadoTab'] == '7') {
            NewsClass::setArticlesShownByLanguage($this->context->language->iso_code);
        }

        $xmlClass = new XMLClass();

        $categoryFeed = SettingsClass::getSettings(SettingsClass::FEED['CATEGORY'], $this->shopID);
        $categoryCron = array();

        $langWithName = array();

        foreach ($feeds as $feed) {
            $iso = str_replace('category_', '', $feed['key']);
            $iso = str_replace(XMLClass::$feedPrefix, '', $iso);
            $iso = explode('-', $iso);

            $currencyId = CurrencyCore::getIdByIsoCode($iso[1], $this->shopID);
            $currency = CurrencyCore::getCurrency($currencyId);
            if(isset($currency) && $currency['active']) {
                if($xmlClass->getTotalFilesCount($this->shopID) === 0) {
                    $totalFiles = 0;
                } else {
                    $totalFiles = ceil(count($xmlClass->productsToFlat(0, 0, false)) / $xmlClass->getTotalFilesCount($this->shopID));
                }

                $langWithName['base'][$feed['key']] = array(
                    'totalFiles' => $totalFiles,
                    'currentFiles' => XMLClass::getTempNumber(XMLClass::TMP_DIR . 'xml/' . $this->shopID . '/' . $feed['key'] . '/'),
                    'xml' => $feed['key'],
                    'url' => $this->getCronUrl($feed['key']),
                    'name' => $this->languages->getLanguageByIETFCode(
                            $this->languages->getLanguageCodeByIso($iso[0])
                        )->name . ' - ' . $iso[1]
                );

                if ($categoryFeed == "1") {
                    $categoryCron[] = array(
                        'xml' => 'category_' . $feed['key'],
                        'url' => $this->getCronUrl('category_' . $feed['key']),
                        'name' => $this->languages->getLanguageByIETFCode(
                                $this->languages->getLanguageCodeByIso($iso[0])
                            )->name . ' - ' . $iso[1]);
                }
            }
        }

        $stockFeed = SettingsClass::getSettings('mergado_heureka_dostupnostni_feed', $this->shopID);
        if ($stockFeed) {
            $langWithName['stock'][] = array(
                'xml' => 'stock',
                'url' => $this->getCronUrl('stock'),
                'name' => $this->l('Stock feed')
            );
        }

        $files = glob($this->modulePath . 'xml/' . $this->shopID . '/*xml');

        $xmlList = array();
        if (is_array($files)) {
            foreach ($files as $filename) {
                $tmpName = str_replace(XMLClass::$feedPrefix, '', basename($filename, '.xml'));
                $name = explode('-', $tmpName);
                if(isset($name[1])) {
                    $name[1] = explode('_', $name[1]);
                } else {
                    $name[1] = null;
                }
                $codedName = explode('_', $tmpName);
                $code = Tools::strtoupper(
                    '_' . Tools::substr(hash('md5', $codedName[0] . Configuration::get('PS_SHOP_NAME')), 1, 11)
                );

                if ($codedName[0] == 'stock') {
                    $xmlList['stock'][substr(basename($filename), 0, strrpos( basename($filename), '_'))] = array(
                        'language' => $this->l('Stock feed'),
                        'url' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . $this->shopID . '/' . basename($filename),
                        'file' => $this->shopID . '/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                } else if ($codedName[0] == 'static') {
                    $xmlList['static'][substr(basename($filename), 0, strrpos( basename($filename), '_'))] = array(
                        'language' => $this->l('Mergado analytic feed'),
                        'url' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . $this->shopID . '/' . basename($filename),
                        'file' => $this->shopID . '/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                } else if ($codedName[0] == 'category') {
                    $name[0] = str_replace('category_', '', $name[0]);
                    $xmlList['category'][substr(basename($filename), 0, strrpos( basename($filename), '_'))] = array(
                        'language' => str_replace(
                            $code, '', $this->languages->getLanguageByIETFCode(
                                $this->languages->getLanguageCodeByIso($name[0])
                            )->name . ' - ' . Tools::strtoupper($name[1][0])
                        ),
                        'url' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . $this->shopID . '/' . basename($filename),
                        'file' => $this->shopID . '/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                } else {
                    $xmlList['base'][substr(basename($filename), 0, strrpos( basename($filename), '_'))] = array(
                        'language' => str_replace(
                            $code, '', $this->languages->getLanguageByIETFCode(
                                $this->languages->getLanguageCodeByIso($name[0])
                            )->name . ' - ' . Tools::strtoupper($name[1][0])
                        ),
                        'url' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . $this->shopID . '/' . basename($filename),
                        'file' => $this->shopID . '/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                }
            }
        }

        $newXml = array();

        if (isset($xmlList['base'])) {
            $newXml['base'] = $xmlList['base'];
        }

        if (isset($xmlList['static'])) {
            $newXml['static'] = $xmlList['static'];
        }

        if (isset($xmlList['stock'])) {
            $newXml['stock'] = $xmlList['stock'];
        }

        if (isset($xmlList['category'])) {
            $newXml['category'] = $xmlList['category'];
        }

        $cookieNewsTime = SettingsClass::getSettings(SettingsClass::COOKIE_NEWS, 0);

        if($cookieNewsTime != 0) {
            $cookieNews = new DateTime($cookieNewsTime);
        } else {
            $cookieNews = new DateTime();
        }

        $this->context->smarty->assign(array(
            'crons' => $langWithName,
            'cronsPartial' => (bool) ($xmlClass->getTotalFilesCount($this->shopID) > 0),
            'importCron' => $this->getImportCronUrl(),
            'setSettings' => $this->getBaseUrl() . '/modules/' . $this->name . '/setSettings.php?' . '&token=' . Tools::substr(Tools::encrypt('mergado/setSettings'), 0, 10),
            'categoryCron' => $categoryCron,
            'xmls' => $newXml,
            'moduleUrl' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/',
            'moduleVersion' => $version,
            'remoteVersion' => $remoteVersion,
            'unreadedNews' => NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code, 'news', 1,true, 'DESC'),
            'unreadedUpdates' => NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code, 'update', 1,true, 'DESC'),
            'unreadedTopNews' => NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code, 'TOP'),
            'disableFeatures' => $this->disableFeatures,
            'disablePlugin' => $this->disablePlugin,
            'lang' => SettingsClass::getLangIso(),
            'domain_type' => SettingsClass::LANG_TO_DOMAIN[strtolower(SettingsClass::getLangIso())],
            'cookieNews' => $cookieNews,
            'now' => new DateTime(),
            'toggleFieldsJSON' => $this->toggleFieldsJSON(),
            'formattedDate' => NewsClass::DATE_OUTPUT_FORMAT, // Because of ps1.6 smarty
        ));

        parent::initContent();

        //TAB1
        $tab1 = [
            'exportProducts' => $this->formExportProducts(),
            'exportStatic' => $this->formExportStatic(),
            'exportCategory' => $this->formExportCategory(),
            'importPrices' => $this->formImportPrices(),
        ];

        //TAB6
        $tab6 = [
            'google' => ['title' => $this->l('Google'), 'form' => $this->formAdSys_google(), 'active' => true],
            'facebook' => ['title' => $this->l('Facebook'), 'form' => $this->formAdSys_facebook()],
            'heureka' => ['title' => $this->l('Heureka'),'form' => $this->formAdSys_heureka()],
            'glami' => ['title' => $this->l('GLAMI'),'form' => $this->formAdSys_glami()],
            'seznam' => ['title' => $this->l('Seznam'),'form' => $this->formAdSys_seznam()],
            'etarget' => ['title' => $this->l('Etarget'),'form' => $this->formAdSys_etarget()],
            'najnakupsk' => ['title' => $this->l('Najnakup.sk'), 'form' => $this->formAdSys_najnakupsk()],
            'pricemania' => ['title' => $this->l('Pricemania'), 'form' => $this->formAdSys_pricemania()],
        ];

        $tab4 = $this->formDevelopers();

        $tab7 = NewsClass::getNewsWithFormatedDate($this->context->language->iso_code, 15);

        $this->context->smarty->assign(array(
            'tab1' => $tab1,
            'tab6' => $tab6,
            'tab4' => $tab4,
            'tab7' => $tab7,
            'noMessages' => $this->l('No new messages'),
            'staticFeed' => SettingsClass::getSettings(SettingsClass::FEED['STATIC'], $this->shopID),
            'categoryFeed' => SettingsClass::getSettings(SettingsClass::FEED['CATEGORY'], $this->shopID),
        ));

        try {
            $this->context->smarty->assign(array(
                'sideAd' => file_get_contents('https://platforms.mergado.com/prestashop/sidebar'),
                'wideAd' => file_get_contents('https://platforms.mergado.com/prestashop/wide'),
            ));
        } catch (Exception $e){
            
        }
    }


    /*******************************************************************************************************************
     * TOGGLE FIELDS
     *******************************************************************************************************************/

    /**
     * EXAMPLE:
     *  'namee' => [
     *    'fields' => [],
     *    'sub-check' => [
     *      'name' => [
     *        'name', 'name', 'name'
     *      ]
     *    ]
     *  ],
     *
     * @return false|string
     */

    public function toggleFieldsJSON() {
        $glamiFields = [];
        $glamiTOPFields = [];
        $glamiMainFields = array_values(SettingsClass::GLAMI_LANGUAGES);
        $glamiMainTOPFields = array_values(SettingsClass::GLAMI_TOP_LANGUAGES);

        foreach(SettingsClass::GLAMI_LANGUAGES as $key => $values) {
            $glamiFields[$values]['fields'] = [SettingsClass::GLAMI['CODE'] . '-' . $key];
        }

        foreach(SettingsClass::GLAMI_TOP_LANGUAGES as $key => $values) {
            $glamiTOPFields[$values]['fields'] = [SettingsClass::GLAMI['CODE_TOP'] . '-' . $key];
        }

        $jsonMap = [
            // Google
            SettingsClass::GOOGLE_ADS['CONVERSIONS'] => [
                'fields' => [SettingsClass::GOOGLE_ADS['CONVERSIONS_CODE'], SettingsClass::GOOGLE_ADS['CONVERSIONS_LABEL']],
            ],
            SettingsClass::GOOGLE_ADS['REMARKETING'] => [
                'fields' => [SettingsClass::GOOGLE_ADS['REMARKETING_ID']]
            ],

            // Facebook
            SettingsClass::FB_PIXEL['ACTIVE'] => [
                'fields' => [SettingsClass::FB_PIXEL['CODE']]
            ],

            // Heureka
            SettingsClass::HEUREKA['VERIFIED_CZ'] => [
                'fields' => [SettingsClass::HEUREKA['VERIFIED_CODE_CZ'], SettingsClass::HEUREKA['WIDGET_CZ']]
            ],
            SettingsClass::HEUREKA['VERIFIED_SK'] => [
                'fields' => [SettingsClass::HEUREKA['VERIFIED_CODE_SK'], SettingsClass::HEUREKA['WIDGET_SK']]
            ],
            SettingsClass::HEUREKA['CONVERSIONS_CZ'] => [
                'fields' => [SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ']]
            ],
            SettingsClass::HEUREKA['CONVERSIONS_SK'] => [
                'fields' => [SettingsClass::HEUREKA['CONVERSIONS_CODE_SK']
                ]
            ],

            // GLAMI
            SettingsClass::GLAMI['ACTIVE'] => [
                'fields' => $glamiMainFields,
                'sub-check' => $glamiFields,
            ],
            SettingsClass::GLAMI['ACTIVE_TOP'] => [
                'fields' => $glamiMainTOPFields,
                'sub-check' => $glamiTOPFields,
            ],

            // Seznam
            SettingsClass::SKLIK['CONVERSIONS'] => [
                'fields' => [
                    SettingsClass::SKLIK['CONVERSIONS_CODE'],
                    SettingsClass::SKLIK['CONVERSIONS_VALUE'],
                ]
            ],
            SettingsClass::SKLIK['RETARGETING'] => [
                'fields' => [
                    SettingsClass::SKLIK['RETARGETING_ID']
                ]
            ],
            // ZBOZI
            SettingsClass::ZBOZI['CONVERSIONS'] => [
                'fields' => [
                    SettingsClass::ZBOZI['CONVERSIONS_ADVANCED'],
                    SettingsClass::ZBOZI['SHOP_ID'],
                    SettingsClass::ZBOZI['SECRET'],
                ]
            ],

            // Etarget
            SettingsClass::ETARGET['ACTIVE'] => [
                'fields' => [
                    SettingsClass::ETARGET['ID'],
                    SettingsClass::ETARGET['HASH']
                ]
            ],

            // Najnakup.sk
            SettingsClass::NAJNAKUP['CONVERSIONS'] => [
                'fields' => [
                    SettingsClass::NAJNAKUP['SHOP_ID'],
                ]
            ],

            // Pricemania
            SettingsClass::PRICEMANIA['VERIFIED'] => [
                'fields' => [
                    SettingsClass::PRICEMANIA['SHOP_ID'],
                ]
            ],
        ];

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
        if(isset($_POST['upgradeModule'])) {
            $mergado = new Mergado();

            if($mergado->updateModule()) {
                $mergado->updateVersionXml();
            }
        }

        if (Tools::isSubmit('submit' . $this->name)) {
            unset($_POST['submit' . $this->name]);

            LogClass::log("Settings saved:\n" . json_encode($_POST) . "\n");

            if (isset($_POST['clrCheckboxes'])) {
                SettingsClass::clearSettings(SettingsClass::EXPORT['BOTH'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['CATALOG'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['SEARCH'], $this->shopID);
            }

            if (isset($_POST['mergado_del_log']) && $_POST['mergado_del_log'] === SettingsClass::ENABLED) {
                LogClass::deleteLog();
            }

            if (isset($_POST['partial_feeds_size']) && $_POST['partial_feeds_size'] !== SettingsClass::getSettings('partial_feeds_size', $_POST['id_shop'])) {
                XMLClass::removeFilesInDirectory(XMLClass::TMP_DIR . 'xml/' . $_POST['id_shop'] . '/');
            }

            if(isset($_POST['id_shop'])) {
                $shopID = $_POST['id_shop'];
                unset($_POST['id_shop']);

                $changed = true;
                foreach ($_POST as $key => $value) {
                    if(SettingsClass::getSettings($key, $shopID) != $value) {
                        SettingsClass::saveSetting($key, $value, $shopID);
                        LogClass::log('Settings value edited: ' . $key . ' => ' . $value);
                        $changed = false;
                    }

                }

                if($changed) {
                    LogClass::log('No value changed during save');
                }
            }

            $this->redirect_after = self::$currentIndex . '&token=' . $this->token . (Tools::isSubmit('submitFilter' . $this->list_id) ? '&submitFilter' . $this->list_id . '=' . (int)Tools::getValue('submitFilter' . $this->list_id) : '') . '&mergadoTab=' . $_POST['page'];
        }

        if (Tools::isSubmit('submit' . $this->name . 'delete')) {
            if (isset($_POST['delete_url'])) {
                if (file_exists(XMLClass::XML_DIR . $_POST['delete_url'])) {
                    unlink(XMLClass::XML_DIR . $_POST['delete_url']);
                }
            }
        }

        if(isset($_POST['controller']) && $_POST['controller'] === 'AdminMergado') {
            if($_POST['action'] === 'mergadoNews') {
                $this->ajaxProcessMergadoNews();
            } elseif($_POST['action'] === 'disableNotification') {

                $this->ajaxProcessMergadoDisableNews($_POST['ids']);
            }

            if (in_array($_POST['action'], array('generate_xml'))) {
                $mergado = new XMLClass();
                $generated = $mergado->generateMergadoFeed($_POST['feedBase']);

                if ($generated && $generated !== 'running') {
                    echo true;
                } elseif ($generated == 'running') {
                    echo $generated;
                } else {
                    echo false;
                }
            } elseif ($_POST['action'] === 'import_prices') {
                $pricesClass = new ImportPricesClass();
                $generated = $pricesClass->importPrices();

                if($generated) {
                    echo true;
                } else {
                    echo false;
                }
            } elseif ($_POST['action'] === 'mmp-cookie-news') {
                $now = new DateTime();
                $date = $now->modify('+14 days')->format(NewsClass::DATE_FORMAT);
                SettingsClass::saveSetting(SettingsClass::COOKIE_NEWS, $date, 0);
            }
        }
    }

    public function ajaxProcessMergadoNews()
    {
        echo json_encode(NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code));
        exit;
    }

    public function ajaxProcessMergadoDisableNews($ids)
    {
        NewsClass::setArticlesShown($ids);
    }

    /*******************************************************************************************************************
     * GET URL
     *******************************************************************************************************************/

    /**
     * Returns url for crons
     *
     * @param $key
     * @return string
     */
    public function getCronUrl($key)
    {
        if (Shop::isFeatureActive()) {
            return $this->getMultistoreShopUrl() . 'modules/' . $this->name . '/cron.php?feed=' . $key .
                '&token=' . Tools::substr(Tools::encrypt('mergado/cron'), 0, 10);
        } else {
            return $this->getBaseUrl() . '/modules/' . $this->name . '/cron.php?feed=' . $key .
                '&token=' . Tools::substr(Tools::encrypt('mergado/cron'), 0, 10);
        }
    }

    public function getImportCronUrl()
    {
        if (Shop::isFeatureActive()) {
            return $this->getMultistoreShopUrl() . 'modules/' . $this->name . '/importPrices.php?' .
                '&token=' . Tools::substr(Tools::encrypt('mergado/importPrices'), 0, 10);
        } else {
            return $this->getBaseUrl() . '/modules/' . $this->name . '/importPrices.php?' .
                '&token=' . Tools::substr(Tools::encrypt('mergado/importPrices'), 0, 10);
        }
    }

    /**
     * Return Base url of MainShop
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' . Tools::getShopDomain(false, true);
    }

    /**
     * Return Specific ShopUrl with domains, physical urls and virtual urls
     *
     * @return string
     */
    public function getMultistoreShopUrl()
    {
        $shop_urls = array();

        foreach (ShopUrlCore::getShopUrls() as $shopUrl) {
            if ($shopUrl->id_shop == $this->shopID) {
                $shop_urls['domain'] = $shopUrl->domain;
                $shop_urls['physical_uri'] = $shopUrl->physical_uri;
                $shop_urls['virtual_uri'] = $shopUrl->virtual_uri;
            }
        }

        return 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' . $shop_urls['domain'] . $shop_urls['physical_uri'] . $shop_urls['virtual_uri'];
    }
}
