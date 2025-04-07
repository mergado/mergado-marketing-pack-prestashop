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


namespace Mergado\Helper;

use AdminController;
use AdminMergadoController;
use Closure;
use HelperForm;
use Tools;

class FormHelper
{
    public static function renderForm(AdminMergadoController $module, string $moduleName, array $formFields, array $fieldValues, Closure $translateFunction, string $defaultLang = null): string
    {
        $helper = new HelperForm();

        $helper->module = $module;
        $helper->name_controller = $moduleName;

        $helper->tpl_vars = ['fields_value' => $fieldValues];

        if (isset($defaultLang)) {
            $helper->default_form_language = $defaultLang;
            $helper->allow_employee_form_lang = $defaultLang;
        }

        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $moduleName;

        $helper->toolbar_btn = [
            'save' =>
                [
                    'desc' => $translateFunction('Save'),
                    'href' => AdminController::$currentIndex . '&configure=' . $moduleName . '&save' . $moduleName .
                        '&token=' . Tools::getAdminTokenLite('AdminModules'),
                ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $translateFunction('Back to list')
            ]
        ];

        return $helper->generateForm($formFields);
    }

    public static function assignValuesByInputType(array $forms, array $fieldValues): array
    {
        //Fill in fields that was not already set
        foreach($forms as $form) {
            foreach($form['form']['input'] as $input) {
                if(!isset($fieldValues[$input['name']])) {
                    switch($input['type']) {
                        case 'hidden':
                            break;
                        case 'text':
                            $fieldValues[$input['name']] = '';
                            break;
                        case 'switch':
                        case 'select':
                        case 'checkbox':
                            $fieldValues[$input['name']] = 0;
                            break;
                    }
                }
            }
        }

        return $fieldValues;
    }
}
