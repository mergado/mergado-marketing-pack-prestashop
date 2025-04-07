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

namespace Mergado\Service\External\Biano\BianoStar;

use Mergado\Helper\LanguageHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\Biano\Biano\BianoService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;

class BianoStarService extends AbstractBaseService
{
    public const SERVICE_NAME = 'bianoStar';
    public const CONSENT_NAME = 'mergado_biano_star_consent';

    public const FIELD_ACTIVE = 'mmp_biano_star_active';
    public const FIELD_SHIPMENT_IN_STOCK = 'mmp_biano_star_shipment_in_stock';
    public const FIELD_SHIPMENT_BACKORDER = 'mmp_biano_star_shipment_backorder';
    public const FIELD_SHIPMENT_OUT_OF_STOCK = 'mmp_biano_star_shipment_out_of_stock';

    public const OPT_OUT = 'mmp_biano_start_opt_out_text_';

    public const DEFAULT_OPT = 'Do not send a satisfaction questionnaire within the Biano Star program.';

    /******************************************************************************************************************
     * IS
     ******************************************************************************************************************/

    public function isActive($lang): bool
    {
        $active = $this->getActive();
        $bianoService = BianoService::getInstance();

        return $active === SettingsService::ENABLED && $bianoService->isActive($lang);
    }

    /*******************************************************************************************************************
     * GET VALUES
     *******************************************************************************************************************/

    public function getActive(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_ACTIVE, 0);
    }

    public function getShipmentInStock(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_SHIPMENT_IN_STOCK, 0);
    }

    public function getShipmentBackorder(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_SHIPMENT_BACKORDER, 0);
    }

    public function getShipmentOutOfStock(): int
    {
        return (int)DatabaseManager::getSettingsFromCache(self::FIELD_SHIPMENT_OUT_OF_STOCK, 0);
    }

    public function getOptOut($lang): string
    {
        return DatabaseManager::getSettingsFromCache(self::OPT_OUT . $lang, '');
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields($languages): array
    {
        $langFields = [];

        foreach ($languages as $lang) {
            $langName = LanguageHelper::getLang(strtoupper($lang['iso_code']));
            $langFields[] = self::OPT_OUT . $langName;
        }

        $otherFields = [
            self::FIELD_SHIPMENT_IN_STOCK,
            self::FIELD_SHIPMENT_BACKORDER,
            self::FIELD_SHIPMENT_OUT_OF_STOCK
        ];

        return [
            self::FIELD_ACTIVE => [
                'fields' => array_merge($langFields, $otherFields),
                'sub-check' => $otherFields
            ]
        ];
    }
}
