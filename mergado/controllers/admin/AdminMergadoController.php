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
use CacheCore as Cache;

require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/XMLClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/SettingsClass.php';

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
                'title' => $this->l('Import prices from mergado - optimalization'),
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
                    'label' => $this->l('Import prices feed URL'),
                    'name' => SettingsClass::IMPORT['URL'],
                    'type' => 'text',
                    'desc' => $this->l('Insert URL of import prices feed from Mergado webpage.'),
                    'visibility' => Shop::CONTEXT_ALL
                ),
                array(
                    'label' => $this->l('Number of products imported in one cron run'),
                    'name' => SettingsClass::IMPORT['COUNT'],
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'desc' => $this->l('Leave blank or 0 if you don\'t have problem with importing product prices.'),
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
            'page' => 2,
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
    public function formCrons()
    {
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Base mergado feed optimalization'),
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
                    'label' => $this->l('Number of products'),
                    'name' => SettingsClass::FEED['MAX_SIZE'],
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'desc' => $this->l('Leave blank or 0 if you don\'t have problem with generating mergado feed. Use lower number of products in one cron run if your feed is still too big and server cant generate it. Changing this value will delete all current temporary files!!!'),
                    'visibility' => Shop::CONTEXT_ALL
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_value = array(
            SettingsClass::FEED['MAX_SIZE'] => isset($this->settingsValues['partial_feeds_size']) ? $this->settingsValues['partial_feeds_size'] : false,
            'page' => 2,
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
    public function formExportSettings()
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
            'input' => $feedLang,
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Mergado\'s category feed'),
                'icon' => 'icon-flag'
            ),
            'input' => array(
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

        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Mergado\'s static feed'),
                'icon' => 'icon-flag'
            ),
            'input' => array(
                array(
                    'label' => $this->l('Export Mergado\'s static feed?'),
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

        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Export settings'),
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
            'delivery_days' => isset($this->settingsValues['delivery_days']) ? $this->settingsValues['delivery_days'] : null,
            'static_feed' => isset($this->settingsValues[SettingsClass::FEED['STATIC']]) ? $this->settingsValues[SettingsClass::FEED['STATIC']] : null,
            'category_feed' => isset($this->settingsValues[SettingsClass::FEED['CATEGORY']]) ? $this->settingsValues[SettingsClass::FEED['CATEGORY']] : null,
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
     * Forms in AdSys Tab in admin section
     *
     * @return mixed
     */
    public function formAdSys()
    {

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Heureka'),
                'icon' => 'icon-cogs'
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
                    'name' => SettingsClass::HEUREKA['VERIFIED_CZ'],
                    'label' => $this->l('Heureka.cz verified by users'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_heureka_overeno_zakazniky_cz_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_heureka_overeno_zakazniky_cz_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL
                ),
                array(
                    'name' => SettingsClass::HEUREKA['VERIFIED_CODE_CZ'],
                    'label' => $this->l('Heureka.cz verified by users code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::HEUREKA['VERIFIED_SK'],
                    'label' => $this->l('Heureka.sk verified by users'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_heureka_overeno_zakazniky_sk_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_heureka_overeno_zakazniky_sk_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::HEUREKA['VERIFIED_CODE_SK'],
                    'label' => $this->l('Heureka.sk verified by users code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::HEUREKA['CONVERSIONS_CZ'],
                    'label' => $this->l('Heureka.cz track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_heureka_konverze_cz_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_heureka_konverze_cz_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ'],
                    'label' => $this->l('Heureka.cz conversion code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::HEUREKA['CONVERSIONS_SK'],
                    'label' => $this->l('Heureka.sk track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_heureka_konverze_sk_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_heureka_konverze_sk_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::HEUREKA['CONVERSIONS_CODE_SK'],
                    'label' => $this->l('Heureka.sk conversion code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::HEUREKA['WIDGET_CZ'],
                    'label' => $this->l('Heureka.cz - widget'),
                    'hint' => $this->l('You need conversion code to enable this feature'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_heureka_widget_cz_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_heureka_widget_cz_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::HEUREKA['WIDGET_SK'],
                    'label' => $this->l('Heureka.sk - widget'),
                    'hint' => $this->l('You need conversion code to enable this feature'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_heureka_widget_sk_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_heureka_widget_sk_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'mergado_heureka_dostupnostni_feed',
                    'label' => $this->l('Heureka stock feed'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_heureka_dostupnostni_feed_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_heureka_dostupnostni_feed_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Zbozi.cz'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => SettingsClass::ZBOZI['CONVERSIONS'],
                    'label' => $this->l('Zbozi track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_zbozi_konverze_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_zbozi_konverze_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::ZBOZI['CONVERSIONS_ADVANCED'],
                    'label' => $this->l('Standard conversion measuring'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_zbozi_advanced_konverze_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_zbozi_advanced_konverze_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::ZBOZI['SHOP_ID'],
                    'label' => $this->l('Zbozi shop ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::ZBOZI['SECRET'],
                    'label' => $this->l('Secret key'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Najnakup.sk'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => SettingsClass::NAJNAKUP['CONVERSIONS'],
                    'label' => $this->l('Najnakup track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_najnakup_konverze_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_najnakup_konverze_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::NAJNAKUP['SHOP_ID'],
                    'label' => $this->l('Najnakup shop ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Pricemania'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => SettingsClass::PRICEMANIA['VERIFIED'],
                    'label' => $this->l('Verified shop'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_pricemania_overeny_obchod_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_pricemania_overeny_obchod_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::PRICEMANIA['SHOP_ID'],
                    'label' => $this->l('Pricemania shop ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('Sklik'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => SettingsClass::SKLIK['CONVERSIONS'],
                    'label' => $this->l('Sklik track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_sklik_konverze_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_sklik_konverze_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::SKLIK['CONVERSIONS_CODE'],
                    'label' => $this->l('Sklik conversion code'),
                    'desc' => $this->l('You can find the code in Sklik → Tools → Conversion Tracking → Conversion Detail / Create New Conversion. The code is in the generated HTML conversion code after: src = "// c.imedia.cz/checkConversion?c=CONVERSION CODE'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::SKLIK['CONVERSIONS_VALUE'],
                    'label' => $this->l('Sklik value'),
                    'type' => 'text',
                    'desc' => $this->l('Leave blank to fill the order value automatically. Total price excluding VAT and shipping is calculated.'),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::SKLIK['RETARGETING'],
                    'label' => $this->l('Sklik retargting'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'seznam_retargeting_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'seznam_retargeting_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::SKLIK['RETARGETING_ID'],
                    'label' => $this->l('Sklik retargeting ID'),
                    'type' => 'text',
                    'desc' => $this->l('The code can be found in Sklik → Tools → Retargeting → View retargeting code. The code is in the generated script after: var list_retargeting_id = RETARGETING CODE'),
                    'visibility' => Shop::CONTEXT_ALL,
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('GoogleAds'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => SettingsClass::GOOGLE_ADS['CONVERSIONS'],
                    'label' => $this->l('GoogleAds conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'mergado_adwords_conversion_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'mergado_adwords_conversion_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::GOOGLE_ADS['CONVERSIONS_CODE'],
                    'label' => $this->l('GoogleAds conversion code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::GOOGLE_ADS['CONVERSIONS_LABEL'],
                    'label' => $this->l('GoogleAds conversion label'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::GOOGLE_ADS['REMARKETING'],
                    'label' => $this->l('GoogleAds remarketing'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'adwords_remarketing_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'adwords_remarketing_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::GOOGLE_ADS['REMARKETING_ID'],
                    'label' => $this->l('GoogleAds remarketing ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[6]['form'] = array(
            'legend' => array(
                'title' => $this->l('Facebook pixel'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => SettingsClass::FB_PIXEL['ACTIVE'],
                    'label' => $this->l('Facebook pixel'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'fb_pixel_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'fb_pixel_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::FB_PIXEL['CODE'],
                    'label' => $this->l('Facebook pixel ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[7]['form'] = array(
            'legend' => array(
                'title' => $this->l('Etarget'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => SettingsClass::ETARGET['ACTIVE'],
                    'label' => $this->l('ETARGET'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                    'class' => 'switch15',
                    'values' => array(
                        array(
                            'id' => 'etarget_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'etarget_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::ETARGET['ID'],
                    'label' => $this->l('ETARGET ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => SettingsClass::ETARGET['HASH'],
                    'label' => $this->l('Hash'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[8]['form'] = array(
            'legend' => array(
                'title' => $this->l('Glami pixel'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(

            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[8]['form']['input'][] = array(
            'name' => SettingsClass::GLAMI['ACTIVE'],
            'label' => $this->l('Module active'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'glami_active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'glami_active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        );

        foreach(SettingsClass::GLAMI_LANGUAGES as $key => $lang) {
            $fields_form[8]['form']['input'][] = array(
                'name' => $lang,
                'label' => $key,
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                'class' => 'switch15',
                'values' => array(
                    array(
                        'id' => 'glami_active_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'glami_active_off',
                        'value' => 0,
                        'label' => $this->l('No')
                    )
                ),
                'visibility' => Shop::CONTEXT_ALL,
            );

            $fields_form[8]['form']['input'][] = array(
                'name' => SettingsClass::GLAMI['CODE'] . '-' . $key,
                'label' => $this->l('Glami Pixel') . ' ' . $key,
                'type' => 'text',
                'visibility' => Shop::CONTEXT_ALL,
            );
        }

        $fields_form[9]['form'] = array(
            'legend' => array(
                'title' => $this->l('Glami TOP'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(

            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[9]['form']['input'][] = array(
            'name' => SettingsClass::GLAMI['ACTIVE_TOP'],
            'label' => $this->l('Module active'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'desc' => $this->l('1. Your website must have HTTPS protocol at least on order confirmation page. 2. You have to set your DNS before use. More informations on: https://www.glami.cz/info/reviews/implementation/'),
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'glami_top_active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'glami_top_active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        );

        foreach(SettingsClass::GLAMI_TOP_LANGUAGES as $key => $lang) {
            $fields_form[9]['form']['input'][] = array(
                'name' => $lang,
                'label' => $key,
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                'class' => 'switch15',
                'values' => array(
                    array(
                        'id' => 'glami_top_active_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'glami_top_active_off',
                        'value' => 0,
                        'label' => $this->l('No')
                    )
                ),
                'visibility' => Shop::CONTEXT_ALL,
            );

            $fields_form[9]['form']['input'][] = array(
                'name' => SettingsClass::GLAMI['CODE_TOP'] . '-' . $key,
                'label' => $this->l('Glami TOP') . ' ' . $key,
                'type' => 'text',
                'visibility' => Shop::CONTEXT_ALL,
            );
        }

        $fields_value = [
            'page' => 6,
            'id_shop' => $this->shopID,
        ];

        foreach ($this->settingsValues as $key => $value) {
            if(!isset($fields_value[$key])) {
                $fields_value[$key] = $value;
            }
        }


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

        if($_GET['mergadoTab'] == '7') {
            NewsClass::setArticlesShownByLanguage($this->context->language->iso_code);
        }

        $categoryFeed = SettingsClass::getSettings(SettingsClass::FEED['CATEGORY'], $this->shopID);
        $categoryCron = array();
        $langWithName = array();
        foreach ($feeds as $feed) {
            $iso = str_replace('category_', '', $feed['key']);
            $iso = str_replace(XMLClass::$feedPrefix, '', $iso);
            $iso = explode('-', $iso);

            $xmlClass = new XMLClass();

            if($xmlClass->getTotalFilesCount($this->shopID) === 0) {
                $totalFiles = 0;
            } else {
                $totalFiles = ceil(count($xmlClass->productsToFlat(0, 0, false)) / $xmlClass->getTotalFilesCount($this->shopID));
            }

            $langWithName[] = array(
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

        $stockFeed = SettingsClass::getSettings('mergado_heureka_dostupnostni_feed', $this->shopID);
        if ($stockFeed) {
            $langWithName[] = array(
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
                    $xmlList['stock'][] = array(
                        'language' => $this->l('Stock feed'),
                        'url' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . $this->shopID . '/' . basename($filename),
                        'file' => $this->shopID . '/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                } else if ($codedName[0] == 'static') {
                    $xmlList['static'][] = array(
                        'language' => $this->l('Mergado static feed'),
                        'url' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . $this->shopID . '/' . basename($filename),
                        'file' => $this->shopID . '/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                } else if ($codedName[0] == 'category') {
                    $name[0] = str_replace('category_', '', $name[0]);
                    $xmlList['category'][] = array(
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
                    $xmlList['base'][] = array(
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

        $this->context->smarty->assign(array(
            'crons' => $langWithName,
            'importCron' => $this->getImportCronUrl(),
            'categoryCron' => $categoryCron,
            'xmls' => $newXml,
            'moduleUrl' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/',
            'moduleVersion' => $version,
            'remoteVersion' => $remoteVersion,
            'unreadedNews' => NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code, ''),
            'unreadedTopNews' => NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code, 'TOP'),
            'disableFeatures' => $this->disableFeatures,
            'disablePlugin' => $this->disablePlugin,
        ));

        parent::initContent();

        $tab1 = $this->formExportSettings();
        $tab6 = $this->formAdSys();
        $tab4 = $this->formDevelopers();
        $tab2 = $this->formCrons();
        $tab2Import = $this->formImportPrices();

        $tab7 = NewsClass::getNewsWithFormatedDate($this->context->language->iso_code, 15);

        $this->context->smarty->assign(array(
            'tab1' => $tab1,
            'tab2' => $tab2,
            'tab2Import' => $tab2Import,
            'tab6' => $tab6,
            'tab4' => $tab4,
            'tab7' => $tab7,
            'noMessages' => $this->l('No new messages'),
            'staticFeed' => SettingsClass::getSettings(SettingsClass::FEED['STATIC'], $this->shopID),
            'categoryFeed' => SettingsClass::getSettings(SettingsClass::FEED['CATEGORY'], $this->shopID),
        ));
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
                SettingsClass::clearSettings('what_to_export', $this->shopID);
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
