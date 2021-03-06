<?php

use Mergado\Tools\SettingsClass;

// Heureka.cz - VERIFIED

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Heureka.cz : Verified by customers', 'heureka'),
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
            'name' => SettingsClass::HEUREKA['VERIFIED_CZ'],
            'label' => $this->module->l('Heureka.cz verified by users', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_overeno_zakazniky_cz_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_overeno_zakazniky_cz_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL
        ),
        array(
            'name' => SettingsClass::HEUREKA['VERIFIED_CODE_CZ'],
            'label' => $this->module->l('Heureka.cz verified by users code', 'heureka'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your store key in the Heureka account administration under Verified customers > Settings and questionnaire data > Secret Key for verified customers.', 'heureka')
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_CZ'],
            'label' => $this->module->l('Heureka.cz - widget', 'heureka'),
            'hint' => $this->module->l('You need conversion code to enable this feature', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_widget_cz_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_widget_cz_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_ID_CZ'],
            'label' => $this->module->l('Widget Id', 'heureka'),
            'type' => 'text',
            'placeholder' => 'Insert Widget Id',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('The ID is the same as the Public Key for conversion tracking. Or you can find the key of your widget in the Heureka account administration under the tab Verified customers > Settings and questionnaire data > Certificate icons Verified customers. The numeric code is in the embed code. It takes the form "... setKey\',\'330BD_YOUR_WIDGET_KEY_2A80\']); _ hwq.push\' ..."', 'heureka')
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_POSITION_CZ'],
            'label' => $this->module->l('Widget position', 'heureka'),
            'type' => 'select',
            'options' => array(
                'query' => array(
                    array('id_option' => 21, 'name' => 'Left'),
                    array('id_option' => 22, 'name' => 'Right'),
                ),
                'id' => 'id_option',
                'name' => 'name'
            ),
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_TOP_MARGIN_CZ'],
            'label' => $this->module->l('Widget top margin', 'heureka'),
            'type' => 'text',
            'placeholder' => '60',
            'suffix' => 'px',
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_MOBILE_CZ'],
            'label' => $this->module->l('Show widget on mobile', 'heureka'),
            'hint' => $this->module->l('You need to turn on widget switch to enable this feature', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_widget_mobile_cz_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_widget_mobile_cz_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('If this option is enabled, the widget will appear on mobile devices regardless of the width setting for hiding the widget.', 'heureka')
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_SCREEN_WIDTH_CZ'],
            'label' => $this->module->l('Hide on screens smaller than'),
            'type' => 'text',
            'placeholder' => 'Min. width to show',
            'suffix' => 'px',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('The setting to hide the widget below a certain screen width (in px) is only valid for desktops. On mobile devices, this setting is ignored.', 'heureka')
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

// Heureka.cz - COVNERSIONS

$fields_form[1]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Heureka.cz : Conversions tracking', 'heureka'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'name' => SettingsClass::HEUREKA['CONVERSIONS_CZ'],
            'label' => $this->module->l('Heureka.cz track conversions', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_konverze_cz_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_konverze_cz_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ'],
            'label' => $this->module->l('Heureka.cz conversion code', 'heureka'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your store conversion tracking key in the Heureka account administration under the Statistics and Reports > Conversion Tracking > Public Key for Conversion Tracking Code.', 'heureka')
        ),
        array(
            'name' => SettingsClass::HEUREKA['CONVERSION_VAT_INCL_CZ'],
            'label' => $this->module->l('With VAT', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'heureka_conv_cz_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'heureka_conv_cz_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the conversion value will be sent with or without VAT. Note: In the specification of conversion tracking, Heureka recommends the price of the order and shipping to be including VAT.', 'heureka'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

// Heureka.sk - VERIFIED

$fields_form[2]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Heureka.sk : Verified by customers', 'heureka'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'name' => SettingsClass::HEUREKA['VERIFIED_SK'],
            'label' => $this->module->l('Heureka.sk verified by users', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_overeno_zakazniky_sk_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_overeno_zakazniky_sk_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['VERIFIED_CODE_SK'],
            'label' => $this->module->l('Heureka.sk verified by users code', 'heureka'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your store key in the Heureka account administration under Verified customers > Settings and questionnaire data > Secret Key for verified customers.', 'heureka')
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_SK'],
            'label' => $this->module->l('Heureka.sk - widget', 'heureka'),
            'hint' => $this->module->l('You need conversion code to enable this feature', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_widget_sk_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_widget_sk_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_ID_SK'],
            'label' => $this->module->l('Widget Id', 'heureka'),
            'type' => 'text',
            'placeholder' => 'Insert Widget Id',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('The ID is the same as the Public Key for conversion tracking. Or you can find the key of your widget in the Heureka account administration under the tab Verified customers > Settings and questionnaire data > Certificate icons Verified customers. The numeric code is in the embed code. It takes the form "... setKey\',\'330BD_YOUR_WIDGET_KEY_2A80\']); _ hwq.push\' ..."', 'heureka')
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_POSITION_SK'],
            'label' => $this->module->l('Widget position', 'heureka'),
            'type' => 'select',
            'suffix' => 'px',
            'options' => array(
                'query' => array(
                    array('id_option' => 21, 'name' => 'Left'),
                    array('id_option' => 22, 'name' => 'Right'),
                ),
                'id' => 'id_option',
                'name' => 'name'
            ),
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_TOP_MARGIN_SK'],
            'label' => $this->module->l('Widget top margin', 'heureka'),
            'type' => 'text',
            'placeholder' => '60',
            'suffix' => 'px',
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_MOBILE_SK'],
            'label' => $this->module->l('Show widget on mobile', 'heureka'),
            'hint' => $this->module->l('You need to turn on widget switch to enable this feature', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_widget_mobile_sk_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_widget_mobile_sk_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('If this option is enabled, the widget will appear on mobile devices regardless of the width setting for hiding the widget.', 'heureka')
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_SCREEN_WIDTH_SK'],
            'label' => $this->module->l('Hide on screens smaller than', 'heureka'),
            'type' => 'text',
            'placeholder' => 'Min. width to show',
            'suffix' => 'px',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('The setting to hide the widget below a certain screen width (in px) is only valid for desktops. On mobile devices, this setting is ignored.', 'heureka')
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

$fields_form[3]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Heureka.sk : Conversions tracking', 'heureka'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'name' => SettingsClass::HEUREKA['CONVERSIONS_SK'],
            'label' => $this->module->l('Heureka.sk track conversions', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_konverze_sk_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_konverze_sk_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['CONVERSIONS_CODE_SK'],
            'label' => $this->module->l('Heureka.sk conversion code', 'heureka'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your store conversion tracking key in the Heureka account administration under the Statistics and Reports > Conversion Tracking > Public Key for Conversion Tracking Code.', 'heureka')
        ),
        array(
            'name' => SettingsClass::HEUREKA['CONVERSION_VAT_INCL_SK'],
            'label' => $this->module->l('With VAT', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'heureka_conv_sk_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'heureka_conv_sk_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the conversion value will be sent with or without VAT. Note: In the specification of conversion tracking, Heureka recommends the price of the order and shipping to be including VAT.', 'heureka'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

$fields_form[4]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Heureka : Other settings', 'heureka'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'name' => 'mergado_heureka_dostupnostni_feed',
            'label' => $this->module->l('Heureka stock feed', 'heureka'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_dostupnostni_feed_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_dostupnostni_feed_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('After activation, the Heureka availability feed will be available in the XML feed tab.', 'heureka'),
        ),
        array(
            'name' => 'mergado_fake_field',
            'label' => $this->module->l('Edit text of consent', 'heureka'),
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('Here you can edit the text of the sentence of consent to the sending of the questionnaire, displayed in the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'heureka'),
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

foreach ($this->languages->getLanguages(true) as $key => $lang) {
    $langName = SettingsClass::getLangIso(strtoupper($lang['iso_code']));

    $fields_form[4]['form']['input'][] = array(
        'name' => 'mergado_heureka_opt_out_text' . '-' . $langName,
        'label' => $this->module->l('Editing consent to the questionnaire', 'heureka') . ' ' . $langName,
        'type' => 'text',
        'visibility' => Shop::CONTEXT_ALL,
    );
}

include __DIR__ . '/partials/helperForm.php';
