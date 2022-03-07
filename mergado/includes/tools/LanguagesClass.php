<?php

namespace Mergado\Tools;

use Language;

class LanguagesClass
{
    const MERGADO_TO_DOMAIN = [
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

    const PACK_LANG_TO_DOMAIN = [
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

    private static function getDomain($domains)
    {
        $langIso = strtolower(self::getLangIso());

        // If exist full iso for example: swiss deutsch
        if (array_key_exists($langIso, $domains)) {
            return $domains[$langIso];
        } else {
            return $domains['other'];
        }
    }

    //TODO: Remove unnecessary lang send to this function
    public static function getLangIso($lang = null)
    {
        if ($lang === null) {
            global $cookie;
            $lang = Language::getIsoById( (int)$cookie->id_lang );
        }

        //I can do this fix, because noone sending 'cs' in lowercase
        $lang = strtoupper($lang);

        if($lang == 'CS') {
            $lang = 'CZ';
        }

        return $lang;
    }
}