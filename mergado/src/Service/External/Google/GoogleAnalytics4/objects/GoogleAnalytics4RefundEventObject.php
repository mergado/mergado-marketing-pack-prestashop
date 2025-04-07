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


namespace Mergado\Service\External\Google\GoogleAnalytics4\objects;

use Exception;
use Mergado\Service\External\Google\GoogleAnalytics4\objects\base\BaseGoogleAnalytics4EventObject;
use Mergado\Service\LogService;

class GoogleAnalytics4RefundEventObject extends BaseGoogleAnalytics4EventObject
{
    /**
     * @var LogService
     */
    private $logger;

    public function __construct()
    {
        $this->logger = LogService::getInstance();
    }

    /**
     * @param string $transactionId
     * @return GoogleAnalytics4RefundEventObject
     */
    public function setTransactionId(string $transactionId) : self
    {
        $this->result['transaction_id'] = $transactionId;
        return $this;
    }

    /**
     * @param string $affiliation
     * @return GoogleAnalytics4RefundEventObject
     */
    public function setAffiliation(string $affiliation) : self
    {
        $this->result['affiliation'] = $affiliation;
        return $this;
    }

    /**
     * @param mixed $shipping
     * @return GoogleAnalytics4RefundEventObject
     */
    public function setShipping($shipping) : self
    {
        $this->result['shipping'] = $shipping;
        return $this;
    }

    /**
     * @param mixed $tax
     * @return GoogleAnalytics4RefundEventObject
     */
    public function setTax($tax) : self
    {
        $this->result['tax'] = $tax;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function getResult()
    {
        if (!isset($this->result['transaction_id'])) {
            if(MERGADO_DEBUG) {
                throw new Exception('Transaction Id are required in ' . get_class());
            } else {
                $this->logger->error('GA4 Refund - Transaction Id is required in ' . get_class());
            }
        }

        return $this->result;
    }
}
