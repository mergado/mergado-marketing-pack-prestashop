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


namespace Mergado\Service\External\Google\GoogleReviews;

use Address;
use Country;
use DateTime;
use Mergado;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\CookieService;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class GoogleReviewsServiceIntegration extends AbstractBaseService
{
    /**
     * @var GoogleReviewsService
     */
    private $googleReviewsService;

    /**
     * @var CookieService
     */
    private $cookieService;

    public const TEMPLATES_PATH = 'views/templates/services/GoogleReviews/';

    protected function __construct()
    {
        $this->googleReviewsService = GoogleReviewsService::getInstance();
        $this->cookieService = CookieService::getInstance();

        parent::__construct();
    }

    public function addBadge(Mergado $module, $smarty): string
    {
        try {
            if (!$this->googleReviewsService->isBadgeActive()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'badge.tpl',
                $smarty,
                [
                    'googleBadge' => $this->getBadgeSmartyVariables(),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function addOptIn(Mergado $module, $smarty, $context, $params, $products): string
    {
        try {
            if (!$this->googleReviewsService->isOptInActive()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'optIn.tpl',
                $smarty,
                [
                    'googleReviewsOptIn' => $this->getOptInSmartyVariables($params, $products, $context->cart),
                    'googleReviewsFunctionalCookies' => $this->cookieService->functionalEnabled()
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    private function getOptInSmartyVariables($params, $products, $cart): array
    {
        // For ps 1.6 ready
        if (PrestashopVersionHelper::is16AndLower()) {
            $orderId = $params['objOrder']->id;
            $customerEmail = $params['cookie']->email;

            $country = new Country((int)$params['objOrder']->id_address_invoice);

            $countryCode = $country->iso_code;
        } else {
            $address = new Address($cart->id_address_delivery);
            $orderId = $params['order']->id;
            $customerEmail = $params['cookie']->email;

            $countryCode = Country::getIsoById($address->id_country);
        }

        $deliveryDateDaysAfter = $this->googleReviewsService->getOptInDeliveryDate();
        $deliveryDate = new DateTime('now');

        if (is_numeric($deliveryDateDaysAfter)) {
            $deliveryDate = $deliveryDate->modify('+' . $deliveryDateDaysAfter . ' days');
        }

        $gtins = [];

        foreach ($products as $product) {
            $gtin = $this->getProductGtin($product);

            if ($gtin !== '') {
                $gtins[] = ["gtin" => $gtin];
            }

            if ($gtins === []) {
                $gtins = false;
            }
        }

        return [
            'MERCHANT_ID' => $this->googleReviewsService->getMerchantId(),
            'POSITION' => $this->googleReviewsService->getOptInPosition(),
            'LANGUAGE' => $this->googleReviewsService->getLanguage(),
            'ORDER' => [
                'ID' => $orderId,
                'CUSTOMER_EMAIL' => $customerEmail,
                'COUNTRY_CODE' => $countryCode,
                'ESTIMATED_DELIVERY_DATE' => $deliveryDate->format('Y-m-d'),
                'PRODUCTS' => json_encode($gtins),
            ],
        ];
    }

    private function getProductGtin($product): string
    {
        if (trim($product['ean13']) !== '') {
            return (string)$product['ean13'];
        }

        if (trim($product['isbn']) !== '') {
            return (string)$product['isbn'];
        }

        if (trim($product['upc']) !== '') {
            return (string)$product['upc'];
        }

        return '';
    }

    private function getBadgeSmartyVariables(): array
    {
        return [
            'MERCHANT_ID' => $this->googleReviewsService->getMerchantId(),
            'POSITION' => $this->googleReviewsService->getBadgePosition(),
            'IS_INLINE' => $this->googleReviewsService->isPositionInline(),
            'LANGUAGE' => $this->googleReviewsService->getLanguage(),
            'ADVERTISEMENT_ENABLED' => $this->cookieService->advertismentEnabled(),
        ];
    }
}
