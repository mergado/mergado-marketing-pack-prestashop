<?php

use Mergado\Biano\BianoClass;
use Mergado\Biano\BianoStarClass;
use Mergado\Tools\LanguagesClass;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->module->l('Biano pixel', 'biano'),
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
    'name' => BianoClass::ACTIVE,
    'label' => $this->module->l('Module active', 'biano'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'class' => 'switch15',
    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Biano Pixel merchantId is available only for CZ, SK, HU, RO, NL languages. Other languages will be using default option.', 'biano'),
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

foreach ($this->languages->getLanguages(true) as $key => $lang) {
    $langName = LanguagesClass::getLangIso(strtoupper($lang['iso_code']));

    $fields_form[0]['form']['input'][] = [
        'name' => BianoClass::getActiveLangFieldName($langName),
        'label' => $langName,
        'validation' => 'isBool',
        'cast' => 'intval',
        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
        'class' => 'switch15',
        'values' => [
            [
                'id' => 'biano_active_on',
                'value' => 1,
                'label' => $this->module->l('Yes')
            ],
            [
                'id' => 'biano_active_off',
                'value' => 0,
                'label' => $this->module->l('No')
            ]
        ],
        'visibility' => Shop::CONTEXT_ALL,
    ];

    if(in_array($langName, BianoClass::LANG_OPTIONS)) {
        $fields_form[0]['form']['input'][] = [
            'name' => BianoClass::getMerchantIdFieldName($langName),
            'label' => $this->module->l('Merchant ID', 'biano') . ' ' . $langName,
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('You can get your merchant ID in your Biano account > Optimization > Biano pixel.', 'biano'),
            'visibility' => Shop::CONTEXT_ALL,
        ];
    }
}

$fields_form[0]['form']['input'][] = [
    'name' => BianoClass::CONVERSION_VAT_INCl,
    'label' => $this->module->l('With VAT', 'biano'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'class' => 'switch15',
    'values' => [
        [
            'id' => 'biano_active_on',
            'value' => 1,
            'label' => $this->module->l('Yes')
        ],
        [
            'id' => 'biano_active_off',
            'value' => 0,
            'label' => $this->module->l('No')
        ]
    ],
    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the conversion value will be sent with or without VAT.', 'biano'),
    'visibility' => Shop::CONTEXT_ALL,
];

$fields_form[1]['form'] = [
    'legend' => [
        'title' => $this->module->l('Biano Star', 'biano'),
        'icon' => 'icon-cogs'
    ],
    'input' => [
        [
            'name' => BianoStarClass::ACTIVE,
            'label' => $this->module->l('Active', 'biano'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => BianoStarClass::ACTIVE . 'on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => BianoStarClass::ACTIVE . 'off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Biano Star is dependent on Biano Pixel. You must first activate the Pixel function and then Biano Star.', 'biano'),
        ],
        [
            'name' => 'mergado_fake_field',
            'label' => $this->module->l('Edit consent to the questionnaire', 'biano'),
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('Here you can edit the sentence of the consent to the sending of the questionnaire, displayed on the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'biano'),
        ],
        [
            'name' => BianoStarClass::SHIPMENT_IN_STOCK,
            'label' => $this->module->l('In stock', 'biano'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => BianoStarClass::SHIPMENT_BACKORDER,
            'label' => $this->module->l('backorder', 'biano'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => BianoStarClass::SHIPMENT_OUT_OF_STOCK,
            'label' => $this->module->l('out of stock', 'biano'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => 'mergado_fake_field',
            'label' => $this->module->l('Edit consent to the questionnaire', 'biano'),
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('Here you can edit the sentence of the consent to the sending of the questionnaire, displayed on the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'biano'),
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

foreach ($this->languages->getLanguages(true) as $key => $lang) {
    $langName = LanguagesClass::getLangIso(strtoupper($lang['iso_code']));

    $fields_form[1]['form']['input'][] = [
        'name' => BianoStarClass::OPT_OUT . $langName,
        'label' => $langName,
        'type' => 'text',
        'visibility' => Shop::CONTEXT_ALL,
    ];
}


include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
