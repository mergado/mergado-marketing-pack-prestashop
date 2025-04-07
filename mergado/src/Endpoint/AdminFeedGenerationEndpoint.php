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

use Exception;
use Mergado\Exception\CronRunningException;
use Mergado\Manager\FeedGeneratorManager;
use Mergado\Service\AlertService;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Service\LogService;
use Mergado\Service\ProductPriceImportService;
use Mergado\Traits\SingletonTrait;
use Mergado\Utility\JsonResponse;
use PrestaShopDatabaseException;
use PrestaShopException;

class AdminFeedGenerationEndpoint implements ParametrizedEndpointInterface
{
    use SingletonTrait;

    /**
     * @var AlertService
     */
    private $alertService;

    /**
     * @var ProductPriceImportService
     */
    private $productPriceImportService;

    public function __construct()
    {
        $this->alertService = AlertService::getInstance();
        $this->productPriceImportService = ProductPriceImportService::getInstance();
    }

    protected function generateFeed(): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'generate_xml') {
            $feed = $_POST['feedName'] ?? '';
            $force = isset($_POST['force']) && $_POST['force'];
            $firstRun = isset($_POST['firstRun']) && $_POST['firstRun'];

            try {
                FeedGeneratorManager::generateFeed($feed, true, $force, $firstRun);
            } catch (Exception $e) {
                (LogService::getInstance())->error($e->getMessage(), ['exception' => $e]);
                JsonResponse::send_json_error(['error' => $e->getMessage()]);
            }
        }
    }

    protected function lowerCronProductStep($controller): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'ajax_lower_cron_product_step') {
            $feed = $_POST['feed'];

            if (ProductFeed::isProductFeed($feed)) {
                $xmlProductFeed = new ProductFeed($feed);
                $xmlProductFeed->deleteTemporaryFiles();

                if ($loweredPerStep = $xmlProductFeed->lowerProductsPerStep()) {
                    JsonResponse::send_json_success(["success" => $controller->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
                } else {
                     $this->alertService->setErrorActive($feed, AlertService::ALERT_NAMES['ERROR_DURING_GENERATION']);
                    JsonResponse::send_json_error(['error' => $controller->trans('Something went wrong. Feed can\'t be generated.', [], 'mergado')]);
                }
            } else if (StockFeed::isStockFeed($feed)) {
                $xmlStockFeed = new StockFeed();
                $xmlStockFeed->deleteTemporaryFiles();

                if ($loweredPerStep = $xmlStockFeed->lowerProductsPerStep()) {
                    JsonResponse::send_json_success(["success" => $controller->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
                } else {
                    $this->alertService->setErrorActive($feed, AlertService::ALERT_NAMES['ERROR_DURING_GENERATION']);
                    JsonResponse::send_json_error(['error' => $controller->trans('Something went wrong. Feed can\'t be generated.', [], 'mergado')]);
                }
            } else if (StaticFeed::isStaticFeed($feed)) {
                $xmlStaticFeed = new StaticFeed();
                $xmlStaticFeed->deleteTemporaryFiles();

                if ($loweredPerStep = $xmlStaticFeed->lowerProductsPerStep()) {
                    JsonResponse::send_json_success(["success" => $controller->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
                } else {
                    $this->alertService->setErrorActive($feed, AlertService::ALERT_NAMES['ERROR_DURING_GENERATION']);
                    JsonResponse::send_json_error(['error' => $controller->trans('Something went wrong. Feed can\'t be generated.', [], 'mergado')]);
                }

            } else if (CategoryFeed::isCategoryFeed($feed)) {
                $xmlCategoryFeed = new CategoryFeed($feed);
                $xmlCategoryFeed->deleteTemporaryFiles();

                if ($loweredPerStep = $xmlCategoryFeed->lowerProductsPerStep()) {
                    JsonResponse::send_json_success(["success" => $controller->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
                } else {
                    $this->alertService->setErrorActive($feed, AlertService::ALERT_NAMES['ERROR_DURING_GENERATION']);
                    JsonResponse::send_json_error(['error' => $controller->trans('Something went wrong. Feed can\'t be generated.', [], 'mergado')]);
                }
            } else if ($feed === 'import') {
                if ($loweredPerStep = $this->productPriceImportService->lowerProductsPerStep()) {
                    JsonResponse::send_json_success(["success" => $controller->trans('Settings saved', [], 'mergado'), "loweredCount" => $loweredPerStep]);
                } else {
                    JsonResponse::send_json_error(['error' => $controller->trans('Something went wrong. Prices can\'t be imported.', [], 'mergado')]);
                }
            }
        }
    }

    protected function saveImportUrl($controller): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'ajax_save_import_url') {
            $url = $_POST['url'] ?? '';

            $result = $this->productPriceImportService->setImportUrl($url);

            if ($result) {
                JsonResponse::send_json_success(["success" => $controller->trans('Settings saved', [], 'mergado')]);
            } else {
                JsonResponse::send_json_error(['error' => $controller->trans('Something went wrong. Import url can\'t be saved.', [], 'mergado')]);
            }
        }
    }

    public function initEndpoints($controller, $context): void
    {
        $this->generateFeed();
        $this->lowerCronProductStep($controller);
        $this->saveImportUrl($controller);
    }
}
