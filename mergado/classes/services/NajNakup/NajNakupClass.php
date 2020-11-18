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

namespace Mergado\NajNakup;

use Exception;
use Mergado;
use Mergado\Tools\SettingsClass;
use CartCore as Cart;
use LanguageCore as Language;

class NajNakupClass
{
    const CONVERSIONS = 'mergado_najnakup_konverze';
    const SHOP_ID = 'mergado_najnakup_shop_id';

    private $active;
    private $shopId;

    public function __construct()
    {
    }

    /**
     * Products
     *
     * @var array $products
     */
    private $products = array();

    /**
     * Add product to products variable
     *
     * @param string $productCode
     */
    public function addProduct($productCode)
    {
        $this->products[] = $productCode;
    }

    /**
     * Send new order to najnakup.sk
     *
     * @param int $shopId
     * @param string $email
     * @param int $shopOrderId
     * @return string
     * @throws Exception
     */
    public function sendNewOrder($shopId, $email, $shopOrderId)
    {
        $url = 'http://www.najnakup.sk/dz_neworder.aspx' . '?w=' . $shopId;
        $url .= '&e=' . urlencode($email);
        $url .= '&i=' . urlencode($shopOrderId);

        foreach ($this->products as $product) {
            $url .= '&p=' . urlencode($product);
        }

        $contents = self::sendRequest($url, "www.najnakup.sk", "80");

        if ($contents === false) {
            throw new Exception('Neznama chyba');
        } elseif ($contents !== '') {
            return $contents;
        } else {
            throw new Exception($contents);
        }
    }

    /**
     * Sends request to najnakup.sk
     *
     * @param string $url
     * @param string $host
     * @param string $port
     * @return string
     * @throws Exception
     */
    private function sendRequest($url, $host, $port)
    {
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

    /**
     * Send data from backend to NajNakup
     *
     * @param $order
     * @param $lang
     * @param $shopId
     * @return string
     */
    public function sendNajnakupValuation($order, $lang, $shopId)
    {

        $active = $this->getActive($shopId);
        $id = $this->getShopId($shopId);

        if ($active === SettingsClass::ENABLED) {

            try {
                $najNakup = new NajNakupClass();

                $cart = new Cart($order['cart']->id, Language::getIdByIso($lang));
                $products = $cart->getProducts();

                foreach ($products as $product) {
                    $pid = $product['id_product'];
                    if ($product['id_product_attribute'] != SettingsClass::DISABLED) {
                        $pid .= '-' . $product['id_product_attribute'];
                    }
                    $najNakup->addProduct($pid);
                }

                return $najNakup->sendNewOrder($id, $order['customer']->email, $order['order']->id);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return false;
    }

    /*******************************************************************************************************************
     * GET FIELD
     ******************************************************************************************************************/

    /**
     * @param $shopId
     * @return mixed
     */
    public function getActive($shopId)
    {
        if (!is_null($this->active)) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(self::CONVERSIONS, $shopId);

        return $this->active;
    }

    /**
     * @param $shopId
     * @return mixed
     */
    public function getShopId($shopId)
    {
        if (!is_null($this->shopId)) {
            return $this->shopId;
        }

        $this->shopId = SettingsClass::getSettings(self::SHOP_ID, $shopId);

        return $this->shopId;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    /**
     * @return \array[][]
     */

    public static function getToggleFields()
    {

        return array(
            self::CONVERSIONS => [
                'fields' => [
                    self::SHOP_ID,
                ]
            ],
        );
    }
}
