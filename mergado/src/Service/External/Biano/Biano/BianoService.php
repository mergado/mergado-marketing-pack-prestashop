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

namespace Mergado\Service\External\Biano\Biano;

use Context;
use Link;
use Mergado\Helper\LanguageHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class BianoService extends AbstractBaseService
{
    public const FIELD_ACTIVE = 'biano_active';
    public const FIELD_MERCHANT_ID = 'biano_merchant_id';
    public const FIELD_ACTIVE_LANG = 'biano-form-active-lang';
    public const FIELD_CONVERSION_VAT_INCl = 'biano_conversion_vat_incl';
    public const LANG_OPTIONS = ['CZ', 'SK', 'RO', 'NL', 'HU'];

    /*******************************************************************************************************************
     * BIANO ACTIVATION
     ******************************************************************************************************************/

    public function isActive($lang): bool
    {
        $active = $this->getActive();
        $activeLanguage = $this->getLanguageActive($lang);
        $merchantId = $this->getMerchantId($lang);

        return $active === SettingsService::ENABLED && $merchantId && $merchantId !== '' && $activeLanguage === SettingsService::ENABLED;
    }

    public function isConversionWithVat(): bool
    {
        return $this->getConversionVatIncluded() === 1;
    }

    /*******************************************************************************************************************
     * GET VALUES
     *******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getLanguageActive($lang): int
    {
        $name = self::getActiveLangFieldName($lang);

        return (int)DatabaseManager::getSettingsFromCache($name);
    }

    public function getMerchantId($lang): string
    {
        $name = self::getMerchantIdFieldName($lang);

        return (string)DatabaseManager::getSettingsFromCache($name, '');
    }

    public function getConversionVatIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSION_VAT_INCl, 0);
    }

    /*******************************************************************************************************************
     * GET VALUES - STATIC
     ******************************************************************************************************************/

    public static function getActiveLangFieldName($langCode): string
    {
        return self::FIELD_ACTIVE_LANG . '-' . $langCode;
    }

    public static function getMerchantIdFieldName($langCode): string
    {
        return self::FIELD_MERCHANT_ID . '-' . $langCode;
    }


    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields($languages): array
    {
        $bianoFields = [];
        $bianoMainFields = [];

        foreach ($languages as $lang) {
            $langName = LanguageHelper::getLang(strtoupper($lang['iso_code']));

            //Get names for language
            $langFieldName = self::getActiveLangFieldName($langName);
            $merchantIdFieldName = self::getMerchantIdFieldName($langName);

            //Assign to arrays
            $bianoMainFields[] = self::getActiveLangFieldName($langName);
            $bianoMainFields[] = self::FIELD_CONVERSION_VAT_INCl;
            $bianoFields[$langFieldName]['fields'] = [$merchantIdFieldName];
        }

        return [
            self::FIELD_ACTIVE => [
                'fields' => $bianoMainFields,
                'sub-check' => $bianoFields,
            ],
        ];
    }
}

