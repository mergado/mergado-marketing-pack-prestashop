<?php declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Endpoint;

use Mergado\Service\ProductPriceImportService;
use Mergado\Traits\SingletonTrait;
use Mergado\Utility\JsonResponse;

class AdminImportPricesEndpoint implements ParametrizedEndpointInterface
{
    use SingletonTrait;

    protected function importPrices($controller): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'ajax_import_prices') {
            $productPriceImportService = ProductPriceImportService::getInstance();
            $result = $productPriceImportService->importPrices();

            // Save lowered value as main if cron is ok without internal error
            if ($productPriceImportService->getLoweredProductsPerStep() !== 0) {
                $productPriceImportService->setLoweredProductsPerStepAsMain();
            }

            if ($result) {
                JsonResponse::send_json_success(["success" => $controller->trans('Mergado prices imported', [], 'mergado'), "feedStatus" => $result]);
            } else {
                JsonResponse::send_json_code(["error" => $controller->trans('Error importing prices. Do you have correct URL in settings?', [], 'mergado')], 424);
            }
            exit;
        }
    }

    public function initEndpoints($controller, $context): void
    {
        $this->importPrices($controller);
    }
}
