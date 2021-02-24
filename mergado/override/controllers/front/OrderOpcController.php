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

class OrderOpcController extends OrderOpcControllerCore
{

    /**
     * Initialize order opc controller
     * @see FrontController::init()
     */
    public function init()
    {
        if (_PS_VERSION_ < 1.7) {
            if (Tools::isSubmit('ajax')) {
                if (Tools::isSubmit('method')) {
                    if (Tools::getValue('method') === 'heurekaConsent')  {
                        if (Tools::getValue('heurekaData') == '1') {
                            $this->context->cookie->mergado_heureka_consent = '1';
                        } else {
                            $this->context->cookie->mergado_heureka_consent = '0';
                        }

                        unset($_POST['ajax']);
                        unset($_GET['ajax']);
                    }

                    if (Tools::getValue('method') === 'zboziConsent')  {
                        if (Tools::getValue('zboziData') == '1') {
                            $this->context->cookie->mergado_zbozi_consent = '1';
                        } else {
                            $this->context->cookie->mergado_zbozi_consent = '0';
                        }

                        unset($_POST['ajax']);
                        unset($_GET['ajax']);
                    }

                    if (Tools::getValue('method') === 'arukeresoConsent')  {
                        if (Tools::getValue('arukeresoData') == '1') {
                            $this->context->cookie->mergado_arukereso_consent = '1';
                        } else {
                            $this->context->cookie->mergado_arukereso_consent = '0';
                        }

                        unset($_POST['ajax']);
                        unset($_GET['ajax']);
                    }
                }
            }
        }
        parent::init();
    }
}
