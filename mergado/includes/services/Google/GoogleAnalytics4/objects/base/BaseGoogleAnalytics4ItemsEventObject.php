<?php

namespace Mergado\includes\services\Google\GoogleAnalytics4\objects\base;

class BaseGoogleAnalytics4ItemsEventObject
{
    public $result = [];

    /**
     * @param BaseGoogleAnalytics4ItemEventObject $item
     * @param string|null $key
     * @return BaseGoogleAnalytics4ItemsEventObject
     */
    public function addItem(BaseGoogleAnalytics4ItemEventObject $item, string $key = null): self
    {
        if ($key) {
            $this->result[$key] = $item->getResult();
        } else {
            $this->result[] = $item->getResult();
        }

        return $this;
    }

    public function getResult() {
        return $this->result;
    }
}
