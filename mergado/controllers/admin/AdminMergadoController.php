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

use Mergado\Arukereso\ArukeresoClass;
use Mergado\Biano\BianoClass;
use Mergado\Etarget\EtargetClass;
use Mergado\Facebook\FacebookClass;
use Mergado\Google\GaRefundClass;
use Mergado\Google\GoogleReviewsClass;
use Mergado\Google\GoogleAdsClass;
use Mergado\Google\GoogleTagManagerClass;
use Mergado\Kelkoo\KelkooClass;
use Mergado\NajNakup\NajNakupClass;
use Mergado\Sklik\SklikClass;
use Mergado\Tools\ImportPricesClass;
use Mergado\Tools\LogClass;
use Mergado\Tools\NewsClass;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLQuery;
use Mergado\Tools\XML\XMLStockFeed;
use Mergado\Tools\XMLClass;
use Mergado\Tools\SettingsClass;
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
                'title' => $this->l('Price import'),
                'icon' => 'icon-flag',
            ),
            'description' => $this->l('Price import from Mergado XML feed'),
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

        //Fill in empty fields
        include __DIR__ . '/partials/helperFormEmptyFieldsFiller.php';

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

        return @$helper->generateForm($fields_form);
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

        //Fill in empty fields
        include __DIR__ . '/partials/helperFormEmptyFieldsFiller.php';

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

        return @$helper->generateForm($fields_form);
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
            'description' => $this->l('Select which combinations you want to activate for generating exports.'),
            'input' => $feedLang,
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submit' . $this->name
            )
        );

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Generate export in batches'),
                'icon' => 'icon-bug'
            ),
            'description' => $this->l('Set how many products will be generated per export batch. Leave blank to generate the entire XML feed at once.'),
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
                    'label' => $this->l('Number of products for Mergado Feed'),
                    'name' => XMLProductFeed::MAX_PRODUCTS,
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Leave blank or 0 if you don\'t have problem with generating mergado feed. Use lower number of products in one cron run if your feed is still too big and server cant generate it. Changing this value will delete all current temporary files!!!'),
                    'visibility' => Shop::CONTEXT_ALL
                ),
                array(
                    'label' => $this->l('Number of products for Heureka stock feed'),
                    'name' => XMLStockFeed::MAX_PRODUCTS,
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Leave blank or 0 if you don\'t have problem with generating Heureka stock feed. Use lower number of products in one cron run if your feed is still too big and server cant generate it. Changing this value will delete all current temporary files!!!'),
                    'visibility' => Shop::CONTEXT_ALL
                ),
                array(
                    'label' => $this->l('Number of categories for Category feed'),
                    'name' => XMLCategoryFeed::MAX_PRODUCTS,
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Leave blank or 0 if you don\'t have problem with generating category feed. Use lower number of categories in one cron run if your feed is still too big and server cant generate it. Changing this value will delete all current temporary files!!!'),
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
                'title' => $this->l('Additional settings'),
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
                    'label' => $this->l('Export cost elements'),
                    'name' => 'm_export',
                    'values' => array(
                        'query' =>
                            array(
                                array(
                                    'id_option' => 'wholesale_prices',
                                    'name' => $this->l('Yes')
                                ),
                            ),
                        'id' => 'id_option',
                        'name' =>'name'
                    ),
                    'hint' => $this->l('Choose whether to export COST and COST_VAT elements to the product feed.')
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Export products with denied orders in Product feeds'),
                    'name' => 'mmp_export',
                    'values' => array(
                        'query' =>
                            array(
                                array(
                                    'id_option' => 'denied_products',
                                    'name' => $this->l('Yes')
                                ),
                            ),
                        'id' => 'id_option',
                        'name' =>'name'
                    ),
                    'hint' => $this->l('By default, the module generates only products with allowed orders. By enabling this option, the module will also generate products with denied orders')
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Export products with denied orders in Other feeds'),
                    'name' => 'mmp_export',
                    'values' => array(
                        'query' =>
                            array(
                                array(
                                    'id_option' => 'denied_products_other',
                                    'name' => $this->l('Yes')
                                ),
                            ),
                        'id' => 'id_option',
                        'name' =>'name'
                    ),
                    'hint' => $this->l('By default, the module generates only products with allowed orders. By enabling this option, the module will also generate products with denied orders')
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
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span><strong>' . $this->l('If not filled in, the value from the field "Label of out-of-stock products with allowed backorders"') . '</strong>',
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
            XMLProductFeed::MAX_PRODUCTS => isset($this->settingsValues['partial_feeds_size']) ? $this->settingsValues['partial_feeds_size'] : false,
            XMLStockFeed::MAX_PRODUCTS => isset($this->settingsValues['partial_feeds_stock_size']) ? $this->settingsValues['partial_feeds_stock_size'] : false,
            XMLCategoryFeed::MAX_PRODUCTS => isset($this->settingsValues['partial_feeds_category_size']) ? $this->settingsValues['partial_feeds_category_size'] : false,
            'm_export_wholesale_prices' => isset($this->settingsValues['m_export_wholesale_prices']) ? $this->settingsValues['m_export_wholesale_prices'] : null,
            'mmp_export_denied_products' => isset($this->settingsValues['mmp_export_denied_products']) ? $this->settingsValues['mmp_export_denied_products'] : null,
            'mmp_export_denied_products_other' => isset($this->settingsValues['mmp_export_denied_products_other']) ? $this->settingsValues['mmp_export_denied_products_other'] : null,
            'delivery_days' => isset($this->settingsValues['delivery_days']) ? $this->settingsValues['delivery_days'] : null,
            'clrCheckboxes' => 1,
            'page' => 1,
            'id_shop' => $this->shopID,
        );

        //Fill in empty fields
        include __DIR__ . '/partials/helperFormEmptyFieldsFiller.php';

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

        return @$helper->generateForm($fields_form);
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
                'title' => $this->l('Mergado\'s analytic feed'),
                'icon' => 'icon-flag'
            ),
            'description' => $this->l('Activation of Mergado Analytical XML export.'),
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

        //Fill in empty fields
        include __DIR__ . '/partials/helperFormEmptyFieldsFiller.php';

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

        return @$helper->generateForm($fields_form);
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

        //Fill in empty fields
        include __DIR__ . '/partials/helperFormEmptyFieldsFiller.php';

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

        return @$helper->generateForm($fields_form);
    }

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
        include_once __DIR__ . '/adsys/google.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_facebook() {
        include_once __DIR__ . '/adsys/facebook.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_heureka() {
        include_once __DIR__ . '/adsys/heureka.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_etarget() {
        include_once __DIR__ . '/adsys/etarget.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_najnakupsk() {
        include_once __DIR__ . '/adsys/najnakupsk.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_seznam() {
        include_once __DIR__ . '/adsys/seznam.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_glami() {
        include_once __DIR__ . '/adsys/glami.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_kelkoo() {
        include_once __DIR__ . '/adsys/kelkoo.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_pricemania() {
        include_once __DIR__ . '/adsys/pricemania.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_biano() {
        include_once __DIR__ . '/adsys/biano.php';
        return @$helper->generateForm($fields_form);
    }

    public function formAdSys_arukereso() {
        include_once __DIR__ . '/adsys/arukereso.php';
        return @$helper->generateForm($fields_form);
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

                $xmlProductFeed = new XMLProductFeed($this->shopID);
                $totalFilesCount = $xmlProductFeed->getTotalFilesCount();
                if($totalFilesCount === 0) {
                    $totalFiles = 0;
                } else {
                    $xmlQuery = new XMLQuery();
                    $totalFiles = ceil(count($xmlQuery->productsToFlat(0, 0)) / $totalFilesCount);
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
                    $xmlCategoryFeed = new XMLCategoryFeed($this->shopID);
                    $totalFilesCount = $xmlCategoryFeed->getTotalFilesCount();
                    if($totalFilesCount === 0) {
                        $totalFiles = 0;
                    } else {
                        $totalFiles = ceil(count(Category::getSimpleCategories($this->context->language->id)) / $totalFilesCount);
                    }

                    $categoryData = array(
                        'totalFiles' => $totalFiles,
                        'currentFiles' => XMLClass::getTempNumber(XMLClass::TMP_DIR . 'xml/' . $this->shopID . '/' . 'category_' . $feed['key'] . '/'),
                        'xml' => 'category_' . $feed['key'],
                        'url' => $this->getCronUrl('category_' . $feed['key']),
                        'name' => $this->languages->getLanguageByIETFCode(
                                $this->languages->getLanguageCodeByIso($iso[0])
                            )->name . ' - ' . $iso[1]);

                    $langWithName['category']['category_' . $feed['key']] = $categoryData;
                    $categoryCron[] = $categoryData;
                }
            }
        }


        $stockFeed = SettingsClass::getSettings('mergado_heureka_dostupnostni_feed', $this->shopID);
        if ($stockFeed) {
            $xmlStockFeed = new XMLStockFeed($this->shopID);
            $maxProductsPerStep = $xmlStockFeed->getTotalFilesCount();

            if($maxProductsPerStep === 0) {
                $totalFiles = 0;
            } else {
                $totalFiles = ceil(count(ProductCore::getSimpleProducts($this->context->language->id, $this->context)) / $maxProductsPerStep);
            }

            $langWithName['stock']['stock'] = array(
                'totalFiles' => $totalFiles,
                'currentFiles' => XMLClass::getTempNumber(XMLClass::TMP_DIR . 'xml/' . $this->shopID . '/' . 'stock' . '/'),
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
            'cronsPartial' => (bool) ($xmlClass->getTotalFilesCount('partial_feeds_size', $this->shopID) > 0),
            'importCron' => $this->getImportCronUrl(),
            'setSettings' => $this->getBaseUrl() . '/modules/' . $this->name . '/setSettings.php?' . '&token=' . Tools::substr(Tools::encrypt('mergado/setSettings'), 0, 10),
            'categoryCron' => $categoryCron,
            'xmls' => $newXml,
            'moduleUrl' => $this->getBaseUrl() . _MODULE_DIR_ . $this->name . '/',
            'moduleVersion' => $version,
            'remoteVersion' => $remoteVersion,
            'phpMinVersion' => Mergado::MERGADO['PHP_MIN_VERSION'],
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
        $tab6 = array(
            'cookies' => array('title' => $this->l('Cookies'), 'form' => $this->pageCookies()),
            'google' => array('title' => $this->l('Google'), 'form' => $this->formAdSys_google()),
            'facebook' => array('title' => $this->l('Facebook'), 'form' => $this->formAdSys_facebook()),
            'heureka' => array('title' => $this->l('Heureka'),'form' => $this->formAdSys_heureka()),
            'glami' => array('title' => $this->l('GLAMI'),'form' => $this->formAdSys_glami()),
            'seznam' => array('title' => $this->l('Seznam'),'form' => $this->formAdSys_seznam()),
            'etarget' => array('title' => $this->l('Etarget'),'form' => $this->formAdSys_etarget()),
            'najnakupsk' => array('title' => $this->l('Najnakup.sk'), 'form' => $this->formAdSys_najnakupsk()),
            'pricemania' => array('title' => $this->l('Pricemania'), 'form' => $this->formAdSys_pricemania()),
            'kelkoo' => array('title' => $this->l('Kelkoo'),'form' => $this->formAdSys_kelkoo()),
            'biano' => array('title' => $this->l('Biano'),'form' => $this->formAdSys_biano()),
            'arukereso' => array('title' => $this->l('Árukereső'),'form' => $this->formAdSys_arukereso()),
        );

        if (isset($_GET['mergadoTab']) && $_GET['mergadoTab'] === '6-cookies') {
            $tab6['cookies']['active'] = true;
        } else {
            $tab6['google']['active'] = true;
        }

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
            SettingsClass::GOOGLE_GTAGJS['ACTIVE'] => [
                'fields' => [
                    SettingsClass::GOOGLE_GTAGJS['CODE'],
                    SettingsClass::GOOGLE_GTAGJS['TRACKING'],
                    SettingsClass::GOOGLE_GTAGJS['ECOMMERCE'],
                    SettingsClass::GOOGLE_GTAGJS['ECOMMERCE_ENHANCED'],
                    SettingsClass::GOOGLE_GTAGJS['CONVERSION_VAT_INCL'],
                ],
                'sub-check' => [
                    SettingsClass::GOOGLE_GTAGJS['TRACKING'] => [
                    'fields' => [
                        SettingsClass::GOOGLE_GTAGJS['ECOMMERCE'],
                        SettingsClass::GOOGLE_GTAGJS['ECOMMERCE_ENHANCED'],
                        ],
                    ],
                ],
                'sub-check-two' => [
                    SettingsClass::GOOGLE_GTAGJS['ECOMMERCE'] => [
                        'fields' => [
                            SettingsClass::GOOGLE_GTAGJS['ECOMMERCE_ENHANCED'],
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
                    SettingsClass::HEUREKA['WIDGET_MOBILE_CZ'],
                    SettingsClass::HEUREKA['WIDGET_SCREEN_WIDTH_CZ'],
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
                    SettingsClass::HEUREKA['WIDGET_MOBILE_SK'],
                    SettingsClass::HEUREKA['WIDGET_SCREEN_WIDTH_SK'],
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
            KelkooClass::getToggleFields(),
            BianoClass::getToggleFields($this->languages->getLanguages(true)),
            NajNakupClass::getToggleFields(),
            EtargetClass::getToggleFields(),
            GoogleReviewsClass::getToggleFields(),
            GaRefundClass::getToggleFields(),
            ZboziClass::getToggleFields($this->languages->getLanguages(true)),
            ArukeresoClass::getToggleFields($this->languages->getLanguages(true)),
            GoogleAdsClass::getToggleFields(),
            GoogleTagManagerClass::getToggleFields()
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

//        if(isset($_POST['upgradeModule'])) {
//            $mergado = new Mergado();
//
//            if($mergado->updateModule()) {
//                $mergado->updateVersionXml();
//            }
//        }

        if (Tools::isSubmit('submit' . $this->name)) {
            unset($_POST['submit' . $this->name]);

            LogClass::log("Settings saved:\n" . json_encode($_POST) . "\n");

            if (isset($_POST['clrCheckboxes'])) {
                SettingsClass::clearSettings(SettingsClass::EXPORT['BOTH'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['DENIED_PRODUCTS'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['DENIED_PRODUCTS_OTHER'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['CATALOG'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['SEARCH'], $this->shopID);
                SettingsClass::clearSettings(SettingsClass::EXPORT['COST'], $this->shopID);
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
