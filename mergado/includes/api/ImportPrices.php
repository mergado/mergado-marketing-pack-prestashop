<?php

use Mergado\Tools\ImportPricesClass;

if ($_POST['action'] === 'ajax_import_prices') {
    $importPrices = new ImportPricesClass();
    $result = $importPrices->importPrices();

    // Save lowered value as main if cron is ok without internal error
    if ($importPrices->getLoweredProductsPerStep() !== 0) {
        $importPrices->setLoweredProductsPerStepAsMain();
    }

    if ($result) {
        JsonResponse::send_json_success(["success" => $this->trans('Mergado prices imported', [], 'mergado'), "feedStatus" => $result]);
    } else {
        JsonResponse::send_json_code(["error" => $this->trans('Error importing prices. Do you have correct URL in settings?', [], 'mergado')], 424);
    }
    exit;
}