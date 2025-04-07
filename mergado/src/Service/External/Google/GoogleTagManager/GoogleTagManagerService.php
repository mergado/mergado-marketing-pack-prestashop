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


namespace Mergado\Service\External\Google\GoogleTagManager;

use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class GoogleTagManagerService extends AbstractBaseService
{
    public const FIELD_ACTIVE = 'mergado_google_tag_manager_active';
    public const FIELD_CODE = 'mergado_google_tag_manager_code';
    public const FIELD_ECOMMERCE_ACTIVE = 'mergado_google_tag_manager_ecommerce';
    public const FIELD_ECOMMERCE_ENHANCED_ACTIVE = 'mergado_google_tag_manager_ecommerce_enhanced';
    public const FIELD_SEND_CUSTOMER_DATA_ACTIVE = 'mergado_google_tag_manager_send_customer_data_active';
    public const FIELD_CONVERSION_VAT_INCL = 'mergado_google_tag_manager_conversion_vat_incl';
    public const FIELD_VIEW_LIST_ITEMS_COUNT = 'mergado_google_tag_manager_view_list_items_count';

    /******************************************************************************************************************
     * IS
     ******************************************************************************************************************/

    public function isActive(): bool
    {
        $active = $this->getActive();
        $code = $this->getCode();

        return $active === SettingsService::ENABLED && $code && $code !== '';
    }

    public function isEcommerceActive(): bool
    {
        $active = $this->getActive();
        $code = $this->getCode();
        $ecommerceActive = $this->getEcommerceActive();

        return $active === SettingsService::ENABLED && $code && $code !== '' && $ecommerceActive === SettingsService::ENABLED;
    }

    public function isEnhancedEcommerceActive(): bool
    {
        $active = $this->getActive();
        $code = $this->getCode();
        $ecommerceActive = $this->getEcommerceActive();
        $enhancedEcommerceActive = $this->getEnhancedEcommerceActive();

        return $active === SettingsService::ENABLED && $code && $code !== '' && $ecommerceActive === SettingsService::ENABLED && $enhancedEcommerceActive === SettingsService::ENABLED;
    }

    public function isSendCustomerDataActive(): bool
    {
        $active = $this->getSendCustomerDataActive();

        return $active === SettingsService::ENABLED && $this->isEnhancedEcommerceActive();
    }


    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getCode(): string
    {
        $code = DatabaseManager::getSettingsFromCache(self::FIELD_CODE, '');

        if (trim($code) !== '' && strpos($code, "GTM-") !== 0) {
            return 'GTM-' . $code;
        }

        return $code;
    }

    public function getEcommerceActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ECOMMERCE_ACTIVE, 0);
    }

    public function getEnhancedEcommerceActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ECOMMERCE_ENHANCED_ACTIVE, 0);
    }

    public function getSendCustomerDataActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_SEND_CUSTOMER_DATA_ACTIVE, 0);
    }

    public function getConversionVatIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSION_VAT_INCL, 1);
    }

    public function getViewListItemsCount(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_VIEW_LIST_ITEMS_COUNT, 0);
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
                    self::FIELD_ECOMMERCE_ACTIVE,
                    self::FIELD_ECOMMERCE_ENHANCED_ACTIVE,
                    self::FIELD_CONVERSION_VAT_INCL,
                ],
                'sub-check' => [
                    self::FIELD_ECOMMERCE_ACTIVE => [
                        'fields' => [
                            self::FIELD_ECOMMERCE_ENHANCED_ACTIVE,
                        ],
                        'sub-check' => [
                            self::FIELD_ECOMMERCE_ENHANCED_ACTIVE => [
                                'fields' => [
                                    self::FIELD_SEND_CUSTOMER_DATA_ACTIVE,
                                ],
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }
}
