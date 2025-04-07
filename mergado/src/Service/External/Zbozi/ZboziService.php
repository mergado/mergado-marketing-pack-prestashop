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

namespace Mergado\Service\External\Zbozi;

use Mergado\Helper\LanguageHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class ZboziService extends AbstractBaseService
{
    public const SERVICE_NAME = 'zbozi';
    public const CONSENT_NAME = 'mergado_zbozi_consent';

    public const FIELD_ACTIVE = 'mergado_zbozi_konverze';
    public const FIELD_ADVANCED_ACTIVE = 'mergado_zbozi_advanced_konverze';
    public const FIELD_VAT_INCL = 'mergado_zbozi_conversion_vat_incl';
    public const FIELD_SHOP_ID = 'mergado_zbozi_shop_id';
    public const FIELD_KEY = 'mergado_zbozi_secret';
    public const FIELD_OPT_OUT = 'zbozi_opt_out_text-';

    public const DEFAULT_OPT = 'Do not send a satisfaction questionnaire within the Zboží.cz program.';

    public function isActive(): bool
    {
        $active = $this->getActive();
        $key = $this->getKey();

        return $active === SettingsService::ENABLED && $key && $key !== '';
    }

    public function isAdvancedActive(): bool
    {
        $active = $this->getAdvancedActive();

        return $active === SettingsService::ENABLED;
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getAdvancedActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ADVANCED_ACTIVE, 0);
    }

    public function getConversionVatIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_VAT_INCL, 1);
    }

    public function getShopId(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_SHOP_ID, '');
    }

    public function getKey(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_KEY, '');
    }

    public function getOptOut($lang): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_OPT_OUT . $lang, '');
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields($languages): array
    {
        $langFields = [];

        foreach ($languages as $key => $lang) {
            $langName = LanguageHelper::getLang(strtoupper($lang['iso_code']));
            $langFields[] = self::FIELD_OPT_OUT . $langName;
        }

        $otherFields = [
            self::FIELD_ADVANCED_ACTIVE,
            self::FIELD_SHOP_ID,
            self::FIELD_KEY,
            self::FIELD_VAT_INCL,
        ];

        return [
            self::FIELD_ACTIVE => [
                'fields' => array_merge($langFields, $otherFields)
            ]
        ];
    }
}

