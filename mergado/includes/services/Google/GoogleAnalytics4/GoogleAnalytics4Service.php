<?php

namespace Mergado\includes\services\Google\GoogleAnalytics4;

use Mergado;
use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\SettingsClass;

class GoogleAnalytics4Service
{
    const ACTIVE = 'mmp-ga-ua-active';
    const CODE = 'mmp-ga-ua-code';
    const ECOMMERCE = 'mmp-ga-ua-ecommerce';
    const SHIPPING_PRICE_INCL = 'mmp-ga-ua-shipping-included';
    const CONVERSION_VAT_INCL = 'mmp-ga-ua-vat-included';
    const REFUND_STATUS = 'mmp-ga4-refund-status';
    const REFUND_API_SECRET = 'mmp-ga4-refund-api-secret';

    const REFUND_URL = 'https://www.google-analytics.com/mp/collect';
    const REFUND_DEBUG_URL = 'https://www.google-analytics.com/debug/mp/collect';

    const TEMPLATES_PATH = 'includes/services/Google/GoogleUniversalAnalytics/templates/';

    private $active;
    private $code;
    private $ecommerce;
    private $shippingPriceIncluded;
    private $conversionVatIncluded;
    private $refundApiSecret;

    private $multistoreShopId;

    use SingletonTrait;

    protected function __construct()
    {
        $this->multistoreShopId = Mergado::getShopId();
    }

    /**
     * IS
     */

    /**
     * @return bool
     */

    public function isActive() : bool
    {
        $active = $this->getActive();
        $code = $this->getCode();

        if ($active == '1' && $code !== '') {
            return true;
        }

        return false;
    }

    public function isActiveEcommerce()
    {
        $activeEcommerce = $this->getEcommerce();

        if ($this->isActive() && $activeEcommerce == '1') {
            return true;
        }

        return false;
    }

    public function isRefundActive()
    {
        if ($this->isActiveEcommerce() && $this->getRefundApiSecret() !== '') {
            return true;
        }

        return false;
    }

    public function isRefundStatusActive($statusId)
    {
        $active = $this->getRefundStatus($statusId);

        if ($active === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * GET
     */

    public function getRefundApiSecret()
    {
        if (!is_null($this->refundApiSecret)) {
            return $this->refundApiSecret;
        }

        $this->refundApiSecret = SettingsClass::getSettings(self::REFUND_API_SECRET, $this->multistoreShopId);

        return $this->refundApiSecret;
    }

    /**
     * @param string $statusId
     * @return false|string|null
     */
    public function getRefundStatus(string $statusId)
    {
        return SettingsClass::getSettings(self::REFUND_STATUS . $statusId, $this->multistoreShopId);
    }

    /**
     * @return boolean
     */
    public function getActive(): bool
    {
        if ( ! is_null( $this->active ) ) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(self::ACTIVE, $this->multistoreShopId);

        return $this->active;
    }


    /**
     * @param bool $raw
     * @return false|string|null
     */
    public function getCode(bool $raw = false)
    {
        if (!is_null($this->code)) {
            return $this->code;
        }

        $code = SettingsClass::getSettings(self::CODE, $this->multistoreShopId);

        if (!$raw) {
            if (trim($code) !== '' && substr($code, 0, 2) !== "G-") {
                $this->code = 'G-' . $code;
            } else {
                $this->code = $code;
            }
        }

        return $this->code;
    }

    /**
     * @return boolean
     */
    public function getEcommerce(): bool
    {
        if ( ! is_null( $this->ecommerce ) ) {
            return $this->ecommerce;
        }

        $this->ecommerce = SettingsClass::getSettings(self::ECOMMERCE, $this->multistoreShopId);

        return $this->ecommerce;
    }

    /**
     * @return boolean
     */
    public function getShippingPriceIncluded(): bool
    {
        if ( ! is_null( $this->shippingPriceIncluded ) ) {
            return $this->shippingPriceIncluded;
        }

        $this->shippingPriceIncluded = SettingsClass::getSettings(self::SHIPPING_PRICE_INCL, $this->multistoreShopId);

        return $this->shippingPriceIncluded;
    }

    /**
     * @return boolean
     */
    public function getConversionVatIncluded(): bool
    {
        if ( ! is_null( $this->conversionVatIncluded ) ) {
            return $this->conversionVatIncluded;
        }

        $this->conversionVatIncluded = SettingsClass::getSettings(self::CONVERSION_VAT_INCL, $this->multistoreShopId);

        return $this->conversionVatIncluded;
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
            self::ACTIVE => [
                'fields' => [
                    self::CODE,
                    self::ECOMMERCE,
                    self::CONVERSION_VAT_INCL,
                    self::SHIPPING_PRICE_INCL,
                    self::REFUND_STATUS,
                    self::REFUND_API_SECRET
                ]
            ],
        ];
    }
}
