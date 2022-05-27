<?php

namespace Mergado\includes\services\Biano\BianoStar;

use Link;
use Media;
use Mergado;
use Mergado\Tools\LanguagesClass;

class BianoStarServiceIntegration
{
    /**
     * BianoStarService
     */
    private $bianoStarService;

    /**
     * @var string
     */
    private $lang;

    public function __construct()
    {
        $this->bianoStarService = new BianoStarService(Mergado::getShopId());
        $this->lang = LanguagesClass::getLangIso();
    }

    public function getService(): BianoStarService
    {
        return $this->bianoStarService;
    }

    public function addCheckboxForPS17($context, $path) {
        if (_PS_VERSION_ >= Mergado::PS_V_17) {

            if ($this->bianoStarService->isActive($this->lang)) {
                $textInLanguage = $this->bianoStarService->getOptOut($this->lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                    $textInLanguage = 'Do not send a satisfaction questionnaire within the Trusted Shop program.';
                }

                $link = new Link;
                $parameters = array("action" => "setBianoStarOpc");
                $ajax_link = $link->getModuleLink('mergado','ajax', $parameters);

                Media::addJsDef(
                    array(
                        "mmp_bianoStar" => array (
                            "ajaxLink" => $ajax_link,
                            "optText" => $textInLanguage,
                            "checkboxChecked" => $context->cookie->__get(BianoStarService::CONSENT_NAME)
                        )
                    )
                );

                // Create a link with ajax path
                $context->controller->addJS($path . BianoStarService::TEMPLATES_PATH . 'order17.js');
            }
        }
    }

    public function addCheckboxForPS16($module, $smarty, $context, $path) {
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            if ($this->bianoStarService->isActive($this->lang)) {
                $textInLanguage = $this->bianoStarService->getOptOut($this->lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0) ) {
                    $textInLanguage = BianoStarService::DEFAULT_OPT;
                }

                $smarty->assign(array(
                    'bianoStar_consentText' => $textInLanguage,
                    'bianoStar_checkboxChecked' => $context->cookie->mergado_arukereso_consent,
                ));

                $context->controller->addJS($path . BianoStarService::TEMPLATES_PATH . 'orderOPC.js');

                return $module->display($path, BianoStarService::TEMPLATES_PATH . 'orderCarrier.tpl');
            }
        }

        return '';
    }

    public function shouldBeSent($consent) {
        // OPT OUT
        if ($consent !== '1' && $this->bianoStarService->isActive($this->lang)) {
            return true;
        }

        return false;
    }
}
