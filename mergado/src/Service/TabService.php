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


namespace Mergado\Service;

class TabService extends AbstractBaseService
{
    public function getTabs($module): array
    {
        return [
            'feeds-product' => [
                'product' => [
                    'title' => $module->l('List of feeds', 'tabservice'),
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
                    'title' => $module->l('Category feeds', 'tabservice'),
                    'active' => $this->isTabActive('category', true),
                    'icon' => 'list',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-other/category.tpl',
                ],
                'static' => [
                    'title' => $module->l('Analytical feeds', 'tabservice'),
                    'active' => $this->isTabActive('static'),
                    'icon' => 'chart-bar-2',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-other/static.tpl',
                ],
                'stock' => [
                    'title' => sprintf($module->l('Heureka%sAvailability feed', 'tabservice'), '<br>'),
                    'active' => $this->isTabActive('stock'),
                    'icon' => 'service-heureka',
                    'contentPath' => __MERGADO_DIR__ . '/views/templates/admin/mergado/pages/partials/tabs-feeds-other/stock.tpl',
                ],
                'import' => [
                    'title' => $module->l('Import prices', 'tabservice'),
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

    private function isTabActive($key, $defaultActive = false): bool {
        if (isset($_GET['mmp-tab'])) {
            $currentActive = $_GET['mmp-tab'];
        } else {
            $currentActive = false;
        }

        return ($currentActive && $currentActive === $key) || (!$currentActive && isset($defaultActive) && $defaultActive);
    }
}
