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

use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\Google\GaRefundService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;
use OrderState;

class GoogleUniversalAnalyticsService extends AbstractBaseService
{
    public const FIELD_ACTIVE = 'mergado_google_analytics_active';
    public const FIELD_CODE = 'mergado_google_analytics_code';
//    public const TRACKING = 'mergado_google_analytics_tracking'; //TODO: REMOVE from templates and code
    public const FIELD_ECOMMERCE = 'mergado_google_analytics_ecommerce';
    public const FIELD_ECOMMERCE_ENHANCED = 'mergado_google_analytics_ecommerce_enhanced';
    public const FIELD_CONVERSION_VAT_INCL = 'mergado_google_analytics_conversion_vat_incl';

    /**
     * IS
     */

    public function isActive(): bool
    {
        $active = $this->getActive();
        $code = $this->getCode();

        return $active === SettingsService::ENABLED && $code !== '';
    }

    public function isActiveEcommerce(): bool
    {
        $activeEcommerce = $this->getEcommerce();

        return $this->isActive() && $activeEcommerce === SettingsService::ENABLED;
    }

    public function isActiveEnhancedEcommerce(): bool
    {
        $activeEnhancedEcommerce = $this->getEnhancedEcommerce();

        return $this->isActiveEcommerce() && $activeEnhancedEcommerce === SettingsService::ENABLED;
    }

    /**
     * GET
     */

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getCode(bool $raw = false): string
    {
        $code = (string)DatabaseManager::getSettingsFromCache(self::FIELD_CODE, '');

        if (!$raw && trim($code) !== '' && strpos($code, "UA-") !== 0) {
            return 'UA-' . $code;
        }

        return $code;
    }

    public function getEcommerce(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ECOMMERCE, 0);
    }

    public function getEnhancedEcommerce(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ECOMMERCE_ENHANCED, 0);
    }

    public function getConversionVatIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSION_VAT_INCL, 1);
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {
        global $cookie;
        $orderStates = new OrderState();
        $states = $orderStates->getOrderStates($cookie->id_lang);

        $fields = [self::FIELD_CODE];
        foreach ($states as $state) {
            $fields[] = GaRefundService::STATUS . $state['id_order_state'];
        }

        return [
            self::FIELD_ACTIVE => [
                'fields' => [
                    self::FIELD_CODE,
                    self::FIELD_ECOMMERCE,
                    self::FIELD_ECOMMERCE_ENHANCED,
                    self::FIELD_CONVERSION_VAT_INCL,
                    $fields
                ],
                'sub-check' => [
                    self::FIELD_ECOMMERCE => [
                        'fields' => [
                            self::FIELD_ECOMMERCE_ENHANCED,
                        ],
                    ],
                ]
            ],
        ];
    }
}
