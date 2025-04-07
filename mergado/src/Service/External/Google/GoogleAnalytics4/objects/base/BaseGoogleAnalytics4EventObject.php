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

namespace Mergado\Service\External\Google\GoogleAnalytics4\objects\base;

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
