<?php

namespace Mergado\Biano;

use Link;
use Media;
use Mergado;
use Mergado\Tools\LanguagesClass;

class BianoStarServiceIntegration
{
    /**
     * BianoStarClass
     */
    private $bianoStarClass;

    /**
     * @var string
     */
    private $lang;

    public function __construct()
    {
        $this->bianoStarClass = new BianoStarClass(Mergado::getShopId());
        $this->lang = LanguagesClass::getLangIso();
    }

    public function getService(): BianoStarClass
    {
        return $this->bianoStarClass;
    }

    public function addCheckboxForPS17($context, $path) {
        //Add checkbox for arukereso
        if (_PS_VERSION_ >= Mergado::PS_V_17) {

            if ($this->bianoStarClass->isActive($this->lang)) {
                $textInLanguage = $this->bianoStarClass->getOptOut($this->lang);

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
                            "checkboxChecked" => $context->cookie->mergado_biano_star_consent
                        )
                    )
                );

                // Create a link with ajax path
                $context->controller->addJS($path . 'views/js/order17/bianoStar.js');
            }
        }
    }

    public function addCheckboxForPS16($module, $smarty, $context, $path) {
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            if ($this->bianoStarClass->isActive($this->lang)) {
                $textInLanguage = $this->bianoStarClass->getOptOut($this->lang);

                if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0) ) {
                    $textInLanguage = BianoStarClass::DEFAULT_OPT;
                }

                $smarty->assign(array(
                    'bianoStar_consentText' => $textInLanguage,
                    'bianoStar_checkboxChecked' => $context->cookie->mergado_arukereso_consent,
                ));

                $context->controller->addJS($path . 'views/js/orderOPC/bianoStar.js');

                return $module->display($path, '/views/templates/front/orderCarrier/bianoStar.tpl');
            }
        }

        return '';
    }

    public function shouldBeSent($consent) {
        // OPT OUT
        if ($consent !== '1' && $this->bianoStarClass->isActive($this->lang)) {
            return true;
        }

        return false;
    }
}
