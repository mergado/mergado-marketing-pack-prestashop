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

namespace Mergado\Service\External\ArukeresoFamily;

use Exception;
use Link;
use Media;
use Mergado;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\External\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\Service\External\ArukeresoFamily\Compari\CompariService;
use Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\Helper\LanguageHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\External\HeurekaGroup\AbstractHeurekaGroupServiceIntegration;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

abstract class AbstractArukeresoFamilyServiceIntegration extends AbstractHeurekaGroupServiceIntegration
{
    /**
     * @var ArukeresoService|PazaruvajService|CompariService
     */
    private $service;

    /**
     * @var string
     */
    private $lang;

    public function __construct($service)
    {
        $this->service = $service;
        $this->lang = LanguageHelper::getLang();

        parent::__construct($this->service);
    }

    /*******************************************************************************************************************
     * GET SMARTY VARIABLES
     ******************************************************************************************************************/

    public function getWidgetSmartyVariables(): array
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

    public function orderConfirmation($items, $customer, $cookie): void
    {
        try {
            $consent = $cookie->__get($this->service::CONSENT_NAME);

            if ($consent !== '1' && $this->service->isActive()) {
                $products = [];

                foreach ($items as $item) {
                    $id = ProductHelper::getProductId($item);
                    $name = $item['product_name'];

                    /** Assign product to array */
                    $products[$id] = $name;
                }

                try {
                    /** Provide your own WebAPI key. You can find your WebAPI key on your partner portal. */
                    $Client = new TrustedShop($this->service->getWebApiKey(), $this->service::SERVICE_URL_SEND);

                    /** Provide the e-mail address of your customer. You can retrieve the e-amil address from the webshop engine. */
                    $Client->SetEmail($customer->email);

                    /** Customer's cart example. */
                    $Cart = $products;

                    /** Provide the name and the identifier of the purchased products.
                     * You can get those from the webshop engine.
                     * It must be called for each of the purchased products. */
                    foreach ($Cart as $ProductIdentifier => $ProductName) {
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
                    $errorMessage = 'ArukeresoFamilyServiceIntegration error: ';

                    if ($this->service instanceof Mergado\Service\External\ArukeresoFamily\Compari\CompariService) {
                        $errorMessage = '[Compari]: Order confirmation error: ';
                    } else if ($this->service instanceof Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajService) {
                        $errorMessage = '[Pazaruvaj]: Order confirmation error: ';
                    } else if ($this->service instanceof Mergado\Service\External\ArukeresoFamily\Arukereso\ArukeresoService) {
                        $errorMessage = '[Arukereso]: Order confirmation error: ';
                    }

                    $this->logger->error($errorMessage, ['exception' => $ex]);
                }
            }

            $cookie->__set($this->service::CONSENT_NAME, '0');
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }


    public function addCheckboxForPs17($context, $path): void
    {
        try {
            if (!$this->service->isActive()) {
                return;
            }

            if (PrestashopVersionHelper::is17AndHigher()) {
                $textInLanguage = $this->service->getOptOut($this->lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Trusted Shop program.';
                }

                $link = new Link;
                $parameters = array("action" => "set" . ucfirst($this->service::SERVICE_NAME) . "Opc");
                $ajax_link = $link->getModuleLink('mergado', 'ajax', $parameters);

                Media::addJsDef(
                    array(
                        "mmp_" . $this->service::SERVICE_NAME => array(
                            "ajaxLink" => $ajax_link,
                            "optText" => $textInLanguage,
                            "checkboxChecked" => $context->cookie->__get($this->service::CONSENT_NAME)
                        )
                    )
                );

                // Create a link with ajax path
                $context->controller->addJS($path . $this->service::JS_PATH . 'order17.js');
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }


    public function addCheckboxForPs16($module, $smarty, $context, $path): string
    {
        try {
            if (!$this->service->isActive()) {
                return '';
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                $textInLanguage = $this->service->getOptOut($this->lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Trusted Shop program.';
                }

                $context->controller->addJS($path . $this->service::JS_PATH . 'orderOPC.js');

                return SmartyTemplateLoader::render(
                    $module,
                    $this->service::TEMPLATES_PATH . 'orderCarrier.tpl',
                    $smarty,
                    [
                        $this->service::SERVICE_NAME . '_consentText' => $textInLanguage,
                        $this->service::SERVICE_NAME . '_checkboxChecked' => $context->cookie->__get($this->service::CONSENT_NAME),
                    ]
                );
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function addWidget($module, &$smarty): string
    {
        try {
            if (!$this->service->isWidgetActive()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                $this->service::TEMPLATES_PATH . 'widget.tpl',
                $smarty,
                [
                    $this->service::SERVICE_NAME . 'Widget' => $this->getWidgetSmartyVariables($module),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }
}
