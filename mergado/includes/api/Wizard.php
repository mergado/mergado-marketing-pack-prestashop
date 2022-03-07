<?php

use Mergado\Tools\SettingsClass;
use Mergado\Tools\XMLClass;

if ($_POST['action'] === 'ajax_set_wizard_complete') {
    try {
        $feed = $_POST['feed'] ?? '';

        switch ($feed) {
            case 'product':
                    SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_PRODUCT'], 1, Mergado::getShopId());
                    break;
            case 'static':
                SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_STATIC'], 1, Mergado::getShopId());
                break;
            case 'stock':
                    SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_STOCK'], 1, Mergado::getShopId());
                    break;
            case 'category':
                SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_CATEGORY'], 1, Mergado::getShopId());
                    break;
            case 'import':
                SettingsClass::saveSetting(XMLClass::WIZARD['FINISHED_IMPORT'], 1, Mergado::getShopId());
                    break;
        }

        JsonResponse::send_json_success(["success" => $this->trans('Settings saved', [], 'mergado')]);
    } catch (Exception $e) {
        JsonResponse::send_json_error(['error' => $this->trans('Something went wrong during save.', [], 'mergado')]);
    }
}
