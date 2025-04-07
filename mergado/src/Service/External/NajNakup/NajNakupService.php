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

namespace Mergado\Service\External\NajNakup;

use Mergado\Manager\DatabaseManager;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;

class NajNakupService extends AbstractBaseService
{
    public const FIELD_CONVERSIONS = 'mergado_najnakup_konverze';
    public const FIELD_SHOP_ID = 'mergado_najnakup_shop_id';

    public function isActive(): bool
    {
        $active = $this->getActive();
        $id = $this->getShopId();

        return $active === SettingsService::ENABLED && $id && $id !== '';
    }

    /*******************************************************************************************************************
     * GET FIELD
     ******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_CONVERSIONS, 0);
    }

    public function getShopId(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_SHOP_ID, '');
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {

        return [
            self::FIELD_CONVERSIONS => [
                'fields' => [
                    self::FIELD_SHOP_ID,
                ]
            ],
        ];
    }
}
