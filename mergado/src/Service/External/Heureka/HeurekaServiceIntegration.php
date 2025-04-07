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


namespace Mergado\Service\External\Heureka;

use Configuration;
use Media;
use Link;
use Cart;
use Exception;
use Language;
use Mergado;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\CookieService;
use Mergado\Service\External\HeurekaGroup\AbstractHeurekaGroupServiceIntegration;
use Mergado\Service\LogService;
use Mergado\Service\SettingsService;
use Mergado\Traits\SingletonTrait;
use Mergado\Utility\SmartyTemplateLoader;
use PrestaShopException;
use Mergado\Helper\LanguageHelper;
use Throwable;
use Tools;

class HeurekaServiceIntegration extends AbstractHeurekaGroupServiceIntegration
{
    use SingletonTrait;

    public const SERVICE_NAME = 'heureka';
    public const CONSENT_NAME = 'mergado_heureka_consent';

    /**
     * @var HeurekaCZService
     */
    private $heurekaCzService;

    /**
     * @var HeurekaSKService
     */
    private $heurekaSkService;

    /**
     * @var CookieService
     */
    private $cookieService;

    /**
     * @var LogService
     */
    protected $logger;

    public const TEMPLATES_PATH = 'views/templates/services/Heureka/';
    public const JS_PATH = 'views/js/services/Heureka/';

    public function __construct()
    {
        $this->heurekaCzService = HeurekaCZService::getInstance();
        $this->heurekaSkService = HeurekaSKService::getInstance();
        $this->cookieService = CookieService::getInstance();
        $this->logger = LogService::getInstance();

        parent::__construct($this->getActiveService());
    }

    public function conversionLegacy($orderCartId, $module, $smarty): string
    {
        try {
            if (!$this->cookieService->advertismentEnabled()) {
                return '';
            }

            $service = $this->getActiveService();

            if (!$service || !$service->isLegacyConversionsActive()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'conversion.tpl',
                $smarty,
                [
                    'heurekaConversionOrderId' => $orderCartId,
                    'heurekaCode' => $service->getLegacyConversionsCode(),
                    'heurekaUrl' => $service::HEUREKA_CONVERSION_URL,
                    'heurekaProducts' => $this->getOrderConfirmationProducts($orderCartId)
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function submitVerify($context, $order): void
    {
        try {
            $lang = LanguageHelper::getLang();
            $service = $this->getActiveService();

            if (!$service || !$this->isVerifyConsentActive($context) || !$service->isVerifiedActive()) {
                return;
            }

            try {
                $cart = new Cart($order['cart']->id, Language::getIdByIso($lang));

                $url = $this->getRequestURL($service::HEUREKA_URL, $service->getVerifiedCode(), $cart, $order, $service->getVerifiedWithItems());

                $this->logger->info("Heureka verify Order ID: " . $order['cart']->id . " - url - " . $url);

                $this->sendRequest($url);
            } catch (PrestaShopException $e) {
                $this->logger->error("Heureka verify failed", ['exception' => $e]);
            }

            $this->setConversionConsentDisabled($context);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    private function sendRequest(string $url): void
    {
        try {
            $parsed = parse_url($url);
            $fp = fsockopen($parsed['host'], 80, $errno, $errstr, 5);

            if (!$fp) {
                $this->logger->error("Heureka verify failed - " . json_encode(['errNo' => $errno, 'errStr' => $errstr]));
                throw new Exception($errstr . ' (' . $errno . ')');
            } else {
                $return = '';
                $out = 'GET ' . $parsed['path'] . '?' . $parsed['query'] . " HTTP/1.1\r\n" .
                    'Host: ' . $parsed['host'] . "\r\n" .
                    "Connection: Close\r\n\r\n";
                fputs($fp, $out);
                while (!feof($fp)) {
                    $return .= fgets($fp, 128);
                }
                fclose($fp);
                $returnParsed = explode("\r\n\r\n", $return);

                $this->logger->info("Heureka verify submit RETURNED: " . json_encode(['return' => $returnParsed]));
                return;
            }
        } catch (Exception $e) {
            $this->logger->error("Heureka verify submit failed", ['exception' => $e]);
        }
    }

    private function getRequestURL($url, $apiKey, $cart, $order, $sendWithItems): string
    {
        $url .= '?id=' . $apiKey;
        $url .= '&email=' . urlencode($order['customer']->email);

        if ($sendWithItems) {
            $products = $cart->getProducts();

            foreach ($products as $product) {
                $exactName = $product['name'];

                if (array_key_exists('attributes_small', $product) && $product['attributes_small'] != '') {
                    $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                    $exactName .= ': ' . implode(' ', $tmpName);
                }

                $url .= '&produkt[]=' . urlencode($exactName);
                if ((int)$product['id_product_attribute'] === SettingsService::DISABLED) {
                    $url .= '&itemId[]=' . urlencode($product['id_product']);
                } else {
                    $url .= '&itemId[]=' . urlencode($product['id_product'] . '-' . $product['id_product_attribute']);
                }
            }
        }

        if (isset($order['order']->id)) {
            $url .= '&orderid=' . urlencode($order['order']->id);
        }

        return $url;
    }

    public function renderWidget($module, $smarty): string
    {
        try {
            $service = $this->getActiveService();

            if (!$service || !$service->isWidgetActive()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'widget.tpl',
                $smarty,
                [
                    'widgetId' => $service->getWidgetId(),
                    'marginTop' => $service->getWidgetTopMargin(),
                    'position' => $service->getWidgetPosition(),
                    'langIso' => strtolower(LanguageHelper::getLang()),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function addVerifiedCheckboxForPs16($module, $smarty, $context, $path): string
    {
        try {
            $service = $this->getActiveService();

            if (PrestashopVersionHelper::is16AndLower()) {
                if ($service && $service->isVerifiedActive()) {
                    $textInLanguage = $service->getOptOutText();

                    $context->controller->addJS($path . self::JS_PATH . 'orderOPC.js');

                    return SmartyTemplateLoader::render(
                        $module,
                        self::TEMPLATES_PATH . 'heureka.tpl',
                        $smarty,
                        [
                            'heureka_consentText' => $textInLanguage,
                            'heureka_checkboxChecked' => $context->cookie->__get(self::CONSENT_NAME),
                        ]
                    );
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function addVerifiedCheckboxForPs17($context, $path): void
    {
        try {
            $service = $this->getActiveService();

            if (PrestashopVersionHelper::is17AndHigher()) {
                if ($service && $service->isVerifiedActive()) {
                    $textInLanguage = $service->getOptOutText();

                    $link = new Link;
                    $parameters = array("action" => "setHeurekaOpc");
                    $ajax_link = $link->getModuleLink('mergado', 'ajax', $parameters);

                    Media::addJsDef(array(
                        "mmp_heureka" => array(
                            "ajaxLink" => $ajax_link,
                            "optText" => $textInLanguage,
                            "checkboxChecked" => $context->cookie->__get(self::CONSENT_NAME),
                        )
                    ));

                    // Create a link with ajax path
                    $context->controller->addJS($path . self::JS_PATH . 'order17.js');
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * HELPERS
     */

    private function getActiveService()
    {
        $lang = LanguageHelper::getLang();

        $service = false;

        if (in_array($lang, ['CZ', 'SK'])) {
            if ($lang === 'CZ') {
                $service = $this->heurekaCzService;
            } else if ($lang === 'SK') {
                $service = $this->heurekaSkService;
            }
        }

        return $service;
    }

    private function getActiveCart($orderCartId): Cart
    {
        $lang = LanguageHelper::getLang();

        $cart = new Cart($orderCartId);

        if (in_array($lang, ['CZ', 'SK'])) {
            if ($lang === 'CZ') {
                $cart = new Cart($orderCartId, Language::getIdByIso(Mergado::LANG_CS));
            } else if ($lang === 'SK') {
                $cart = new Cart($orderCartId, Language::getIdByIso(Mergado::LANG_SK));
            }
        }

        return $cart;
    }

    private function isVerifyConsentActive($context): bool
    {
        return $context->cookie->__get(self::CONSENT_NAME) != '1';
    }

    private function setConversionConsentDisabled($context): void
    {
        $context->cookie->__set(self::CONSENT_NAME, '0');
    }

    private function getOrderConfirmationProducts($orderCartId): array
    {
        $service = $this->getActiveService();
        $cart = $this->getActiveCart($orderCartId);
        $products = $cart->getProducts();

        $query = [];

        foreach ($products as $product) {
            $exactName = $product['name'];

            if (array_key_exists('attributes_small', $product) && $product['attributes_small'] !== '') {
                $tmpName = array_reverse(explode(', ', $product['attributes_small']));
                $exactName .= ': ' . implode(' ', $tmpName);
            }

            $id = '';

            if ((int)$product['id_product_attribute'] === SettingsService::DISABLED) {
                $id .= urlencode($product['id_product']);
            } else {
                $id .= urlencode($product['id_product'] . '-' . $product['id_product_attribute']);
            }

            $item = array(
                'id' => $id,
                'name' => $exactName,
                'qty' => $product['quantity'],
            );

            if ($service->getLegacyConversionsVatIncluded()) {
                $item['unitPrice'] = Tools::ps_round($product['price_wt'], Configuration::get('PS_PRICE_DISPLAY_PRECISION'));
            } else {
                $item['unitPrice'] = Tools::ps_round($product['price'], Configuration::get('PS_PRICE_DISPLAY_PRECISION'));
            }

            $query[] = $item;
        }


        return $query;
    }
}
