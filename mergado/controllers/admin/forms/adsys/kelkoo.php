<?php

use Mergado\Kelkoo\KelkooClass;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->l('Kelkoo', 'kelkoo'),
        'icon' => 'icon-cogs',
    ],
    'input' => [],
    'submit' => [
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

$fields_form[0]['form']['input'][] = [
    'name' => KelkooClass::ACTIVE,
    'label' => $this->l('Module active', 'kelkoo'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
//    'desc' => $this->l('1. Your website must have HTTPS protocol at least on order confirmation page. 2. You have to set your DNS before use. More informations on: https://www.glami.cz/info/reviews/implementation/') . '<br><span class="mmp-tag mmp-tag--question"></span>' . $this->l('You can find your Glami TOP API key in the Glami Administration at the Glami TOP page > Implementation > Developer Implementation Guide> Javascript Integration section.', 'kelkoo'),
    'class' => 'switch15',
    'values' => [
        [
            'id' => 'kelkoo_active_on',
            'value' => 1,
            'label' => $this->l('Yes')
        ],
        [
            'id' => 'kelkoo_active_off',
            'value' => 0,
            'label' => $this->l('No')
        ]
    ],
    'visibility' => Shop::CONTEXT_ALL,
];

$fields_form[0]['form']['input'][] = [
    'name' => KelkooClass::COUNTRY,
    'label' => $this->l('Kelkoo country', 'kelkoo'),
    'type' => 'select',
    'options' => [
        'query' => KelkooClass::COUNTRIES,
        'id' => 'id_option',
        'name' => 'name'
    ]
];

$fields_form[0]['form']['input'][] = [
    'name' => KelkooClass::COM_ID,
    'label' => $this->l('Kelkoo merchant id', 'kelkoo'),
    'type' => 'text',
    'visibility' => Shop::CONTEXT_ALL,
];

$fields_form[0]['form']['input'][] = [
    'name' => KelkooClass::CONVERSION_VAT_INCL,
    'label' => $this->l('With VAT', 'kelkoo'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'class' => 'switch15',
    'values' => [
        [
            'id' => 'kelkoo_active_on',
            'value' => 1,
            'label' => $this->l('Yes')
        ],
        [
            'id' => 'kelkoo_active_off',
            'value' => 0,
            'label' => $this->l('No')
        ]
    ],
    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Choose whether the conversion value will be sent with or without VAT.', 'kelkoo'),
    'visibility' => Shop::CONTEXT_ALL,
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
