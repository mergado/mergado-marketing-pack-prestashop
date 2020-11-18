<?php

use Mergado\Facebook\FacebookClass;
use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->l('Facebook pixel'),
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
            'name' => FacebookClass::ACTIVE,
            'label' => $this->l('Facebook pixel'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'fb_pixel_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'fb_pixel_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => FacebookClass::CODE,
            'label' => $this->l('Facebook pixel ID'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Pixel ID naleznete v administraci Business Manager pro Facebook. Přejděte do nastavení Správce událostí > Přidat nový zdroj dat > Facebook pixel a vytvořte pixel. Na stránce Přehled daného pixelu vlevo nahoře je pod názvem zobrazené pixel ID.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => FacebookClass::CONVERSION_VAT_INCL,
            'label' => $this->l('With VAT'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'fbpixel_active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'fbpixel_active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->l('Choose whether the conversion value will be sent with or without VAT.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
