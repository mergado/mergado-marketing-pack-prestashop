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


namespace Mergado\Service;

use Mergado\Manager\DatabaseManager;

class CookieService extends AbstractBaseService
{
    // CookieLaw
    public const COOKIE_LAW_ADVERTISEMENT = 'DmCookiesMarketing';
    public const COOKIE_LAW_ANALYTICAL = 'DmCookiesAnalytics';
    public const COOKIE_LAW_FUNCTIONAL = 'DmCookiesFunctional'; // not yet implemented

    //Cookie form
    public const FIELD_COOKIES_ENABLE = 'form-cookie-enable-always';
    public const FIELD_ADVERTISEMENT_USER = 'form-cookie-advertisement';
    public const FIELD_ANALYTICAL_USER = 'form-cookie-analytical';
    public const FIELD_FUNCTIONAL_USER = 'form-cookie-functional';

    /**
     * Google Analytics (gtag.js)
     *
     * @return bool
     */
    public function analyticalEnabled(): bool
    {
        if ($this->isCookieBlockingEnabled()) {
            if (self::isCookieActive(self::COOKIE_LAW_ANALYTICAL)) {
                return true;
            }

            $cookieName = $this->getAnalyticalCustomName();

            return $cookieName !== '' && self::isCookieActive($cookieName);
        }

        return true;
    }

    /**
     * Glami Pixel, Biano Pixel, etarget, Sklik, Kelkoo, Heureka order confirmation
     *
     * @return bool
     */
    public function advertismentEnabled(): bool
    {
        if ($this->isCookieBlockingEnabled()) {
            if (self::isCookieActive(self::COOKIE_LAW_ADVERTISEMENT)) {
                return true;
            }

            $cookieName = $this->getAdvertisementCustomName();

            return $cookieName !== '' && self::isCookieActive($cookieName);
        }

        return true;
    }

    /**
     * Heureka widget, Google reviews?
     */
    public function functionalEnabled(): bool
    {
        if ($this->isCookieBlockingEnabled()) {
            if (self::isCookieActive(self::COOKIE_LAW_FUNCTIONAL)) {
                return true;
            }

            $cookieName = $this->getFunctionalCustomName();

            return $cookieName !== '' && self::isCookieActive($cookieName);
        }

        return true;
    }

    // HELPERS
    public static function isCookieActive($cookieName): bool
    {
        return isset($_COOKIE[$cookieName]) && filter_var($_COOKIE[$cookieName], FILTER_VALIDATE_BOOLEAN);
    }

    // ADMIN FORM VALUES
    public function isCookieBlockingEnabled(): bool
    {
        $val = DatabaseManager::getSettingsFromCache(self::FIELD_COOKIES_ENABLE);

        return $val === '1';
    }

    public function getAdvertisementCustomName()
    {
        $advertisementName = DatabaseManager::getSettingsFromCache(self::FIELD_ADVERTISEMENT_USER, '');

        if (trim($advertisementName) !== '') {
            return $advertisementName;
        }

        return '';
    }

    public function getAnalyticalCustomName()
    {
        $analyticalName = DatabaseManager::getSettingsFromCache(self::FIELD_ANALYTICAL_USER, '');

        if (trim($analyticalName) !== '') {
            return $analyticalName;
        }

        return '';
    }

    public function getFunctionalCustomName()
    {
        $functionalName = DatabaseManager::getSettingsFromCache(self::FIELD_FUNCTIONAL_USER, '');

        if (trim($functionalName) !== '') {
            return $functionalName;
        }

        return '';
    }

    /*******************************************************************************************************************
     * Javascript
     ******************************************************************************************************************/

    public function createJsVariables(): array
    {
        $analyticalNames = implode('", "', array_filter([self::COOKIE_LAW_ANALYTICAL, $this->getAnalyticalCustomName()]));
        $advertisementNames = implode('", "', array_filter([self::COOKIE_LAW_ADVERTISEMENT, $this->getAdvertisementCustomName()]));
        $functionalNames = implode('", "', array_filter([self::COOKIE_LAW_FUNCTIONAL, $this->getFunctionalCustomName()]));

        return [
            'functions' => [],
            'sections' => [
                'functional' => [
                    'onloadStatus' => (int) $this->functionalEnabled(),
                    'functions' => [],
                    'names' => [$functionalNames],
                ],
                'analytical' => [
                    'onloadStatus' => (int) $this->analyticalEnabled(),
                    'functions' => [],
                    'names' => [$analyticalNames],
                ],
                'advertisement' => [
                    'onloadStatus' => (int) $this->advertismentEnabled(),
                    'functions' => [],
                    'names' => [$advertisementNames],
                ],
            ],
        ];

    }
}
