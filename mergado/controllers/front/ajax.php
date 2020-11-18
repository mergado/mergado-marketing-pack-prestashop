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

require_once _PS_MODULE_DIR_.'mergado/mergado.php';

class MergadoAjaxModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $module = new Mergado;

        // You may should do some security work here, like checking an hash from your module
        if (Tools::isSubmit('action')) {
            switch (Tools::getValue('action')) {
                case 'setHeurekaOpc':
                    if (_PS_VERSION_ >= 1.7) {
                        if (Tools::getValue('heurekaData') == '1') {
                            $this->context->cookie->mergado_heureka_consent = true;
                        } else {
                            $this->context->cookie->mergado_heureka_consent = false;
                        }
                    }

                    $response = true;

                    // Edit default response and do some work here
//                    $response = array('status' => true, "message" => $module->l('Set!'));

                    break;

                default:
                    break;

            }
        }

        // Classic json response
        $json = Tools::jsonEncode($response);
        echo $json;
        die;
    }
}
