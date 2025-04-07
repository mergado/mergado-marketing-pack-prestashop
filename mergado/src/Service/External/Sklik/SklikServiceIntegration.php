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


namespace Mergado\Service\External\Sklik;

use Mergado;
use Mergado\Helper\ControllerHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\CookieService;
use Mergado\Service\Data\CustomerDataService;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class SklikServiceIntegration extends AbstractBaseService
{
    /**
     * @var SklikService
     */
    private $sklikService;

    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    /**
     * @var CustomerDataService
     */
    private $customerDataService;

    /**
     * @var CookieService
     */
    private $cookieService;

    public const TEMPLATES_PATH = 'views/templates/services/Sklik/';

    protected function __construct()
    {
        $this->sklikService = SklikService::getInstance();
        $this->controllerHelper = ControllerHelper::getInstance();
        $this->customerDataService = CustomerDataService::getInstance();
        $this->cookieService = CookieService::getInstance();

        parent::__construct();
    }

    public function retargeting(Mergado $module, $context, $smarty): string
    {
        try {
            if (!$this->sklikService->isRetargetingActive()) {
                return '';
            }

            if ($this->controllerHelper->isOrderConfirmation()) {
                $customerData = $this->customerDataService->getCustomerInfoOnOrderPage($context->controller->id_order);
            } else {
                $customerData = $this->customerDataService->getCustomerInfo();
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'retargeting.tpl',
                $smarty,
                [
                    'customerData' => count($customerData) > 0 ? $customerData : false,
                    'seznam_retargeting_id' => $this->sklikService->getRetargetingId(),
                    'seznam_consent_advertisement' => (int)$this->cookieService->advertismentEnabled()
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function conversion(Mergado $module, $smarty, $context, $order): string
    {
        try {
            if (!$this->sklikService->isConversionsActive()) {
                return '';
            }

            if ($this->controllerHelper->isOrderConfirmation()) {
                $customerData = $this->customerDataService->getCustomerInfoOnOrderPage($context->controller->id_order);
            } else {
                $customerData = $this->customerDataService->getCustomerInfo();
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'conversion.tpl',
                $smarty,
                [
                    'customerData' => count($customerData) > 0 ? $customerData : false,
                    'sklikData' => $this->getConversionsData($order),
                    'sklikConsent' => (bool)$this->cookieService->advertismentEnabled()
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    private function getConversionsData($order): array
    {
        $conversionCode = $this->sklikService->getConversionsCode();
        $conversionValue = $this->sklikService->getConversionsValue();

        // Value of order preset by user
        if (trim($conversionValue) === '') {

            // If user selected with or without VAT
            if ($this->sklikService->getConversionsVatIncluded()) {
                $conversionValue = (float)$order->total_products_wt;
            } else {
                $conversionValue = (float)$order->total_products;
            }
        }

        return [
            'conversionCode' => $conversionCode,
            'conversionValue' => $conversionValue,
        ];
    }
}
