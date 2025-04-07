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

use Mergado\Service\AlertService;
use Mergado\Traits\SingletonTrait;

class AdminAlertEndpoint implements EndpointInterface
{
    use SingletonTrait;

    /**
     * @var AlertService
     */
    private $alertService;

    public function __construct()
    {
        $this->alertService = AlertService::getInstance();
    }

    protected function addAlert(): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'ajax_add_alert') {
            $alertName = $_POST['name'] ?? '';
            $feedName = $_POST['feed'] ?? '';

            if ($alertName !== '' && $feedName !== '') {
                $this->alertService->setErrorActive($feedName, $alertName);
                exit;
            }

            exit;
        }
    }

    protected function disableAlert(): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'ajax_disable_alert') {
            $alertName = $_POST['name'] ?? '';
            $feedName = $_POST['feed'] ?? '';

            $this->alertService->setAlertDisabled($feedName, $alertName);

            exit;
        }
    }

    protected function disableSection(): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'ajax_disable_section') {
            $sectionName = $_POST['section'] ?? '';

            if ($sectionName !== '') {
                $this->alertService->setSectionDisabled($sectionName);
                exit;
            }

            exit;
        }
    }

    public function initEndpoints(): void
    {
        $this->addAlert();
        $this->disableAlert();
        $this->disableSection();
    }
}
