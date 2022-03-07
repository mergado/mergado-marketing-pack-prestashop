<?php

use Mergado\Tools\ImportPricesClass;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLStaticFeed;
use Mergado\Tools\XML\XMLStockFeed;
use Mergado\Tools\XMLClass;


if ($_POST['action'] === 'generate_xml') {
    $feed = isset($_POST['feedName']) ? $_POST['feedName'] : '';
    $force = isset($_POST['force']) ? $_POST['force'] : false;
    $firstRun = isset($_POST['firstRun']) ? $_POST['firstRun'] : false;

    $mergado = new XMLClass();
    $generated = $mergado->generateFeed($feed, true, $force, $firstRun);
}

if ($_POST['action'] === 'ajax_lower_cron_product_step') {
    $feed = $_POST['feed'];

    if (XMLProductFeed::isProductFeed($feed)) {
        $xmlProductFeed = new XMLProductFeed($feed);
        $xmlProductFeed->deleteTemporaryFiles();

        if ($loweredPerStep = $xmlProductFeed->lowerProductsPerStep()) {
            JsonResponse::send_json_success(["success" => $this->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
        } else {
            $alertClass = new AlertClass();
            $alertClass->setErrorActive($feed, AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION']);
            JsonResponse::send_json_error(['error' => $this->trans('Something went wrong. Feed can\'t be generated.', [], 'mergado')]);
        }

    } else if (XMLStockFeed::isStockFeed($feed)) {
        $xmlStockFeed = new XMLStockFeed();
        $xmlStockFeed->deleteTemporaryFiles();

        if ($loweredPerStep = $xmlStockFeed->lowerProductsPerStep()) {
            JsonResponse::send_json_success(["success" => $this->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
        } else {
            $alertClass = new AlertClass();
            $alertClass->setErrorActive($feed, AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION']);
            JsonResponse::send_json_error(['error' => $this->trans('Something went wrong. Feed can\'t be generated.', [], 'mergado')]);
        }
    } else if (XMLStaticFeed::isStaticFeed($feed)) {
        $xmlStaticFeed = new XMLStaticFeed();
        $xmlStaticFeed->deleteTemporaryFiles();

        if ($loweredPerStep = $xmlStaticFeed->lowerProductsPerStep()) {
            JsonResponse::send_json_success(["success" => $this->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
        } else {
            $alertClass = new AlertClass();
            $alertClass->setErrorActive($feed, AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION']);
            JsonResponse::send_json_error(['error' => $this->trans('Something went wrong. Feed can\'t be generated.', [], 'mergado')]);
        }

    } else if (XMLCategoryFeed::isCategoryFeed($feed)) {
        $xmlCategoryFeed = new XMLCategoryFeed($feed);
        $xmlCategoryFeed->deleteTemporaryFiles();

        if ($loweredPerStep = $xmlCategoryFeed->lowerProductsPerStep()) {
            JsonResponse::send_json_success(["success" => $this->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
        } else {
            $alertClass = new AlertClass();
            $alertClass->setErrorActive($feed, AlertClass::ALERT_NAMES['ERROR_DURING_GENERATION']);
            JsonResponse::send_json_error(['error' => $this->trans('Something went wrong. Feed can\'t be generated.', [], 'mergado')]);
        }

    } else if ($feed === 'import') {
        $importPricesClass = new ImportPricesClass();

        if ($loweredPerStep = $importPricesClass->lowerProductsPerStep()) {
            JsonResponse::send_json_success(["success" => $this->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
        } else {
            JsonResponse::send_json_error(['error' => $this->trans('Something went wrong. Prices can\'t be imported.', [], 'mergado')]);
        }
    }
}

if ($_POST['action'] === 'ajax_save_import_url') {
    $url = $_POST['url'] ?? '';

    $importPricesClass = new ImportPricesClass();

    $result = $importPricesClass->setImportUrl($url);

    if ($result) {
        JsonResponse::send_json_success(["success" => $this->trans('Settings saved', [], 'mergado')]);
    } else {
        JsonResponse::send_json_error(['error' => $this->trans('Something went wrong. Import url can\'t be saved.', [], 'mergado')]);
    }
}
