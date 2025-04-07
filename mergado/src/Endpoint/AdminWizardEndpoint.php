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
use Mergado;
use Mergado\Helper\ShopHelper;
use Mergado\Manager\DatabaseManager;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Service\ProductPriceImportService;
use Mergado\Traits\SingletonTrait;
use Mergado\Utility\JsonResponse;

class AdminWizardEndpoint implements EndpointInterface
{
    use SingletonTrait;

    protected function setCompleted(): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'ajax_set_wizard_complete') {
            try {
                $feed = $_POST['feed'] ?? '';

                switch ($feed) {
                    case 'product':
                        DatabaseManager::saveSetting(ProductFeed::WIZARD_FINISHED_DB_NAME, 1, ShopHelper::getId());
                        break;
                    case 'static':
                        DatabaseManager::saveSetting(StaticFeed::WIZARD_FINISHED_DB_NAME, 1, ShopHelper::getId());
                        break;
                    case 'stock':
                        DatabaseManager::saveSetting(StockFeed::WIZARD_FINISHED_DB_NAME, 1, ShopHelper::getId());
                        break;
                    case 'category':
                        DatabaseManager::saveSetting(CategoryFeed::WIZARD_FINISHED_DB_NAME, 1, ShopHelper::getId());
                        break;
                    case 'import':
                        DatabaseManager::saveSetting(ProductPriceImportService::WIZARD_FINISHED_DB_NAME, 1, ShopHelper::getId());
                        break;
                }

                JsonResponse::send_json_success(["success" => 'Settings saved']);
            } catch (Exception $e) {
                JsonResponse::send_json_error(['error' => 'Something went wrong during save.']);
            }
        }
    }

    public function initEndpoints(): void
    {
        $this->setCompleted();
    }
}
