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

namespace Mergado\Service\External\Sklik;

use Mergado;
use Mergado\Manager\DatabaseManager;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;

class SklikService extends AbstractBaseService
{
    public const FIELD_CONVERSIONS_ACTIVE = 'mergado_sklik_konverze';
    public const FIELD_CONVERSIONS_CODE = 'mergado_sklik_konverze_kod';
    public const FIELD_CONVERSIONS_VALUE = 'mergado_sklik_konverze_hodnota';
    public const FIELD_CONVERSION_VAT_INCL = 'sklik_conversion_vat_incl';

    public const FIELD_RETARGETING_ACTIVE = 'seznam_retargeting';
    public const FIELD_RETARGETING_ID = 'seznam_retargeting_id';

    /******************************************************************************************************************
     * CONVERSIONS
     *****************************************************************************************************************/

    public function isConversionsActive(): bool
    {
        $active = $this->getConversionsActive();
        $code = $this->getConversionsCode();

        return $active === SettingsService::ENABLED && $code && $code !== '';
    }

    /******************************************************************************************************************
     * RETARGETING
     *****************************************************************************************************************/

    public function isRetargetingActive(): bool
    {
        $active = $this->getRetargetingActive();
        $code = $this->getRetargetingId();

        return $active === Mergado\Service\SettingsService::ENABLED && $code && $code !== '';
    }


    /*******************************************************************************************************************
     * GET FIELD
     ******************************************************************************************************************/

    public function getConversionsActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS_ACTIVE, 0);
    }

    public function getConversionsCode(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS_CODE, '');
    }

    public function getConversionsValue(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS_VALUE, '');
    }

    public function getConversionsVatIncluded(): int
    {
        return (int) DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSION_VAT_INCL, 0);
    }

    public function getRetargetingActive(): int
    {
        return (int) DatabaseManager::getSettingsFromCache(self::FIELD_RETARGETING_ACTIVE, 0);
    }

    public function getRetargetingId(): string
    {
        return (string) DatabaseManager::getSettingsFromCache(self::FIELD_RETARGETING_ID, '');
    }


    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {
        return [
            self::FIELD_CONVERSIONS_ACTIVE => [
                'fields' => [
                    self::FIELD_CONVERSIONS_CODE,
                    self::FIELD_CONVERSIONS_VALUE,
                    self::FIELD_CONVERSION_VAT_INCL,
                ]
            ],
            self::FIELD_RETARGETING_ACTIVE => [
                'fields' => [
                    self::FIELD_RETARGETING_ID
                ]
            ],
        ];
    }
}

;
