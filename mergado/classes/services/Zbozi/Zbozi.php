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

namespace Mergado\Zbozi;

use Exception;
use Mergado;
use Mergado\Tools\SettingsClass;
use CarrierCore as Carrier;

require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Zbozi/ZboziCartItem.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/services/Zbozi/DeliveryType.php';

class Zbozi
{

    const ZBOZI_SANDBOX = false;

    /**
     * Endpoint URL
     *
     * @var string BASE_URL
     */
    const BASE_URL = 'https://%%DOMAIN%%/action/%%SHOP_ID%%/conversion/backend';

    /**
     * Private identifier of request creator
     *
     * @var string $PRIVATE_KEY
     */
    public $PRIVATE_KEY;

    /**
     * Public identifier of request creator
     *
     * @var string $SHOP_ID
     */
    public $SHOP_ID;

    /**
     * Identifier of this order
     *
     * @var string $orderId
     */
    public $orderId;

    /**
     * Customer email
     * Should not be set unless customer allows to do so.
     *
     * @var string $email
     */
    public $email;

    /**
     * How the order will be transfered to the customer
     *
     * @var string $deliveryType
     */
    public $deliveryType;

    /**
     * Cost of delivery (in CZK)
     *
     * @var float $deliveryPrice
     */
    public $deliveryPrice;

    /**
     * How the order was paid
     *
     * @var string $paymentType
     */
    public $paymentType;

    /**
     * Other fees (in CZK)
     *
     * @var string $otherCosts
     */
    public $otherCosts;

    /**
     * Array of CartItem
     *
     * @var array $cart
     */
    public $cart = array();

    /**
     * Check if string is not empty
     *
     * @param string $question String to test
     * @return boolean
     */
    private static function isNullOrEmptyString($question)
    {
        return (!isset($question) || trim($question) === '');
    }

    /**
     * Initialize Zbozi service
     *
     * @param string $shopId Shop identifier
     * @param string $privateKey Shop private key
     * @throws ZboziException can be thrown if \p $privateKey and/or \p $shopId
     * is missing or invalid.
     */
    public function __construct($shopId, $privateKey)
    {
        if ($this::isNullOrEmptyString($shopId)) {
            throw new ZboziException('shopId si mandatory');
        } else {
            $this->SHOP_ID = $shopId;
        }

        if ($this::isNullOrEmptyString($privateKey)) {
            throw new ZboziException('privateKey si mandatory');
        } else {
            $this->PRIVATE_KEY = $privateKey;
        }
    }

    /**
     * Adds ordered product using name
     *
     * @param string $productName Ordered product name
     */
    public function addProduct($productName)
    {
        $item = new CartItem();
        $item->productName = $productName;
        $this->cart[] = $item;
    }

    /**
     * Adds ordered product using item ID
     *
     * @param string $itemId Ordered product item ID
     */
    public function addProductItemId($itemId)
    {
        $item = new CartItem();
        $item->itemId = $itemId;
        $this->cart[] = $item;
    }

    /**
     * Adds ordered product using array which can contains
     * \p productName ,
     * \p itemId ,
     * \p unitPrice ,
     * \p quantity
     *
     * @param array $cartItem Array of various CartItem attributes
     */
    public function addCartItem($cartItem)
    {
        $item = new CartItem();
        if (array_key_exists("productName", $cartItem)) {
            $item->productName = $cartItem["productName"];
        }
        if (array_key_exists("itemId", $cartItem)) {
            $item->itemId = $cartItem["itemId"];
        }
        if (array_key_exists("unitPrice", $cartItem)) {
            $item->unitPrice = $cartItem["unitPrice"];
        }
        if (array_key_exists("quantity", $cartItem)) {
            $item->quantity = $cartItem["quantity"];
        }

        $this->cart[] = $item;
    }

    /**
     * Sets order attributes within
     * \p email ,
     * \p deliveryType ,
     * \p deliveryPrice ,
     * \p orderId ,
     * \p otherCosts ,
     * \p paymentType ,
     *
     * @param array $orderAttributes Array of various order attributes
     */
    public function setOrder($orderAttributes)
    {
        if (array_key_exists("email", $orderAttributes) && $orderAttributes["email"]) {
            $this->email = $orderAttributes["email"];
        }
        $this->deliveryType = $orderAttributes["deliveryType"];
        $this->deliveryPrice = $orderAttributes["deliveryPrice"];
        $this->orderId = $orderAttributes["orderId"];
        $this->otherCosts = $orderAttributes["otherCosts"];
        $this->paymentType = $orderAttributes["paymentType"];
    }


    /**
     * Creates HTTP request and returns response body
     *
     * @param string $url URL
     * @return boolean true if everything is perfect else throws exception
     * @throws ZboziException can be thrown if connection to Zbozi.cz
     * server cannot be established.
     */
    protected function sendRequest($url)
    {
        $encoded_json = json_encode(get_object_vars($this));

        if (extension_loaded('curl')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3 /* seconds */);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $response = curl_exec($ch);

            if ($response === false) {
                throw new ZboziException('Unable to establish connection to Zbozi service: ' . curl_error($ch));
            }
        } else {
            // use key 'http' even if you send the request to https://...
            $options = array(
                'http' => array(
                    'header' => "Content-type: application/json",
                    'method' => 'POST',
                    'content' => $encoded_json,
                ),
            );
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                throw new ZboziException('Unable to establish connection to Zbozi service');
            }
        }

        $decoded_response = json_decode($response, true);

        if ((int)($decoded_response["status"] / 100) === 2) {
            return true;
        } else {
            throw new ZboziException('Request was not accepted: ' . $decoded_response['statusMessage']);
        }
    }

    /**
     * Returns endpoint URL
     *
     * @return string URL where the request will be called
     */
    private function getUrl()
    {
        $url = $this::BASE_URL;
        $url = str_replace("%%SHOP_ID%%", $this->SHOP_ID, $url);

        if (self::ZBOZI_SANDBOX) {
            $url = str_replace("%%DOMAIN%%", "sandbox.zbozi.cz", $url);
        } else {
            $url = str_replace("%%DOMAIN%%", "www.zbozi.cz", $url);
        }

        return $url;
    }

    /**
     * Sends request to Zbozi service and checks for valid response
     *
     * @return boolean true if everything is perfect else throws exception
     * @throws ZboziException can be thrown if connection to Zbozi.cz
     * server cannot be established or mandatory values are missing.
     */
    public function send()
    {
        $url = $this->getUrl();

        try {
            $status = $this->sendRequest($url);
            return $status;
        } catch (Exception $e) {
            throw new ZboziException($e->getMessage());
        }
    }

    /**
     * Send data from backend to Zbozi
     *
     * @param $order
     * @param $shopID
     * @return bool
     */
    public static function sendZbozi($order, $shopID)
    {
        $ZboziClass = new ZboziClass($shopID);

        $active = $ZboziClass->isActive();
        $advanced = $ZboziClass->isAdvancedActive();
        $id = $ZboziClass->getShopId();
        $secret = $ZboziClass->getKey();
        $withVat = $ZboziClass->getVatIncluded();

        // Default is with vat
        if ($withVat === false) {
            $withVat = true;
        }

        if ($active) {
            if ($advanced) {
                // Extended process
                try {
                    $zbozi = new Zbozi($id, $secret);

                    foreach ($order['order']->getProducts() as $product) {
                        $pid = $product['product_id'];
                        if ($product['product_attribute_id'] != SettingsClass::DISABLED) {
                            $pid .= '-' . $product['product_attribute_id'];
                        }

                        // If in settings set with TAX or without
                        if ($withVat) {
                            $unitPrice = $product['unit_price_tax_incl'];
                        } else {
                            $unitPrice = $product['unit_price_tax_excl'];
                        }

                        $zbozi->addCartItem(array(
                            'itemId' => $pid,
                            'productName' => $product['product_name'],
                            'unitPrice' => $unitPrice,
                            'quantity' => $product['product_quantity'],
                        ));
                    }

                    // If in settings set with TAX or without
                    if ($withVat) {
                        $deliveryPrice = $order['order']->total_shipping_tax_incl;
                        $other = $order['order']->total_discounts_tax_incl;
                    } else {
                        $deliveryPrice = $order['order']->total_shipping_tax_excl;
                        $other = $order['order']->total_discounts_tax_excl;
                    }

                    $carrier = new Carrier($order['order']->id_carrier);

                    if ($other && $other > 0) {
                        $other = $other * -1;
                    }

                    $zbozi->setOrder(array(
                        'orderId' => $order['order']->id,
                        'email' => $order['customer']->email,
                        'deliveryType' => DeliveryType::getDeliveryType($carrier->name),
                        'deliveryPrice' => (string) $deliveryPrice,
                        'deliveryDate' => $order['order']->delivery_date,
                        'paymentType' => $order['order']->payment,
                        'otherCosts' => $other
                    ));

                    $zbozi->send();

                    return true;
                } catch (ZboziException $e) {
                    echo 'Error: ' . $e->getMessage();
                } catch (Exception $e) {
                    echo 'Error: ' . $e->getMessage();
                }
            }
        }

        return false;
    }
};

/**
 * Thrown when an service returns an exception
 */
class ZboziException extends Exception
{
};
