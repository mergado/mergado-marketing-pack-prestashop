<?php

use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->l('Biano pixel'),
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
    'name' => SettingsClass::BIANO['ACTIVE'],
    'label' => $this->l('Module active'),
    'validation' => 'isBool',
    'cast' => 'intval',
    'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
    'class' => 'switch15',
    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Biano Pixel merchantId is available only for CZ and SK languages. Other languages will be using default option.'),
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

foreach ($this->languages->getLanguages(true) as $key => $lang) {
    $langName = SettingsClass::getLangIso(strtoupper($lang['iso_code']));

    $fields_form[0]['form']['input'][] = array(
        'name' => \Mergado\Biano\BianoClass::getActiveLangFieldName($langName),
        'label' => $langName,
        'validation' => 'isBool',
        'cast' => 'intval',
        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
        'class' => 'switch15',
        'values' => array(
            array(
                'id' => 'biano_active_on',
                'value' => 1,
                'label' => $this->l('Yes')
            ),
            array(
                'id' => 'biano_active_off',
                'value' => 0,
                'label' => $this->l('No')
            )
        ),
        'visibility' => Shop::CONTEXT_ALL,
    );

    if(in_array($langName, SettingsClass::BIANO['LANG_OPTIONS'])) {
        $fields_form[0]['form']['input'][] = array(
            'name' => \Mergado\Biano\BianoClass::getMerchantIdFieldName($langName),
            'label' => $this->l('Merchant ID') . ' ' . $langName,
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('You can get your merchantId by sending an email to info@biano.cz or info@biano.sk'),
            'visibility' => Shop::CONTEXT_ALL,
        );
    }
}

include __DIR__ . '/partials/helperForm.php';
