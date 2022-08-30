<?php

namespace Mergado\includes\services\Google\GoogleAnalytics4\objects\base;

use Exception;
use Mergado\Tools\LogClass;

class BaseGoogleAnalytics4ItemEventObject
{
    public $result = [];

    /**
     * @param string $itemId
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setItemId(string $itemId): self
    {
        $this->result['item_id'] = $itemId;
        return $this;
    }

    /**
     * @param string $itemName
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setItemName(string $itemName): self
    {
        $this->result['item_name'] = $itemName;
        return $this;
    }

    /**
     * @param string $affiliation
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setAffiliation(string $affiliation): self
    {
        $this->result['affiliation'] = $affiliation;
        return $this;
    }

    /**
     * @param string $coupon
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setCoupon(string $coupon): self
    {
        $this->result['coupon'] = $coupon;
        return $this;
    }

    /**
     * @param string $currency
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setCurrency(string $currency): self
    {
        $this->result['currency'] = $currency;
        return $this;
    }

    /**
     * @param float | int $discount
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setDiscount($discount): self
    {
        $this->result['discount'] = $discount;
        return $this;
    }

    /**
     * @param int $index
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setIndex(int $index): self
    {
        $this->result['index'] = $index;
        return $this;
    }

    /**
     * @param string $itemBrand
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setItemBrand(string $itemBrand): self
    {
        $this->result['item_brand'] = $itemBrand;
        return $this;
    }

    /**
     * @param array | string $items
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setItemCategories($items): self
    {
        if (is_array($items)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($i === 0) {
                    $this->result['item_category'] = $items[$i];
                } else {
                    $this->result['item_category' . $i] = $items[$i];
                }
            }
        } elseif (is_string($items)) {
            $this->result['item_category'] = $items;
        }

        return $this;
    }

    /**
     * @param string $itemListId
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setItemListId(string $itemListId): self
    {
        $this->result['item_list_id'] = $itemListId;
        return $this;
    }

    /**
     * @param string $itemListName
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setItemListName(string $itemListName): self
    {
        $this->result['item_list_name'] = $itemListName;
        return $this;
    }

    /**
     * @param string $itemVariant
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setItemVariant(string $itemVariant): self
    {
        $this->result['item_variant'] = $itemVariant;
        return $this;
    }

    /**
     * @param string $locationId
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setLocationId(string $locationId): self
    {
        $this->result['location_id'] = $locationId;
        return $this;
    }

    /**
     * @param mixed $price
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setPrice($price): self
    {
        $this->result['price'] = $price;
        return $this;
    }

    /**
     * @param int $quantity
     * @return BaseGoogleAnalytics4ItemEventObject
     */
    public function setQuantity(int $quantity): self
    {
        $this->result['quantity'] = $quantity;
        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getResult()
    {
        if (!isset($this->result['item_id']) && !isset($this->result['item_name'])) {
            if(MERGADO_DEBUG) {
                throw new Exception('ItemId or ItemName need to be specified');
            } else {
                LogClass::log('Mergado log: BaseGa4ItemEventObjectItemEventObject needs to have ItemId or ItemName specified.');
            }
        }

        return $this->result;
    }
}
