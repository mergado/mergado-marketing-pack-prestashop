<?php

use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->l('Glami pixel'),
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
    ),
    'submit' => array(
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    )
);

$fields_form[0]['form']['input'][] = array(
    'name' => SettingsClass::GLAMI['ACTIVE'],
    'label' => $this->l('Module active'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'class' => 'switch15',
    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Váš piXel naleznete v administraci Glami na stránce Glami piXel > Implementace Glami piXel pro vývojáře > sekce Glami piXel kód pro VÁŠ ESHOP.'),
    'values' => array(
        array(
            'id' => 'glami_active_on',
            'value' => 1,
            'label' => $this->l('Yes')
        ),
        array(
            'id' => 'glami_active_off',
            'value' => 0,
            'label' => $this->l('No')
        )
    ),
    'visibility' => Shop::CONTEXT_ALL,
);

foreach (SettingsClass::GLAMI_LANGUAGES as $key => $lang) {
    $fields_form[0]['form']['input'][] = array(
        'name' => $lang,
        'label' => $key,
        'validation' => 'isBool',
        'cast' => 'intval',
        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
        'class' => 'switch15',
        'values' => array(
            array(
                'id' => 'glami_active_on',
                'value' => 1,
                'label' => $this->l('Yes')
            ),
            array(
                'id' => 'glami_active_off',
                'value' => 0,
                'label' => $this->l('No')
            )
        ),
        'visibility' => Shop::CONTEXT_ALL,
    );

    $fields_form[0]['form']['input'][] = array(
        'name' => SettingsClass::GLAMI['CODE'] . '-' . $key,
        'label' => $this->l('Glami Pixel') . ' ' . $key,
        'type' => 'text',
        'visibility' => Shop::CONTEXT_ALL,
    );
}

$fields_form[1]['form'] = array(
    'legend' => array(
        'title' => $this->l('Glami TOP'),
        'icon' => 'icon-cogs',
    ),
    'input' => array(),
    'submit' => array(
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    )
);

$fields_form[1]['form']['input'][] = array(
    'name' => SettingsClass::GLAMI['ACTIVE_TOP'],
    'label' => $this->l('Module active'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'desc' => $this->l('1. Your website must have HTTPS protocol at least on order confirmation page. 2. You have to set your DNS before use. More informations on: https://www.glami.cz/info/reviews/implementation/') . '<br><span class="mmp-tag mmp-tag--question"></span>' . $this->l('Váš API klíč pro Glami TOP naleznete v administraci Glami na stránce Glami TOP > Implementace > Průvodce implementace pro vývojáře > sekce Integrace pomocí Javascriptu.'),
    'class' => 'switch15',
    'values' => array(
        array(
            'id' => 'glami_top_active_on',
            'value' => 1,
            'label' => $this->l('Yes')
        ),
        array(
            'id' => 'glami_top_active_off',
            'value' => 0,
            'label' => $this->l('No')
        )
    ),
    'visibility' => Shop::CONTEXT_ALL,
);

$fields_form[1]['form']['input'][] = array(
    'name' => SettingsClass::GLAMI['SELECTION_TOP'],
    'label' => $this->l('Glami website'),
    'type' => 'select',
    'options' => array(
        'query' => SettingsClass::GLAMI_TOP_LANGUAGES,
        'id' => 'id_option',
        'name' => 'name'
    )
);

$fields_form[1]['form']['input'][] = array(
    'name' => SettingsClass::GLAMI['CODE_TOP'],
    'label' => $this->l('Glami TOP'),
    'type' => 'text',
    'visibility' => Shop::CONTEXT_ALL,
);

include __DIR__ . '/partials/helperForm.php';
