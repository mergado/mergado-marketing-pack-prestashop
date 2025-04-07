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


namespace Mergado\Service\External\HeurekaGroup;

use Mergado\Manager\DatabaseManager;

abstract class AbstractHeurekaGroupService
{
    public function isConversionActive(): bool
    {
        $active = $this->getConversionActive();
        $apiKey = $this->getConversionApiKey();

        return $active === 1 && $apiKey && $apiKey !== '';
    }

    public function getConversionActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(static::FIELD_CONVERSIONS_ACTIVE, 0);
    }

    public function getConversionApiKey(): string
    {
        return DatabaseManager::getSettingsFromCache(static::FIELD_CONVERSIONS_API_KEY, '');
    }
}
