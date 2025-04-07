<?php declare(strict_types=1);

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

namespace Mergado\Service\External\Google;

use Mergado;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService;
use Mergado\Service\SettingsService;
use Mergado\Manager\DatabaseManager;
use Throwable;

class GaRefundService extends AbstractBaseService
{
    public const STATUS = 'ga_refund_status';

    public function isStatusActive($statusId): bool
    {
        $active = $this->getStatus($statusId);

        return $active === SettingsService::ENABLED;
    }


    /*******************************************************************************************************************
     * Get field value
     *******************************************************************************************************************/

    public function getStatus($statusId): int
    {
        return (int) DatabaseManager::getSettingsFromCache(self::STATUS . $statusId, 0);
    }

    public function sendRefundCode($products, $orderId, $partial = false): bool
    {
        try {
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
                CURLOPT_URL => $this->createRefundUrl($products, $orderId, $partial),
            ]);

            curl_exec($ch);
            $errorCount = curl_errno($ch);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);

            curl_close($ch);

            if ($info['http_code'] == 200 && $errorCount <= 0) {
                $this->logger->info('Mergado log [GUA]: Refund successful - order ' . $orderId . ' - Request info: ' . json_encode($info));
                return true;
            }

            $this->logger->error('GA refund error for order ' . $orderId . ' - Curl error: ' . $error);
            return true;

        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    private function createRefundUrl($products, $orderId, $partial = false): string
    {
        $googleUniversalAnalyticsService = GoogleUniversalAnalyticsService::getInstance();

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
