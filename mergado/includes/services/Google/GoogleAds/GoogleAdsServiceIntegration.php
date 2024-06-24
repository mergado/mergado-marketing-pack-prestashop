<?php

namespace Mergado\includes\services\Google\GoogleAds;

use ContextCore;
use CurrencyCore;
use Mergado;
use Mergado\includes\helpers\CustomerHelper;
use Mergado\includes\traits\SingletonTrait;

class GoogleAdsServiceIntegration
{
    /**
     * @var GoogleAdsService
     */

    private $googleAdsService;

    /**
     * @var false|string|null
     */
    private $sendTo;

    use SingletonTrait;

    protected function __construct()
    {
        $this->googleAdsService = GoogleAdsService::getInstance();
        $this->sendTo = $this->googleAdsService->getConversionsCode();
    }

    public function conversion($orderId, $currencyIso, $module, $smarty, $path)
    {
        if ($this->googleAdsService->isConversionsActive()) {

            $smarty->assign(array(
                'gads_sendTo' => sprintf("%s/%s", $this->googleAdsService->getConversionsCode(), $this->googleAdsService->getConversionsLabel()),
                'gads_currency' => $currencyIso,
                'gads_transactionId' => $orderId,
                'gads_withVat' => $this->googleAdsService->isConversionWithVat(),
                'gads_withShipping' => $this->googleAdsService->isConversionShippingPriceIncluded(),
            ));

            return $module->display($path, GoogleAdsService::TEMPLATES_PATH . 'conversion.tpl');
        }

        return '';
    }

}
