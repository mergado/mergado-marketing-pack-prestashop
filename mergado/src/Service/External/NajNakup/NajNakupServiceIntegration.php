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
 *  @license   license.txt
 */


namespace Mergado\Service\External\NajNakup;

use Exception;
use Cart;
use Language;
use Mergado\Service\AbstractBaseService;
use Throwable;

class NajNakupServiceIntegration extends AbstractBaseService
{

    private $products = [];

    /**
     * @var NajNakupService|mixed
     */
    private $najNakupService;

    protected function __construct()
    {
        $this->najNakupService = NajNakupService::getInstance();

        parent::__construct();
    }

    /**
     * Add product to products variable
     */
    private function addProduct($productCode): void
    {
        $this->products[] = $productCode;
    }

    /**
     * Send new order to najnakup.sk
     * @throws Exception
     */
    private function sendNewOrder($shopId, $email, $shopOrderId)
    {
        $url = 'http://www.najnakup.sk/dz_neworder.aspx' . '?w=' . $shopId;
        $url .= '&e=' . urlencode($email);
        $url .= '&i=' . urlencode($shopOrderId);

        foreach ($this->products as $product) {
            $url .= '&p=' . urlencode($product);
        }

        $contents = $this->sendRequest($url, "www.najnakup.sk", "80");

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
     */
    public function sendNajnakupValuation($order, $lang)
    {
        try {
            if ($this->najNakupService->isActive()) {
                $cart = new Cart($order['cart']->id, Language::getIdByIso($lang));
                $products = $cart->getProducts();

                foreach ($products as $product) {
                    $pid = $product['id_product'];
                    if ($product['id_product_attribute'] != '0') {
                        $pid .= '-' . $product['id_product_attribute'];
                    }
                    $this->addProduct($pid);
                }

                return $this->sendNewOrder($this->najNakupService->getShopId(), $order['customer']->email, $order['order']->id);
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return false;
    }
}
