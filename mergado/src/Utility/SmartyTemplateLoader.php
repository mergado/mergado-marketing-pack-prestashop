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


namespace Mergado\Utility;

use Mergado\Helper\DebugHelper;

class SmartyTemplateLoader {

    public static function render($module, string $templatePath, $smarty = null, array $variables = null, bool $debug = false)
    {
        // Assign variables to template
        if ($variables) {
            $smarty->assign($variables);
        }

        if ($debug) {
            DebugHelper::dd($smarty->getTemplateVars());
        }

        // Render template
        $result = $module->display(__MERGADO_DIR__, $templatePath);

        // Remove variables from template
        if ($variables && is_array($variables)) {
            $smarty->clearAssign(array_keys($variables));
        }

        return $result;
    }
}
