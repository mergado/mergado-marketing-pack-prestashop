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
require_once _PS_MODULE_DIR_ . 'mergado/classes/MergadoClass.php';

class AdminMergadoController extends ModuleAdminController {

    protected $multishop;
    protected $multishopAllowed = false;
    protected $languages;
    protected $currencies;
    protected $modulePath;
    protected $settingsValues;

    public function __construct() {
        $this->bootstrap = true;
        $this->className = 'AdminMergado';
        $this->table = 'mergado';
        $this->name = 'mergado';
        $this->bootstrap = true;
        $this->languages = new Language();
        $this->currencies = new Currency();
        $this->modulePath = _PS_MODULE_DIR_ . 'mergado/';

        $settingsTable = MergadoClass::getWholeSettings();
        $settingsValues = array();
        foreach ($settingsTable as $s) {
            $this->settingsValues[$s['key']] = $s['value'];
        }

        parent::__construct();

        if (!Configuration::get('MERGADO_LOG_TOKEN')) {
            Configuration::updateValue('MERGADO_LOG_TOKEN', Tools::getAdminTokenLite('AdminMergadoLog'));
        }
    }

    public function formDevelopers() {
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
                    'name' => 'mergado_dev_log',
                    'label' => $this->l('Enable log'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'class' => 'switch15',
                    'desc' => $this->l('Send this to support:') . " " . MergadoClass::getLogLite(),
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
            'mergado_dev_log' => $this->settingsValues['mergado_dev_log'],
            'page' => 4
        );


        $this->show_toolbar = true;
        $this->show_form_cancel_button = false;



        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;

        $helper->tpl_vars = array('fields_value' => $fields_value);
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        if (version_compare(_PS_VERSION_, '1.6') < 0) {
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->submit_action = 'save' . $this->name;
            $helper->token = Tools::getValue('token');
        }

        if (version_compare(_PS_VERSION_, '1.6') < 0) {
            
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

    public function formExportSettings() {

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
            foreach ($this->currencies->getCurrencies(false, true) as $currency) {

                $feedLang = array_merge($feedLang, array(
                    array(
                        'label' => $lang['name'] . ' - ' . $currency['iso_code'],
                        'hint' => $this->l('Export to this language?'),
                        'name' => MergadoClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'],
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
                        'class' => 'switch15',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => MergadoClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'] . '_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => MergadoClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'] . '_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'visibility' => Shop::CONTEXT_ALL
                    ),
                ));

                $defaultValues = array_merge($defaultValues, array(
                    MergadoClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'] => $this->settingsValues[MergadoClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code']]
                ));
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
                    'name' => 'category_feed',
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'static_feed',
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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

            $optionsArray = array_merge(
                    $optionsArray, array(
                'what_to_export_' . $option['id_option'] => $this->settingsValues['what_to_export_' . $option['id_option']]
                    )
            );
        }

        $fields_value = array(
            'delivery_days' => $this->settingsValues['delivery_days'],
            'static_feed' => $this->settingsValues['static_feed'],
            'category_feed' => $this->settingsValues['category_feed'],
            'clrCheckboxes' => 1,
            'page' => 1,
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;

        $helper->tpl_vars = array('fields_value' => array_merge($fields_value, $optionsArray, $defaultValues));
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        if (version_compare(_PS_VERSION_, '1.6') < 0) {
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->submit_action = 'save' . $this->name;
            $helper->token = Tools::getValue('token');
        }

        if (version_compare(_PS_VERSION_, '1.6') < 0) {
            
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

    public function formAdSys() {

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
                    'name' => 'mergado_heureka_overeno_zakazniky_cz',
                    'label' => $this->l('Heureka.cz verified by users'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_heureka_overeno_zakazniky_kod_cz',
                    'label' => $this->l('Heureka.cz verified by users code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'mergado_heureka_overeno_zakazniky_sk',
                    'label' => $this->l('Heureka.sk verified by users'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_heureka_overeno_zakazniky_kod_sk',
                    'label' => $this->l('Heureka.sk verified by users code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'mergado_heureka_konverze_cz',
                    'label' => $this->l('Heureka.cz track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_heureka_konverze_cz_kod',
                    'label' => $this->l('Heureka.cz conversion code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'mergado_heureka_konverze_sk',
                    'label' => $this->l('Heureka.sk track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_heureka_konverze_sk_kod',
                    'label' => $this->l('Heureka.sk conversion code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'mergado_heureka_widget_cz',
                    'label' => $this->l('Heureka.cz - widget'),
                    'hint' => $this->l('You need conversion code to enable this feature'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_heureka_widget_sk',
                    'label' => $this->l('Heureka.sk - widget'),
                    'hint' => $this->l('You need conversion code to enable this feature'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name
                )
        ));

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Zbozi.cz'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => 'mergado_zbozi_konverze',
                    'label' => $this->l('Zbozi track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_zbozi_shop_id',
                    'label' => $this->l('Zbozi shop ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'mergado_zbozi_secret',
                    'label' => $this->l('Secret key'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name
                )
        ));

        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Najnakup.sk'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => 'mergado_najnakup_konverze',
                    'label' => $this->l('Najnakup track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_najnakup_shop_id',
                    'label' => $this->l('Najnakup shop ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name
                )
        ));

        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Pricemania'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => 'mergado_pricemania_overeny_obchod',
                    'label' => $this->l('Verified shop'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_pricemania_shop_id',
                    'label' => $this->l('Pricemania shop ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name
                )
        ));

        $fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('Sklik'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => 'mergado_sklik_konverze',
                    'label' => $this->l('Sklik track conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_sklik_konverze_kod',
                    'label' => $this->l('Sklik conversion code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'mergado_sklik_konverze_hodnota',
                    'label' => $this->l('Sklik value'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'seznam_retargeting',
                    'label' => $this->l('Sklik retargting'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'seznam_retargeting_id',
                    'label' => $this->l('Sklik retargeting ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name
                )
        ));

        $fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('Adwords'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'name' => 'mergado_adwords_conversion',
                    'label' => $this->l('Adwords conversions'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'mergado_adwords_conversion_code',
                    'label' => $this->l('Adwords conversion code'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'mergado_adwords_conversion_label',
                    'label' => $this->l('Adwords conversion label'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'adwords_remarketing',
                    'label' => $this->l('Adwords remarketing'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'adwords_remarketing_id',
                    'label' => $this->l('Adwords remarketing ID'),
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
                    'name' => 'fb_pixel',
                    'label' => $this->l('Facebook pixel'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'fb_pixel_code',
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
                    'name' => 'etarget',
                    'label' => $this->l('ETARGET'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
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
                    'name' => 'etarget_id',
                    'label' => $this->l('ETARGET ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ),
                array(
                    'name' => 'etarget_hash',
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

        $fields_value = array(
            'mergado_heureka_overeno_zakazniky_cz' => $this->settingsValues['mergado_heureka_overeno_zakazniky_cz'],
            'mergado_heureka_overeno_zakazniky_kod_cz' => $this->settingsValues['mergado_heureka_overeno_zakazniky_kod_cz'],
            'mergado_heureka_overeno_zakazniky_sk' => $this->settingsValues['mergado_heureka_overeno_zakazniky_sk'],
            'mergado_heureka_overeno_zakazniky_kod_sk' => $this->settingsValues['mergado_heureka_overeno_zakazniky_kod_sk'],
            'mergado_heureka_konverze_cz' => $this->settingsValues['mergado_heureka_konverze_cz'],
            'mergado_heureka_konverze_cz_kod' => $this->settingsValues['mergado_heureka_konverze_cz_kod'],
            'mergado_heureka_konverze_sk' => $this->settingsValues['mergado_heureka_konverze_sk'],
            'mergado_heureka_konverze_sk_kod' => $this->settingsValues['mergado_heureka_konverze_sk_kod'],
            'mergado_heureka_widget_cz' => $this->settingsValues['mergado_heureka_widget_cz'],
            'mergado_heureka_widget_sk' => $this->settingsValues['mergado_heureka_widget_sk'],
            'mergado_heureka_dostupnostni_feed' => $this->settingsValues['mergado_heureka_dostupnostni_feed'],
            'mergado_zbozi_konverze' => $this->settingsValues['mergado_zbozi_konverze'],
            'mergado_zbozi_shop_id' => $this->settingsValues['mergado_zbozi_shop_id'],
            'mergado_zbozi_secret' => $this->settingsValues['mergado_zbozi_secret'],
            'mergado_sklik_konverze' => $this->settingsValues['mergado_sklik_konverze'],
            'mergado_sklik_konverze_kod' => $this->settingsValues['mergado_sklik_konverze_kod'],
            'mergado_sklik_konverze_hodnota' => $this->settingsValues['mergado_sklik_konverze_hodnota'],
            'mergado_adwords_conversion' => $this->settingsValues['mergado_adwords_conversion'],
            'mergado_adwords_conversion_code' => $this->settingsValues['mergado_adwords_conversion_code'],
            'mergado_adwords_conversion_label' => $this->settingsValues['mergado_adwords_conversion_label'],
            'mergado_najnakup_konverze' => $this->settingsValues['mergado_najnakup_konverze'],
            'mergado_najnakup_shop_id' => $this->settingsValues['mergado_najnakup_shop_id'],
            'mergado_pricemania_overeny_obchod' => $this->settingsValues['mergado_pricemania_overeny_obchod'],
            'mergado_pricemania_shop_id' => $this->settingsValues['mergado_pricemania_shop_id'],
            'fb_pixel' => $this->settingsValues['fb_pixel'],
            'fb_pixel_code' => $this->settingsValues['fb_pixel_code'],
            'adwords_remarketing' => $this->settingsValues['adwords_remarketing'],
            'adwords_remarketing_id' => $this->settingsValues['adwords_remarketing_id'],
            'seznam_retargeting' => $this->settingsValues['seznam_retargeting'],
            'seznam_retargeting_id' => $this->settingsValues['seznam_retargeting_id'],
            'etarget' => $this->settingsValues['etarget'],
            'etarget_id' => $this->settingsValues['etarget_id'],
            'etarget_hash' => $this->settingsValues['etarget_hash'],
            'page' => 6,
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;

        $helper->tpl_vars = array('fields_value' => $fields_value);
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        if (version_compare(_PS_VERSION_, '1.6') < 0) {
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->submit_action = 'save' . $this->name;
            $helper->token = Tools::getValue('token');
        }

        if (version_compare(_PS_VERSION_, '1.6') < 0) {
            
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

    public function initContent() {
        $sql = 'SELECT `key` FROM `' . _DB_PREFIX_ . $this->table . '` WHERE `key` LIKE "';
        $sql .= pSQL(MergadoClass::$feedPrefix) . '%" AND `value` = 1';
        $feeds = Db::getInstance()->executeS($sql);

        $mergadoModule = new Mergado();
        $version = $mergadoModule->version;

        $categoryFeed = MergadoClass::getSettings('category_feed');
        $categoryCron = array();
        $langWithName = array();
        foreach ($feeds as $feed) {
            $iso = str_replace('category_', '', $feed['key']);
            $iso = str_replace(MergadoClass::$feedPrefix, '', $iso);
            $iso = explode('-', $iso);

            $langWithName[] = array(
                'url' => $this->getCronUrl($feed['key']),
                'name' => $this->languages->getLanguageByIETFCode(
                        $this->languages->getLanguageCodeByIso($iso[0])
                )->name . ' - ' . $iso[1]
            );

            if ($categoryFeed == "1") {
                $categoryCron[] = array(
                    'url' => $this->getCronUrl('category_' . $feed['key']),
                    'name' => $this->languages->getLanguageByIETFCode(
                            $this->languages->getLanguageCodeByIso($iso[0])
                    )->name . ' - ' . $iso[1]);
            }
        }

        $stockFeed = MergadoClass::getSettings('mergado_heureka_dostupnostni_feed');
        if ($stockFeed) {
            $langWithName[] = array(
                'url' => $this->getCronUrl('stock'),
                'name' => $this->l('Stock feed')
            );
        }

        $files = glob($this->modulePath . 'xml/*xml');
        $xmlList = array();
        if (is_array($files)) {
            foreach ($files as $filename) {
                $tmpName = str_replace(MergadoClass::$feedPrefix, '', basename($filename, '.xml'));

                $name = explode('-', $tmpName);
                $name[1] = explode('_', $name[1]);
                $codedName = explode('_', $tmpName);
                $code = Tools::strtoupper(
                                '_' . Tools::substr(hash('md5', $codedName[0] . Configuration::get('PS_SHOP_NAME')), 1, 11)
                );

                if ($codedName[0] == 'stock') {
                    $xmlList['stock'][] = array(
                        'language' => $this->l('Stock feed'),
                        'url' => $this->baseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                } else if ($codedName[0] == 'static') {
                    $xmlList['static'][] = array(
                        'language' => $this->l('Mergado static feed'),
                        'url' => $this->baseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . basename($filename),
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
                        'url' => $this->baseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . basename($filename),
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
                        'url' => $this->baseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . basename($filename),
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
            'categoryCron' => $categoryCron,
            'xmls' => $newXml,
            'moduleUrl' => $this->baseUrl() . _MODULE_DIR_ . $this->name . '/',
            'moduleVersion' => $version
        ));

        $before = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mergado/views/templates/admin/mergado/before.tpl');
        $after = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mergado/views/templates/admin/mergado/after.tpl');

        parent::initContent();

        $tab1 = $this->formExportSettings();
        $tab6 = $this->formAdSys();
        $tab4 = $this->formDevelopers();

        $this->context->smarty->assign(array(
            'tab1' => $tab1,
            'tab6' => $tab6,
            'tab4' => $tab4,
            'staticFeed' => MergadoClass::getSettings('static_feed'),
            'categoryFeed' => MergadoClass::getSettings('category_feed'),
        ));
    }

    public function postProcess() {

        if (Tools::isSubmit('submit' . $this->name)) {
            unset($_POST['submit' . $this->name]);

            MergadoClass::log("Settings edit:\n" . json_encode($_POST) . "\n");

            if (isset($_POST['clrCheckboxes'])) {
                MergadoClass::clearSettings('what_to_export');
            }

            if (isset($_POST['mergado_del_log']) && $_POST['mergado_del_log'] == '1') {
                MergadoClass::deleteLog();
            }

            foreach ($_POST as $key => $value) {
                $this->saveData($key, $value);
            }


            $this->redirect_after = self::$currentIndex . '&token=' . $this->token . (Tools::isSubmit('submitFilter' . $this->list_id) ? '&submitFilter' . $this->list_id . '=' . (int) Tools::getValue('submitFilter' . $this->list_id) : '') . '&mergadoTab=' . $_POST['page'];
        }
    }

    public function saveData($key, $value) {
        $exists = Db::getInstance()->getRow(
                'SELECT id FROM ' . _DB_PREFIX_ . $this->table . ' WHERE `key`="' . pSQL($key) . '"'
        );
        if ($exists) {
            Db::getInstance()->update($this->table, array('value' => pSQL($value)), '`key` = "' . pSQL($key) . '"');
        } else {
            Db::getInstance()->insert($this->table, array(
                'key' => pSQL($key),
                'value' => pSQL($value),
            ));
        }
    }

    public function getCronUrl($key) {
        return $this->baseUrl() . '/modules/' . $this->name . '/cron.php?feed=' . $key .
                '&token=' . Tools::substr(Tools::encrypt('mergado/cron'), 0, 10);
    }

    public function baseUrl() {
        return 'http' . (Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' . Tools::getShopDomain(false, true);
    }

}
