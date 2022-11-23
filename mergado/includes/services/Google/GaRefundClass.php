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
use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\LogClass;
use Mergado\Tools\SettingsClass;
use OrderStateCore;


class GaRefundClass
{
    use SingletonTrait;
    const STATUS = 'ga_refund_status';

    protected function __constructor() { }

    public function isStatusActive($statusId, $shopId)
    {
        $active = $this->getStatus($statusId, $shopId);

        if ($active == SettingsClass::ENABLED) {
            return true;
        } else {
            return false;
        }
    }


    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

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

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_URL => $this->createRefundUrl($products, $orderId, $shopId, $partial),
        ]);

        curl_exec($ch);
        $errorCount = curl_errno($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        if ($info['http_code'] == 200 && $errorCount <= 0) {
            LogClass::log('Mergado log [GUA]: Refund successful - order ' . $orderId . ' - Request info: ' . json_encode($info));
            return true;
        } else {
            LogClass::log('Mergado log [GUA]: Error refund - order ' . $orderId . ' - Error: ' . $error);
            return true;
        }
    }

    private function createRefundUrl($products, $orderId, $shopId, $partial = false)
    {
        $googleUniversalAnalyticsService = Mergado\includes\services\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService::getInstance();

        $data = [
            'v' => '1', // Version.
            'tid' => $googleUniversalAnalyticsService->getCode(), // Tracking ID / Property ID.
            'cid' => '35009a79-1a05-49d7-b876-2b884d0f825b', // Anonymous Client ID
            't' => 'event', // Event hit type.
            'ec' => 'Ecommerce', // Event Category. Required.
            'ea' => 'Refund', // Event Action. Required.
            'ni' => '1', // Non-interaction parameter.
            'ti' => $orderId, // Transaction ID,
            'pa' => 'refund',
        ];

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
}
