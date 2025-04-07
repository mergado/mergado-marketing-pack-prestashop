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

namespace Mergado\Service\External\ArukeresoFamily;

use Mergado\Helper\LanguageHelper;
use Mergado\Service\External\HeurekaGroup\AbstractHeurekaGroupService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

abstract class AbstractArukeresoFamilyService extends AbstractHeurekaGroupService
{
    /*******************************************************************************************************************
     * IS
     *******************************************************************************************************************/

    public function isActive(): bool
    {
        $active = $this->getActive();
        $webApiKey = $this->getWebApiKey();

        return $active === SettingsService::ENABLED && $webApiKey && $webApiKey !== '';
    }

    public function isWidgetActive(): bool
    {
        $active = $this->getActive();
        $widgetActive = $this->getWidgetActive();
        $webApiKey = $this->getWebApiKey();

        return $active === SettingsService::ENABLED && $widgetActive === SettingsService::ENABLED && $webApiKey && $webApiKey !== '';
    }

    /*******************************************************************************************************************
     * Get constant selectboxes .. because of translations
     *******************************************************************************************************************/

    public static function DESKTOP_POSITIONS($translationFunction = null): array
    {
        if (is_null($translationFunction)) {
            return [
                0 => ['id_option' => 0, 'name' => 'Left', 'value' => 'L'],
                1 => ['id_option' => 1, 'name' => 'Right', 'value' => 'R'],
                2 => ['id_option' => 2, 'name' => 'Bottom left', 'value' => 'BL'],
                3 => ['id_option' => 3, 'name' => 'Bottom right', 'value' => 'BR'],
            ];
        }

        return [
            0 => ['id_option' => 0, 'name' => $translationFunction('Left', 'arukeresoclass'), 'value' => 'L'],
            1 => ['id_option' => 1, 'name' => $translationFunction('Right', 'arukeresoclass'), 'value' => 'R'],
            2 => ['id_option' => 2, 'name' => $translationFunction('Bottom left', 'arukeresoclass'), 'value' => 'BL'],
            3 => ['id_option' => 3, 'name' => $translationFunction('Bottom right', 'arukeresoclass'), 'value' => 'BR'],
        ];
    }

    public static function MOBILE_POSITIONS($translationFunction = null): array
    {
        if (is_null($translationFunction)) {
            return [
                0 => ['id_option' => 0, 'name' => 'On the left side', 'value' => 'L'],
                1 => ['id_option' => 1, 'name' => 'On the right side', 'value' => 'R'],
                2 => ['id_option' => 2, 'name' => 'At the left bottom of the window', 'value' => 'BL'],
                3 => ['id_option' => 3, 'name' => 'At the right bottom of the window', 'value' => 'BR'],
                4 => ['id_option' => 4, 'name' => 'Wide button at the bottom of the page', 'value' => 'W'],
                5 => ['id_option' => 5, 'name' => 'On the left, only the badge is visible', 'value' => 'LB'],
                6 => ['id_option' => 6, 'name' => 'On the left, only the text is visible', 'value' => 'LT'],
                7 => ['id_option' => 7, 'name' => 'On the right, only badge is visible', 'value' => 'RB'],
                8 => ['id_option' => 8, 'name' => 'On the right, only the text is visible', 'value' => 'RT'],
                9 => ['id_option' => 9, 'name' => 'At the left bottom of the window, only the badge is visible', 'value' => 'BLB'],
                10 => ['id_option' => 10, 'name' => 'At the left bottom of the window, only the text is visible', 'value' => 'BLT'],
                11 => ['id_option' => 11, 'name' => 'At the right bottom of the window, only the badge is visible', 'value' => 'BRB'],
                12 => ['id_option' => 12, 'name' => 'At the right bottom of the window, only the text is visible', 'value' => 'BRT'],
                13 => ['id_option' => 13, 'name' => 'Don\'t show on mobile devices', 'value' => ''],
            ];
        }

        return [
            0 => ['id_option' => 0, 'name' => $translationFunction('On the left side', 'arukeresoclass'), 'value' => 'L'],
            1 => ['id_option' => 1, 'name' => $translationFunction('On the right side', 'arukeresoclass'), 'value' => 'R'],
            2 => ['id_option' => 2, 'name' => $translationFunction('At the left bottom of the window', 'arukeresoclass'), 'value' => 'BL'],
            3 => ['id_option' => 3, 'name' => $translationFunction('At the right bottom of the window', 'arukeresoclass'), 'value' => 'BR'],
            4 => ['id_option' => 4, 'name' => $translationFunction('Wide button at the bottom of the page', 'arukeresoclass'), 'value' => 'W'],
            5 => ['id_option' => 5, 'name' => $translationFunction('On the left, only the badge is visible', 'arukeresoclass'), 'value' => 'LB'],
            6 => ['id_option' => 6, 'name' => $translationFunction('On the left, only the text is visible', 'arukeresoclass'), 'value' => 'LT'],
            7 => ['id_option' => 7, 'name' => $translationFunction('On the right, only badge is visible', 'arukeresoclass'), 'value' => 'RB'],
            8 => ['id_option' => 8, 'name' => $translationFunction('On the right, only the text is visible', 'arukeresoclass'), 'value' => 'RT'],
            9 => ['id_option' => 9, 'name' => $translationFunction('At the left bottom of the window, only the badge is visible', 'arukeresoclass'), 'value' => 'BLB'],
            10 => ['id_option' => 10, 'name' => $translationFunction('At the left bottom of the window, only the text is visible', 'arukeresoclass'), 'value' => 'BLT'],
            11 => ['id_option' => 11, 'name' => $translationFunction('At the right bottom of the window, only the badge is visible', 'arukeresoclass'), 'value' => 'BRB'],
            12 => ['id_option' => 12, 'name' => $translationFunction('At the right bottom of the window, only the text is visible', 'arukeresoclass'), 'value' => 'BRT'],
            13 => ['id_option' => 13, 'name' => $translationFunction('Don\'t show on mobile devices', 'arukeresoclass'), 'value' => ''],
        ];
    }

    public static function APPEARANCE_TYPES($translationFunction = null): array
    {
        if (is_null($translationFunction)) {
            return [
                0 => ['id_option' => 0, 'name' => 'By placing the cursor over a widget', 'value' => 0],
                1 => ['id_option' => 1, 'name' => 'With a click', 'value' => 1],
            ];
        }

        return [
            0 => ['id_option' => 0, 'name' => $translationFunction('By placing the cursor over a widget', 'arukeresoclass'), 'value' => 0],
            1 => ['id_option' => 1, 'name' => $translationFunction('With a click', 'arukeresoclass'), 'value' => 1],
        ];
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(static::FIELD_ACTIVE, 0);
    }

    public function getWebApiKey(): string
    {
        return DatabaseManager::getSettingsFromCache(static::FIELD_WEB_API_KEY, '');
    }

    public function getOptOut($lang): string
    {
        return DatabaseManager::getSettingsFromCache(static::FIELD_OPT_OUT . $lang, '');
    }

    public function getWidgetActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(static::FIELD_WIDGET_ACTIVE, 0);
    }

    public function getWidgetDesktopPosition(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(static::FIELD_WIDGET_DESKTOP_POSITION, 0);
    }

    public function getWidgetMobilePosition(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(static::FIELD_WIDGET_MOBILE_POSITION, 0);
    }

    public function getWidgetMobileWidth(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(static::FIELD_WIDGET_MOBILE_WIDTH, 480);
    }

    public function getWidgetAppearanceType(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(static::FIELD_WIDGET_APPEARANCE_TYPE, 0);
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields($languages): array
    {
        $langFields = [];

        foreach ($languages as $key => $lang) {
            $langName = LanguageHelper::getLang(strtoupper($lang['iso_code']));
            $langFields[] = static::FIELD_OPT_OUT . $langName;
        }

        $otherFields = [
            static::FIELD_WEB_API_KEY,
            static::FIELD_WIDGET_ACTIVE,
        ];

        return [
            static::FIELD_ACTIVE => [
                'fields' => array_merge($langFields, $otherFields),
                'sub-check' => [
                    static::FIELD_WIDGET_ACTIVE => [
                        'fields' => [
                            static::FIELD_WIDGET_DESKTOP_POSITION,
                            static::FIELD_WIDGET_MOBILE_POSITION,
                            static::FIELD_WIDGET_MOBILE_WIDTH,
                            static::FIELD_WIDGET_APPEARANCE_TYPE
                        ]
                    ]
                ]
            ],
            static::FIELD_CONVERSIONS_ACTIVE => [
                'fields' => [
                    static::FIELD_CONVERSIONS_API_KEY
                ]
            ]
        ];
    }
}

