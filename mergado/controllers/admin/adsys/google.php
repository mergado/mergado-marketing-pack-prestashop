<?php

use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->l('GoogleAds'),
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
            'name' => SettingsClass::GOOGLE_ADS['CONVERSIONS'],
            'label' => $this->l('GoogleAds conversions'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_adwords_conversion_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_adwords_conversion_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_ADS['CONVERSIONS_CODE'],
            'label' => $this->l('GoogleAds conversion code'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Konverzní kód získáte v administraci Google Ads účtu > Nástroje a nastavení > Měření – konverze > Přidat konverzi > Webová stránka. Vytvořte novou konverzi a poté klikněte na Nainstalovat značku sami. Kód se nachází v sekci “Globální značka webu” a má tuto podobu AW-123456789.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_ADS['CONVERSIONS_LABEL'],
            'label' => $this->l('GoogleAds conversion label'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Konverzní štítek najdete na stejné stránce jako konverzní kód. Štítek se nachází v sekci “Fragment události” v elementu send_to v části za lomítkem. Má například podobu /SqrGHAdS-MerfQC.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_ADS['REMARKETING'],
            'label' => $this->l('GoogleAds remarketing'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'adwords_remarketing_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'adwords_remarketing_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Remarketing ID získáte v administraci Google Ads účtu > Nástroje a nastavení > Správce publik > Zdroje publik > Nastavit značku Google Ads. Vytvořte novou značku a poté klikněte na Nainstalovat značku sami. Kód se nachází v sekci “Globální značka webu” a má tuto podobu AW-123456789.'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_ADS['REMARKETING_ID'],
            'label' => $this->l('GoogleAds remarketing ID'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
