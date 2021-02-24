<?php

use Mergado\NajNakup\NajNakupClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Najnakup.sk', 'najnakupsk'),
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
            'name' => NajNakupClass::CONVERSIONS,
            'label' => $this->module->l('Najnakup track conversions', 'najnakupsk'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_najnakup_konverze_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_najnakup_konverze_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => NajNakupClass::SHOP_ID,
            'label' => $this->module->l('Najnakup shop ID', 'najnakupsk'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Your unique store ID for Najnakup.sk', 'najnakupsk'),
            'visibility' => Shop::CONTEXT_ALL,
        )
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
