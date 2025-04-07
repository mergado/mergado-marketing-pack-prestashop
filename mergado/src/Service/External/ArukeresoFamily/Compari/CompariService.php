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

namespace Mergado\Service\External\ArukeresoFamily\Compari;

use Mergado\Service\External\ArukeresoFamily\AbstractArukeresoFamilyService;
use Mergado\Traits\SingletonTrait;

class CompariService extends AbstractArukeresoFamilyService
{
    use SingletonTrait;

    public const SERVICE_NAME = 'compari';
    public const CONSENT_NAME = 'mergado_compari_consent';

    // BASE
    public const FIELD_ACTIVE = 'mmp-compari-active';
    public const FIELD_WEB_API_KEY = 'mmp-compari-web-api-key';
    public const FIELD_OPT_OUT = 'mmp-compari-verify-opt-out-text-';

    //WIDGET
    public const FIELD_WIDGET_ACTIVE = 'mmp-compari-widget-active';
    public const FIELD_WIDGET_DESKTOP_POSITION = 'mmp-compari-widget-desktop-position';
    public const FIELD_WIDGET_MOBILE_POSITION = 'mmp-compari-widget-mobile-position';
    public const FIELD_WIDGET_MOBILE_WIDTH = 'mmp-compari-widget-mobile-width';
    public const FIELD_WIDGET_APPEARANCE_TYPE = 'mmp-compari-widget-appearance-type';

    //CONVERSIONS
    public const FIELD_CONVERSIONS_ACTIVE = 'mmp-compari-conversions-active';
    public const FIELD_CONVERSIONS_API_KEY = 'mmp-compari-conversions-api-key';

    public const CONVERSION_SDK_URL = '//www.arukereso.hu/ocm/sdk.js';
    public const CONVERSION_VARIABLE_NAME = 'arukereso';
    public const CONVERSION_SERVICE_LANG = 'hu';

    public const TEMPLATES_PATH = 'views/templates/services/ArukeresoFamily/Compari/';
    public const JS_PATH = 'views/js/services/ArukeresoFamily/Compari/';

    public const SERVICE_URL_SEND = 'https://www.compari.ro/'; // it is used!
}

