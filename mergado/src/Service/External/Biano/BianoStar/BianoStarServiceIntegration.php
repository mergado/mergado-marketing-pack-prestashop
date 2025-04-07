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
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Service\External\Biano\BianoStar;

use Link;
use Media;
use Mergado;
use Mergado\Helper\LanguageHelper;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class BianoStarServiceIntegration extends AbstractBaseService
{
    /**
     * BianoStarService
     */
    private $bianoStarService;

    /**
     * @var string
     */
    private $lang;

    public const TEMPLATES_PATH = 'views/templates/services/BianoStar/';
    public const JS_PATH = 'views/js/services/BianoStar/';

    protected function __construct()
    {
        $this->bianoStarService = BianoStarService::getInstance();
        $this->lang = LanguageHelper::getLang();

        parent::__construct();
    }

    public function getService(): BianoStarService
    {
        return $this->bianoStarService;
    }

    public function addCheckboxForPS17($context, $path): void
    {
        try {
            if (!$this->bianoStarService->isActive($this->lang)) {
                return;
            }

            if (PrestashopVersionHelper::is17AndHigher()) {
                $textInLanguage = $this->bianoStarService->getOptOut($this->lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Trusted Shop program.';
                }

                $link = new Link;
                $parameters = array("action" => "setBianoStarOpc");
                $ajax_link = $link->getModuleLink('mergado', 'ajax', $parameters);

                Media::addJsDef(
                    array(
                        "mmp_bianoStar" => array(
                            "ajaxLink" => $ajax_link,
                            "optText" => $textInLanguage,
                            "checkboxChecked" => $context->cookie->__get(BianoStarService::CONSENT_NAME)
                        )
                    )
                );

                // Create a link with ajax path
                $context->controller->addJS($path . self::JS_PATH . 'order17.js');
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function addCheckboxForPS16($module, $smarty, $context, $path): string
    {
        try {
            if (!$this->bianoStarService->isActive($this->lang)) {
                return '';
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                $textInLanguage = $this->bianoStarService->getOptOut($this->lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = BianoStarService::DEFAULT_OPT;
                }

                $context->controller->addJS($path . self::JS_PATH . 'orderOPC.js');

                return SmartyTemplateLoader::render(
                    $module,
                    self::TEMPLATES_PATH . 'orderCarrier.tpl',
                    $smarty,
                    [
                        'bianoStar_consentText' => $textInLanguage,
                        'bianoStar_checkboxChecked' => $context->cookie->mergado_arukereso_consent,
                    ]
                );
            }

            return '';
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }
}
