<?php

use Mergado\Tools\SettingsClass;
use Mergado\Tools\XMLClass;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->l('Price import'),
        'icon' => 'icon-flag',
    ],
    'description' => $this->l('Price import from Mergado XML feed'),
    'input' => [
        [
            'type' => 'hidden',
            'name' => 'page'
        ],
        [
            'type' => 'hidden',
            'name' => 'id_shop'
        ],
        [
            'label' => $this->l('Import prices feed URL'),
            'name' => SettingsClass::IMPORT['URL'],
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Insert URL of import prices feed from Mergado webpage.'),
            'visibility' => Shop::CONTEXT_ALL
        ],
        [
            'label' => $this->l('Number of products imported in one cron run'),
            'name' => XMLClass::OPTIMIZATION['IMPORT_FEED'],
            'validation' => 'isInt',
            'cast' => 'intval',
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Leave blank or 0 if you don\'t have problem with importing product prices.'),
            'visibility' => Shop::CONTEXT_ALL
        ],
    ],
    'submit' => [
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

$fields_value = [
    SettingsClass::IMPORT['URL'] => $this->settingsValues[SettingsClass::IMPORT['URL']] ?? false,
    'page' => 1,
    'id_shop' => $this->shopID,
];

//Fill in empty fields
include __MERGADO_FORMS_DIR__ . '/helpers/helperFormEmptyFieldsFiller.php';

$this->show_toolbar = true;
$this->show_form_cancel_button = false;

$helper = new HelperForm();

$helper->module = $this;
$helper->name_controller = $this->name;

$helper->tpl_vars = ['fields_value' => $fields_value];
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
    $helper->toolbar_btn = [
        'save' =>
            [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
        'back' => [
            'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        ]
    ];
}

return @$helper->generateForm($fields_form);