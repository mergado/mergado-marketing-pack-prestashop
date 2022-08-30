<?php

namespace Mergado\includes\services\Google\GoogleUniversalAnalytics;

use Mergado;
use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\SettingsClass;
use OrderStateCore;

class GoogleUniversalAnalyticsService
{
    const ACTIVE = 'mergado_google_analytics_active';
    const CODE = 'mergado_google_analytics_code';
//    const TRACKING = 'mergado_google_analytics_tracking'; //TODO: REMOVE from templates and code
    const ECOMMERCE = 'mergado_google_analytics_ecommerce';
    const ECOMMERCE_ENHANCED = 'mergado_google_analytics_ecommerce_enhanced';
    const CONVERSION_VAT_INCL = 'mergado_google_analytics_conversion_vat_incl';

    const TEMPLATES_PATH = 'includes/services/Google/GoogleUniversalAnalytics/templates/';

    private $active;
    private $code;
    private $ecommerce;
    private $ecommerceEnhanced;
    private $conversionVatIncluded;

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

    public function isActiveEnhancedEcommerce()
    {
        $activeEnhancedEcommerce = $this->getEnhancedEcommerce();

        if ($this->isActiveEcommerce() && $activeEnhancedEcommerce == '1') {
            return true;
        }

        return false;
    }

    /**
     * GET
     */

    /**
     * @return false
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

        $this->code = SettingsClass::getSettings(self::CODE, $this->multistoreShopId);

        if (!$raw) {
            if (trim($this->code) !== '' && substr($this->code, 0, 3) !== "UA-") {
                $this->code = 'UA-' . $this->code;
            }
        }


        return $this->code;
    }

    /**
     * @return false
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
     * @return false
     */
    public function getEnhancedEcommerce(): bool
    {
        if ( ! is_null( $this->ecommerceEnhanced ) ) {
            return $this->ecommerceEnhanced;
        }

        $this->ecommerceEnhanced = SettingsClass::getSettings(self::ECOMMERCE_ENHANCED, $this->multistoreShopId);

        return $this->ecommerceEnhanced;
    }

    /**
     * @return false
     */
    public function getConversionVatIncluded(): bool
    {
        if ( ! is_null( $this->conversionVatIncluded ) ) {
            return $this->conversionVatIncluded;
        }

        $this->conversionVatIncluded = SettingsClass::getSettings(self::CONVERSION_VAT_INCL, $this->multistoreShopId);// TODO: default ON = 1

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
        global $cookie;
        $orderStates = new OrderStateCore();
        $states = $orderStates->getOrderStates($cookie->id_lang);

        $fields = [self::CODE];
        foreach ($states as $state) {
            $fields[] = Mergado\Google\GaRefundClass::STATUS . $state['id_order_state'];
        }

        return [
            self::ACTIVE => [
                'fields' => [
                    self::CODE,
                    self::ECOMMERCE,
                    self::ECOMMERCE_ENHANCED,
                    self::CONVERSION_VAT_INCL,
                    $fields
                ]
            ],
        ];
    }
}
