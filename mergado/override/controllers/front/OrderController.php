<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\External\Biano\BianoStar\BianoStarService;
use Mergado\Service\External\Heureka\HeurekaServiceIntegration;
use Mergado\Service\External\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\Service\External\ArukeresoFamily\Compari\CompariService;
use Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\Service\External\Zbozi\ZboziService;

include_once _PS_MODULE_DIR_ . 'mergado/vendor/autoload.php';

class OrderController extends OrderControllerCore
{
    /**
     * Initialize order controller
     * @see FrontController::init()
     */
    public function init()
    {

        if (PrestashopVersionHelper::is16AndLower()) {
             $this->setConsentCookie(HeurekaServiceIntegration::SERVICE_NAME, HeurekaServiceIntegration::CONSENT_NAME);
             $this->setConsentCookie(ZboziService::SERVICE_NAME, ZboziService::CONSENT_NAME);
             $this->setConsentCookie(ArukeresoService::SERVICE_NAME, ArukeresoService::CONSENT_NAME);
             $this->setConsentCookie(CompariService::SERVICE_NAME, CompariService::CONSENT_NAME);
             $this->setConsentCookie(PazaruvajService::SERVICE_NAME, PazaruvajService::CONSENT_NAME);
             $this->setConsentCookie(BianoStarService::SERVICE_NAME, BianoStarService::CONSENT_NAME);
        }

        parent::init();
    }

    public function setConsentCookie($serviceName, $consentName): bool
    {
        if (Tools::getValue($serviceName . '_consent') === 'on') {
            $this->context->cookie->__set($consentName, '1');
        } else {
            $this->context->cookie->__set($consentName, '0');
        }

        return true;
    }
}
