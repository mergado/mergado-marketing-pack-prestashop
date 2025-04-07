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
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Service\Data;

use Address;
use Cart;
use Context;
use Country;
use Customer;
use Mergado\Service\AbstractBaseService;
use Order;

class CustomerDataService extends AbstractBaseService
{
    public $context;

    public function __construct()
    {
        $this->context = Context::getContext();

        parent::__construct();
    }

    public function getCustomerInfoOnOrderPage($orderId): array
    {
        $order = new Order($orderId);
        $address = new Address($order->id_address_invoice, $this->context->language->id);

        $phone = $this->getPhoneFromAddress($address);
        $email = $order->getCustomer()->email;
        $generalAddress = $this->getAddress($address);

        return $this->populateResultArray($email, $phone, $generalAddress);
    }

    public function getCustomerInfo(): array
    {
        $email = $this->getEmail();
        $phone = $this->getPhone();
        $address = $this->getAddress();

        return $this->populateResultArray($email, $phone, $address);
    }

    protected function populateResultArray(?string $email, ?string $phone, ?array $address): array
    {
        $result = [];

        if ($email) {
            $result['email'] = $email;
        }

        if ($phone) {
            $result['phone'] = $phone;
        }

        if ($address && count($address) > 0) {
            $result['address'] = $address;
        }

        return $result;
    }

    protected function getEmail(): string
    {
        // If logged, take logged
        if ($this->context->customer->isLogged()) {
            return $this->context->customer->email;
        }

        // If not logged and has cart, take from cart
        if ($this->hasCart()) {
            $cartId = $this->context->cookie->id_cart;
            $langId = $this->context->language->id;

            $cart = new Cart($cartId, $langId);
            $customer = $this->getCustomerFromCart($cart);

            if ($customer) {
                return $customer->email;
            }
        }

        return '';
    }

    protected function getPhone()
    {
        // If has cart, take from cart
        if ($this->hasCart()) {
            $cartAddress = $this->getCartAddress();

            return $this->getPhoneFromAddress($cartAddress);
        }

        // If logged, take logged
        if ($this->context->customer->isLogged()) {
            $addresses = $this->context->customer->getAddresses($this->context->language->id);

            if ($addresses) {
                $address = reset($addresses);

                return $this->getPhoneFromAddress($address);
            }
        }

        return '';
    }

    protected function getPhoneFromAddress($address)
    {
        if (is_object($address)) {
            $phone = $address->phone_mobile ?: $address->phone;
        } else {
            $phone = $address['phone_mobile'] ?: $address['phone'];
        }

        return $phone;
    }

    protected function getAddress($address = null)
    {
        if ($address) {
            return $this->getAddressDataForAds($address);
        }

        // If has cart, take from cart
        if ($this->hasCart()) {
            $cartAddress = $this->getCartAddress();

            if ($cartAddress) {
                return $this->getAddressDataForAds($cartAddress);
            }
        }

        // If logged, take logged
        if ($this->context->customer->isLogged()) {
            $addresses = $this->context->customer->getAddresses($this->context->language->id);

            if ($addresses) {
                $address = reset($addresses);

                return $this->getAddressDataForAds($address);
            }
        }

        return null;
    }

    protected function getAddressDataForAds($address): array
    {
        $result = [];

        $items = [
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'street' => 'address1',
            'city' => 'city',
            'postal_code' => 'postcode'
        ];

        if (is_object($address)) {
            foreach($items as $key => $item) {
                if (!$this->isNullOrEmpty($address->{$item})) {
                    $result[$key] = $address->{$item};
                }
            }

            if (!$this->isNullOrEmpty($address->country)) {
                $result["country"] =  (new Country($address->id_country))->iso_code;
            }
        } else {
            foreach($items as $key => $item) {
                if (!$this->isNullOrEmpty($address[$item])) {
                    $result[$key] = $address[$item];
                }
            }

            if (!$this->isNullOrEmpty($address['country'])) {
                $result["country"] =  (new Country($address['id_country']))->iso_code;
            }
        }

        return $result;
    }

    protected function getCartAddress(): Address
    {
        $cartId = $this->context->cookie->id_cart;
        $langId = $this->context->language->id;

        $cart = new Cart($cartId, $langId);

        return new Address($cart->id_address_invoice, $langId);
    }

    protected function hasCart() : bool
    {
        return (bool)$this->context->cookie->id_cart;
    }

    protected function getCustomerFromCart($cart)
    {
        if ($cart->id_customer) {
            return new Customer($cart->id_customer);
        }

        return false;
    }

    protected function isNullOrEmpty($value) {
        return $value === null || (is_string($value) && trim($value) === '');
    }
}
