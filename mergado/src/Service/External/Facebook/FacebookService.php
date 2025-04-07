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

namespace Mergado\Service\External\Facebook;

use Mergado\Manager\DatabaseManager;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;

class FacebookService extends AbstractBaseService
{
    public const FIELD_ACTIVE = 'fb_pixel';
    public const FIELD_CODE = 'fb_pixel_code';
    public const FIELD_CONVERSION_VAT_INCL = 'fb_pixel_conversion_vat_incl';

    public function isActive(): bool
    {
        $active = $this->getActive();
        $code = $this->getCode();

        return $active === SettingsService::ENABLED && $code && $code !== '';
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getCode(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_CODE, '');
    }

    public function getConversionVatIncluded(): int
    {
        return (int) DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSION_VAT_INCL, 0);
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
                    self::FIELD_CONVERSION_VAT_INCL,
                ]
            ],
        ];
    }
}
