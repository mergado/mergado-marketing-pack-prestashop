<?php

use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Pricemania', 'pricemania'),
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
            'name' => SettingsClass::PRICEMANIA['VERIFIED'],
            'label' => $this->module->l('Verified shop', 'pricemania'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_pricemania_overeny_obchod_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_pricemania_overeny_obchod_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::PRICEMANIA['SHOP_ID'],
            'label' => $this->module->l('Pricemania shop ID', 'pricemania'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Your unique Store ID from Pricemania.', 'pricemania'),
            'visibility' => Shop::CONTEXT_ALL,
        )
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
