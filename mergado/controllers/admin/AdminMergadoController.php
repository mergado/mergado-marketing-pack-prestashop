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

        $this->fields_value = $defaultValues;



        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Export settings'),
                'icon' => 'icon-flag'
            ),
            'input' => array_merge($feedLang, array(
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
            )),
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

        $this->fields_value = array(
            'delivery_days' => $this->settingsValues['delivery_days']
        );

        $this->fields_value = array_merge($this->fields_value, $optionsArray, $defaultValues);
        $this->show_toolbar = true;
        $this->show_form_cancel_button = false;

        return parent::renderForm();
    }

    public function formAdSys() {

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Advertisment systems'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
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
                    'defaultValue' => $this->settingsValues['mergado_zbozi_konverze']
                ),
                array(
                    'name' => 'mergado_zbozi_shop_id',
                    'label' => $this->l('Zbozi shop ID'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'defaultValue' => $this->settingsValues['mergado_zbozi_shop_id']
                ),
                array(
                    'name' => 'mergado_zbozi_secret',
                    'label' => $this->l('Secret key'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'defaultValue' => $this->settingsValues['mergado_zbozi_secret']
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );

        $this->fields_value = array(
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
            'mergado_zbozi_secret' => $this->settingsValues['mergado_zbozi_secret']
        );

        $this->show_toolbar = true;
        $this->show_form_cancel_button = false;
        return parent::renderForm();
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
                        'language' => 'stock',
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

        $this->context->smarty->assign(array(
            'tab1' => $tab1,
            'tab6' => $tab6,
        ));
    }

    public function postProcess() {

        if (Tools::isSubmit('submitAdd' . $this->name)) {

            unset($_POST['submitAdd' . $this->name]);
            MergadoClass::clearSettings('what_to_export');

            foreach ($_POST as $key => $value) {
                $this->saveData($key, $value);
            }

            $this->setRedirectAfter(self::$currentIndex . '&token=' . $this->token . (Tools::isSubmit('submitFilter' . $this->list_id) ? '&submitFilter' . $this->list_id . '=' . (int) Tools::getValue('submitFilter' . $this->list_id) : ''));
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
