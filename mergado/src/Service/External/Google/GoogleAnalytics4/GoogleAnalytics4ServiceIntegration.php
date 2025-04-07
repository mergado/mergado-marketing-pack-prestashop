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


namespace Mergado\Service\External\Google\GoogleAnalytics4;

use Media;
use Mergado;
use Mergado\Helper\ControllerHelper;
use Mergado\Helper\NavigationAccessHelper;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\External\Google\GoogleAnalytics4\objects\base\BaseGoogleAnalytics4ItemEventObject;
use Mergado\Service\External\Google\GoogleAnalytics4\objects\base\BaseGoogleAnalytics4ItemsEventObject;
use Mergado\Service\External\Google\GoogleAnalytics4\objects\GoogleAnalytics4RefundEventObject;
use Order;
use Throwable;
use Tools;

class GoogleAnalytics4ServiceIntegration extends AbstractBaseService
{
    /**
     * @var GoogleAnalytics4Service
     */
    private $googleAnalytics4Service;

    /**
     * @var ControllerHelper
     */
    private $controllerHelper;

    public const TEMPLATES_PATH = 'views/templates/services/GoogleAnalytics4/';
    public const JS_PATH = 'views/js/services/GoogleAnalytics4/';

    protected function __construct()
    {
        $this->googleAnalytics4Service = GoogleAnalytics4Service::getInstance();
        $this->controllerHelper = ControllerHelper::getInstance();

        parent::__construct();
    }

    public function insertDefaultHelpers($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            Media::addJsDef(
                [
                    'mergado_GA4_settings' => [
                        'withShipping' => $this->googleAnalytics4Service->getShippingPriceIncluded(),
                        'withVat' => $this->googleAnalytics4Service->getConversionVatIncluded(),
                        'sendTo' => $this->googleAnalytics4Service->getCode()
                    ]
                ]
            );

            $context->controller->addJS($path . self::JS_PATH . 'shared/helpers/ga4Helpers.js');
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function addToCart($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                $context->controller->addJS($path . self::JS_PATH . '16/addToCart.js'); // User added product to cart
            } else {
                $context->controller->addJS($path . self::JS_PATH . '17/addToCart.js'); // User added product to cart
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function addProductDetailView($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (!$this->controllerHelper->isProductDetail()) {
                return;
            }

            $refreshed = (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'max-age=0');

            if (isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"] && !$refreshed) {
                global $smarty;

                if (PrestashopVersionHelper::is16AndLower()) {
                    $shopUrl = $smarty->tpl_vars['base_dir']->value;
                } else {
                    $shopUrl = $smarty->tpl_vars['urls']->value['shop_domain_url'];
                }

                if (strpos($_SERVER["HTTP_REFERER"], $shopUrl) !== false) {
                    if (PrestashopVersionHelper::is16AndLower()) {
                        $context->controller->addJS($path . self::JS_PATH . '16/selectItem.js'); // User came to product detail from another page
                    } else {
                        $context->controller->addJS($path . self::JS_PATH . '17/selectItem.js'); // User came to product detail from another page
                    }
                }
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                $context->controller->addJS($path . self::JS_PATH . '16/viewItem.js'); // User see detail page
            } else {
                $context->controller->addJS($path . self::JS_PATH . '17/viewItem.js'); // User see detail page
                $context->controller->addJS($path . self::JS_PATH . '17/selectContent.js'); // User change variation
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function viewItemList($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (!$this->controllerHelper->isCategory() && !$this->controllerHelper->isIndex()) {
                return;
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                $context->controller->addJS($path . self::JS_PATH . '16/viewItemList.js'); // User see detail page
            } else {
                $context->controller->addJS($path . self::JS_PATH . '17/viewItemList.js'); // User see detail page
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function search($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (!$this->controllerHelper->isSearch()) {
                return;
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                $context->controller->addJS($path . self::JS_PATH . '16/search.js'); // User searching
            } else {
                $context->controller->addJS($path . self::JS_PATH . '17/search.js'); // User searching
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }


    public function viewCart($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                if (($this->controllerHelper->isCart() || $this->controllerHelper->isCheckout() || $this->controllerHelper->isOnePageCheckout())) {
                    $context->controller->addJS($path . self::JS_PATH . '16/viewCart.js'); // User searching
                }
            } else {
                if ($this->controllerHelper->isCart()) {
                    $context->controller->addJS($path . self::JS_PATH . '17/viewCart.js'); // User searching
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function beginCheckout($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (!NavigationAccessHelper::userRefreshedPage()) {
                if (NavigationAccessHelper::isInternalRedirect()) {
                    if (PrestashopVersionHelper::is16AndLower()) {
                        if ($this->controllerHelper->isCheckout() || $this->controllerHelper->isOnePageCheckout() || $this->controllerHelper->isCart()) {
                            $context->controller->addJS($path . self::JS_PATH . '16/beginCheckout.js'); // User came to checkout
                        }
                    } else {
                        if ($this->controllerHelper->isCheckout() && NavigationAccessHelper::isRedirectFromCartPage($context)) { // Coming from cart page
                            $context->controller->addJS($path . self::JS_PATH . '17/beginCheckout.js'); // User came to checkout
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function addPaymentInfo($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                if (($this->controllerHelper->isCheckout() || $this->controllerHelper->isOnePageCheckout())) {
                    $context->controller->addJS($path . self::JS_PATH . '16/addPaymentInfo.js'); // User added payment method
                }
            } else {
                if ($this->controllerHelper->isCheckout()) {
                    $context->controller->addJS($path . self::JS_PATH . '17/addPaymentInfo.js'); // User added payment method
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function addShippingInfo($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                if ($this->controllerHelper->isCheckout() || $this->controllerHelper->isOnePageCheckout()) {
                    $context->controller->addJS($path . self::JS_PATH . '16/addShippingInfo.js'); // User added shipping method
                }
            } else {
                if ($this->controllerHelper->isCheckout()) {
                    $context->controller->addJS($path . self::JS_PATH . '17/addShippingInfo.js'); // User added shipping method
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function removeFromCart($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                if ($this->controllerHelper->isCart() || $this->controllerHelper->isCheckout() || $this->controllerHelper->isOnePageCheckout()) {
                    $context->controller->addJS($path . self::JS_PATH . '16/removeFromCart.js'); // User added shipping method
                }
            } else {
                if ($this->controllerHelper->isCart()) {
                    $context->controller->addJS($path . self::JS_PATH . '17/removeFromCart.js'); // User added shipping method
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function cartEvents($context, $path): void
    {
        try {
            if (!$this->googleAnalytics4Service->isActiveEcommerce()) {
                return;
            }

            if (PrestashopVersionHelper::is16AndLower()) {
                // TODO
            } else {
                if ($this->controllerHelper->isCart()) {
                    $context->controller->addJS($path . self::JS_PATH . '17/cartEvents.js'); // User added shipping method
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function purchase($context, $path): void
    {
        try {
            if ($this->controllerHelper->isOrderConfirmation() && $this->googleAnalytics4Service->isActiveEcommerce()) {
                $context->controller->addJS($path . self::JS_PATH . 'shared/purchase.js'); // User added shipping method
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function sendRefundOrderPartial($context, $products, $orderId, $orderStateId, $cancelQuantities): void
    {
        try {
            if ($this->googleAnalytics4Service->isRefundActive() && $this->googleAnalytics4Service->isRefundStatusActive($orderStateId)) {
                $order = new Order($orderId);

                // Check if order has full refund status already ... and don't send it again
                $orderHistory = $order->getHistory($context->language->id);
                $hasRefundedStatus = false;

                foreach ($orderHistory as $history) {
                    if ($this->googleAnalytics4Service->isRefundStatusActive($history['id_order_state'])) {
                        $hasRefundedStatus = true;
                    }
                }

                if (!$hasRefundedStatus) {
                    $eventObjectItems = new BaseGoogleAnalytics4ItemsEventObject();

                    foreach ($products as $id) {
                        $eventObjectItem = new BaseGoogleAnalytics4ItemEventObject();
                        $productId = ProductHelper::getProductId($order->getProducts()[$id]);

                        $eventObjectItem
                            ->setItemId($productId)
                            ->setQuantity(Tools::getValue('cancelQuantity')[$id]);

                        $eventObjectItems->addItem($eventObjectItem);
                    }

                    $refundObject = new GoogleAnalytics4RefundEventObject();
                    $refundObject
                        ->setTransactionId((string)$order->id)
                        ->setItems($eventObjectItems)
                        ->setSendTo($this->googleAnalytics4Service->getCode());

                    $this->sendRefundCode($refundObject, $order->id);
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function sendRefundOrderFull($context, $orderId, $orderStateId): void
    {
        try {
            if ($this->googleAnalytics4Service->isRefundActive() && $this->googleAnalytics4Service->isRefundStatusActive($orderStateId)) {
                $order = new Order($orderId);

                // Check if order has full refund status already ... and don't send it again
                $orderHistory = $order->getHistory($context->language->id);
                $hasRefundedStatus = false;

                foreach ($orderHistory as $history) {
                    if ($this->googleAnalytics4Service->isRefundStatusActive($history['id_order_state'])) {
                        $hasRefundedStatus = true;
                    }
                }

                if (!$hasRefundedStatus) {
                    $refundObject = new GoogleAnalytics4RefundEventObject();
                    $refundObject
                        ->setTransactionId((string)$order->id)
                        ->setSendTo($this->googleAnalytics4Service->getCode());

                    $this->sendRefundCode($refundObject, $order->id);
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function sendRefundCode($refundObject, $orderId): bool
    {
        $ch = curl_init();
        $url = GoogleAnalytics4Service::REFUND_URL;

        if (MERGADO_DEBUG) {
            $url = GoogleAnalytics4Service::REFUND_DEBUG_URL;
        }

        $url = http_build_url($url, ['measurement_id' => $this->googleAnalytics4Service->getCode(), 'api_secret' => $this->googleAnalytics4Service->getRefundApiSecret()]);

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $this->buildRefundBody($refundObject),
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_URL => $url
        ));

        curl_exec($ch);
        $errorCount = curl_errno($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        // Normal 204 .. debug endpoint 200
        if ($info['http_code'] == 204 || $info['http_code'] == 200 && $errorCount <= 0) {
            $this->logger->info('Mergado log [GA4]: Refund successful - order ' . $orderId . ' - Request info: ' . json_encode($info));
            return true;
        }

        $this->logger->error('GA4 refund failed for order ' . $orderId . ' - Curl error: ' . $error);
        return true;
    }

    public function buildRefundBody($refundObject)
    {
        return json_encode([
            'events' => [[
                "name" => 'refund',
                "params" => $refundObject->getResult(),
            ]]
        ]);
    }
}
