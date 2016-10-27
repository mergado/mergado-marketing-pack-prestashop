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

class AdminMergadoController extends ModuleAdminControllerCore {

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

    public function init() {

        $feedLang = array();
        foreach ($this->languages->getLanguages(true) as $lang) {
            foreach ($this->currencies->getCurrencies(false, true) as $currency) {

                $feedLang = array_merge($feedLang, array(
                    MergadoClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code'] => array(
                        'title' => $lang['name'] . ' - ' . $currency['iso_code'],
                        'hint' => $this->l('Export to this language?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues[MergadoClass::$feedPrefix . $lang['iso_code'] . '-' . $currency['iso_code']]
                    ),
                ));
            }
        }

        $feedLang = array_merge($feedLang, array(
            'delivery_days' => array(
                'title' => $this->l('Delivery days'),
                'type' => 'text',
                'hint' => $this->l('In how many days can you delivery the product when it is out of stock'),
                'visibility' => Shop::CONTEXT_ALL,
                'defaultValue' => $this->settingsValues['delivery_days']
            )
        ));

        $feedSettings = array(
            'mergado_lang' => array(
                'title' => $this->l('Export configuration'),
                'class' => 'separate1',
                'icon' => 'icon-cogs',
                'description' => $this->l('Select languages for which you aim to export Mergado feed'),
                'fields' => $feedLang,
                'submit' => array('title' => $this->l('Save')),
            )
        );

        $this->fields_options = array_merge($feedSettings, array(
            'heureka' => array(
                'title' => $this->l('Heureka'),
                'class' => 'separate6',
                'icon' => 'icon-cogs',
                'fields' => array(
                    'mergado_heureka_overeno_zakazniky_cz' => array(
                        'title' => $this->l('Heureka.cz verified by users'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_overeno_zakazniky_cz']
                    ),
                    'mergado_heureka_overeno_zakazniky_kod_cz' => array(
                        'title' => $this->l('Heureka.cz verified by users code'),
                        'type' => 'text',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_overeno_zakazniky_kod_cz']
                    ),
                    'mergado_heureka_overeno_zakazniky_sk' => array(
                        'title' => $this->l('Heureka.sk verified by users'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_overeno_zakazniky_sk']
                    ),
                    'mergado_heureka_overeno_zakazniky_kod_sk' => array(
                        'title' => $this->l('Heureka.sk verified by users code'),
                        'type' => 'text',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_overeno_zakazniky_kod_sk']
                    ),
                    'mergado_heureka_konverze_cz' => array(
                        'title' => $this->l('Heureka.cz track conversions'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_konverze_cz']
                    ),
                    'mergado_heureka_konverze_cz_kod' => array(
                        'title' => $this->l('Heureka.cz conversion code'),
                        'type' => 'text',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_konverze_cz_kod']
                    ),
                    'mergado_heureka_konverze_sk' => array(
                        'title' => $this->l('Heureka.sk track conversions'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_konverze_sk']
                    ),
                    'mergado_heureka_konverze_sk_kod' => array(
                        'title' => $this->l('Heureka.sk conversion code'),
                        'type' => 'text',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_konverze_sk_kod']
                    ),
                    'mergado_heureka_widget_cz' => array(
                        'title' => $this->l('Heureka.cz - widget'),
                        'hint' => $this->l('You need conversion code to enable this feature'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_widget_cz']
                    ),
                    'mergado_heureka_widget_sk' => array(
                        'title' => $this->l('Heureka.sk - widget'),
                        'hint' => $this->l('You need conversion code to enable this feature'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_heureka_widget_sk']
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'zbozi' => array(
                'title' => $this->l('Zbozi'),
                'class' => 'separate6',
                'icon' => 'icon-cogs',
                'fields' => array(
                    'mergado_zbozi_konverze' => array(
                        'title' => $this->l('Zbozi track conversions'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_zbozi_konverze']
                    ),
                    'mergado_zbozi_shop_id' => array(
                        'title' => $this->l('Zbozi shop ID'),
                        'type' => 'text',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_zbozi_shop_id']
                    ),
                    'mergado_zbozi_secret' => array(
                        'title' => $this->l('Secret key'),
                        'type' => 'text',
                        'visibility' => Shop::CONTEXT_ALL,
                        'defaultValue' => $this->settingsValues['mergado_zbozi_secret']
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
        ));

        parent::init();
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

        $this->context->smarty->assign(array(
            'crons' => $langWithName,
            'xmls' => $xmlList,
            'moduleUrl' => $this->baseUrl() . _MODULE_DIR_ . $this->name . '/',
            'moduleVersion' => $version
        ));

        $before = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mergado/views/templates/admin/mergado/before.tpl');
        $after = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mergado/views/templates/admin/mergado/after.tpl');

        parent::initContent();

        $this->context->smarty->assign(array(
            'content' => $before . $this->content . $after
        ));
    }

    public function postProcess() {

        if (Tools::isSubmit('submitOptions' . $this->name)) {

            unset($_POST['submitOptions' . $this->name]);

            foreach ($_POST as $key => $value) {
                if ($key === $submitBtn) {
                    continue;
                }

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
