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

class CacheService extends AbstractBaseService
{
    private static $data = [];

    public static function get($key, callable $dataFunction = null) {
        if (isset(self::$data[$key])) {
            return self::$data[$key];
        }

        self::$data[$key] = $dataFunction();
        return self::$data[$key];
    }

    public static function set($key, $value): void
    {
        self::$data[$key] = $value;
    }

    public static function isset($key): bool {
        return isset(self::$data[$key]);
    }
}
