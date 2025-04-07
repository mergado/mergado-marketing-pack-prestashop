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

class PrestashopVersionHelper
{
    public const VERSION_16 = '1.6';
    public const VERSION_17 = '1.7';

    public static function is16(): bool
    {
        return version_compare(_PS_VERSION_, self::VERSION_16, '>=') && version_compare(_PS_VERSION_, self::VERSION_17, '<');
    }

    public static function is17(): bool
    {
        return version_compare(_PS_VERSION_, self::VERSION_16, '>') && version_compare(_PS_VERSION_, self::VERSION_17, '<=');
    }

    public static function is16AndLower(): bool
    {
        return (bool)version_compare(_PS_VERSION_, self::VERSION_17, '<');
    }

    public static function is16AndHigher(): bool
    {
        return (bool)version_compare(_PS_VERSION_, self::VERSION_16, '>=');
    }

    public static function is17AndHigher(): bool
    {
        return (bool)version_compare(_PS_VERSION_, self::VERSION_17, '>=');
    }
}
