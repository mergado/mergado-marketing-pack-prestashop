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

class TemplateLoader {

	public static function getTemplate(string $path, array $variables = null)
	{
        if ($variables) {
            extract($variables); // Extract variables for template
        }

		ob_start();

		include $path;

        return ob_get_clean();
	}
}
