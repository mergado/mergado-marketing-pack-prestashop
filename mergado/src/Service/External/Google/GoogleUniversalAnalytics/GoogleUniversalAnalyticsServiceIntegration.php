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


namespace Mergado\Service\External\Google\GoogleUniversalAnalytics;

use Media;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\Google\Gtag\GtagIntegrationHelper;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class GoogleUniversalAnalyticsServiceIntegration extends AbstractBaseService
{
    /**
     * @var GoogleUniversalAnalyticsService
     */
    private $googleUniversalAnalyticsService;

    public const TEMPLATES_PATH = 'views/templates/services/GoogleUniversalAnalytics/';
    public const JS_PATH = 'views/js/services/GoogleUniversalAnalytics/';

    protected function __construct()
    {
        $this->googleUniversalAnalyticsService = GoogleUniversalAnalyticsService::getInstance();

        parent::__construct();
    }

    /*******************************************************************************************************************
     * GTAG JS
     ******************************************************************************************************************/

    public function purchase($orderId, $order, $orderProducts, $module, $context, $smarty): string
    {
        try {
            if(!$this->googleUniversalAnalyticsService->isActiveEcommerce()) {
                return '';
            }

            $withVat = $this->googleUniversalAnalyticsService->getConversionVatIncluded();
            $sendTo = $this->googleUniversalAnalyticsService->getCode();

            $data = GtagIntegrationHelper::getOrderDataForAds($order, $orderId, $withVat, true, $sendTo);
            $data['items'] = GtagIntegrationHelper::getOrderProductsForAds($orderProducts, (int)$context->language->id, $withVat);

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

    public function userClickedProduct($context, $path): void
    {
        try {
            if (!$this->googleUniversalAnalyticsService->isActiveEnhancedEcommerce()) {
                return;
            }

            if (isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"]) {
                global $smarty;

                if (PrestashopVersionHelper::is16AndLower()) {
                    $shopUrl = $smarty->tpl_vars['base_dir']->value;
                } else {
                    $shopUrl = $smarty->tpl_vars['urls']->value['shop_domain_url'];
                }

                if (strpos($_SERVER["HTTP_REFERER"], $shopUrl) !== false) {
                    $context->controller->addJS($path . self::JS_PATH . 'productClick.js');
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function insertScripts($context, $path): void
    {
        if ($this->googleUniversalAnalyticsService->isActiveEnhancedEcommerce()) {
            Media::addJsDef(
                [
                    'mergado_gua_settings' => [
                        'withVat' => $this->googleUniversalAnalyticsService->getConversionVatIncluded(),
                        'sendTo' => $this->googleUniversalAnalyticsService->getCode(),
                    ],
                ]
            );

            $context->controller->addJS($path . self::JS_PATH . 'enhanced.js');
        }
    }
}
