<?php

namespace Mergado\NajNakup;

class MergadoNajNakup {

    private $products = array();

    public function addProduct($productCode) {
        $this->products[] = $productCode;
    }

    public function sendNewOrder($shopId, $email, $shopOrderId) {
        $url = 'http://www.najnakup.sk/dz_neworder.aspx' . '?w=' . $shopId;
        $url .= '&e=' . urlencode($email);
        $url .= '&i=' . urlencode($shopOrderId);
        foreach ($this->products as $product) {
            $url .= '&p=' . urlencode($product);
        }
        //odoslanie informacie na server
        $contents = self::sendRequest($url, "www.najnakup.sk", "80");
        if ($contents === false) {
            throw new Exception('Neznama chyba');
        } elseif ($contents != '') {
            return $contents;
        } else {
            throw new Exception($contents);
        }
    }

    private static function sendRequest($url, $host, $port) {
        $fp = fsockopen($host, $port, $errno, $errstr, 6);
        if (!$fp) {
            throw new Exception($errstr . ' (' . $errno . ')');
        } else {
            $return = '';
            $out = "GET " . $url;
            $out .= " HTTP/1.1\r\n";
            $out .= "Host: " . $host . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                $return .= fgets($fp, 128);
            }
            fclose($fp);
            $rp1 = explode("\r\n\r\n", $return);
            return $rp1[sizeof($rp1) - 1] == '0' ? '' : $rp1[sizeof($rp1) - 1];
        }
    }

}
