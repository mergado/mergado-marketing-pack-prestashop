<?php

namespace Mergado\Includes\Services\Google\GoogleAnalytics\GA4;

namespace Mergado\includes\services\Google\GoogleAnalytics4\objects\base;

class BaseGoogleAnalytics4EventObject
{
    /**
     * @var array
     */
    protected $result = [];

    /**
     * @param string $currency
     * @return BaseGoogleAnalytics4EventObject
     */
    public function setCurrency(string $currency): self
    {
        $this->result['currency'] = $currency;
        return $this;
    }

    /**
     * @param $value
     * @return BaseGoogleAnalytics4EventObject
     */
    public function setValue($value): self
    {
        $this->result['value'] = $value;
        return $this;
    }

    /**
     * @param BaseGoogleAnalytics4ItemsEventObject $items
     * @return BaseGoogleAnalytics4EventObject
     */
    public function setItems(BaseGoogleAnalytics4ItemsEventObject $items): self
    {
        $this->result['items'] = $items->getResult();
        return $this;
    }

    /**
     * @param string $sendTo
     * @return BaseGoogleAnalytics4EventObject
     */
    public function setSendTo(string $sendTo): self
    {
        $this->result['send_to'] = $sendTo;
        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }
}
