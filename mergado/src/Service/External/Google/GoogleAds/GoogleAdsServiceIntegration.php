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


namespace Mergado\Service\External\Google\GoogleAds;

use Media;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\Google\Gtag\GtagIntegrationHelper;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class GoogleAdsServiceIntegration extends AbstractBaseService
{
    /**
     * @var GoogleAdsService
     */

    private $googleAdsService;

    public const TEMPLATES_PATH = 'views/templates/services/GoogleAds/';
    public const JS_PATH = 'views/js/services/GoogleAds/';

    protected function __construct()
    {
        $this->googleAdsService = GoogleAdsService::getInstance();

        parent::__construct();
    }

    public function purchase($orderId, $order, $orderProducts, $module, $context, $smarty): string {
        try {
            if (!$this->googleAdsService->isEnhancedConversionsActive()) {
                return '';
            }

            $withVat = $this->googleAdsService->isConversionWithVat();
            $shippingPriceIncluded = $this->googleAdsService->isConversionShippingPriceIncluded();
            $sendTo = sprintf("%s/%s", $this->googleAdsService->getConversionsCode(), $this->googleAdsService->getConversionsLabel());

            $data = GtagIntegrationHelper::getOrderDataForAds($order, $orderId, $withVat, $shippingPriceIncluded, $sendTo);
            $data['items'] = GtagIntegrationHelper::getOrderProductsForAds($orderProducts, (int)$context->language->id, $withVat, $this->googleAdsService->getRemarketingTypeForTemplate());


            return SmartyTemplateLoader::render($module,
                GtagIntegrationHelper::TEMPLATES_PATH . 'purchase.tpl',
                $smarty,
                [
                    'gtag_purchase_data' => json_encode($data, JSON_NUMERIC_CHECK)
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function conversion($orderId, $currencyIso, $module, $smarty): string
    {
        try {
            if (!$this->googleAdsService->isConversionsActive()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'conversion.tpl',
                $smarty,
                [
                    'gads_sendTo' => sprintf("%s/%s", $this->googleAdsService->getConversionsCode(), $this->googleAdsService->getConversionsLabel()),
                    'gads_currency' => $currencyIso,
                    'gads_transactionId' => $orderId,
                    'gads_withVat' => $this->googleAdsService->isConversionWithVat(),
                    'gads_withShipping' => $this->googleAdsService->isConversionShippingPriceIncluded(),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function insertScripts($context, $path): void
    {
        if ($this->googleAdsService->isRemarketingActive()) {
            Media::addJsDef(
                [
                    'mergado_gads_settings' => [
                        'remarketingType' => $this->googleAdsService->getRemarketingTypeForTemplate(),
                        'sendTo' => $this->googleAdsService->getConversionsCode(),
                    ],
                ]
            );

            $context->controller->addJS($path . self::JS_PATH . 'enhanced.js');
        }
    }
}
