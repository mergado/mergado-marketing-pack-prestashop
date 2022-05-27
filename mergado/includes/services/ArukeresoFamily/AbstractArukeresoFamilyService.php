<?php

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

namespace Mergado\includes\services\ArukeresoFamily;

use Mergado;
use Mergado\Tools\LanguagesClass;
use Mergado\Tools\SettingsClass;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

abstract class AbstractArukeresoFamilyService
{
    // Input variables
    private $active;
    private $webApiKey;
    private $widgetActive;
    private $widgetDesktopPosition;
    private $widgetMobilePosition;
    private $widgetMobileWidth;
    private $widgetAppearanceType;

    // Main settings variables
    private $multistoreShopId;

    public function __construct()
    {
        $this->multistoreShopId = Mergado::getShopId();
    }

    /*******************************************************************************************************************
     * IS
     *******************************************************************************************************************/

    public function isActive()
    {
        $active = $this->getActive();
        $webApiKey = $this->getWebApiKey();

        if ($active === SettingsClass::ENABLED && $webApiKey && $webApiKey !== '') {
            return true;
        } else {
            return false;
        }
    }

    public function isWidgetActive()
    {
        $active = $this->getActive();
        $widgetActive = $this->getWidgetActive();
        $webApiKey = $this->getWebApiKey();

        if ($active === SettingsClass::ENABLED && $widgetActive === SettingsClass::ENABLED && $webApiKey && $webApiKey !== '') {
            return true;
        } else {
            return false;
        }
    }

    /*******************************************************************************************************************
     * Get constant selectboxes .. because of translations
     *******************************************************************************************************************/

    /**
     * @param null $module
     * @return array[]
     */
    public static function DESKTOP_POSITIONS($module = null)
    {
        if (is_null($module)) {
            return [
                0 => ['id_option' => 0, 'name' => 'Left', 'value' => 'L'],
                1 => ['id_option' => 1, 'name' => 'Right', 'value' => 'R'],
                2 => ['id_option' => 2, 'name' => 'Bottom left', 'value' => 'BL'],
                3 => ['id_option' => 3, 'name' => 'Bottom right', 'value' => 'BR'],
            ];
        } else {
            return [
                0 => ['id_option' => 0, 'name' => $module->l('Left', 'arukeresoclass'), 'value' => 'L'],
                1 => ['id_option' => 1, 'name' => $module->l('Right', 'arukeresoclass'), 'value' => 'R'],
                2 => ['id_option' => 2, 'name' => $module->l('Bottom left', 'arukeresoclass'), 'value' => 'BL'],
                3 => ['id_option' => 3, 'name' => $module->l('Bottom right', 'arukeresoclass'), 'value' => 'BR'],
            ];
        }
    }

    /**
     * @param null $module
     * @return array[]
     */
    public static function MOBILE_POSITIONS($module = null) {
        if (is_null($module)) {
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
        } else {
            return [
                0 => ['id_option' => 0, 'name' => $module->l('On the left side', 'arukeresoclass'), 'value' => 'L'],
                1 => ['id_option' => 1, 'name' => $module->l('On the right side', 'arukeresoclass'), 'value' => 'R'],
                2 => ['id_option' => 2, 'name' => $module->l('At the left bottom of the window', 'arukeresoclass'), 'value' => 'BL'],
                3 => ['id_option' => 3, 'name' => $module->l('At the right bottom of the window', 'arukeresoclass'), 'value' => 'BR'],
                4 => ['id_option' => 4, 'name' => $module->l('Wide button at the bottom of the page', 'arukeresoclass'), 'value' => 'W'],
                5 => ['id_option' => 5, 'name' => $module->l('On the left, only the badge is visible', 'arukeresoclass'), 'value' => 'LB'],
                6 => ['id_option' => 6, 'name' => $module->l('On the left, only the text is visible', 'arukeresoclass'), 'value' => 'LT'],
                7 => ['id_option' => 7, 'name' => $module->l('On the right, only badge is visible', 'arukeresoclass'), 'value' => 'RB'],
                8 => ['id_option' => 8, 'name' => $module->l('On the right, only the text is visible', 'arukeresoclass'), 'value' => 'RT'],
                9 => ['id_option' => 9, 'name' => $module->l('At the left bottom of the window, only the badge is visible', 'arukeresoclass'), 'value' => 'BLB'],
                10 => ['id_option' => 10, 'name' => $module->l('At the left bottom of the window, only the text is visible', 'arukeresoclass'), 'value' => 'BLT'],
                11 => ['id_option' => 11, 'name' => $module->l('At the right bottom of the window, only the badge is visible', 'arukeresoclass'), 'value' => 'BRB'],
                12 => ['id_option' => 12, 'name' => $module->l('At the right bottom of the window, only the text is visible', 'arukeresoclass'), 'value' => 'BRT'],
                13 => ['id_option' => 13, 'name' => $module->l('Don\'t show on mobile devices', 'arukeresoclass'), 'value' => ''],
            ];
        }
    }

    /**
     * @param null $module
     * @return array[]
     */
    public static function APPEARANCE_TYPES($module = null) {
        if (is_null($module)) {
            return [
                0 => ['id_option' => 0, 'name' => 'By placing the cursor over a widget', 'value' => 0],
                1 => ['id_option' => 1, 'name' => 'With a click', 'value' => 1],
            ];
        } else {
            return [
                0 => ['id_option' => 0, 'name' => $module->l('By placing the cursor over a widget', 'arukeresoclass'), 'value' => 0],
                1 => ['id_option' => 1, 'name' => $module->l('With a click', 'arukeresoclass'), 'value' => 1],
            ];
        }
    }

    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    /**
     * @return false|string|null
     */
    public function getActive()
    {
        if (!is_null($this->active)) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(static::ACTIVE, $this->multistoreShopId);

        return $this->active;
    }

    /**
     * @return false|string|null
     */
    public function getWebApiKey()
    {
        if (!is_null($this->webApiKey)) {
            return $this->webApiKey;
        }

        $this->webApiKey = SettingsClass::getSettings(static::WEB_API_KEY, $this->multistoreShopId);

        return $this->webApiKey;
    }

    /**
     * @param $lang
     * @return false|string|null
     */
    public function getOptOut($lang)
    {
        return SettingsClass::getSettings(static::OPT_OUT . $lang, $this->multistoreShopId);
    }

    /**
     * @return false|string|null
     */
    public function getWidgetActive()
    {
        if (!is_null($this->widgetActive)) {
            return $this->widgetActive;
        }

        $this->widgetActive = SettingsClass::getSettings(static::WIDGET_ACTIVE, $this->multistoreShopId);

        return $this->widgetActive;
    }

    /**
     * @return false|string|null
     */
    public function getWidgetDesktopPosition()
    {
        if (!is_null($this->widgetDesktopPosition)) {
            return $this->widgetDesktopPosition;
        }

        $this->widgetDesktopPosition = SettingsClass::getSettings(static::WIDGET_DESKTOP_POSITION, $this->multistoreShopId);

        return $this->widgetDesktopPosition;
    }

    /**
     * @return false|string|null
     */
    public function getWidgetMobilePosition()
    {
        if (!is_null($this->widgetMobilePosition)) {
            return $this->widgetMobilePosition;
        }

        $this->widgetMobilePosition = SettingsClass::getSettings(static::WIDGET_MOBILE_POSITION, $this->multistoreShopId);

        return $this->widgetMobilePosition;
    }

    /**
     * @return false|string|null
     */
    public function getWidgetMobileWidth()
    {
        if (!is_null($this->widgetMobileWidth)) {
            return $this->widgetMobileWidth;
        }

        $this->widgetMobileWidth = SettingsClass::getSettings(static::WIDGET_MOBILE_WIDTH, $this->multistoreShopId);

        return $this->widgetMobileWidth;
    }

    /**
     * @return false|string|null
     */
    public function getWidgetAppearanceType()
    {
        if (!is_null($this->widgetAppearanceType)) {
            return $this->widgetAppearanceType;
        }

        $this->widgetAppearanceType = SettingsClass::getSettings(static::WIDGET_APPEARANCE_TYPE, $this->multistoreShopId);

        return $this->widgetAppearanceType;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

     /**
     * @param $languages
     * @return array[]
     */
    public static function getToggleFields($languages)
    {
        $langFields = [];

        foreach ($languages as $key => $lang) {
            $langName = LanguagesClass::getLangIso(strtoupper($lang['iso_code']));
            $langFields[] = static::OPT_OUT . $langName;
        }

        $otherFields = [
            static::WEB_API_KEY,
            static::WIDGET_ACTIVE,
        ];

        return [
            static::ACTIVE => [
                'fields' => array_merge($langFields, $otherFields),
                'sub-check' => [
                    static::WIDGET_ACTIVE => [
                        'fields' => [
                            static::WIDGET_DESKTOP_POSITION,
                            static::WIDGET_MOBILE_POSITION,
                            static::WIDGET_MOBILE_WIDTH,
                            static::WIDGET_APPEARANCE_TYPE
                        ]
                    ]
                ]
            ],
        ];
    }
}

