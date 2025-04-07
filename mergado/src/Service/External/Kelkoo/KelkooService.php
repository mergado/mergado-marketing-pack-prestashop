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

namespace Mergado\Service\External\Kelkoo;

use Mergado\Manager\DatabaseManager;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;

class KelkooService extends AbstractBaseService
{
    public const FIELD_ACTIVE = 'kelkoo_active';
    public const FIELD_COM_ID = 'kelkoo_merchant_id';
    public const FIELD_COUNTRY = 'kelkoo_country';
    public const FIELD_CONVERSION_VAT_INCL = 'kelkoo_conversion_vat_incl';

    public const COUNTRIES = [
        ['id_option' => 1, 'name' => 'Austria', 'type_code' => 'at'],
        ['id_option' => 2, 'name' => 'Belgium', 'type_code' => 'be'],
        ['id_option' => 3, 'name' => 'Brazil', 'type_code' => 'br'],
        ['id_option' => 4, 'name' => 'Switzerland', 'type_code' => 'ch'],
        ['id_option' => 5, 'name' => 'Czech Republic', 'type_code' => 'cz'],
        ['id_option' => 6, 'name' => 'Germany', 'type_code' => 'de'],
        ['id_option' => 7, 'name' => 'Denmark', 'type_code' => 'dk'],
        ['id_option' => 8, 'name' => 'Spain', 'type_code' => 'es'],
        ['id_option' => 9, 'name' => 'Finland', 'type_code' => 'fi'],
        ['id_option' => 10, 'name' => 'France', 'type_code' => 'fr'],
        ['id_option' => 11, 'name' => 'Ireland', 'type_code' => 'ie'],
        ['id_option' => 12, 'name' => 'Italy', 'type_code' => 'it'],
        ['id_option' => 13, 'name' => 'Mexico', 'type_code' => 'mx'],
        ['id_option' => 14, 'name' => 'Flemish Belgium', 'type_code' => 'nb'],
        ['id_option' => 15, 'name' => 'Netherlands', 'type_code' => 'nl'],
        ['id_option' => 16, 'name' => 'Norway', 'type_code' => 'no'],
        ['id_option' => 17, 'name' => 'Poland', 'type_code' => 'pl'],
        ['id_option' => 18, 'name' => 'Portugal', 'type_code' => 'pt'],
        ['id_option' => 19, 'name' => 'Russia', 'type_code' => 'ru'],
        ['id_option' => 20, 'name' => 'Sweden', 'type_code' => 'se'],
        ['id_option' => 21, 'name' => 'United Kingdom', 'type_code' => 'uk'],
        ['id_option' => 22, 'name' => 'United States', 'type_code' => 'us'],
    ];

    public function isActive(): bool
    {
        $kelkoo_active = $this->getActive();
        $kelkoo_country = $this->getCountry();
        $kelkoo_merchant_id = $this->getComId();

        return $kelkoo_active === SettingsService::ENABLED && $kelkoo_country && $kelkoo_country !== '' && $kelkoo_merchant_id && $kelkoo_merchant_id !== '';
    }

    public function getActiveDomain(): string
    {
        $activeLangId = $this->getCountry();

        foreach (self::COUNTRIES as $item) {
            if ($item['id_option'] === (int)$activeLangId) {
                return $item['type_code'];
            }
        }

        return '';
    }

    /*******************************************************************************************************************
     * GET FIELD
     ******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getComId(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_COM_ID, '');
    }

    public function getCountry(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_COUNTRY, '');
    }

    public function getConversionVatIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSION_VAT_INCL, 0);
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {
        return [
            self::FIELD_ACTIVE => [
                'fields' => [
                    self::FIELD_COUNTRY,
                    self::FIELD_COM_ID,
                    self::FIELD_CONVERSION_VAT_INCL
                ]
            ]
        ];
    }
}
