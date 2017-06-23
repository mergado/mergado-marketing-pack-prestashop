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

/**
 * Provides access to ZboziKonverze service.
 *
 * Example of usage:
 *
 * \code
 * try {
 *     // Initialize
 *     $zbozi = new ZboziKonverze(1234567890, "fedcba9876543210123456789abcdef");
 *
 *     // Set order details
 *     $zbozi->setOrder(array(
 *         "deliveryType" => "Česká pošta (do ruky)",
 *         "deliveryDate" => "2016-02-29",
 *         "deliveryPrice" => 80,
 *         "email" => "email@example.com",
 *         "orderId" => "2016-007896",
 *         "otherCosts" => 20,
 *         "paymentType" => "dobírka",
 *         "totalPrice" => 7500.50  //1×5000.50 + 4×600 + 80 + 20
 *     ));
 *
 *     // Add bought items
 *     $zbozi->addCartItem(array(
 *         "itemId" => "1357902468",
 *         "productName" => "Samsung Galaxy S3 (i9300)",
 *         "quantity" => 1,
 *         "unitPrice" => 5000.50,
 *     ));
 *
 *     $zbozi->addCartItem(array(
 *         "itemId" => "2468013579",
 *         "productName" => "BARUM QUARTARIS 165/70 R14 81 T",
 *         "quantity" => 4,
 *         "unitPrice" => 600,
 *     ));
 *
 *     // Finally send request
 *     $zbozi->send();
 *
 * } catch (Exception $e) {
 *     // Error should be handled
 *     print "Error: " . $e->getMessage();
 * }
 * \endcode
 *
 * @author Zbozi.cz <zbozi@firma.seznam.cz>
 */
require_once _PS_MODULE_DIR_.'mergado/classes/MergadoCartItem.php';

class MergadoZboziKonverze
{
    /**
     * Endpoint URL.
     *
     * @var string BASE_URL
     */
    const BASE_URL = 'https://%%DOMAIN%%/action/%%SHOP_ID%%/conversion/backend';

    /**
     * Private identifier of request creator.
     *
     * @var string
     */
    public $PRIVATE_KEY;

    /**
     * Public identifier of request creator.
     *
     * @var string
     */
    public $SHOP_ID;

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
     * How the order will be transfered to the customer.
     *
     * @var string
     */
    public $deliveryType;

    /**
     * Promised day of delivery.
     *
     * @var string
     */
    public $deliveryDate;

    /**
     * Cost of delivery (in CZK).
     *
     * @var float
     */
    public $deliveryPrice;

    /**
     * How the order was paid.
     *
     * @var string
     */
    public $paymentType;

    /**
     * Other fees (in CZK).
     *
     * @var string
     */
    public $otherCosts;

    /**
     * Total price of this order (in CZK).
     *
     * @var float
     */
    public $totalPrice;

    /**
     * Array of CartItem.
     *
     * @var array
     */
    public $cart = array();

    /**
     * Determine URL where the request will be send to.
     *
     * @var bool
     */
    private $sandbox;

    /**
     * Set if sandbox URL will be used.
     *
     * @param bool $val
     */
    public function useSandbox($val)
    {
        $this->sandbox = $val;
    }

    /**
     * Check if string is not empty.
     *
     * @param string $question String to test
     *
     * @return bool
     */
    private static function isNullOrEmptyString($question)
    {
        return !isset($question) || trim($question) === '';
    }

    /**
     * Initialize ZboziKonverze service.
     *
     * @param string $shopId     Shop identifier
     * @param string $privateKey Shop private key
     *
     * @throws Exception can be thrown if \p $privateKey and/or \p $shopId
     *                   is missing or invalid.
     */
    public function __construct($shopId, $privateKey)
    {
        if ($this::isNullOrEmptyString($shopId)) {
            throw new Exception('shopId si mandatory');
        } else {
            $this->SHOP_ID = $shopId;
        }

        if ($this::isNullOrEmptyString($privateKey)) {
            throw new Exception('privateKey si mandatory');
        } else {
            $this->PRIVATE_KEY = $privateKey;
        }

        $this->sandbox = false;
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
        $item = new MergadoCartItem();
        $item->productName = $productName;
        $this->cart[] = $item;
    }

    /**
     * Adds ordered product using item ID.
     *
     * @param string $itemId Ordered product item ID
     */
    public function addProductItemId($itemId)
    {
        $item = new MergadoCartItem();
        $item->itemId = $itemId;
        $this->cart[] = $item;
    }

    /**
     * Adds ordered product using array which can contains
     * \p productName ,
     * \p itemId ,
     * \p unitPrice ,
     * \p quantity.
     *
     * @param array $cartItem Array of various CartItem attributes
     */
    public function addCartItem($cartItem)
    {
        $item = new MergadoCartItem();
        if (array_key_exists('productName', $cartItem)) {
            $item->productName = $cartItem['productName'];
        }
        if (array_key_exists('itemId', $cartItem)) {
            $item->itemId = $cartItem['itemId'];
        }
        if (array_key_exists('unitPrice', $cartItem)) {
            $item->unitPrice = $cartItem['unitPrice'];
        }
        if (array_key_exists('quantity', $cartItem)) {
            $item->quantity = $cartItem['quantity'];
        }

        $this->cart[] = $item;
    }

    /**
     * Adds total price (in CZK).
     *
     * @param float $totalPrice Total price of the order
     */
    public function addTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * Sets order attributes within
     * \p email ,
     * \p deliveryType ,
     * \p deliveryPrice ,
     * \p deliveryDate ,
     * \p orderId ,
     * \p otherCosts ,
     * \p paymentType ,
     * \p totalPrice.
     *
     * @param array $orderAttributes Array of various order attributes
     */
    public function setOrder($orderAttributes)
    {
        $this->email = $orderAttributes['email'];
        $this->deliveryType = $orderAttributes['deliveryType'];
        $this->deliveryPrice = $orderAttributes['deliveryPrice'];
        $this->deliveryDate = $orderAttributes['deliveryDate'];
        $this->orderId = $orderAttributes['orderId'];
        $this->otherCosts = $orderAttributes['otherCosts'];
        $this->paymentType = $orderAttributes['paymentType'];
        $this->totalPrice = $orderAttributes['totalPrice'];
    }

    /**
     * Creates HTTP request and returns response body.
     *
     * @param string $url URL
     *
     * @return bool true if everything is perfect else throws exception
     *
     * @throws Exception can be thrown if connection to Zbozi.cz
     *                   server cannot be established.
     */
    protected function sendRequest($url)
    {
        $encoded_json = Tools::jsonEncode(get_object_vars($this));

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header' => 'Content-type: application/json',
                'method' => 'POST',
                'content' => $encoded_json,
            ),
        );
        $context = stream_context_create($options);
        $response = Tools::file_get_contents($url, false, $context);

        if ($response === false) {
            throw new Exception('Unable to establish connection to ZboziKonverze service');
        }

        $decoded_response = Tools::jsonDecode($response, true);
        if ($decoded_response['status'] == 200) {
            return true;
        } else {
            throw new Exception('Request was not accepted.');
        }
    }

    /**
     * Returns endpoint URL.
     *
     * @return string URL where the request will be called
     */
    private function getUrl()
    {
        $url = $this::BASE_URL;
        $url = str_replace('%%SHOP_ID%%', $this->SHOP_ID, $url);

        if ($this->sandbox) {
            $url = str_replace('%%DOMAIN%%', 'sandbox.zbozi.cz', $url);
        } else {
            $url = str_replace('%%DOMAIN%%', 'www.zbozi.cz', $url);
        }

        return $url;
    }

    /**
     * Sends request to ZboziKonverze service and checks for valid response.
     *
     * @return bool true if everything is perfect else throws exception
     *
     * @throws Exception can be thrown if connection to Zbozi.cz
     *                   server cannot be established or mandatory values are missing.
     */
    public function send()
    {
        $url = $this->getUrl();

        // send request and check for valid response
        try {
            $status = $this->sendRequest($url);

            return $status;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
