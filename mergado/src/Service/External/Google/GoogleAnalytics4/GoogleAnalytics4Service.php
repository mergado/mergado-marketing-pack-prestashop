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


namespace Mergado\Service\External\Google\GoogleAnalytics4;

use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class GoogleAnalytics4Service extends AbstractBaseService
{
    public const FIELD_ACTIVE = 'mmp-ga-ua-active';
    public const FIELD_CODE = 'mmp-ga-ua-code';
    public const FIELD_ECOMMERCE = 'mmp-ga-ua-ecommerce';
    public const FIELD_SHIPPING_PRICE_INCL = 'mmp-ga-ua-shipping-included';
    public const FIELD_CONVERSION_VAT_INCL = 'mmp-ga-ua-vat-included';
    public const FIELD_REFUND_STATUS = 'mmp-ga4-refund-status';
    public const FIELD_REFUND_API_SECRET = 'mmp-ga4-refund-api-secret';

    public const REFUND_URL = 'https://www.google-analytics.com/mp/collect';
    public const REFUND_DEBUG_URL = 'https://www.google-analytics.com/debug/mp/collect';

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

    public function isRefundActive(): bool
    {
        return $this->isActiveEcommerce() && $this->getRefundApiSecret() !== '';
    }

    public function isRefundStatusActive($statusId): bool
    {
        $active = $this->getRefundStatus($statusId);

        return $active === SettingsService::ENABLED;
    }

    /**
     * GET
     */

    public function getRefundApiSecret(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_REFUND_API_SECRET, '');
    }

    public function getRefundStatus($statusId): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_REFUND_STATUS . $statusId, 0);
    }

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getCode(bool $raw = false): string
    {
        $code = (string)DatabaseManager::getSettingsFromCache(self::FIELD_CODE, '');

        if (!$raw && trim($code) !== '' && substr($code, 0, 2) !== "G-") {
            return 'G-' . $code;
        }

        return $code;
    }

    public function getEcommerce(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ECOMMERCE, 0);
    }

    public function getShippingPriceIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_SHIPPING_PRICE_INCL, 0);
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
        return [
            self::FIELD_ACTIVE => [
                'fields' => [
                    self::FIELD_CODE,
                    self::FIELD_ECOMMERCE,
                    self::FIELD_CONVERSION_VAT_INCL,
                    self::FIELD_SHIPPING_PRICE_INCL,
                    self::FIELD_REFUND_STATUS,
                    self::FIELD_REFUND_API_SECRET
                ]
            ],
        ];
    }
}
