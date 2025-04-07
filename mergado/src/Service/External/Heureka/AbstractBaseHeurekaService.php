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


namespace Mergado\Service\External\Heureka;

use Mergado\Helper\LanguageHelper;
use Mergado\Service\External\HeurekaGroup\AbstractHeurekaGroupService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

abstract class AbstractBaseHeurekaService extends AbstractHeurekaGroupService
{
    public const FIELD_OPT_OUT_TEXT_PREFIX = 'mergado_heureka_opt_out_text-';

    public const DEFAULT_OPT = 'Do not send a satisfaction questionnaire within the Verified by Customer program.';
    public const POSITION_LEFT = 21;
    public const POSITION_RIGHT = 22;

    /******************************************************************************************************************
     * IS
     ******************************************************************************************************************/

    public function isVerifiedActive(): bool
    {
        return $this->getVerifiedActive() === SettingsService::ENABLED && $this->getVerifiedCode() !== '';
    }

    public function isWidgetActive(): bool
    {
        return $this->getWidgetActive() === SettingsService::ENABLED && $this->getWidgetId() !== '';
    }

    public function isLegacyConversionsActive(): bool
    {
        return $this->getLegacyConversionsActive() === SettingsService::ENABLED && $this->getLegacyConversionsCode() !== '';
    }

    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    public function getUrl(): string
    {
        return $this::HEUREKA_URL;
    }

    public function getVerifiedActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this::FIELD_VERIFIED, 0);
    }

    public function getWidgetActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this::FIELD_WIDGET, 0);
    }

    public function getLegacyConversionsActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this::FIELD_LEGACY_CONVERSIONS, 0);
    }

    public function getVerifiedCode(): string
    {
        return (string)DatabaseManager::getSettingsFromCache($this::FIELD_LEGACY_CONVERSIONS, '');
    }

    public function getWidgetId(): string
    {
        return (string)DatabaseManager::getSettingsFromCache($this::FIELD_WIDGET_ID, '');
    }

    public function getLegacyConversionsCode(): string
    {
        return (string)DatabaseManager::getSettingsFromCache($this::FIELD_LEGACY_CONVERSIONS_CODE, '');
    }

    public function getVerifiedWithItems(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this::FIELD_VERIFIED_WITH_ITEMS, 1);
    }

    public function getWidgetPosition(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this::FIELD_WIDGET_POSITION, self::POSITION_LEFT);
    }

    public function getWidgetTopMargin(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this::FIELD_WIDGET_POSITION, 60);
    }

    public function getLegacyConversionsVatIncluded(): int
    {
        return (int)DatabaseManager::getSettingsFromCache($this::FIELD_LEGACY_CONVERSION_VAT_INCL, 1);
    }

    public function getOptOutText(): string
    {
        $text = (string)DatabaseManager::getSettingsFromCache(self::FIELD_OPT_OUT_TEXT_PREFIX . LanguageHelper::getLang(), self::DEFAULT_OPT);

        if (trim($text) === '') {
            return self::DEFAULT_OPT;
        }

        return $text;
    }
}
