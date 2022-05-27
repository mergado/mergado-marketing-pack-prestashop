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

namespace Mergado\includes\services\ArukeresoFamily;

use Exception;
use Link;
use Media;
use Mergado;
use Mergado\Tools\HelperClass;
use TrustedShop;

abstract class AbstractArukeresoFamilyServiceIntegration
{
    private $service;

    public function __construct($service) {
        $this->service = $service;
    }

    /*******************************************************************************************************************
     * GET SMARTY VARIABLES
     ******************************************************************************************************************/

    public function getWidgetSmartyVariables()
    {
        return [
            "WEB_API_KEY" => $this->service->getWebApiKey(),
            "DESKTOP_POSITION" => $this->service::DESKTOP_POSITIONS()[$this->service->getWidgetDesktopPosition()]['value'],
            "MOBILE_POSITION" => $this->service::MOBILE_POSITIONS()[$this->service->getWidgetMobilePosition()]['value'],
            "MOBILE_WIDTH" => $this->service->getWidgetMobileWidth(),
            "APPEARANCE_TYPE" => $this->service::APPEARANCE_TYPES()[$this->service->getWidgetAppearanceType()]['value']
        ];
    }

    /*******************************************************************************************************************
     * FUNCTIONS
     ******************************************************************************************************************/

    /**
     * @param $items
     * @param $customer
     * @param $consent
     * @return string
     */
    public function orderConfirmation($items, $customer, $cookie)
    {
        $consent = $cookie->__get($this->service::CONSENT_NAME);

        if ($this->shouldBeSent($consent)) {
            $products = [];

            foreach($items as $item) {
                $id = HelperClass::getProductId($item);
                $name = $item['product_name'];

                /** Assign product to array */
                $products[$id] = $name;
            }

            try {
                /** Provide your own WebAPI key. You can find your WebAPI key on your partner portal. */
                $Client = new TrustedShop($this->service->getWebApiKey());

                /** Provide the e-mail address of your customer. You can retrieve the e-amil address from the webshop engine. */
                $Client->SetEmail($customer->email);

                /** Customer's cart example. */
                $Cart = $products;

                /** Provide the name and the identifier of the purchased products.
                 * You can get those from the webshop engine.
                 * It must be called for each of the purchased products. */
                foreach($Cart as $ProductIdentifier => $ProductName) {
                    /** If both product name and identifier are available, you can provide them this way: */
                    $Client->AddProduct($ProductName, $ProductIdentifier);
                    /** If neither is available, you can leave out these calls. */
                }

                /** This method perpares to send us the e-mail address and the name of the purchased products set above.
                 *  It returns an HTML code snippet which must be added to the webshop's source.
                 *  After the generated code is downloaded into the customer's browser it begins to send purchase information. */

                echo $Client->Prepare();
                /** Here you can implement error handling. The error message can be obtained in the manner shown below. This step is optional. */
            } catch (Exception $ex) {
                $ErrorMessage = $ex->getMessage();
            }
        }

        $cookie->__set($this->service::CONSENT_NAME, '0');
    }


    public function addCheckboxForPs17($context, $path) {
        if (_PS_VERSION_ >= Mergado::PS_V_17) {
            $lang = Mergado\Tools\LanguagesClass::getLangIso();

            if ($this->service->isActive()) {
                $textInLanguage = $this->service->getOptOut($lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Trusted Shop program.';
                }

                $link = new Link;
                $parameters = array("action" => "set" . ucfirst($this->service::SERVICE_NAME) . "Opc");
                $ajax_link = $link->getModuleLink('mergado','ajax', $parameters);

                Media::addJsDef(
                    array(
                        "mmp_" . $this->service::SERVICE_NAME => array (
                            "ajaxLink" => $ajax_link,
                            "optText" => $textInLanguage,
                            "checkboxChecked" => $context->cookie->__get($this->service::CONSENT_NAME)
                        )
                    )
                );

                // Create a link with ajax path
                $context->controller->addJS($path . $this->service::TEMPLATES_PATH . 'order17.js');
            }
        }
    }


    public function addCheckboxForPs16($module, $smarty, $context, $path) {
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $lang = Mergado\Tools\LanguagesClass::getLangIso();

            if ($this->service->isActive()) {
                $textInLanguage = $this->service->getOptOut($lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0) ) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Trusted Shop program.';
                }

                $smarty->assign(array(
                    $this->service::SERVICE_NAME . '_consentText' => $textInLanguage,
                    $this->service::SERVICE_NAME . '_checkboxChecked' => $context->cookie->__get($this->service::CONSENT_NAME),
                ));

                $context->controller->addJS($path . $this->service::TEMPLATES_PATH . 'orderOPC.js');

                return $module->display($path, $this->service::TEMPLATES_PATH . 'orderCarrier.tpl');
            }
        }
    }

    public function addWidget($module, &$smarty, $path) {
        if ($this->service->isWidgetActive()) {
            $smarty->assign(
                array(
                    $this->service::SERVICE_NAME . 'Widget' => $this->getWidgetSmartyVariables(),
                )
            );

            return $module->display($path, $this->service::TEMPLATES_PATH . 'widget.tpl');
        }
    }

    public function shouldBeSent($consent) {
        // OPT OUT
        if ($consent !== '1' && $this->service->isActive()) {
            return true;
        }

        return false;
    }
}
