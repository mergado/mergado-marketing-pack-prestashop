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

use Mergado\includes\services\Biano\BianoStar\BianoStarService;
use Mergado\Heureka\HeurekaClass;
use Mergado\includes\services\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\includes\services\ArukeresoFamily\Compari\CompariService;
use Mergado\includes\services\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\Zbozi\ZboziClass;

require_once _PS_MODULE_DIR_ . 'mergado/mergado.php';

class MergadoAjaxModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $response = false;

        if (_PS_VERSION_ >= 1.7) {
            if (Tools::isSubmit('action')) {
                switch (Tools::getValue('action')) {
                    case 'setArukeresoOpc':
                        $response = $this->setConsentCookie(ArukeresoService::SERVICE_NAME, ArukeresoService::CONSENT_NAME);
                        break;
                    case 'setCompariOpc':
                        $response = $this->setConsentCookie(CompariService::SERVICE_NAME, CompariService::CONSENT_NAME);
                        break;
                    case 'setPazaruvajOpc':
                        $response = $this->setConsentCookie(PazaruvajService::SERVICE_NAME, PazaruvajService::CONSENT_NAME);
                        break;
                    case 'setHeurekaOpc':
                        $response = $this->setConsentCookie(HeurekaClass::SERVICE_NAME, HeurekaClass::CONSENT_NAME);
                        break;
                    case 'setZboziOpc':
                        $response = $this->setConsentCookie(ZboziClass::SERVICE_NAME, ZboziClass::CONSENT_NAME);
                        break;
                    case 'setBianoStarOpc':
                        $response = $this->setConsentCookie(BianoStarService::SERVICE_NAME, BianoStarService::CONSENT_NAME);
                        break;
                    default:
                        break;
                }
            }
        }

        if (Tools::isSubmit('action')) {
            switch (Tools::getValue('action')) {
                case 'getCartData';
                    $response = \Mergado\includes\helpers\CartHelper::getAjaxCartData();
                default:
                    break;
            }
        }

        // Classic json response
        $json = json_encode($response);
        echo $json;
        die;
    }

    public function setConsentCookie($serviceName, $consentName) {
        if (Tools::getValue($serviceName . 'Data') == '1') {
            $this->context->cookie->__set($consentName, '1');
        } else {
            $this->context->cookie->__set($consentName, '0');
        }

        return true;
    }
}
