<?php

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

namespace Mergado\includes\services\Google\GoogleAds;

use Mergado;
use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\SettingsClass;

class GoogleAdsService
{
    const CONVERSIONS_ACTIVE = 'mergado_adwords_conversion';
    const ENHANCED_CONVERSION_ACTIVE = 'mmp-google-gads-enhanced-conversions-active';
    const REMARKETING_ACTIVE = 'adwords_remarketing';
    const REMARKETING_TYPE = 'mergado_adwords_remarketing_type';
    const CONVERSIONS_CODE = 'mergado_adwords_conversion_code';
    const CONVERSIONS_LABEL = 'mergado_adwords_conversion_label';
    const CONVERSIONS_VAT_INCLUDED = 'mergado_adwords_conversion_vat_included';
    const CONVERSIONS_SHIPPING_PRICE_INCLUDED = 'mergado_adwords_shipping_price_included';

    const TEMPLATES_PATH = 'includes/services/Google/GoogleAds/templates/';

    const REMARKETING_TYPES = [
        0 => ['id_option' => 0, 'name' => 'Retail', 'value' => 'retail'],
        1 => ['id_option' => 1, 'name' => 'Custom', 'value' => 'custom'],
    ];

    private $conversionsActive;
    private $enhancedConversionsActive;
    private $remarketingActive;
    private $remarketingType;
    private $conversionsCode;
    private $conversionsLabel;
    private $conversionVatIncluded;
    private $shippingPriceIncluded;

    // Main settings variables
    private $multistoreShopId;

    use SingletonTrait;

    protected function __construct()
    {
        $this->multistoreShopId = Mergado::getShopId();
    }

    /*******************************************************************************************************************
     * IS
     *******************************************************************************************************************/

    /**
     * @return bool
     */
    public function isConversionsActive()
    {
        $active = $this->getConversionsActive();
        $code = $this->getConversionsCode();
        $label = $this->getConversionsLabel();

        if ($active === SettingsClass::ENABLED && $code && $code !== '' && $label && $label !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isEnhancedConversionsActive()
    {
        $active = $this->getEnhancedConversionsActive();

        if ($this->isConversionsActive() && $active === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isRemarketingActive()
    {
        $active = $this->getRemarketingActive();
        $code = $this->getConversionsCode();

        if ($active === SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    public function isConversionWithVat(): bool
    {
        return $this->getConversionVatIncluded() == 1;
    }

    public function isConversionShippingPriceIncluded(): bool
    {
        return $this->getConversionShippingPriceIncluded() == 1;
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    /**
     * @return false|string|null
     */
    public function getConversionsActive()
    {
        if (!is_null($this->conversionsActive)) {
            return $this->conversionsActive;
        }

        $this->conversionsActive = SettingsClass::getSettings(self::CONVERSIONS_ACTIVE, $this->multistoreShopId);

        return $this->conversionsActive;
    }

    /**
     * @return false|string|null
     */
    public function getEnhancedConversionsActive()
    {
        if (!is_null($this->enhancedConversionsActive)) {
            return $this->enhancedConversionsActive;
        }

        $this->enhancedConversionsActive = SettingsClass::getSettings(self::ENHANCED_CONVERSION_ACTIVE, $this->multistoreShopId);

        return $this->enhancedConversionsActive;
    }

    /**
     * @return false|string|null
     */
    public function getRemarketingActive()
    {
        if (!is_null($this->remarketingActive)) {
            return $this->remarketingActive;
        }

        $this->remarketingActive = SettingsClass::getSettings(self::REMARKETING_ACTIVE, $this->multistoreShopId);

        return $this->remarketingActive;
    }

    /**
     * @return false|string|null
     */
    public function getConversionsCode($raw = false)
    {
        if (!is_null($this->conversionsCode)) {
            return $this->conversionsCode;
        }

        $code = SettingsClass::getSettings(self::CONVERSIONS_CODE, $this->multistoreShopId);

        if (!$raw) {
            if (trim($code) !== '' && substr( $code, 0, 3 ) !== "AW-") {
                $this->conversionsCode = 'AW-' . $code;
            } else {
                $this->conversionsCode = $code;
            }
        }

        return $this->conversionsCode;
    }

    /**
     * @return false|string|null
     */
    public function getConversionsLabel()
    {
        if (!is_null($this->conversionsLabel)) {
            return $this->conversionsLabel;
        }

        $this->conversionsLabel = SettingsClass::getSettings(self::CONVERSIONS_LABEL, $this->multistoreShopId);

        return $this->conversionsLabel;
    }

    /**
     * @return false|string|null
     */
    public function getRemarketingType()
    {
        if (!is_null($this->remarketingType)) {
            return $this->remarketingType;
        }

        $this->remarketingType = SettingsClass::getSettings(self::REMARKETING_TYPE, $this->multistoreShopId);

        return $this->remarketingType;
    }

    /**
     * @return mixed
     */
    public function getRemarketingTypeForTemplate()
    {
        return self::REMARKETING_TYPES[$this->getRemarketingType()]['value'];
    }

    /**
     * @return boolean
     */
    public function getConversionVatIncluded()
    {
        if ( ! is_null( $this->conversionVatIncluded ) ) {
            return $this->conversionVatIncluded;
        }

        $this->conversionVatIncluded = SettingsClass::getSettings(self::CONVERSIONS_VAT_INCLUDED, $this->multistoreShopId);

        return $this->conversionVatIncluded;
    }

    /**
     * @return boolean
     */
    public function getConversionShippingPriceIncluded()
    {
        if ( ! is_null( $this->shippingPriceIncluded ) ) {
            return $this->shippingPriceIncluded;
        }

        $this->shippingPriceIncluded = SettingsClass::getSettings(self::CONVERSIONS_SHIPPING_PRICE_INCLUDED, $this->multistoreShopId);

        return $this->shippingPriceIncluded;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    /**
     * @return array[]
     */
    public static function getToggleFields()
    {
        return [
            self::CONVERSIONS_ACTIVE => [
                'fields' => [
                    self::ENHANCED_CONVERSION_ACTIVE,
                    self::CONVERSIONS_LABEL,
                    self::CONVERSIONS_VAT_INCLUDED,
                    self::CONVERSIONS_SHIPPING_PRICE_INCLUDED
                ]
            ],
        ];
    }
}
