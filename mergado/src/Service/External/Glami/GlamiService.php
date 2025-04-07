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


namespace Mergado\Service\External\Glami;

use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class GlamiService extends AbstractBaseService
{
    public const FIELD_PIXEL_ACTIVE = 'glami_active';
    public const FIELD_PIXEL_CONVERSION_VAT_INCLUDED = 'glami_conversion_vat_incl';
    public const FIELD_TOP_ACTIVE = 'glami_top_active';
    public const FIELD_TOP_CODE = 'glami_top_code';
    public const FIELD_TOP_SELECTION = 'glami_top_selection';

    public const FIELD_PIXEL_CODE_PREFIX = 'glami_pixel_code';

    public const PIXEL_LANGUAGES = [
        'CZ' => 'glami-form-active-lang-CZ',
        'DE' => 'glami-form-active-lang-DE',
        'FR' => 'glami-form-active-lang-FR',
        'SK' => 'glami-form-active-lang-SK',
        'RO' => 'glami-form-active-lang-RO',
        'HU' => 'glami-form-active-lang-HU',
        'RU' => 'glami-form-active-lang-RU',
        'GR' => 'glami-form-active-lang-GR',
        'TR' => 'glami-form-active-lang-TR',
        'BG' => 'glami-form-active-lang-BG',
        'HR' => 'glami-form-active-lang-HR',
        'SI' => 'glami-form-active-lang-SI',
        'ES' => 'glami-form-active-lang-ES',
        'BR' => 'glami-form-active-lang-BR',
        'ECO' => 'glami-form-active-lang-ECO'
    ];

    public const PIXEL_TOP_LANGUAGES = [
        ['id_option' => 1, 'name' => 'glami.cz', 'type_code' => 'cz'],
        ['id_option' => 2, 'name' => 'glami.de', 'type_code' => 'de'],
        ['id_option' => 3, 'name' => 'glami.fr', 'type_code' => 'fr'],
        ['id_option' => 4, 'name' => 'glami.sk', 'type_code' => 'sk'],
        ['id_option' => 5, 'name' => 'glami.ro', 'type_code' => 'ro'],
        ['id_option' => 6, 'name' => 'glami.hu', 'type_code' => 'hu'],
        ['id_option' => 7, 'name' => 'glami.ru', 'type_code' => 'ru'],
        ['id_option' => 8, 'name' => 'glami.gr', 'type_code' => 'gr'],
        ['id_option' => 9, 'name' => 'glami.com.tr', 'type_code' => 'tr'],
        ['id_option' => 10, 'name' => 'glami.bg', 'type_code' => 'bg'],
        ['id_option' => 11, 'name' => 'glami.hr', 'type_code' => 'hr'],
        ['id_option' => 12, 'name' => 'glami.si', 'type_code' => 'si'],
        ['id_option' => 13, 'name' => 'glami.es', 'type_code' => 'es'],
        ['id_option' => 14, 'name' => 'glami.com.br', 'type_code' => 'br'],
        ['id_option' => 15, 'name' => 'glami.eco', 'type_code' => 'eco'],
    ];

    public function isPixelActive(string $lang): bool
    {
        return $this->getPixelActive() === SettingsService::ENABLED &&
            $this->isLanguageActive($lang) &&
            $this->isPixelCodeFilled($lang);
    }

    public function isTopActive(): bool
    {
        return $this->getTopActive() === SettingsService::ENABLED && $this->getTopCode() !== '';
    }

    public function isLanguageActive(string $lang): bool
    {
        return isset(self::PIXEL_LANGUAGES[$lang]) && (bool)DatabaseManager::getSettingsFromCache(self::PIXEL_LANGUAGES[$lang]);
    }

    public function isPixelCodeFilled(string $lang): bool
    {
        return $this->getPixelLangCode($lang) !== '';
    }

    public function getPixelActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_PIXEL_ACTIVE);
    }

    public function getPixelLangCode(string $lang): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_PIXEL_CODE_PREFIX . '-' . $lang, '');
    }

    public function getTopActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_TOP_ACTIVE);
    }

    public function getPixelConversionVatIncluded(): bool
    {
        return (bool)DatabaseManager::getSettingsFromCache(self::FIELD_PIXEL_CONVERSION_VAT_INCLUDED, '0');
    }

    public function getTopCode(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_TOP_CODE, '');
    }

    public function getTopSelection(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_TOP_SELECTION, 1);
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {
        $glamiFields = [];
        $glamiMainFields = array_values(self::PIXEL_LANGUAGES);

        foreach (self::PIXEL_LANGUAGES as $key => $values) {
            $glamiFields[$values]['fields'] = [self::FIELD_PIXEL_CODE_PREFIX . '-' . $key];
        }

        $glamiMainFields[] = self::FIELD_PIXEL_CONVERSION_VAT_INCLUDED;

        return [
            self::FIELD_PIXEL_ACTIVE => [
                'fields' => $glamiMainFields,
                'sub-check' => $glamiFields,
            ],
            self::FIELD_TOP_ACTIVE => [
                'fields' => [
                    self::FIELD_TOP_CODE,
                    self::FIELD_TOP_SELECTION,
                ],
            ],
        ];
    }

}
