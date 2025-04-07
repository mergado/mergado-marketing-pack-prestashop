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

class JsonResponse
{
    public static function send_json_success($content): void
    {
        http_response_code(200);
        echo json_encode($content);
        exit;
    }

    public static function send_json_error($content): void
    {
        http_response_code(500);
        echo json_encode($content);
        exit;
    }

    public static function send_json_code($content, $code): void
    {
        http_response_code($code);
        echo json_encode($content);
        exit;
    }
}
