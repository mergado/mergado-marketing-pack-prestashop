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


namespace Mergado\Form;

use Mergado\Helper\FormHelper;
use Mergado\Manager\DatabaseManager;

abstract class AbstractForm
{
    abstract protected function getDefaultFieldValues() : array;
    abstract protected function getFormDefinition($module, $moduleName, $translateFunction) : array;

    public function render($module, $moduleName, $defaultLang, $translateFunction): string
    {
        $formFields = $this->getFormDefinition($module, $moduleName, $translateFunction);

        $fieldValues = [];

        // Fill all other fields from database
        $settingsTable = DatabaseManager::getWholeSettings();
        $settingsValues = [];

        foreach ($settingsTable as $s) {
            $settingsValues[$s['key']] = $s['value'];
        }

        foreach ($settingsValues as $key => $value) {
            if (!isset($fieldValues[$key])) {
                $fieldValues[$key] = $value;
            }
        }

        // Assign default fields
        $fieldValues = array_merge($fieldValues, $this->getDefaultFieldValues());

        // Default values
        $fieldValuesFilled = FormHelper::assignValuesByInputType($formFields, $fieldValues);

        return FormHelper::renderForm($module, $moduleName,$formFields, $fieldValuesFilled, $translateFunction, $defaultLang);
    }
}
