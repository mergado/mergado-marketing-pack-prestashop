<?php

use Mergado\includes\services\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\Tools\LanguagesClass;

$defaultFields = [
    'input' => [
        [
            'name' => ArukeresoService::ACTIVE,
            'label' => $this->module->l('Enable Trusted Shop', 'arukereso'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_arukereso_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_arukereso_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => ArukeresoService::WEB_API_KEY,
            'label' => $this->module->l('WebAPI key', 'arukereso'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You will find the WebAPI key in the Arukereso portal under Megbízható Bolt Program > Csatlakozás > Árukereső WebAPI kulcs', 'arukereso'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => 'mergado_fake_field',
            'label' => $this->module->l('Editing consent to the questionnaire', 'arukereso'),
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l(' Here you can edit the sentence of the consent to the sending of the questionnaire, displayed on the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'arukereso'),
        ],
    ]
];

foreach ($this->languages->getLanguages(true) as $key => $lang) {
    $langName = LanguagesClass::getLangIso(strtoupper($lang['iso_code']));

    $defaultFields['input'][] = [
        'name' => ArukeresoService::OPT_OUT . $langName,
        'label' => $langName,
        'type' => 'text',
        'visibility' => Shop::CONTEXT_ALL,
    ];
}

$widgetFields = [
    [
        'name' => ArukeresoService::WIDGET_ACTIVE,
        'label' => $this->module->l('Enable widget Trusted Shop', 'arukereso'),
        'validation' => 'isBool',
        'cast' => 'intval',
        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
        'class' => 'switch15',
        'values' => [
            [
                'id' => 'mergado_arukereso_widget_on',
                'value' => 1,
                'label' => $this->module->l('Yes')
            ],
            [
                'id' => 'mergado_arukereso_widget_off',
                'value' => 0,
                'label' => $this->module->l('No')
            ]
        ],
        'visibility' => Shop::CONTEXT_ALL,
    ],
    [
        'name' => ArukeresoService::WIDGET_DESKTOP_POSITION,
        'label' => $this->module->l('Widget position on desktop', 'arukereso'),
        'type' => 'select',
        'options' => [
            'query' => ArukeresoService::DESKTOP_POSITIONS($this->module),
            'id' => 'id_option',
            'name' => 'name'
        ],
    ],
    [
        'name' => ArukeresoService::WIDGET_APPEARANCE_TYPE,
        'label' => $this->module->l('Appearance type on desktop', 'arukereso'),
        'type' => 'select',
        'class' => 'w-auto-i',
        'options' => [
            'query' => ArukeresoService::APPEARANCE_TYPES($this->module),
            'id' => 'id_option',
            'name' => 'name'
        ],
    ],
    [
        'name' => ArukeresoService::WIDGET_MOBILE_POSITION,
        'label' => $this->l('Widget position on mobile', 'arukereso'),
        'type' => 'select',
        'options' => [
            'query' => ArukeresoService::MOBILE_POSITIONS($this->module),
            'id' => 'id_option',
            'name' => 'name'
        ],
    ],
    [
        'name' => ArukeresoService::WIDGET_MOBILE_WIDTH,
        'label' => $this->l('Width on the mobile', 'arukereso'),
        'type' => 'text',
        'suffix' => 'px',
        'visibility' => Shop::CONTEXT_ALL,
    ],
];

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->l('Árukereső Trusted Shop', 'arukereso'),
        'icon' => 'icon-cogs'
    ],
    'input' => array_merge($defaultFields['input'], $widgetFields),
    'submit' => [
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
