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


namespace Mergado\Service\External\Pricemania;

use Mergado\Manager\DatabaseManager;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;

class PricemaniaService extends AbstractBaseService
{
    public const FIELD_ACTIVE = 'mergado_pricemania_overeny_obchod';
    public const FIELD_SHOP_ID = 'mergado_pricemania_shop_id';

    /**
     * IS
     */

    public function isActive(): bool
    {
        return $this->getActive() === SettingsService::ENABLED && $this->getShopId() !== '';
    }

    /*
     * GET
     */

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
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
            self::FIELD_ACTIVE => [
                'fields' => [
                    self::FIELD_SHOP_ID,
                ]
            ]
        ];
    }
}
