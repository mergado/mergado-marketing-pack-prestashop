<?php

use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->module->l('Pricemania', 'pricemania'),
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
            'name' => SettingsClass::PRICEMANIA['VERIFIED'],
            'label' => $this->module->l('Verified shop', 'pricemania'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_pricemania_overeny_obchod_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_pricemania_overeny_obchod_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => SettingsClass::PRICEMANIA['SHOP_ID'],
            'label' => $this->module->l('Pricemania shop ID', 'pricemania'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Your unique Store ID from Pricemania.', 'pricemania'),
            'visibility' => Shop::CONTEXT_ALL,
        ]
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
