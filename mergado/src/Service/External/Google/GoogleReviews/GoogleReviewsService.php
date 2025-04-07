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


namespace Mergado\Service\External\Google\GoogleReviews;


use Mergado\Service\AbstractBaseService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class GoogleReviewsService extends AbstractBaseService
{
    //Both services
    public const FIELD_MERCHANT_ID = 'gr_merchant_id';
    public const FIELD_LANGUAGE = 'gr_language';

    //Opt-in
    public const FIELD_OPT_IN_ACTIVE = 'gr_optin_active';
    public const FIELD_OPT_IN_POSITION = 'gr_optin_position';
    public const FIELD_OPT_IN_LANGUAGE = 'gr_optin_language';
    public const FIELD_OPT_IN_DELIVERY_DATE = 'gr_optin_delivery_date';

    //Badge
    public const FIELD_BADGE_ACTIVE = 'gr_badge_active';
    public const FIELD_BADGE_POSITION = 'gr_badge_position';

    public const LANGUAGES = [
        0 => ['id' => 0, 'name' => 'automatically'],
        1 => ['id' => 1, 'name' => 'af'],
        2 => ['id' => 2, 'name' => 'ar'],
        3 => ['id' => 3, 'name' => 'cs'],
        4 => ['id' => 4, 'name' => 'da'],
        5 => ['id' => 5, 'name' => 'de'],
        6 => ['id' => 6, 'name' => 'en'],
        7 => ['id' => 7, 'name' => 'en-AU'],
        8 => ['id' => 8, 'name' => 'en-GB'],
        9 => ['id' => 9, 'name' => 'en-US'],
        10 => ['id' => 10, 'name' => 'es'],
        11 => ['id' => 11, 'name' => 'es-419'],
        12 => ['id' => 12, 'name' => 'fil'],
        13 => ['id' => 13, 'name' => 'fr'],
        14 => ['id' => 14, 'name' => 'ga'],
        15 => ['id' => 15, 'name' => 'id'],
        16 => ['id' => 16, 'name' => 'it'],
        17 => ['id' => 17, 'name' => 'ja'],
        18 => ['id' => 18, 'name' => 'ms'],
        19 => ['id' => 19, 'name' => 'nl'],
        20 => ['id' => 20, 'name' => 'no'],
        21 => ['id' => 21, 'name' => 'pl'],
        22 => ['id' => 22, 'name' => 'pt-BR'],
        23 => ['id' => 23, 'name' => 'pt-PT'],
        24 => ['id' => 24, 'name' => 'ru'],
        25 => ['id' => 25, 'name' => 'sv'],
        26 => ['id' => 26, 'name' => 'tr'],
        27 => ['id' => 27, 'name' => 'zh-CN'],
        28 => ['id' => 28, 'name' => 'zh-TW']
    ];

    /*******************************************************************************************************************
     * Get constant select boxes .. because of translations
     *******************************************************************************************************************/

    public static function OPT_IN_POSITIONS_FOR_SELECT($translateFunction = null): array
    {
        if (is_null($translateFunction)) {
            return [
                0 => ['id' => 0, 'name' => 'Center', 'codePosition' => 'CENTER_DIALOG'],
                1 => ['id' => 1, 'name' => 'Bottom right', 'codePosition' => 'BOTTOM_RIGHT_DIALOG'],
                2 => ['id' => 2, 'name' => 'Bottom left', 'codePosition' => 'BOTTOM_LEFT_DIALOG'],
                3 => ['id' => 3, 'name' => 'Top right', 'codePosition' => 'TOP_RIGHT_DIALOG'],
                4 => ['id' => 4, 'name' => 'Top left', 'codePosition' => 'TOP_LEFT_DIALOG'],
                5 => ['id' => 5, 'name' => 'Bottom tray', 'codePosition' => 'BOTTOM_TRAY']
            ];
        }

        return [
            0 => ['id' => 0, 'name' => $translateFunction('Center', 'googlereviewsservice'), 'codePosition' => 'CENTER_DIALOG'],
            1 => ['id' => 1, 'name' => $translateFunction('Bottom right', 'googlereviewsservice'), 'codePosition' => 'BOTTOM_RIGHT_DIALOG'],
            2 => ['id' => 2, 'name' => $translateFunction('Bottom left', 'googlereviewsservice'), 'codePosition' => 'BOTTOM_LEFT_DIALOG'],
            3 => ['id' => 3, 'name' => $translateFunction('Top right', 'googlereviewsservice'), 'codePosition' => 'TOP_RIGHT_DIALOG'],
            4 => ['id' => 4, 'name' => $translateFunction('Top left', 'googlereviewsservice'), 'codePosition' => 'TOP_LEFT_DIALOG'],
            5 => ['id' => 5, 'name' => $translateFunction('Bottom tray', 'googlereviewsservice'), 'codePosition' => 'BOTTOM_TRAY']
        ];
    }

    public static function BADGE_POSITIONS_FOR_SELECT($translateFunction = null): array
    {
        if (is_null($translateFunction)) {
            return [
                0 => ['id' => 0, 'name' => 'Bottom right', 'codePosition' => 'BOTTOM_RIGHT'],
                1 => ['id' => 1, 'name' => 'Bottom left', 'codePosition' => 'BOTTOM_LEFT'],
                2 => ['id' => 2, 'name' => 'Inline', 'codePosition' => 'INLINE'],
            ];
        }

        return [
            0 => ['id' => 0, 'name' => $translateFunction('Bottom right', 'googlereviewsservice'), 'codePosition' => 'BOTTOM_RIGHT'],
            1 => ['id' => 1, 'name' => $translateFunction('Bottom left', 'googlereviewsservice'), 'codePosition' => 'BOTTOM_LEFT'],
            2 => ['id' => 2, 'name' => $translateFunction('Inline', 'googlereviewsservice'), 'codePosition' => 'INLINE'],
        ];
    }

    /******************************************************************************************************************
     * IS
     ******************************************************************************************************************/

    public function isOptInActive(): bool
    {
        $active = $this->getOptInActive();
        $merchantId = $this->getMerchantId();

        return $active === SettingsService::ENABLED && $merchantId && $merchantId !== '';
    }

    public function isBadgeActive(): bool
    {
        $optInActive = $this->getOptInActive();
        $active = $this->getBadgeActive();
        $merchantId = $this->getMerchantId();

        return $optInActive === SettingsService::ENABLED && $active === SettingsService::ENABLED && $merchantId && $merchantId !== '';
    }

    public function isPositionInline(): bool
    {
        return $this->getBadgePosition() === self::BADGE_POSITIONS_FOR_SELECT()[2]['id'];
    }

    /******************************************************************************************************************
     * GET FIELDS
     ******************************************************************************************************************/

    public function getOptInActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_OPT_IN_ACTIVE, 0);
    }

    public function getOptInPosition(): string
    {
        return (string)self::OPT_IN_POSITIONS_FOR_SELECT()[DatabaseManager::getSettingsFromCache(self::FIELD_OPT_IN_POSITION, 0)]['codePosition'];
    }

    public function getOptInDeliveryDate(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_OPT_IN_DELIVERY_DATE, '');
    }

    public function getBadgeActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_BADGE_ACTIVE, 0);
    }

    public function getMerchantId(): string
    {
        return (string)DatabaseManager::getSettingsFromCache(self::FIELD_MERCHANT_ID, '');
    }

    public function getBadgePosition(): string
    {
        return (string)self::BADGE_POSITIONS_FOR_SELECT()[DatabaseManager::getSettingsFromCache(self::FIELD_BADGE_POSITION, 0)]['codePosition'];
    }

    public function getLanguage(): string
    {
        return (string)self::LANGUAGES[DatabaseManager::getSettingsFromCache(self::FIELD_LANGUAGE, 0)]['name'];
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {
        return [
            self::FIELD_OPT_IN_ACTIVE => [
                'fields' => [
                    self::FIELD_MERCHANT_ID,
                    self::FIELD_BADGE_ACTIVE,
                    self::FIELD_OPT_IN_DELIVERY_DATE,
                    self::FIELD_LANGUAGE,
                    self::FIELD_OPT_IN_POSITION,
                ],
                'sub-check' => [
                    self::FIELD_BADGE_ACTIVE => [
                        'fields' => [
                            self::FIELD_BADGE_POSITION
                        ]
                    ],
                ]
            ],
        ];
    }
}
