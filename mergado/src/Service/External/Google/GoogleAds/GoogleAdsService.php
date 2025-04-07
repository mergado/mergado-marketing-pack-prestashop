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
 * @author    www.mergado.cz
 * @copyright 2016 Mergado technologies, s. r. o.
 * @license   LICENSE.txt
 */

namespace Mergado\Service\External\Google\GoogleAds;

use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class GoogleAdsService extends AbstractBaseService
{
    public const FIELD_CONVERSIONS_ACTIVE = 'mergado_adwords_conversion';
    public const FIELD_ENHANCED_CONVERSION_ACTIVE = 'mmp-google-gads-enhanced-conversions-active';
    public const FIELD_REMARKETING_ACTIVE = 'adwords_remarketing';
    public const FIELD_REMARKETING_TYPE = 'mergado_adwords_remarketing_type';
    public const FIELD_CONVERSIONS_CODE = 'mergado_adwords_conversion_code';
    public const FIELD_CONVERSIONS_LABEL = 'mergado_adwords_conversion_label';
    public const FIELD_CONVERSIONS_VAT_INCLUDED = 'mergado_adwords_conversion_vat_included';
    public const FIELD_CONVERSIONS_SHIPPING_PRICE_INCLUDED = 'mergado_adwords_shipping_price_included';

    public const REMARKETING_TYPES = [
        0 => ['id_option' => 0, 'name' => 'Retail', 'value' => 'retail'],
        1 => ['id_option' => 1, 'name' => 'Custom', 'value' => 'custom'],
    ];

    /*******************************************************************************************************************
     * IS
     *******************************************************************************************************************/

    /**
     * @return bool
     */
    public function isConversionsActive(): bool
    {
        $active = $this->getConversionsActive();
        $code = $this->getConversionsCode();
        $label = $this->getConversionsLabel();

        return $active === SettingsService::ENABLED && $code && $code !== '' && $label && $label !== '';
    }

    public function isEnhancedConversionsActive(): bool
    {
        $active = $this->getEnhancedConversionsActive();

        return $this->isConversionsActive() && $active === SettingsService::ENABLED;
    }

    public function isRemarketingActive(): bool
    {
        $active = $this->getRemarketingActive();
        $code = $this->getConversionsCode();

        return $active === SettingsService::ENABLED && $code && $code !== '';
    }

    public function isConversionWithVat(): bool
    {
        return $this->getConversionVatIncluded() === SettingsService::ENABLED;
    }

    public function isConversionShippingPriceIncluded(): bool
    {
        return $this->getConversionShippingPriceIncluded() === SettingsService::ENABLED;
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    public function getConversionsActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS_ACTIVE, 0);
    }

    public function getEnhancedConversionsActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ENHANCED_CONVERSION_ACTIVE, 0);
    }

    public function getRemarketingActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_REMARKETING_ACTIVE, 0);
    }

    public function getConversionsCode($raw = false): string
    {
        $code = (string)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS_CODE, '');

        if (!$raw && trim($code) !== '' && strpos($code, "AW-") !== 0) {
            return 'AW-' . $code;
        }

        return $code;
    }

    public function getConversionsLabel(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS_LABEL, '');
    }

    public function getRemarketingType(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_REMARKETING_TYPE, 0);
    }

    public function getRemarketingTypeForTemplate(): string
    {
        return self::REMARKETING_TYPES[$this->getRemarketingType()]['value'];
    }

    public function getConversionVatIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS_VAT_INCLUDED, 0);
    }

    public function getConversionShippingPriceIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS_SHIPPING_PRICE_INCLUDED, 0);
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {
        return [
            self::FIELD_CONVERSIONS_ACTIVE => [
                'fields' => [
                    self::FIELD_ENHANCED_CONVERSION_ACTIVE,
                    self::FIELD_CONVERSIONS_LABEL,
                    self::FIELD_CONVERSIONS_VAT_INCLUDED,
                    self::FIELD_CONVERSIONS_SHIPPING_PRICE_INCLUDED,
                ]
            ],
            self::FIELD_REMARKETING_ACTIVE => [
                'fields' => [
                    self::FIELD_REMARKETING_TYPE
                ]
            ]
        ];
    }
}
