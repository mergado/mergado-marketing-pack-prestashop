<?php

use Mergado\Sklik\SklikClass;
use Mergado\Tools\LanguagesClass;
use Mergado\Tools\SettingsClass;
use Mergado\Zbozi\ZboziClass;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->module->l('Sklik', 'seznam'),
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
            'name' => SklikClass::CONVERSIONS_ACTIVE,
            'label' => $this->module->l('Sklik track conversions', 'seznam'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'values' => [
                [
                    'id' => 'mergado_sklik_konverze_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_sklik_konverze_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => SklikClass::CONVERSIONS_CODE,
            'label' => $this->module->l('Sklik conversion code', 'seznam'),
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find the code in Sklik → Tools → Conversion Tracking → Conversion Detail / Create New Conversion. The code is in the generated HTML conversion code after: id: ', 'seznam'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => SklikClass::CONVERSIONS_VALUE,
            'label' => $this->module->l('Sklik value', 'seznam'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Leave blank to fill the order value automatically. Total price excluding VAT and shipping is calculated.', 'seznam'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => SklikClass::CONVERSION_VAT_INCL,
            'label' => $this->module->l('Sklik conversions with VAT', 'seznam'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'sklik_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'sklik_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the conversion value will be sent with or without VAT. Note: In the specification of conversion tracking, Sklik recommends the conversion value to be excluding VAT.', 'seznam'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => SklikClass::RETARGETING_ACTIVE,
            'label' => $this->module->l('Sklik retargting', 'seznam'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'seznam_retargeting_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'seznam_retargeting_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => SklikClass::RETARGETING_ID,
            'label' => $this->module->l('Sklik retargeting ID', 'seznam'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('The code can be found in Sklik → Tools → Retargeting → View retargeting code. The code is in the generated script after: var list_retargeting_id = RETARGETING CODE', 'seznam'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ],
];

$fields_form[1]['form'] = [
    'legend' => [
        'title' => $this->module->l('Zbozi.cz', 'seznam'),
        'icon' => 'icon-cogs'
    ],
    'input' => [
        [
            'name' => ZboziClass::ACTIVE,
            'label' => $this->module->l('Zbozi track conversions', 'seznam'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_zbozi_konverze_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_zbozi_konverze_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => ZboziClass::ADVANCED_ACTIVE,
            'label' => $this->module->l('Standard conversion measuring', 'seznam'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'values' => [
                [
                    'id' => 'mergado_zbozi_advanced_konverze_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_zbozi_advanced_konverze_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Unlike limited tracking, Standard Conversion Tracking allows you to keep track of the number and value of conversions, as well as conversion rate, cost per conversion, direct conversions, units sold, etc', 'seznam'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => ZboziClass::SHOP_ID,
            'label' => $this->module->l('Zbozi.cz store ID', 'seznam'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your unique store ID in admin page zbozi.cz > Branches > ESHOP > Conversion Tracking > Store ID', 'seznam'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => ZboziClass::KEY,
            'label' => $this->module->l('Secret key', 'seznam'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your unique Secret Key in admin page zbozi.cz > Branches > ESHOP > Conversion Tracking > Your unique Secret Key.', 'seznam'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => ZboziClass::VAT_INCL,
            'label' => $this->module->l('With VAT', 'seznam'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'zbozi_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'zbozi_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the conversion value will be sent with or without VAT. Note: In the specification of conversion tracking, Zboží.cz recommends the price of the order and shipping to be including VAT.', 'seznam'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => 'mergado_fake_field',
            'label' => $this->module->l('Edit text of consent', 'seznam'),
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('Here you can edit the text of the sentence of consent to the sending of the questionnaire, displayed in the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'seznam'),
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
        'name' => ZboziClass::OPT_OUT . $langName,
        'label' => $this->module->l('Editing consent to the questionnaire', 'seznam') . ' ' . $langName,
        'type' => 'text',
        'visibility' => Shop::CONTEXT_ALL,
    ];
}

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
