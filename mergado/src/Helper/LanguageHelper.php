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

use Language;

class LanguageHelper
{
    public const MERGADO_TO_DOMAIN = [
        'cz' => 'cz',
        'sk' => 'sk',
        'hu' => 'hu',
        'pl' => 'pl',
        'hr' => 'hr',
        'sl' => 'si', // slovenia
        'de' => 'de',
        'de_AT' => 'at',
        'de_CH' => 'ch', // switzerland
        'rs' => 'rs', // serbia
        'sr_RS' => 'rs',
        'other' => 'com',
    ];

    public const PACK_LANG_TO_DOMAIN = [
        'cz' => 'cz',
        'sk' => 'cz',
        'hu' => 'hu',
        'other' => 'com',
    ];

    public static function getPackDomain()
    {
        return self::getDomain(self::PACK_LANG_TO_DOMAIN);
    }

    public static function getMergadoDomain()
    {
        return self::getDomain(self::MERGADO_TO_DOMAIN);
    }

    public static function getLang(): string
    {
        $lang = self::getLangIso();

        //I can do this fix, because noone sending 'cs' in lowercase
        $lang = strtoupper($lang);

        if($lang === 'CS') {
            $lang = 'CZ';
        }

        return $lang;
    }

    public static function getLangIso(): string
    {
        global $cookie;
        return Language::getIsoById( (int)$cookie->id_lang );
    }

    private static function getDomain($domains)
    {
        $langIso = strtolower(self::getLang());

        // If exist full iso for example: swiss deutsch
        if (array_key_exists($langIso, $domains)) {
            return $domains[$langIso];
        }

        return $domains['other'];
    }
}
