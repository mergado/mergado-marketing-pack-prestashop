<?php

/**
 * 2007-2016 PrestaShop
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
 *  @author     PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2016 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
class AdminController extends AdminControllerCore {

    protected function initTabModuleList() {
        if (!$this->isFresh(Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, 86400)) {
            if (file_exists(_PS_MODULE_DIR_ . 'mergado/mergado.php')) {
                require_once(_PS_MODULE_DIR_ . 'mergado/mergado.php');
                $mergado = new Mergado();
                $mergado->updateVersionXml();
            }
        }

        parent::initTabModuleList();
    }

    public function postProcess() {

        $mName = '';
        $updateRequest = Tools::getValue('update');
        if ($updateRequest && $updateRequest == 'mergado') {
            $mName = 'mergado';
        }

        $updateAllRequest = Tools::getValue('updateAll');
        if ($updateAllRequest) {
            $mName = 'mergado';
        }

        $checkRequest = Tools::getValue('check');
        if ($checkRequest && $checkRequest == '1') {
            $mName = 'mergado';
        }

        if ($mName == 'mergado' && file_exists(_PS_MODULE_DIR_ . $mName . '/' . $mName . '.php')) {
            require_once(_PS_MODULE_DIR_ . $mName . '/' . $mName . '.php');
            $mergado = new Mergado();
            $tryUpdate = $mergado->updateModule();
            unset($mergado);

            if ($tryUpdate) {
                $this->mergadoCopyFiles($tryUpdate['from'], $tryUpdate['to']);
                //$this->mergadoDeleteFiles($tryUpdate['delete']);
            }
        }

        parent::postProcess();
    }

    public function mergadoDeleteFiles($dir) {
        foreach (array_diff(scandir($dir), array('..', '.')) as $file) {
            if (is_dir($dir . $file))
                $this->mergadoDeleteFiles($dir . $file . '/');
            else
                unlink($dir . $file);
        }
        rmdir($dir);
    }

    public function mergadoCopyFiles($from, $to) {

        foreach (array_diff(scandir($from), array('..', '.')) as $file) {
            if (is_dir($from . '/' . $file))
                $this->mergadoCopyFiles($from . '/' . $file, $to . '/' . $file);
            else {
                $copy = copy($from . '/' . $file, $to . '/' . $file);
                //echo $from .'/'. $file . ' > ' . $to .'/'. $file . ': '.$copy.'<br/><hr/>';
            }
        }
        mkdir($to);
    }

}
