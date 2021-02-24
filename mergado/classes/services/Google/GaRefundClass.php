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

namespace Mergado\Google;

use http\Exception\BadUrlException;
use Mergado;
use Mergado\Tools\SettingsClass;
use OrderStateCore;

class GaRefundClass
{
    const ACTIVE = 'ga_refund_active';
    const CODE = 'ga_refund_code';
    const STATUS = 'ga_refund_status';

    private $active;
    private $code;

    public function isActive($shopId)
    {
        $active = $this->getActive($shopId);
        $code = $this->getCode($shopId);

        if ($active === SettingsClass::ENABLED && $code && $code !== '') {
            return true;
        } else {
            return false;
        }
    }

    public function isStatusActive($statusId, $shopId)
    {
        $active = $this->getStatus($statusId, $shopId);

        if ($active === SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }


    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    /**
     * @param $shopId
     * @return false|string|null
     */
    public function getActive($shopId)
    {
        if (!is_null($this->active)) {
            return $this->active;
        }

        $this->active = SettingsClass::getSettings(self::ACTIVE, $shopId);

        return $this->active;
    }

    /**
     * @param $shopId
     * @return false|string|null
     */
    public function getCode($shopId)
    {
        if (!is_null($this->code)) {
            return $this->code;
        }

        $code = SettingsClass::getSettings(self::CODE, $shopId);

        if (substr( $code, 0, 3 ) !== "UA-") {
            $this->code = 'UA-' . $code;
        } else {
            $this->code = $code;
        }

        return $this->code;
    }

    /**
     * @param $statusId
     * @param $shopId
     * @return false|string|null
     */
    public function getStatus($statusId, $shopId)
    {
        return SettingsClass::getSettings(self::STATUS . $statusId, $shopId);
    }

    public function sendRefundCode($products, $orderId, $shopId, $partial = false)
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_URL => $this->createRefundUrl($products,$orderId, $shopId, $partial),
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return true;
        } else {
            $decoded_response = json_decode($response, true);

            if ((int)($decoded_response["status"] / 100) === 2) {
                return true;
            }
        }
    }

    private function createRefundUrl($products, $orderId, $shopId, $partial = false)
    {
        $data = array(
            'v' => '1', // Version.
            'tid' => $this->getCode($shopId), // Tracking ID / Property ID.
            'cid' => '35009a79-1a05-49d7-b876-2b884d0f825b', // Anonymous Client ID
            't' => 'event', // Event hit type.
            'ec' => 'Ecommerce', // Event Category. Required.
            'ea' => 'Refund', // Event Action. Required.
            'ni' => '1', // Non-interaction parameter.
            'ti' => $orderId, // Transaction ID,
            'pa' => 'refund',
        );

        if ($partial) {
            $counter = 1;
            foreach($products as $product) {
                $data['pr' . $counter . 'id'] = $product['id'];
                $data['pr' . $counter . 'qt'] = $product['quantity'];
                $counter++;
            }
        }

//        $url = 'https://www.google-analytics.com/debug/collect?';
        $url = 'https://www.google-analytics.com/collect?';
        $url .= http_build_query($data);

        return $url;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields()
    {
        global $cookie;
        $orderStates = new OrderStateCore();
        $states = $orderStates->getOrderStates($cookie->id_lang);

        $fields = array(self::CODE);
        foreach ($states as $state) {
            $fields[] = self::STATUS . $state['id_order_state'];
        }

        return array(
            self::ACTIVE => [
                'fields' => [
                    $fields
                ]
            ],
        );
    }
}
