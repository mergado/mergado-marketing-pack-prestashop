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

namespace Mergado\Service\External\ArukeresoFamily\Arukereso;

use Mergado\Service\External\ArukeresoFamily\AbstractArukeresoFamilyService;
use Mergado\Traits\SingletonTrait;

class ArukeresoService extends AbstractArukeresoFamilyService
{
    use SingletonTrait;

    public const SERVICE_NAME = 'arukereso';
    public const CONSENT_NAME = 'mergado_arukereso_consent';

    // BASE
    public const FIELD_ACTIVE = 'arukereso-active';
    public const FIELD_WEB_API_KEY = 'arukereso-web-api-key';
    public const FIELD_OPT_OUT = 'arukereso-verify-opt-out-text-';

    //WIDGET
    public const FIELD_WIDGET_ACTIVE = 'arukereso-widget-active';
    public const FIELD_WIDGET_DESKTOP_POSITION = 'arukereso-widget-desktop-position';
    public const FIELD_WIDGET_MOBILE_POSITION = 'arukereso-widget-mobile-position';
    public const FIELD_WIDGET_MOBILE_WIDTH = 'arukereso-widget-mobile-width';
    public const FIELD_WIDGET_APPEARANCE_TYPE = 'arukereso-widget-appearance-type';

    //CONVERSIONS
    public const FIELD_CONVERSIONS_ACTIVE = 'mmp-arukereso-conversions-active';
    public const FIELD_CONVERSIONS_API_KEY = 'mmp-arukereso-conversions-api-key';

    public const CONVERSION_SDK_URL = '//www.arukereso.hu/ocm/sdk.js';
    public const CONVERSION_VARIABLE_NAME = 'arukereso';
    public const CONVERSION_SERVICE_LANG = 'hu';

    public const TEMPLATES_PATH = 'views/templates/services/ArukeresoFamily/Arukereso/';
    public const JS_PATH = 'views/js/services/ArukeresoFamily/Arukereso/';

    public const SERVICE_URL_SEND = 'https://www.arukereso.hu/'; // it is used!
}

