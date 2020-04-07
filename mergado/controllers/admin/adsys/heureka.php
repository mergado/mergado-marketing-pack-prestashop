<?php

use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->l('Heureka'),
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
            'label' => $this->l('Heureka.cz verified by users'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_overeno_zakazniky_cz_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_overeno_zakazniky_cz_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL
        ),
        array(
            'name' => SettingsClass::HEUREKA['VERIFIED_CODE_CZ'],
            'label' => $this->l('Heureka.cz verified by users code'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Klíč vašeho obchodu naleznete v administraci Heureka účtu pod záložkou Ověřeno zákazníky > Nastavení a data dotazníků > Tajný klíč pro Ověřeno zákazníky.')
        ),
        array(
            'name' => SettingsClass::HEUREKA['VERIFIED_SK'],
            'label' => $this->l('Heureka.sk verified by users'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_overeno_zakazniky_sk_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_overeno_zakazniky_sk_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['VERIFIED_CODE_SK'],
            'label' => $this->l('Heureka.sk verified by users code'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Klíč vašeho obchodu naleznete v administraci Heureka účtu pod záložkou Ověřeno zákazníky > Nastavení a data dotazníků > Tajný klíč pro Ověřeno zákazníky.')
        ),
        array(
            'name' => SettingsClass::HEUREKA['CONVERSIONS_CZ'],
            'label' => $this->l('Heureka.cz track conversions'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_konverze_cz_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_konverze_cz_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['CONVERSIONS_CODE_CZ'],
            'label' => $this->l('Heureka.cz conversion code'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Klíč měření konverzí vašeho obchodu naleznete v administraci Heureka účtu pod záložkou Statistiky a reporty > Měření konverzí > Veřejný klíč pro kód měření konverzí.')
        ),
        array(
            'name' => SettingsClass::HEUREKA['CONVERSIONS_SK'],
            'label' => $this->l('Heureka.sk track conversions'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_konverze_sk_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_konverze_sk_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['CONVERSIONS_CODE_SK'],
            'label' => $this->l('Heureka.sk conversion code'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->l('Klíč měření konverzí vašeho obchodu naleznete v administraci Heureka účtu pod záložkou Statistiky a reporty > Měření konverzí > Veřejný klíč pro kód měření konverzí.')
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_CZ'],
            'label' => $this->l('Heureka.cz - widget'),
            'hint' => $this->l('You need conversion code to enable this feature'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_widget_cz_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_widget_cz_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::HEUREKA['WIDGET_SK'],
            'label' => $this->l('Heureka.sk - widget'),
            'hint' => $this->l('You need conversion code to enable this feature'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_widget_sk_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_widget_sk_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => 'mergado_heureka_dostupnostni_feed',
            'label' => $this->l('Heureka stock feed'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_heureka_dostupnostni_feed_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'mergado_heureka_dostupnostni_feed_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL
        )
    ),
    'submit' => array(
        'title' => $this->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
