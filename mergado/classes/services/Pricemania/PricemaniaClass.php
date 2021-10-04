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

namespace Mergado\Pricemania;

use Exception;
use Mergado;
use Mergado\Tools\SettingsClass;
use Tools;
use CartCore as Cart;
use LanguageCore as Language;

class PricemaniaClass
{
    /**
     * Endpoint URL.
     *
     * @var string BASE_URL
     */
    const BASE_URL = 'http://www.pricemania.sk/overeny-obchod-objednavka?id=%SHOP_ID%&objednavka_
id=%ORDER_ID%&email=%EMAIL%%PRODUKTY%';

    /**
     * Public identifier of request creator.
     *
     * @var string
     */
    public $shopId;

    /**
     * Identifier of this order.
     *
     * @var string
     */
    public $orderId;

    /**
     * Customer email.
     *
     * @var string
     */
    public $email;

    /**
     * Order products
     *
     * @var array
     */
    public $produkty;


    /**
     * Initialize
     *
     * @param string $shopId Shop identifier
     *
     * @throws Exception can be thrown if $shopId
     *                   is missing or invalid.
     */
    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }


    /**
     * Adds ordered product using name.
     *
     * @param string $productName Ordered product name
     */
    public function addProduct($productName)
    {
        $item = urlencode($productName);
        $this->produkty[] = $item;
    }


    /**
     * Sets order attributes within
     * \p email ,
     * \p orderId
     *
     * @param array $orderAttributes Array of various order attributes
     */
    public function setOrder($orderAttributes)
    {
        $this->email = $orderAttributes['email'];
        $this->orderId = $orderAttributes['orderId'];
    }

    /**
     * Creates HTTP request and returns response body.
     *
     * @param string $url URL
     *
     * @return bool true if everything is perfect else throws exception
     * @throws Exception
     */
    protected function sendRequest($url)
    {
        $response = Tools::file_get_contents($url);

        if ($response === false) {
            throw new Exception('Unable to establish connection to service');
        } else {
            return true;
        }
    }

    /**
     * Sends request
     *
     * @return bool true if everything is perfect else throws exception
     * @throws Exception
     */
    public function send()
    {
        $url = str_replace('%SHOP_ID%', $this->shopId, self::BASE_URL);
        $url = str_replace('%ORDER_ID%', $this->orderId, $url);
        $url = str_replace('%EMAIL%', $this->email, $url);

        $produktyParam = "";
        foreach ($this->produkty as $key => $value) {
            $produktyParam .= '&produkt=' . $value;
        }

        $url = str_replace('%PRODUKTY%', $produktyParam, $url);

        try {
            $status = $this->sendRequest($url);

            return $status;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Send data from backend to Pricemania
     *
     * @param $order
     * @param $lang
     * @param $shopID
     * @return bool
     */
    public static function sendPricemaniaOverenyObchod($order, $lang, $shopID)
    {
        $active = SettingsClass::getSettings(SettingsClass::PRICEMANIA['VERIFIED'], $shopID);
        $id = SettingsClass::getSettings(SettingsClass::PRICEMANIA['SHOP_ID'], $shopID);

        if ($active === SettingsClass::ENABLED) {
            try {
                $pm = new PricemaniaClass($id);
                $cart = new Cart($order['cart']->id, Language::getIdByIso($lang));
                $products = $cart->getProducts();
                foreach ($products as $product) {
                    $exactName = $product['name'];

                    if (array_key_exists('attributes_small', $product) && $product['attributes_small'] != '') {
                        $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                        $exactName .= ': ' . implode(' ', $tmpName);
                    }


                    $pm->addProduct($exactName);
                }

                $pm->setOrder(array(
                    'email' => $order['customer']->email,
                    'orderId' => $order['order']->id
                ));

                $pm->send();
                return true;
            } catch (Exception $e) {
                echo 'Error: ' . $e->getMessage();
            }
        }

        return false;
    }
}
