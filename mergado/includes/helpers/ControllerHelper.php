<?php

namespace Mergado\includes\helpers;

use ToolsCore;

class ControllerHelper
{
    public static function isIndex(): bool
    {
        $controller = ToolsCore::getValue('controller');

        if ($controller) {
            return $controller === 'index';
        }

        return false;
    }

    public static function isCart(): bool
    {
        $controller = ToolsCore::getValue('controller');

        if ($controller) {
            return $controller === 'cart';
        }

        return false;
    }

    public static function isProductDetail(): bool
    {
        $controller = ToolsCore::getValue('controller');

        if ($controller) {
            return $controller === 'product';
        }

        return false;
    }

    public static function isCategory(): bool
    {
        $controller = ToolsCore::getValue('controller');

        if ($controller) {
            return $controller === 'category';
        }

        return false;
    }

    public static function isSearch(): bool
    {
        $controller = ToolsCore::getValue('controller');

        if ($controller) {
            return $controller === 'search';
        }

        return false;
    }

    /**
     * Any checkout step
     */
    public static function isCheckout(): bool
    {
        $controller = ToolsCore::getValue('controller');

        if ($controller) {
            return $controller === 'order';
        }

        return false;
    }

    public static function isOnePageCheckout(): bool
    {
        $controller = ToolsCore::getValue('controller');

        if ($controller) {
            return $controller === 'orderopc';
        }

        return false;
    }

    public static function isOrderConfirmation(): bool
    {
        $controller = ToolsCore::getValue('controller');

        if ($controller) {
            return $controller === 'orderconfirmation';
        }

        return false;
    }
}
