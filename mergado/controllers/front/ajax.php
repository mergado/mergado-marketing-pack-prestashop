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

use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\Data\CartDataService;
use Mergado\Service\External\Heureka\HeurekaServiceIntegration;
use Mergado\Service\External\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\Service\External\ArukeresoFamily\Compari\CompariService;
use Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\Service\External\Biano\BianoStar\BianoStarService;
use Mergado\Service\External\Zbozi\ZboziService;

include_once _PS_MODULE_DIR_ . 'mergado/vendor/autoload.php';

class MergadoAjaxModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $response = false;

        if (PrestashopVersionHelper::is17AndHigher() && Tools::isSubmit('action')) {
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
                    $response = $this->setConsentCookie(HeurekaServiceIntegration::SERVICE_NAME, HeurekaServiceIntegration::CONSENT_NAME);
                    break;
                case 'setZboziOpc':
                    $response = $this->setConsentCookie(ZboziService::SERVICE_NAME, ZboziService::CONSENT_NAME);
                    break;
                case 'setBianoStarOpc':
                    $response = $this->setConsentCookie(BianoStarService::SERVICE_NAME, BianoStarService::CONSENT_NAME);
                    break;
                default:
                    break;
            }
        }

        if (Tools::isSubmit('action')) {
            switch (Tools::getValue('action')) {
                case 'getCartData';
                    $response = CartDataService::getInstance()->getAjaxCartData();
                default:
                    break;
            }
        }

        // Classic json response
        $json = json_encode($response);
        echo $json;
        die;
    }

    public function setConsentCookie($serviceName, $consentName): bool
    {
        if (Tools::getValue($serviceName . 'Data') === '1') {
            $this->context->cookie->__set($consentName, '1');
        } else {
            $this->context->cookie->__set($consentName, '0');
        }

        return true;
    }
}
