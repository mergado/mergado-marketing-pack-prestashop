<?php

use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->l('Zbozi.cz'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'type' => 'hidden',
            'name' => 'page'
        ),
        array(
            'type' => 'hidden',
            'name' => 'id_shop'
        ),
        array(
            'name' => SettingsClass::ZBOZI['CONVERSIONS'],
            'label' => $this->l('Zbozi track conversions'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_zbozi_konverze_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_zbozi_konverze_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::ZBOZI['CONVERSIONS_ADVANCED'],
            'label' => $this->l('Standard conversion measuring'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'values' => array(
                array(
                    'id' => 'mergado_zbozi_advanced_konverze_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_zbozi_advanced_konverze_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Narozdíl od omezeného měření umožní Standardní měření konverzí mít přehled o počtu a hodnotě konverzí, a dále také o konverzním poměru, ceně za konverzi, přímých konverzích, počtu prodaných kusů, apod.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::ZBOZI['SHOP_ID'],
            'label' => $this->l('Zbozi shop ID'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Vaše ID provozovny naleznete v administraci zbozi.cz > Provozovny > ESHOP > Měření konverzí > ID provozovny.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::ZBOZI['SECRET'],
            'label' => $this->l('Secret key'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Váš unikátní tajný klíč naleznete v administraci zbozi.cz > Provozovny > ESHOP > Měření konverzí > Váš unikátní tajný klíč.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    )
);

$fields_form[1]['form'] = array(
    'legend' => array(
        'title' => $this->l('Sklik'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'name' => SettingsClass::SKLIK['CONVERSIONS'],
            'label' => $this->l('Sklik track conversions'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'values' => array(
                array(
                    'id' => 'mergado_sklik_konverze_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_sklik_konverze_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::SKLIK['CONVERSIONS_CODE'],
            'label' => $this->l('Sklik conversion code'),
            'desc' => $this->l('You can find the code in Sklik → Tools → Conversion Tracking → Conversion Detail / Create New Conversion. The code is in the generated HTML conversion code after: src = "// c.imedia.cz/checkConversion?c=CONVERSION CODE'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::SKLIK['CONVERSIONS_VALUE'],
            'label' => $this->l('Sklik value'),
            'type' => 'text',
            'desc' => $this->l('Leave blank to fill the order value automatically. Total price excluding VAT and shipping is calculated.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::SKLIK['RETARGETING'],
            'label' => $this->l('Sklik retargting'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'seznam_retargeting_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'seznam_retargeting_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::SKLIK['RETARGETING_ID'],
            'label' => $this->l('Sklik retargeting ID'),
            'type' => 'text',
            'desc' => $this->l('The code can be found in Sklik → Tools → Retargeting → View retargeting code. The code is in the generated script after: var list_retargeting_id = RETARGETING CODE'),
            'visibility' => Shop::CONTEXT_ALL,
        )
    ),
    'submit' => array(
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
