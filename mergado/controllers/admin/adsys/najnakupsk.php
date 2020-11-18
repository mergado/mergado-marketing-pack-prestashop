<?php

use Mergado\NajNakup\NajNakupClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->l('Najnakup.sk'),
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
            'label' => $this->l('Najnakup track conversions'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_najnakup_konverze_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_najnakup_konverze_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => NajNakupClass::SHOP_ID,
            'label' => $this->l('Najnakup shop ID'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Vaše jedinečné ID obchodu z Najnakup.sk.'),
            'visibility' => Shop::CONTEXT_ALL,
        )
    ),
    'submit' => array(
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
