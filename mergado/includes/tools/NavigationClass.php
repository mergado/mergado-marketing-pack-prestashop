<?php

namespace Mergado\Tools;

class NavigationClass
{
    private $defaultPageUrl;

    public function __construct($context)
    {
        $this->defaultPageUrl = $context->link->getAdminLink('AdminMergado', true);
    }

    public function getPageLink($name)
    {
        return $this->defaultPageUrl . '&page=' . $name;
    }

    public function getPageLinkWithTab($name, $tab)
    {
        return $this->defaultPageUrl . '&page=' . $name . '&mmp-tab=' . $tab;
    }

    public function getWizardUrl($pageName, $wizard, $tab)
    {
        return $this->defaultPageUrl . '&page=' . $pageName . '&mmp-wizard=' . $wizard . '&mmp-tab=' . $tab;
    }
}