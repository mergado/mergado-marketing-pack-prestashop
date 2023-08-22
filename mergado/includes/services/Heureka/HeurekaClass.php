<?php

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    www.mergado.cz
 * @copyright 2016 Mergado technologies, s. r. o.
 * @license   LICENSE.txt
 */

namespace Mergado\Heureka;

use CurrencyCore;
use Exception;
use Mergado;
use Mergado\Tools\LogClass;
use LanguageCore as Language;
use Cart;
use Mergado\Tools\SettingsClass;

class HeurekaClass
{
    const SERVICE_NAME = 'heureka';
    const CONSENT_NAME = 'mergado_heureka_consent';

    const HEUREKA_URL = 'https://www.heureka.cz/direct/dotaznik/objednavka.php';
    const HEUREKA_URL_SK = 'https://www.heureka.sk/direct/dotaznik/objednavka.php';

    /**
     * Send data from backend to Heureka
     *
     * @param $apiKey
     * @param $order
     * @param $lang
     * @param $sendWithItems
     * @throws \PrestaShopException
     */
    public static function heurekaVerify($apiKey, $order, $lang, $sendWithItems)
    {
        $url = null;

        $currencyIso = CurrencyCore::getCurrency($order['cart']->id_currency)['iso_code'];

        //If order by euros - then in SK
        if($currencyIso == 'EUR') {

            $url = HeurekaClass::HEUREKA_URL_SK;
        }

        //If order in CZK - then CZ
        if($currencyIso == 'CZK') {
            $url = HeurekaClass::HEUREKA_URL;
        }

        $url .= '?id=' . $apiKey;
        $url .= '&email=' . urlencode($order['customer']->email);

        $cart = new Cart($order['cart']->id, Language::getIdByIso($lang));

        if ($sendWithItems) {
            $products = $cart->getProducts();

            foreach ($products as $product) {
                $exactName = $product['name'];

                if (array_key_exists('attributes_small', $product) && $product['attributes_small'] != '') {
                    $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                    $exactName .= ': ' . implode(' ', $tmpName);
                }

                $url .= '&produkt[]=' . urlencode($exactName);
                if ($product['id_product_attribute'] === SettingsClass::DISABLED) {
                    $url .= '&itemId[]=' . urlencode($product['id_product']);
                } else {
                    $url .= '&itemId[]=' . urlencode($product['id_product'] . '-' . $product['id_product_attribute']);
                }
            }
        }

        if (isset($order['order']->id)) {
            $url .= '&orderid=' . urlencode($order['order']->id);
        }

        LogClass::log("Heureka verify Order ID: " . $order['cart']->id . " - url - " . $url);
        self::sendRequest($url);
    }

    /**
     * Send heureka request
     *
     * @param $url
     * @return string
     */
    private static function sendRequest($url)
    {
        try {
            $parsed = parse_url($url);
            $fp = fsockopen($parsed['host'], 80, $errno, $errstr, 5);

            if (!$fp) {
                LogClass::log("Heureka verify ERROR: " . json_encode(['errNo' => $errno, 'errStr' => $errstr]));
                throw new Exception($errstr . ' (' . $errno . ')');
            } else {
                $return = '';
                $out = 'GET ' . $parsed['path'] . '?' . $parsed['query'] . " HTTP/1.1\r\n" .
                    'Host: ' . $parsed['host'] . "\r\n" .
                    "Connection: Close\r\n\r\n";
                fputs($fp, $out);
                while (!feof($fp)) {
                    $return .= fgets($fp, 128);
                }
                fclose($fp);
                $returnParsed = explode("\r\n\r\n", $return);

                LogClass::log("Heureka verify RETURNED: " . json_encode(['return' => $returnParsed]));
                return empty($returnParsed[1]) ? '' : trim($returnParsed[1]);
            }
        } catch (Exception $e) {
            LogClass::log("Heureka verify ERROR: " . json_encode(['exception' => $e->getMessage()]));
        }
    }
}
