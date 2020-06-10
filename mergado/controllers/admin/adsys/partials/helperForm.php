<?php

$fields_value = [
    'page' => 6,
    'id_shop' => $this->shopID,
];

foreach ($this->settingsValues as $key => $value) {
    if (!isset($fields_value[$key])) {
        $fields_value[$key] = $value;
    }
}

//Fill in empty fields
include __DIR__ . '../../../partials/helperFormEmptyFieldsFiller.php';

$helper = new HelperForm();

$helper->module = $this;
$helper->name_controller = $this->name;

$helper->tpl_vars = array('fields_value' => $fields_value);

if (isset($this->defaultLang)) {
    $helper->default_form_language = $this->defaultLang;
    $helper->allow_employee_form_lang = $this->defaultLang;
}

if (isset($this->displayName)) {
    $helper->title = $this->displayName;
}

$helper->show_toolbar = true;
$helper->toolbar_scroll = true;
$helper->submit_action = 'submit' . $this->name;
if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {
    $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
    $helper->submit_action = 'save' . $this->name;
    $helper->token = Tools::getValue('token');
}

if (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) {

} else {
    $helper->toolbar_btn = array(
        'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
        'back' => array(
            'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
    );
}
?>