<?php

namespace Mergado\includes\services\Google\GoogleAnalytics4;

use MediaCore;
use Mergado;
use Mergado\includes\helpers\ControllerHelper;
use Mergado\includes\helpers\RefererHelper;
use Mergado\includes\services\Google\GoogleAnalytics4\objects\base\BaseGoogleAnalytics4ItemEventObject;
use Mergado\includes\services\Google\GoogleAnalytics4\objects\base\BaseGoogleAnalytics4ItemsEventObject;
use Mergado\includes\services\Google\GoogleAnalytics4\objects\GoogleAnalytics4RefundEventObject;
use Mergado\includes\traits\SingletonTrait;
use Mergado\Tools\LogClass;
use OrderCore;
use ToolsCore;

class GoogleAnalytics4ServiceIntegration
{
    /**
     * @var GoogleAnalytics4Service
     */
    private $googleAnalytics4Service;
    /**
     * @var false|string|null
     */
    private $sendTo;

    use SingletonTrait;

    protected function __construct()
    {
        $this->googleAnalytics4Service = GoogleAnalytics4Service::getInstance();
        $this->sendTo = $this->googleAnalytics4Service->getCode();
    }

    /**
     * Insert gtag events
     *
     * @param $context
     * @param $path
     * @return void
     */
    public function insertDefaultHelpers($context, $path)
    {
        if ($this->googleAnalytics4Service->isActiveEcommerce()) {
            MediaCore::addJsDef(
                ['mergado_GA4_settings' => [
                        'withShipping' => $this->googleAnalytics4Service->getShippingPriceIncluded(),
                        'withVat' => $this->googleAnalytics4Service->getConversionVatIncluded(),
                        'sendTo' => $this->sendTo
                    ]
                ]
            );

            $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/shared/helpers/ga4Helpers.js');
        }
    }

    public function addToCart($context, $path)
    {
        if ($this->googleAnalytics4Service->isActiveEcommerce()) {
            if (_PS_VERSION_ < Mergado::PS_V_17) {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/addToCart.js'); // User added product to cart
            } else {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/addToCart.js'); // User added product to cart
            }
        }
    }

    public function addProductDetailView($context, $path)
    {
        if (ControllerHelper::isProductDetail() && $this->googleAnalytics4Service->isActiveEcommerce()) {
            $refreshed = (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'max-age=0');

            if(isset($_SERVER["HTTP_REFERER"])) {
                if($_SERVER["HTTP_REFERER"] && !$refreshed) {
                    global $smarty;

                    if(_PS_VERSION_ < Mergado::PS_V_17) {
                        $shopUrl = $smarty->tpl_vars['base_dir']->value;
                    } else {
                        $shopUrl = $smarty->tpl_vars['urls']->value['shop_domain_url'];
                    }

                    if(strpos($_SERVER["HTTP_REFERER"], $shopUrl) !== false) {
                        if (_PS_VERSION_ < Mergado::PS_V_17) {
                            $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/selectItem.js'); // User came to product detail from another page
                        } else {
                            $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/selectItem.js'); // User came to product detail from another page
                        }
                    }
                }
            }

            if (_PS_VERSION_ < Mergado::PS_V_17) {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/viewItem.js'); // User see detail page
            } else {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/viewItem.js'); // User see detail page
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/selectContent.js'); // User change variation
            }
        }
    }

    public function viewItemList($context, $path)
    {
        if ((ControllerHelper::isCategory() || ControllerHelper::isIndex()) && $this->googleAnalytics4Service->isActiveEcommerce()) {
            if (_PS_VERSION_ < Mergado::PS_V_17) {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/viewItemList.js'); // User see detail page
            } else {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/viewItemList.js'); // User see detail page
            }
        }
    }

    public function search($context, $path)
    {
        if (ControllerHelper::isSearch() && $this->googleAnalytics4Service->isActiveEcommerce()) {
            if (_PS_VERSION_ < Mergado::PS_V_17) {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/search.js'); // User searching
            } else {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/search.js'); // User searching
            }
        }
    }


    public function viewCart($context, $path)
    {
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            if ((ControllerHelper::isCart() || ControllerHelper::isCheckout() || ControllerHelper::isOnePageCheckout()) && $this->googleAnalytics4Service->isActiveEcommerce()) {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/viewCart.js'); // User searching
            }
        } else {
            if (ControllerHelper::isCart() && $this->googleAnalytics4Service->isActiveEcommerce()) {
                $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/viewCart.js'); // User searching
            }
        }
    }

    public function beginCheckout($context, $path)
    {
        if ($this->googleAnalytics4Service->isActiveEcommerce()) {
            if(!RefererHelper::pageHasBeenRefreshed()) {
                if(RefererHelper::userNotCameFromOutside()) {
                    if ((_PS_VERSION_ < Mergado::PS_V_17)) {
                        if (ControllerHelper::isCheckout() || ControllerHelper::isOnePageCheckout() || ControllerHelper::isCart()) {
                            $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/beginCheckout.js'); // User came to checkout
                        }
                    } else {
                        if (ControllerHelper::isCheckout() && RefererHelper::userCameFromCartPage($context)) { // Coming from cart page
                            $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/beginCheckout.js'); // User came to checkout
                        }
                    }
                }
            }
        }
    }

    public function addPaymentInfo($context, $path)
    {
        if ($this->googleAnalytics4Service->isActiveEcommerce()) {
            if (_PS_VERSION_ < Mergado::PS_V_17) {
                if ((ControllerHelper::isCheckout() || ControllerHelper::isOnePageCheckout())) {
                    $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/addPaymentInfo.js'); // User added payment method
                }
            } else {
                if (ControllerHelper::isCheckout()) {
                    $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/addPaymentInfo.js'); // User added payment method
                }
            }
        }
    }

    public function addShippingInfo($context, $path)
    {
        if ($this->googleAnalytics4Service->isActiveEcommerce()) {
            if (_PS_VERSION_ < Mergado::PS_V_17) {
                if (ControllerHelper::isCheckout() || ControllerHelper::isOnePageCheckout()) {
                    $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/addShippingInfo.js'); // User added shipping method
                }
            } else {
                if (ControllerHelper::isCheckout()) {
                    $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/addShippingInfo.js'); // User added shipping method
                }
            }
        }
    }

    public function removeFromCart($context, $path)
    {
        if ($this->googleAnalytics4Service->isActiveEcommerce()) {
            if (_PS_VERSION_ < Mergado::PS_V_17) {
                if (ControllerHelper::isCart() || ControllerHelper::isCheckout() || ControllerHelper::isOnePageCheckout()) {
                    $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/16/removeFromCart.js'); // User added shipping method
                }
            } else {
                if (ControllerHelper::isCart()) {
                    $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/removeFromCart.js'); // User added shipping method
                }
            }
        }
    }

    public function cartEvents($context, $path)
    {
        if ($this->googleAnalytics4Service->isActiveEcommerce()) {
            if (_PS_VERSION_ < Mergado::PS_V_17) {
                // TODO
            } else {
                if (ControllerHelper::isCart()) {
                    $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/17/cartEvents.js'); // User added shipping method
                }
            }
        }
    }

    public function purchase($context, $path)
    {
        if (ControllerHelper::isOrderConfirmation() && $this->googleAnalytics4Service->isActiveEcommerce()) {
            $context->controller->addJS($path . 'includes/services/Google/GoogleAnalytics4/templates/shared/purchase.js'); // User added shipping method
        }
    }

    public function sendRefundOrderPartial($context, $products, $orderId, $orderStateId, $cancelQuantities)
    {
        if ($this->googleAnalytics4Service->isRefundActive() && $this->googleAnalytics4Service->isRefundStatusActive($orderStateId)) {
            $order = new OrderCore($orderId);

            // Check if order has full refund status already ... and don't send it again
            $orderHistory = $order->getHistory($context->language->id);
            $hasRefundedStatus = false;

            foreach($orderHistory as $history) {
                if ($this->googleAnalytics4Service->isRefundStatusActive($history['id_order_state'])) {
                    $hasRefundedStatus = true;
                }
            }

            if (!$hasRefundedStatus) {
                $eventObjectItems = new BaseGoogleAnalytics4ItemsEventObject();

                foreach($products as $id) {
                    $eventObjectItem = new BaseGoogleAnalytics4ItemEventObject();
                    $productId = Mergado\Tools\HelperClass::getProductId($order->getProducts()[$id]);

                    $eventObjectItem
                        ->setItemId($productId)
                        ->setQuantity(ToolsCore::getValue('cancelQuantity')[$id]);

                    $eventObjectItems->addItem($eventObjectItem);
                }

                $refundObject = new GoogleAnalytics4RefundEventObject();
                $refundObject
                    ->setTransactionId($order->id)
                    ->setItems($eventObjectItems)
                    ->setSendTo($this->sendTo);

                $this->sendRefundCode($refundObject, $order->id);
            }
        }
    }

    public function sendRefundOrderFull($context, $orderId, $orderStateId)
    {
        if ($this->googleAnalytics4Service->isRefundActive() && $this->googleAnalytics4Service->isRefundStatusActive($orderStateId)) {
            $order = new OrderCore($orderId);

            // Check if order has full refund status already ... and don't send it again
            $orderHistory = $order->getHistory($context->language->id);
            $hasRefundedStatus = false;

            foreach($orderHistory as $history) {
                if ($this->googleAnalytics4Service->isRefundStatusActive($history['id_order_state'])) {
                    $hasRefundedStatus = true;
                }
            }

            if (!$hasRefundedStatus) {
                $refundObject = new GoogleAnalytics4RefundEventObject();
                $refundObject
                    ->setTransactionId($order->id)
                    ->setSendTo($this->sendTo);

                $this->sendRefundCode($refundObject, $order->id);
            }
        }
    }

    public function sendRefundCode($refundObject, $orderId)
    {
        $ch = curl_init();
        $url = GoogleAnalytics4Service::REFUND_URL;

        if (MERGADO_DEBUG) {
            $url = GoogleAnalytics4Service::REFUND_DEBUG_URL;
        }

        $url = http_build_url($url, ['measurement_id' => $this->sendTo, 'api_secret' => $this->googleAnalytics4Service->getRefundApiSecret()]);

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
            LogClass::log('Mergado log [GA4]: Refund successful - order ' . $orderId . ' - Request info: ' . json_encode($info));
            return true;
        } else {
            LogClass::log('Mergado log [GA4]: Error refund - order ' . $orderId . ' - Error: ' . $error);
            return true;
        }
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
