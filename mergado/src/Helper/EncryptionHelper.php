<?php declare(strict_types=1);

namespace Mergado\Helper;

use Tools;

class EncryptionHelper
{
    public static function encrypt(string $string)
    {
        if (PrestashopVersionHelper::is9AndHigher()) {
            return hash_hmac('sha256', 'mergado/importPrices', _COOKIE_KEY_);
        } else {
            return Tools::encrypt($string);
        }
    }
}
