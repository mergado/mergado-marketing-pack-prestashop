<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */


class AdminOrdersController extends AdminOrdersControllerCore
{
    public function postProcess()
    {
        // Disabled for now. Partial refunds not working.
//        if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0) {
//            $order = new Order(Tools::getValue('id_order'));
//            if (!Validate::isLoadedObject($order)) {
//                $this->errors[] = $this->trans('The order cannot be found within your database.', array(), 'Admin.Orderscustomers.Notification');
//            }
//            ShopUrl::cacheMainDomainForShop((int)$order->id_shop);
//        }
//        if (Tools::isSubmit('partialRefund') && isset($order)) {
//            if (Tools::isSubmit('partialRefundProduct') && ($refunds = Tools::getValue('partialRefundProduct')) && is_array($refunds)) {
//
//                $GaRefundClass = new \Mergado\Google\GaRefundClass();
//                if ($GaRefundClass->isActive(Mergado::getShopId())) {
//                    $products = array();
//                    foreach ($refunds as $id => $quantity) {
//                        $quantity = Tools::getValue('partialRefundProductQuantity');
//                        if ($quantity[$id]) {
//
//                            $productId = Mergado\Tools\HelperClass::getProductId($order->getProducts()[$id]);
//
//                            $products[] = array(
//                                'id' => $productId,
//                                'quantity' => (int)$quantity[$id],
//                            );
//                        }
//                    }
//                    $orderId = Tools::getValue('id_order');
//                    $GaRefundClass = new \Mergado\Google\GaRefundClass();
//                    $GaRefundClass->sendRefundCode($products, $orderId, Mergado::getShopId(), true);
//                }
//            }
//        }
//
//        $this->errors = [];
        parent::postProcess();
    }
}
