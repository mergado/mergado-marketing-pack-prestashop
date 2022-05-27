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

namespace Mergado\includes\services\ArukeresoFamily\Pazaruvaj;

use Mergado;
use Mergado\includes\services\ArukeresoFamily\AbstractArukeresoFamilyService;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class PazaruvajService extends AbstractArukeresoFamilyService
{
    const SERVICE_NAME = 'pazaruvaj';
    const CONSENT_NAME = 'mergado_pazaruvaj_consent';

    // BASE
    const ACTIVE = 'mmp-pazaruvaj-active';
    const WEB_API_KEY = 'mmp-pazaruvaj-web-api-key';
    const OPT_OUT = 'mmp-pazaruvaj-verify-opt-out-text-';

    //WIDGET
    const WIDGET_ACTIVE = 'mmp-pazaruvaj-widget-active';
    const WIDGET_DESKTOP_POSITION = 'mmp-pazaruvaj-widget-desktop-position';
    const WIDGET_MOBILE_POSITION = 'mmp-pazaruvaj-widget-mobile-position';
    const WIDGET_MOBILE_WIDTH = 'mmp-pazaruvaj-widget-mobile-width';
    const WIDGET_APPEARANCE_TYPE = 'mmp-pazaruvaj-widget-appearance-type';

    const TEMPLATES_PATH = 'includes/services/ArukeresoFamily/Pazaruvaj/templates/';
}

