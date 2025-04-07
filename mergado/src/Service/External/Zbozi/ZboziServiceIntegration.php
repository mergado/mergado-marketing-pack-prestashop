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
 * @author    www.mergado.cz
 * @copyright 2016 Mergado technologies, s. r. o.
 * @license   LICENSE.txt
 */

namespace Mergado\Service\External\Zbozi;

use Mergado;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\CookieService;
use Mergado\Service\SettingsService;
use Carrier;
use Link;
use Media;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class ZboziServiceIntegration extends AbstractBaseService
{
    /**
     * @var ZboziService
     */
    private $zboziService;

    /**
     * @var CookieService
     */
    private $cookieService;

    public const TEMPLATES_PATH = 'views/templates/services/Zbozi/';
    public const JS_PATH = 'views/js/services/Sklik/';

    protected function __construct()
    {
        $this->zboziService = ZboziService::getInstance();
        $this->cookieService = CookieService::getInstance();

        parent::__construct();
    }

    public function backendConversion($order, $context): bool
    {
        try {
            if ($context->cookie->__get(ZboziService::CONSENT_NAME) === '1') {
                return false;
            }

            if (!$this->zboziService->isActive() || !$this->zboziService->isAdvancedActive()) {
                return false;
            }

            $id = $this->zboziService->getShopId();
            $secret = $this->zboziService->getKey();
            $withVat = $this->zboziService->getConversionVatIncluded();

            // Extended process
            $zbozi = new Zbozi($id, $secret);

            foreach ($order['order']->getProducts() as $product) {
                $pid = $product['product_id'];
                if ((int)$product['product_attribute_id'] !== SettingsService::DISABLED) {
                    $pid .= '-' . $product['product_attribute_id'];
                }

                // If in settings set with TAX or without
                if ($withVat) {
                    $unitPrice = $product['unit_price_tax_incl'];
                } else {
                    $unitPrice = $product['unit_price_tax_excl'];
                }

                $zbozi->addCartItem([
                    'itemId' => $pid,
                    'productName' => $product['product_name'],
                    'unitPrice' => $unitPrice,
                    'quantity' => $product['product_quantity'],
                ]);
            }

            // If in settings set with TAX or without
            if ($withVat) {
                $deliveryPrice = $order['order']->total_shipping_tax_incl;
                $other = $order['order']->total_discounts_tax_incl;
            } else {
                $deliveryPrice = $order['order']->total_shipping_tax_excl;
                $other = $order['order']->total_discounts_tax_excl;
            }

            $carrier = new Carrier($order['order']->id_carrier);

            if ($other && $other > 0) {
                $other = $other * -1;
            }

            $zbozi->setOrder([
                'orderId' => $order['order']->id,
                'email' => $order['customer']->email,
                'deliveryType' => DeliveryType::getDeliveryType($carrier->name),
                'deliveryPrice' => (string)$deliveryPrice,
                'deliveryDate' => $order['order']->delivery_date,
                'paymentType' => $order['order']->payment,
                'otherCosts' => $other
            ]);

            $zbozi->send();

            $context->cookie->__set(ZboziService::CONSENT_NAME, '0');

            return true;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        $context->cookie->__set(ZboziService::CONSENT_NAME, '0');

        return false;
    }

    public function addCheckboxVerifyForPs17($context, $path): void
    {
        try {
            if (PrestashopVersionHelper::is16AndLower()) {
                return;
            }

            if (!$this->zboziService->isActive()) {
                return;
            }

            $lang = Mergado\Helper\LanguageHelper::getLang();

            $defaultText = $this->zboziService->getOptOut('en_US');
            $checkboxText = $this->zboziService->getOptOut($lang);

            if (!$checkboxText || ($checkboxText === '') || ($checkboxText === 0)) {
                $checkboxText = $defaultText;
            }

            if (!$checkboxText || ($checkboxText === '') || ($checkboxText === 0)) {
                $checkboxText = ZboziService::DEFAULT_OPT;
            }

            $link = new Link;
            $parameters = array("action" => "setZboziOpc");
            $ajax_link = $link->getModuleLink('mergado', 'ajax', $parameters);

            Media::addJsDef(array(
                "mmp_zbozi" => array(
                    "ajaxLink" => $ajax_link,
                    "optText" => $checkboxText,
                    "checkboxChecked" => $context->cookie->__get(ZboziService::CONSENT_NAME),
                )
            ));

            // Create a link with ajax path
            $context->controller->addJS($path . self::JS_PATH . 'order17.js');
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function addCheckboxVerifyForPs16(Mergado $module, $smarty, $context, $path): string
    {
        try {
            if (PrestashopVersionHelper::is17AndHigher()) {
                return '';
            }

            if (!$this->zboziService->isActive()) {
                return '';
            }

            $lang = Mergado\Helper\LanguageHelper::getLang();

            $textInLanguage = $this->zboziService->getOptOut($lang);

            if (!$textInLanguage || ($textInLanguage === '') || ($textInLanguage === 0)) {
                $textInLanguage = 'If you check this, we will send the content of your order together with your e-mail address to Zboží.cz.';
            }

            $context->controller->addJS($path . self::JS_PATH . 'orderOPC.js');

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'checkboxVerifyPs16.tpl',
                $smarty,
                [
                    'zbozi_consentText' => $textInLanguage,
                    'zbozi_checkboxChecked' => $context->cookie->__get(ZboziService::CONSENT_NAME),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function frontendConversion($orderId, $module, $smarty, $languageCode): string
    {

        try {
            if (!$this->zboziService->isActive()) {
                return '';
            }

            $advancedActive = $this->zboziService->isAdvancedActive();
            $shopId = $this->zboziService->getShopId();

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'conversion.tpl',
                $smarty,
                [
                    'sandboxEnabled' => (int)Zbozi::isSandboxEnabled(),
                    'orderId' => $orderId,
                    'advertisementEnabled' => (int)$this->cookieService->advertismentEnabled(),
                    'shopId' => $shopId,
                    'advancedActive' => $advancedActive,
                    'languageCode' => str_replace('-', '_', $languageCode),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }
}

