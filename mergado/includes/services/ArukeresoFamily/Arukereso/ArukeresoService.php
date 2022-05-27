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

namespace Mergado\includes\services\ArukeresoFamily\Arukereso;

use Mergado;
use Mergado\includes\services\ArukeresoFamily\AbstractArukeresoFamilyService;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class ArukeresoService extends AbstractArukeresoFamilyService
{
    const SERVICE_NAME = 'arukereso';
    const CONSENT_NAME = 'mergado_arukereso_consent';

    // BASE
    const ACTIVE = 'arukereso-active';
    const WEB_API_KEY = 'arukereso-web-api-key';
    const OPT_OUT = 'arukereso-verify-opt-out-text-';

    //WIDGET
    const WIDGET_ACTIVE = 'arukereso-widget-active';
    const WIDGET_DESKTOP_POSITION = 'arukereso-widget-desktop-position';
    const WIDGET_MOBILE_POSITION = 'arukereso-widget-mobile-position';
    const WIDGET_MOBILE_WIDTH = 'arukereso-widget-mobile-width';
    const WIDGET_APPEARANCE_TYPE = 'arukereso-widget-appearance-type';

    const TEMPLATES_PATH = 'includes/services/ArukeresoFamily/Arukereso/templates/';
}

