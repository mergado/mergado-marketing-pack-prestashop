<?php

use Mergado\Etarget\EtargetClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Etarget', 'etarget'),
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
            'name' => EtargetClass::ACTIVE,
            'label' => $this->module->l('ETARGET', 'etarget'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'etarget_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'etarget_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => EtargetClass::ID,
            'label' => $this->module->l('ETARGET ID', 'etarget'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => EtargetClass::HASH,
            'label' => $this->module->l('Hash', 'etarget'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
