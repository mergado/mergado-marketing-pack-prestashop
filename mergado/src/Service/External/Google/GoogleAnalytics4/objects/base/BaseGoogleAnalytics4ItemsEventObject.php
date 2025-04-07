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
