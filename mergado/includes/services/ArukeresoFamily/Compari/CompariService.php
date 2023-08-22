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

namespace Mergado\includes\services\ArukeresoFamily\Compari;

use Mergado;
use Mergado\includes\services\ArukeresoFamily\AbstractArukeresoFamilyService;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class CompariService extends AbstractArukeresoFamilyService
{
    const SERVICE_NAME = 'compari';
    const CONSENT_NAME = 'mergado_compari_consent';

    // BASE
    const ACTIVE = 'mmp-compari-active';
    const WEB_API_KEY = 'mmp-compari-web-api-key';
    const OPT_OUT = 'mmp-compari-verify-opt-out-text-';

    //WIDGET
    const WIDGET_ACTIVE = 'mmp-compari-widget-active';
    const WIDGET_DESKTOP_POSITION = 'mmp-compari-widget-desktop-position';
    const WIDGET_MOBILE_POSITION = 'mmp-compari-widget-mobile-position';
    const WIDGET_MOBILE_WIDTH = 'mmp-compari-widget-mobile-width';
    const WIDGET_APPEARANCE_TYPE = 'mmp-compari-widget-appearance-type';

    const TEMPLATES_PATH = 'includes/services/ArukeresoFamily/Compari/templates/';

    const SERVICE_URL_SEND = 'https://www.compari.ro/'; // it is used!
}

