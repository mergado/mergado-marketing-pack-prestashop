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
        
        if(!Configuration::get('MERGADO_LOG_TOKEN')){
            Configuration::updateValue('MERGADO_LOG_TOKEN', Tools::getAdminTokenLite('AdminMergadoLog'));
        }
    }

    public function formDevelopers() {
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Support help'),
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
                    'desc' => $this->l('Send this to support:') . " " . MergadoClass::getLogLite(),
                    'type' => 'switch',
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
                    'label' => $this->l('Delete log file on save'),
                    'name' => 'mergado_del_log',
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
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
                'title' => $this->l('Save')
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
                        'type' => 'switch',
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
                'title' => $this->l('Save')
            )
        );

        $fields_form[1]['form'] = array(
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
                'title' => $this->l('Save')
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
                    'type' => 'switch',
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
                    'type' => 'switch',
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
                    'type' => 'switch',
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
                    'type' => 'switch',
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
                    'type' => 'switch',
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
                    'type' => 'switch',
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
                    'type' => 'switch',
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
                    'title' => $this->l('Save')
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
                    'type' => 'switch',
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
                    'title' => $this->l('Save')
                )
        ));

        $fields_form[2]['form'] = array(
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
                    'type' => 'switch',
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
                )
                ,
                'submit' => array(
                    'title' => $this->l('Save')
                )
        ));

        $fields_form[3]['form'] = array(
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
                    'type' => 'switch',
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
            ),
            'submit' => array(
                'title' => $this->l('Save')
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

        return $helper->generateForm($fields_form);
    }

    public function initContent() {
        $sql = 'SELECT `key` FROM `' . _DB_PREFIX_ . $this->table . '` WHERE `key` LIKE "';
        $sql .= pSQL(MergadoClass::$feedPrefix) . '%" AND `value` = 1';
        $feeds = Db::getInstance()->executeS($sql);

        $mergadoModule = new Mergado();
        $version = $mergadoModule->version;

        $langWithName = array();
        foreach ($feeds as $feed) {
            $iso = str_replace(MergadoClass::$feedPrefix, '', $feed['key']);
            $iso = explode('-', $iso);

            $langWithName[] = array(
                'url' => $this->getCronUrl($feed['key']),
                'name' => $this->languages->getLanguageByIETFCode(
                        $this->languages->getLanguageCodeByIso($iso[0])
                )->name . ' - ' . $iso[1],
            );
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
                $codedName = explode('_', $tmpName);

                $code = Tools::strtoupper(
                                '_' . Tools::substr(hash('md5', $codedName[0] . Configuration::get('PS_SHOP_NAME')), 1, 11)
                );

                if ($codedName[0] == 'stock') {
                    $xmlList[] = array(
                        'language' => $this->l('Stock feed'),
                        'url' => $this->baseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                } else {
                    $xmlList[] = array(
                        'language' => str_replace(
                                $code, '', $this->languages->getLanguageByIETFCode(
                                        $this->languages->getLanguageCodeByIso($name[0])
                                )->name . ' - ' . Tools::strtoupper($name[1])
                        ),
                        'url' => $this->baseUrl() . _MODULE_DIR_ . $this->name . '/xml/' . basename($filename),
                        'name' => basename($filename),
                        'date' => filemtime($filename),
                    );
                }
            }
        }

        $this->context->smarty->assign(array(
            'crons' => $langWithName,
            'xmls' => $xmlList,
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
            'tab4' => $tab4
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
