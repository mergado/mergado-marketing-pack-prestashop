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

namespace Mergado\Service\External\Etarget;

use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class EtargetService extends AbstractBaseService
{
    public const FIELD_ACTIVE = 'etarget';
    public const FIELD_ID = 'etarget_id';
    public const FIELD_HASH = 'etarget_hash';

    public function isActive(): bool
    {
        $active = $this->getActive();
        $code = $this->getId();

        return $active === SettingsService::ENABLED && $code && $code !== '';
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getId(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_ID, '');
    }

    public function getHash(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_HASH, '');
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {
        return [
            self::FIELD_ACTIVE => [
                'fields' => [
                    self::FIELD_ID,
                    self::FIELD_HASH
                ]
            ],
        ];
    }
}
