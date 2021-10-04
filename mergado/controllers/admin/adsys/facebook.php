<?php

use Mergado\Facebook\FacebookClass;
use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Facebook pixel', 'facebook'),
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
            'label' => $this->module->l('Facebook pixel', 'facebook'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'fb_pixel_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'fb_pixel_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => FacebookClass::CODE,
            'label' => $this->module->l('Facebook pixel ID', 'facebook'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Pixel ID can be found in your Facebook Business Manager. Go to Events Manager > Add new data feed > Facebook pixel. Pixel ID is displayed below the title on the Overview page at the top left.', 'facebook'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => FacebookClass::CONVERSION_VAT_INCL,
            'label' => $this->module->l('With VAT', 'facebook'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'fbpixel_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'fbpixel_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the conversion value will be sent with or without VAT.', 'facebook'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
