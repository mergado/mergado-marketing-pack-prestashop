<?php

namespace Mergado\includes\services\Google\GoogleAnalytics4\objects;

use Exception;
use Mergado\includes\services\Google\GoogleAnalytics4\objects\base\BaseGoogleAnalytics4EventObject;
use Mergado\Tools\LogClass;

class GoogleAnalytics4RefundEventObject extends BaseGoogleAnalytics4EventObject
{
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
                LogClass::log('Mergado log: Transaction Id are required in ' . get_class());
            }
        }

        return $this->result;
    }
}
