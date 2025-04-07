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


namespace Mergado\Service;

use Mergado\Endpoint\AdminAlertEndpoint;
use Mergado\Endpoint\AdminFeedDeletionEndpoint;
use Mergado\Endpoint\AdminFeedGenerationEndpoint;
use Mergado\Endpoint\AdminImportPricesEndpoint;
use Mergado\Endpoint\AdminNewsEndpoint;
use Mergado\Endpoint\AdminWizardEndpoint;
use Mergado\Traits\SingletonTrait;

class ApiService extends AbstractBaseService
{
    use SingletonTrait;

    public function initAdminEndpoints($controller, $context): void
    {
        AdminAlertEndpoint::getInstance()->initEndpoints();
        AdminNewsEndpoint::getInstance()->initEndpoints($controller, $context);
        AdminImportPricesEndpoint::getInstance()->initEndpoints($controller, $context);
        AdminFeedDeletionEndpoint::getInstance()->initEndpoints();
        AdminWizardEndpoint::getInstance()->initEndpoints();
        AdminFeedGenerationEndpoint::getInstance()->initEndpoints($controller, $context);
    }
}
