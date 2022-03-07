<?php

use Mergado\Etarget\EtargetClass;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->module->l('Etarget', 'etarget'),
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
            'name' => EtargetClass::ACTIVE,
            'label' => $this->module->l('ETARGET', 'etarget'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'etarget_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'etarget_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => EtargetClass::ID,
            'label' => $this->module->l('ETARGET ID', 'etarget'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => EtargetClass::HASH,
            'label' => $this->module->l('Hash', 'etarget'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
