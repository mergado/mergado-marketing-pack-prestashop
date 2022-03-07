<?php

use Mergado\Tools\SettingsClass;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLStockFeed;
use Mergado\Tools\XMLClass;

$fields_form[0]['form'] = [
    'input' => [
        [
            'label' => $this->module->l('Number of categories for Category feed', 'feeds-other'),
            'name' => XMLClass::OPTIMIZATION['CATEGORY_FEED'],
            'validation' => 'isInt',
            'cast' => 'intval',
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => $this->module->l('Leave blank to generate the entire XML feed at once.', 'feeds-other'),
            'hint' => $this->module->l('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects! Default number is set to 3000 items per batch step.', 'feeds-other')
        ],
    ],
];

$fields_form[1]['form'] = [
    'input' => [
        [
            'label' => $this->module->l('Number of products for Analytical feed', 'feeds-other'),
            'name' => XMLClass::OPTIMIZATION['STOCK_FEED'],
            'validation' => 'isInt',
            'cast' => 'intval',
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => $this->module->l('Leave blank to generate the entire XML feed at once.', 'feeds-other'),
            'hint' => $this->module->l('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects! Default number is set to 5000 items per batch step.', 'feeds-other')
        ],
    ],
];

$fields_form[2]['form'] = [
    'input' => [
        [
            'label' => $this->module->l('Number of products for Heureka Availability feed', 'feeds-other'),
            'name' => XMLClass::OPTIMIZATION['STATIC_FEED'],
            'validation' => 'isInt',
            'cast' => 'intval',
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => $this->module->l('Leave blank to generate the entire XML feed at once.', 'feeds-other'),
            'hint' => $this->module->l('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects! Default number is set to 5000 items per batch step.', 'feeds-other')
        ],
    ],
];

$fields_form[3]['form'] = [
    'input' => [
        [
            'label' => $this->module->l('Number of products imported in one cron run', 'feeds-other'),
            'name' => XMLClass::OPTIMIZATION['IMPORT_FEED'],
            'validation' => 'isInt',
            'cast' => 'intval',
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => $this->module->l('Leave blank to import the entire XML feed at once.', 'feeds-other'),
            'hint' => $this->module->l('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects! Default number is set to 3000 items per batch step.', 'feeds-other')
        ],
    ],
];

$fields_form[4]['form'] = [
    'input' => [
        [
            'type' => 'checkbox',
            'label' => $this->module->l('Export products with denied orders in Other feeds', 'feeds-other'),
            'name' => 'mmp_export',
            'values' => [
                'query' =>
                    [
                        [
                            'id_option' => 'denied_products_other',
                            'name' => $this->module->l('Yes', 'feeds-other')
                        ],
                    ],
                'id' => 'id_option',
                'name' => 'name'
            ],
            'hint' => $this->module->l('By default, the module generates only products with allowed orders. By enabling this option, the module will also generate products with denied orders', 'feeds-other')
        ],
    ]
];

$fields_form[5]['form'] = [
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
            'type' => 'hidden',
            'name' => 'mmp-tab',
        ],
        [
            'type' => 'hidden',
            'name' => 'clrCheckboxesOther'
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save', 'feeds-other'),
        'name' => 'submit' . $this->name
    ]
];

$fields_value = [
    XMLClass::OPTIMIZATION['CATEGORY_FEED'] => $this->settingsValues[XMLClass::OPTIMIZATION['CATEGORY_FEED']] ?? false,
    XMLClass::OPTIMIZATION['STOCK_FEED'] => $this->settingsValues[XMLClass::OPTIMIZATION['STOCK_FEED']] ?? false,
    XMLClass::OPTIMIZATION['STATIC_FEED'] => $this->settingsValues[XMLClass::OPTIMIZATION['STATIC_FEED']] ?? false,
    XMLClass::OPTIMIZATION['CATEGORY_FEED'] => $this->settingsValues[XMLClass::OPTIMIZATION['CATEGORY_FEED']] ?? false,
    XMLClass::OPTIMIZATION['IMPORT_FEED'] => $this->settingsValues[XMLClass::OPTIMIZATION['IMPORT_FEED']] ?? false,
    'mmp_export_denied_products_other' => $this->settingsValues['mmp_export_denied_products_other'] ?? null,
    'clrCheckboxesOther' => 1,
    'page' => 'feeds-other',
    'mmp-tab' => 'settings',
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
                'desc' => $this->module->l('Save', 'feeds-other'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
        'back' => [
            'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->module->l('Back to list', 'feeds-other')
        ]
    ];
}
