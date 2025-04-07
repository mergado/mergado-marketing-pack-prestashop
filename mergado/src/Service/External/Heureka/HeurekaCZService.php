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

use Mergado\Traits\SingletonTrait;

class HeurekaCZService extends AbstractBaseHeurekaService
{
    use SingletonTrait;

    // Verified
    public const FIELD_VERIFIED = 'mergado_heureka_overeno_zakazniky_cz';
    public const FIELD_VERIFIED_CODE = 'mergado_heureka_overeno_zakazniky_kod_cz';
    public const FIELD_VERIFIED_WITH_ITEMS = 'mergado_heureka_overeno_zakazniky_with_items_cz';

    // Verified - Widget
    public const FIELD_WIDGET = 'mergado_heureka_widget_cz';
    public const FIELD_WIDGET_ID = 'mergado_heureka_widget_id_cz';
    public const FIELD_WIDGET_POSITION = 'mergado_heureka_widget_position_cz';
    public const FIELD_WIDGET_TOP_MARGIN = 'mergado_heureka_widget_top_margin_cz';

    // Conversions
    public const FIELD_LEGACY_CONVERSIONS = 'mergado_heureka_konverze_cz';
    public const FIELD_LEGACY_CONVERSIONS_CODE = 'mergado_heureka_konverze_cz_kod';
    public const FIELD_LEGACY_CONVERSION_VAT_INCL = 'mergado_heureka_conversion_vat_incl_cz';

    public const FIELD_CONVERSIONS_ACTIVE = 'mmp-heureka-conversions-active--cz';
    public const FIELD_CONVERSIONS_API_KEY = 'mmp-heureka-conversions-api-key--cz';

    public const CONVERSION_SDK_URL = '//www.heureka.cz/ocm/sdk.js';
    public const CONVERSION_VARIABLE_NAME = 'heureka';
    public const CONVERSION_SERVICE_LANG = 'cz';

    // Endpoints
    public const HEUREKA_URL = 'https://www.heureka.cz/direct/dotaznik/objednavka.php';
    public const HEUREKA_CONVERSION_URL = 'https://im9.cz/js/ext/1-roi-async.js';

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields(): array
    {
        return [
            self::FIELD_VERIFIED => [
                'fields' => [
                    self::FIELD_VERIFIED_CODE,
                ],
            ],
            self::FIELD_WIDGET => [
                'fields' => [
                    self::FIELD_WIDGET_ID,
                    self::FIELD_WIDGET_POSITION,
                    self::FIELD_WIDGET_TOP_MARGIN,
                ],
            ],
            self::FIELD_LEGACY_CONVERSIONS => [
                'fields' => [
                    self::FIELD_LEGACY_CONVERSIONS_CODE,
                    self::FIELD_LEGACY_CONVERSION_VAT_INCL,
                ],
            ],
        ];
    }
}
