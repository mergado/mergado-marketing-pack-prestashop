<?php

use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->module->l('Glami pixel', 'glami'),
        'icon' => 'icon-cogs'
    ],
    'input' => [
        [
            'type' => 'hidden',
            'name' => 'page'
        ],
        [
            'type' => 'hidden',
            'name' => 'id_shop'
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

$fields_form[0]['form']['input'][] = [
    'name' => SettingsClass::GLAMI['ACTIVE'],
    'label' => $this->module->l('Module active', 'glami'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'class' => 'switch15',
    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your piXel in the Glami Administration at Glami piXel page > Implementing Glami piXel for Developers > Glami piXel Code section for YOUR ESHOP', 'glami'),
    'values' => [
        [
            'id' => 'glami_active_on',
            'value' => 1,
            'label' => $this->module->l('Yes')
        ],
        [
            'id' => 'glami_active_off',
            'value' => 0,
            'label' => $this->module->l('No')
        ]
    ],
    'visibility' => Shop::CONTEXT_ALL,
];

$fields_form[0]['form']['input'][] = [
    'name' => SettingsClass::GLAMI['CONVERSION_VAT_INCL'],
    'label' => $this->module->l('With VAT'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'class' => 'switch15',
    'values' => [
        [
            'id' => 'glam_conv_active_on',
            'value' => 1,
            'label' => $this->module->l('Yes')
        ],
        [
            'id' => 'glam_conv_active_off',
            'value' => 0,
            'label' => $this->module->l('No')
        ]
    ],
    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the conversion value will be sent with or without VAT.', 'glami'),
    'visibility' => Shop::CONTEXT_ALL,
];

foreach (SettingsClass::GLAMI_LANGUAGES as $key => $lang) {
    $fields_form[0]['form']['input'][] = [
        'name' => $lang,
        'label' => $key,
        'validation' => 'isBool',
        'cast' => 'intval',
        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
        'class' => 'switch15',
        'values' => [
            [
                'id' => 'glami_active_on',
                'value' => 1,
                'label' => $this->module->l('Yes')
            ],
            [
                'id' => 'glami_active_off',
                'value' => 0,
                'label' => $this->module->l('No')
            ]
        ],
        'visibility' => Shop::CONTEXT_ALL,
    ];

    $fields_form[0]['form']['input'][] = [
        'name' => SettingsClass::GLAMI['CODE'] . '-' . $key,
        'label' => $this->module->l('Glami Pixel', 'glami') . ' ' . $key,
        'type' => 'text',
        'visibility' => Shop::CONTEXT_ALL,
    ];
}

$fields_form[1]['form'] = [
    'legend' => [
        'title' => $this->module->l('Glami TOP', 'glami'),
        'icon' => 'icon-cogs',
    ],
    'input' => [],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

$fields_form[1]['form']['input'][] = [
    'name' => SettingsClass::GLAMI['ACTIVE_TOP'],
    'label' => $this->module->l('Module active', 'glami'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'desc' => $this->module->l('1. Your website must have HTTPS protocol at least on order confirmation page. 2. You have to set your DNS before use. More informations on: https://www.glami.cz/info/reviews/implementation/', 'glami') . '<br><span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your Glami TOP API key in the Glami Administration at the Glami TOP page > Implementation > Developer Implementation Guide> Javascript Integration section.', 'glami'),
    'class' => 'switch15',
    'values' => [
        [
            'id' => 'glami_top_active_on',
            'value' => 1,
            'label' => $this->module->l('Yes')
        ],
        [
            'id' => 'glami_top_active_off',
            'value' => 0,
            'label' => $this->module->l('No')
        ]
    ],
    'visibility' => Shop::CONTEXT_ALL,
];

$fields_form[1]['form']['input'][] = [
    'name' => SettingsClass::GLAMI['SELECTION_TOP'],
    'label' => $this->module->l('Glami website', 'glami'),
    'type' => 'select',
    'options' => [
        'query' => SettingsClass::GLAMI_TOP_LANGUAGES,
        'id' => 'id_option',
        'name' => 'name'
    ]
];

$fields_form[1]['form']['input'][] = [
    'name' => SettingsClass::GLAMI['CODE_TOP'],
    'label' => $this->module->l('Glami TOP', 'glami'),
    'type' => 'text',
    'visibility' => Shop::CONTEXT_ALL,
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';