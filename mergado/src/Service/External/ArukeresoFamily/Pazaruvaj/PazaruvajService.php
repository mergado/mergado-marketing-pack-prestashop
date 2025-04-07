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

namespace Mergado\Service\External\ArukeresoFamily\Pazaruvaj;

use Mergado\Service\External\ArukeresoFamily\AbstractArukeresoFamilyService;
use Mergado\Traits\SingletonTrait;

class PazaruvajService extends AbstractArukeresoFamilyService
{
    use SingletonTrait;

    public const SERVICE_NAME = 'pazaruvaj';
    public const CONSENT_NAME = 'mergado_pazaruvaj_consent';

    // BASE
    public const FIELD_ACTIVE = 'mmp-pazaruvaj-active';
    public const FIELD_WEB_API_KEY = 'mmp-pazaruvaj-web-api-key';
    public const FIELD_OPT_OUT = 'mmp-pazaruvaj-verify-opt-out-text-';

    //WIDGET
    public const FIELD_WIDGET_ACTIVE = 'mmp-pazaruvaj-widget-active';
    public const FIELD_WIDGET_DESKTOP_POSITION = 'mmp-pazaruvaj-widget-desktop-position';
    public const FIELD_WIDGET_MOBILE_POSITION = 'mmp-pazaruvaj-widget-mobile-position';
    public const FIELD_WIDGET_MOBILE_WIDTH = 'mmp-pazaruvaj-widget-mobile-width';
    public const FIELD_WIDGET_APPEARANCE_TYPE = 'mmp-pazaruvaj-widget-appearance-type';

    //CONVERSIONS
    public const FIELD_CONVERSIONS_ACTIVE = 'mmp-pazaruvaj-conversions-active';
    public const FIELD_CONVERSIONS_API_KEY = 'mmp-pazaruvaj-conversions-api-key';

    public const CONVERSION_SDK_URL = '//www.arukereso.hu/ocm/sdk.js';
    public const CONVERSION_VARIABLE_NAME = 'arukereso';
    public const CONVERSION_SERVICE_LANG = 'hu';

    public const TEMPLATES_PATH = 'views/templates/services/ArukeresoFamily/Pazaruvaj/';
    public const JS_PATH = 'views/js/services/ArukeresoFamily/Pazaruvaj/';

    public const SERVICE_URL_SEND = 'https://www.pazaruvaj.com/'; // it is used!
}

