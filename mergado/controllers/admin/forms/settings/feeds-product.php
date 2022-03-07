<?php

use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XMLClass;

$options = [
    [
        'id_option' => 'both',
        'name' => $this->module->l('Everywhere', 'feeds-product')
    ],
    [
        'id_option' => 'catalog',
        'name' => $this->module->l('Catalog', 'feeds-product')
    ],
    [
        'id_option' => 'search',
        'name' => $this->module->l('Search', 'feeds-product')
    ]
];

$feedLang = [];
$defaultValues = [];

foreach ($this->languages->getLanguages(true) as $lang) {
    foreach ($this->currencies->getCurrencies(false, true, true) as $currency) {

        $feedLang = array_merge($feedLang, [
            [
                'label' => $lang['name'] . ' - ' . $currency['iso_code'],
                'hint' => $this->module->l('Export to this language?', 'feeds-product'),
                'name' => XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'],
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
                'class' => 'switch15',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'] . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'feeds-product')
                    ],
                    [
                        'id' => XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'] . '_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'feeds-product')
                    ]
                ],
                'visibility' => Shop::CONTEXT_ALL
            ],
        ]);

        if (isset($this->settingsValues[XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code']])) {
            $defaultValues = array_merge($defaultValues, [
                XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'] => $this->settingsValues[XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code']]
            ]);
        }
    }
}

$fields_value = $defaultValues;

$fields_form[0]['form'] = [
    'input' => [
        [
            'type' => 'checkbox',
            'label' => $this->module->l('Export cost elements?', 'feeds-product'),
            'name' => 'm_export',
            'values' => [
                'query' =>
                    [
                        [
                            'id_option' => 'wholesale_prices',
                            'name' => $this->module->l('Yes', 'feeds-product')
                        ],
                    ],
                'id' => 'id_option',
                'name' => 'name'
            ],
            'hint' => $this->module->l('Choose whether to export COST and COST_VAT elements to the product feed.', 'feeds-product')
        ],
        [
            'type' => 'checkbox',
            'label' => $this->module->l('Export by visibility', 'feeds-product'),
            'name' => 'what_to_export',
            'values' => [
                'query' => $options,
                'id' => 'id_option',
                'name' => 'name'],
            'hint' => $this->module->l('Choose which products will be exported by visibility.', 'feeds-product')
        ],
    ]
];


$fields_form[1]['form'] = [
    'input' => [
        [
            'type' => 'checkbox',
            'label' => $this->module->l('Export products with denied orders in Product feeds', 'feeds-product'),
            'name' => 'mmp_export',
            'values' => [
                'query' =>
                    [
                        [
                            'id_option' => 'denied_products',
                            'name' => $this->module->l('Yes', 'feeds-product')
                        ],
                    ],
                'id' => 'id_option',
                'name' => 'name'
            ],
            'hint' => $this->module->l('By default, the module generates only products with allowed orders. By enabling this option, the module will also generate products with denied orders', 'feeds-product')
        ],
    ]
];

$fields_form[2]['form'] = [
    'input' => [
        [
            'label' => $this->module->l('Delivery days', 'feeds-product'),
            'type' => 'text',
            'name' => 'delivery_days',
            'hint' => $this->module->l('In how many days can you delivery the product when it is out of stock', 'feeds-product'),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('If not filled in, the value from the field "Label of out-of-stock products with allowed backorders"', 'feeds-product'),
            'visibility' => Shop::CONTEXT_ALL
        ]
    ],
];

$fields_form[3]['form'] = [
    'input' => [
        [
            'label' => $this->module->l('Change the number of products per batch (Change only if advised by our support team)', 'feeds-product'),
            'name' => XMLClass::OPTIMIZATION['PRODUCT_FEED'],
            'validation' => 'isInt',
            'cast' => 'intval',
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => $this->module->l('Leave blank to generate the entire XML feed at once.', 'feeds-product'),
            'hint' => $this->module->l('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects!<br><br>Default number is set to 1500 items per batch step.', 'feeds-product')
        ],
    ],
];

$fields_form[4]['form'] = [
    'input' => [
        [
            'type' => 'hidden',
            'name' => 'page'
        ],
        [
            'type' => 'hidden',
            'name' => 'mmp-tab',
        ],
        [
            'type' => 'hidden',
            'name' => 'id_shop'
        ],
        [
            'type' => 'hidden',
            'name' => 'clrCheckboxesProduct'
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save', 'feeds-product'),
        'name' => 'submit' . $this->name
    ]
];

$optionsArray = [];
foreach ($options as $option) {
    if (isset($this->settingsValues['what_to_export_' . $option['id_option']])) {
        $optionsArray = array_merge(
            $optionsArray, [
                'what_to_export_' . $option['id_option'] => $this->settingsValues['what_to_export_' . $option['id_option']]
            ]
        );
    }
}

$fields_value = [
    XMLClass::OPTIMIZATION['PRODUCT_FEED'] => $this->settingsValues[XMLClass::OPTIMIZATION['PRODUCT_FEED']] ?? false,
    'm_export_wholesale_prices' => $this->settingsValues['m_export_wholesale_prices'] ?? null,
    'mmp_export_denied_products' => $this->settingsValues['mmp_export_denied_products'] ?? null,
    'delivery_days' => $this->settingsValues['delivery_days'] ?? null,
    'clrCheckboxesProduct' => 1,
    'page' => 'feeds-product',
    'mmp-tab' => 'settings',
    'id_shop' => $this->shopID,
];

//Fill in empty fields
include __MERGADO_FORMS_DIR__ . '/helpers/helperFormEmptyFieldsFiller.php';

$helper = new HelperForm();

$helper->module = $this;
$helper->name_controller = $this->name;

$helper->tpl_vars = ['fields_value' => array_merge($fields_value, $optionsArray, $defaultValues)];

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
    $helper->toolbar_btn = [
        'save' =>
            [
                'desc' => $this->module->l('Save', 'feeds-product'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
        'back' => [
            'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->module->l('Back to list', 'feeds-product')
        ]
    ];
}
