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
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   LICENSE.txt
 */

class Pricemania
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
     * @param string $shopId     Shop identifier
     *
     * @throws Exception can be thrown if $shopId
     *                   is missing or invalid.
     */
    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * Sets customer email.
     *
     * @param string $email Customer email address
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Adds order ID.
     *
     * @param int $orderId Order identifier
     */
    public function addOrderId($orderId)
    {
        $this->orderId = $orderId;
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
     */
    protected function sendRequest($url)
    {                
        $response = Tools::file_get_contents($url);

        if ($response === false) {
            throw new Exception('Unable to establish connection to service');
        }
    }
   
    /**
     * Sends request
     *
     * @return bool true if everything is perfect else throws exception
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
}
