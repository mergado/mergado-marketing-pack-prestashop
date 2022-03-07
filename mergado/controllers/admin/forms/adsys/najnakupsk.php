<?php

use Mergado\NajNakup\NajNakupClass;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->module->l('Najnakup.sk', 'najnakupsk'),
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
        [
            'name' => NajNakupClass::CONVERSIONS,
            'label' => $this->module->l('Najnakup track conversions', 'najnakupsk'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_najnakup_konverze_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_najnakup_konverze_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => NajNakupClass::SHOP_ID,
            'label' => $this->module->l('Najnakup shop ID', 'najnakupsk'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Your unique store ID for Najnakup.sk', 'najnakupsk'),
            'visibility' => Shop::CONTEXT_ALL,
        ]
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';