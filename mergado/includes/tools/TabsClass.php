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

namespace Mergado\Tools;

use Mergado;

class TabsClass
{

    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function getTabs()
    {
        return [
            'feeds-product' => [
                'product' => [
                    'title' => $this->module->l('List of feeds', 'tabsclass'),
                    'active' => $this->isTabActive('product', true),
                    'icon' => 'list',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-product/product.tpl',
                ],
                'settings' => [
                    'title' => '',
                    'active' => $this->isTabActive('settings'),
                    'icon' => 'settings',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-product/settings.tpl',
                ]
            ],
            'feeds-other' => [
                'category' => [
                    'title' => $this->module->l('Category feeds', 'tabsclass'),
                    'active' => $this->isTabActive('category', true),
                    'icon' => 'list',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-other/category.tpl',
                ],
                'static' => [
                    'title' => $this->module->l('Analytical feeds', 'tabsclass'),
                    'active' => $this->isTabActive('static'),
                    'icon' => 'chart-bar-2',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-other/static.tpl',
                ],
                'stock' => [
                    'title' => sprintf($this->module->l('Heureka%sAvailability feed', 'tabsclass'), '<br>'),
                    'active' => $this->isTabActive('stock'),
                    'icon' => 'service-heureka',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-other/stock.tpl',
                ],
                'import' => [
                    'title' => $this->module->l('Import prices', 'tabsclass'),
                    'active' => $this->isTabActive('import'),
                    'icon' => 'import',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-other/import.tpl',
                ],
                'settings' => [
                    'title' => '',
                    'active' => $this->isTabActive('settings'),
                    'icon' => 'settings',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-other/settings.tpl',
                ],
            ],
        ];
    }

    private function isTabActive($key, $defaultActive = false)
    {
            if (isset($_GET['mmp-tab'])) {
                $currentActive = $_GET['mmp-tab'];
            } else {
                $currentActive = false;
            }

            if (($currentActive && $currentActive === $key) || (!$currentActive && isset($defaultActive) && $defaultActive)) {
                return true;
            } else {
                return false;
            }
    }
}
