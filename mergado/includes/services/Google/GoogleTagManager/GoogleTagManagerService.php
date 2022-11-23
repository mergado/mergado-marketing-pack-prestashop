<?php

namespace Mergado\includes\services\Google\GoogleTagManager;

use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\SettingsClass;

class GoogleTagManagerService
{
    const ACTIVE = 'mergado_google_tag_manager_active';
    const CODE = 'mergado_google_tag_manager_code';
    const ECOMMERCE_ACTIVE = 'mergado_google_tag_manager_ecommerce';
    const ECOMMERCE_ENHANCED_ACTIVE = 'mergado_google_tag_manager_ecommerce_enhanced';
    const CONVERSION_VAT_INCL = 'mergado_google_tag_manager_conversion_vat_incl';
    const VIEW_LIST_ITEMS_COUNT = 'mergado_google_tag_manager_view_list_items_count';

    const TEMPLATES_PATH = 'includes/services/Google/GoogleTagManager/templates/';
    const HELPERS_PATH = 'includes/services/Google/GoogleTagManager/helpers/';

    private $active;
    private $code;
    private $ecommerceActive;
    private $enhancedEcommerceActive;
    private $conversionVatIncluded;
    private $viewListItemsCount;

    private $multistoreShopId;

    use SingletonTrait;

    protected function __construct()
    {
        $this->multistoreShopId = \Mergado::getShopId();
    }

    /******************************************************************************************************************
     * IS
     ******************************************************************************************************************/

    /**
     * @return bool
     */
    public function isActive()
    {
        $active = $this->getActive();
        $code = $this->getCode();

        if ($active === SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isEcommerceActive()
    {
        $active = $this->getActive();
        $code = $this->getCode();
        $ecommerceActive = $this->getEcommerceActive();

        if ($active === SettingsClass::ENABLED && $code && $code !== '' && $ecommerceActive === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isEnhancedEcommerceActive()
    {
        $active = $this->getActive();
        $code = $this->getCode();
        $ecommerceActive = $this->getEcommerceActive();
        $enhancedEcommerceActive = $this->getEnhancedEcommerceActive();

        if ($active === SettingsClass::ENABLED && $code && $code !== '' && $ecommerceActive === SettingsClass::ENABLED && $enhancedEcommerceActive === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }


    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    /**
     * @return false|string|null
     */
    public function getActive()
    {
        if (!is_null($this->active)) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(self::ACTIVE, $this->multistoreShopId);

        return $this->active;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        if (!is_null($this->code)) {
            return $this->code;
        }

        $code = SettingsClass::getSettings(self::CODE, $this->multistoreShopId);

        if (trim($code) !== '' && substr( $code, 0, 4 ) !== "GTM-") {
            $this->code = 'GTM-' . $code;
        } else {
            $this->code = $code;
        }

        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getEcommerceActive()
    {
        if (!is_null($this->ecommerceActive)) {
            return $this->ecommerceActive;
        }

        $this->ecommerceActive = SettingsClass::getSettings(self::ECOMMERCE_ACTIVE, $this->multistoreShopId);

        return $this->ecommerceActive;
    }

    /**
     * @return mixed
     */
    public function getEnhancedEcommerceActive()
    {
        if (!is_null($this->enhancedEcommerceActive)) {
            return $this->enhancedEcommerceActive;
        }

        $this->enhancedEcommerceActive = SettingsClass::getSettings(self::ECOMMERCE_ENHANCED_ACTIVE, $this->multistoreShopId);

        return $this->enhancedEcommerceActive;
    }

    /**
     * @return false|string|null
     */
    public function getConversionVatIncluded() : bool
    {
        if (!is_null($this->conversionVatIncluded)) {
            return $this->conversionVatIncluded;
        }

        $this->conversionVatIncluded = SettingsClass::getSettings(self::CONVERSION_VAT_INCL, $this->multistoreShopId);

        // Default value is true
        if ($this->conversionVatIncluded === false) {
            $this->conversionVatIncluded = true;
        }

        return $this->conversionVatIncluded;
    }

    /**
     * @return mixed
     */
    public function getViewListItemsCount()
    {
        if (!is_null($this->viewListItemsCount)) {
            return $this->viewListItemsCount;
        }

        $viewListItemsCount = SettingsClass::getSettings(self::VIEW_LIST_ITEMS_COUNT, $this->multistoreShopId);

        if (trim($viewListItemsCount) === '') {
            $this->viewListItemsCount = 0;
        } else {
            $this->viewListItemsCount = $viewListItemsCount;
        }

        return $this->viewListItemsCount;
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
                    self::ECOMMERCE_ACTIVE,
                    self::ECOMMERCE_ENHANCED_ACTIVE,
                    self::CONVERSION_VAT_INCL,
                ],
                'sub-check' => [
                    self::ECOMMERCE_ACTIVE => [
                        'fields' => [
                            self::ECOMMERCE_ENHANCED_ACTIVE,
                        ],
                    ],
                ]
            ],
        ];
    }
}
