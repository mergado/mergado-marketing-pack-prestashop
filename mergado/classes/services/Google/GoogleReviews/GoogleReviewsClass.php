<?php

namespace Mergado\Google;

use Address;
use Country;
use DateTime;
use Mergado;
use Mergado\Tools\SettingsClass;

class GoogleReviewsClass
{
    //Both services
    const MERCHANT_ID = 'gr_merchant_id';
    const LANGUAGE = 'gr_language';

    //Opt-in
    const OPT_IN_ACTIVE = 'gr_optin_active';
    const OPT_IN_POSITION = 'gr_optin_position';
    const OPT_IN_LANGUAGE = 'gr_optin_language';
    const OPT_IN_DELIVERY_DATE = 'gr_optin_delivery_date';

    //Badge
    const BADGE_ACTIVE = 'gr_badge_active';
    const BADGE_POSITION = 'gr_badge_position';

    const LANGUAGES = array(
        0 => ['id' => 0, 'name' => 'automatically'],
        1 => ['id' => 1, 'name' => 'af'],
        2 => ['id' => 2, 'name' => 'ar'],
        3 => ['id' => 3, 'name' => 'cs'],
        4 => ['id' => 4, 'name' => 'da'],
        5 => ['id' => 5, 'name' => 'de'],
        6 => ['id' => 6, 'name' => 'en'],
        7 => ['id' => 7, 'name' => 'en-AU'],
        8 => ['id' => 8, 'name' => 'en-GB'],
        9 => ['id' => 9, 'name' => 'en-US'],
        10 => ['id' => 10, 'name' => 'es'],
        11 => ['id' => 11, 'name' => 'es-419'],
        12 => ['id' => 12, 'name' => 'fil'],
        13 => ['id' => 13, 'name' => 'fr'],
        14 => ['id' => 14, 'name' => 'ga'],
        15 => ['id' => 15, 'name' => 'id'],
        16 => ['id' => 16, 'name' => 'it'],
        17 => ['id' => 17, 'name' => 'ja'],
        18 => ['id' => 18, 'name' => 'ms'],
        19 => ['id' => 19, 'name' => 'nl'],
        20 => ['id' => 20, 'name' => 'no'],
        21 => ['id' => 21, 'name' => 'pl'],
        22 => ['id' => 22, 'name' => 'pt-BR'],
        23 => ['id' => 23, 'name' => 'pt-PT'],
        24 => ['id' => 24, 'name' => 'ru'],
        25 => ['id' => 25, 'name' => 'sv'],
        26 => ['id' => 26, 'name' => 'tr'],
        27 => ['id' => 27, 'name' => 'zh-CN'],
        28 => ['id' => 28, 'name' => 'zh-TW']
    );

    private $shopId;
    private $merchantId;
    private $optInActive;
    private $optInPosition;
    private $optInDeliveryDate;
    private $badgeActive;
    private $badgePosition;
    private $language;

    /**
     * GoogleReviews constructor.
     * @param $shopId
     */
    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    /*******************************************************************************************************************
     * Get constant selectboxes .. because of translations
     *******************************************************************************************************************/

    /**
     * @param null $module
     * @return array[]
     */
    public static function OPT_IN_POSITIONS_FOR_SELECT($module = null) {
        if (is_null($module)) {
            return array(
                0 => ['id' => 0, 'name' => 'Center', 'codePosition' => 'CENTER_DIALOG'],
                1 => ['id' => 1, 'name' => 'Bottom right', 'codePosition' => 'BOTTOM_RIGHT_DIALOG'],
                2 => ['id' => 2, 'name' => 'Bottom left', 'codePosition' => 'BOTTOM_LEFT_DIALOG'],
                3 => ['id' => 3, 'name' => 'Top right', 'codePosition' => 'TOP_RIGHT_DIALOG'],
                4 => ['id' => 4, 'name' => 'Top left', 'codePosition' => 'TOP_LEFT_DIALOG'],
                5 => ['id' => 5, 'name' => 'Bottom tray', 'codePosition' => 'BOTTOM_TRAY']
            );
        } else {
            return array(
                0 => ['id' => 0, 'name' => $module->l('Center', 'googlereviewsclass'), 'codePosition' => 'CENTER_DIALOG'],
                1 => ['id' => 1, 'name' => $module->l('Bottom right', 'googlereviewsclass'), 'codePosition' => 'BOTTOM_RIGHT_DIALOG'],
                2 => ['id' => 2, 'name' => $module->l('Bottom left', 'googlereviewsclass'), 'codePosition' => 'BOTTOM_LEFT_DIALOG'],
                3 => ['id' => 3, 'name' => $module->l('Top right', 'googlereviewsclass'), 'codePosition' => 'TOP_RIGHT_DIALOG'],
                4 => ['id' => 4, 'name' => $module->l('Top left', 'googlereviewsclass'), 'codePosition' => 'TOP_LEFT_DIALOG'],
                5 => ['id' => 5, 'name' => $module->l('Bottom tray', 'googlereviewsclass'), 'codePosition' => 'BOTTOM_TRAY']
            );
        }
    }

    /**
     * @param null $module
     * @return array[]
     */
    public static function BADGE_POSITIONS_FOR_SELECT($module = null) {
        if (is_null($module)) {
            return array(
                0 => ['id' => 0, 'name' => 'Bottom right', 'codePosition' => 'BOTTOM_RIGHT'],
                1 => ['id' => 1, 'name' => 'Bottom left', 'codePosition' => 'BOTTOM_LEFT'],
                2 => ['id' => 2, 'name' => 'Inline', 'codePosition' => 'INLINE'],
            );
        } else {
            return array(
                0 => ['id' => 0, 'name' => $module->l('Bottom right', 'googlereviewsclass'), 'codePosition' => 'BOTTOM_RIGHT'],
                1 => ['id' => 1, 'name' => $module->l('Bottom left', 'googlereviewsclass'), 'codePosition' => 'BOTTOM_LEFT'],
                2 => ['id' => 2, 'name' => $module->l('Inline', 'googlereviewsclass'), 'codePosition' => 'INLINE'],
            );
        }
    }

    /******************************************************************************************************************
     * IS
     ******************************************************************************************************************/

    /**
     * @return bool
     */
    public function isOptInActive()
    {
        $active = $this->getOptInActive();
        $merchantId = $this->getMerchantId();

        if ($active === '1' && $merchantId && $merchantId !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isBadgeActive()
    {
        $optInActive = $this->getOptInActive();
        $active = $this->getBadgeActive();
        $merchantId = $this->getMerchantId();

        if ($optInActive == '1' && $active === '1' && $merchantId && $merchantId !== '') {
            return true;
        } else {
            return false;
        }
    }

    public function isPositionInline()
    {
        return $this->getBadgePosition() == self::BADGE_POSITIONS_FOR_SELECT()[2]['id'];
    }


    /******************************************************************************************************************
     * GET SMARTY VARIABLES
     ******************************************************************************************************************/


    /**
     * @param $params
     * @param $products
     * @param $cart
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOptInSmartyVariables($params, $products, $cart)
    {
        // For ps 1.6 ready
        if (_PS_VERSION_ < Mergado::PS_V_17) {
            $orderId = $params['objOrder']->id;
            $customerEmail = $params['cookie']->email;

            $country = new Country((int)$params['objOrder']->id_address_invoice);

            $countryCode = $country->iso_code;
            $deliveryDateDaysAfter = $this->getOptInDeliveryDate();
        } else {
            $address = new Address($cart->id_address_delivery);
            $orderId = $params['order']->id;
            $customerEmail = $params['cookie']->email;

            $countryCode = Country::getIsoById($address->id_country);
            $deliveryDateDaysAfter = $this->getOptInDeliveryDate();
        }

        $deliveryDate = new DateTime('now');

        if (is_numeric($deliveryDateDaysAfter)) {
            $deliveryDate = $deliveryDate->modify( '+' . $deliveryDateDaysAfter . ' days');
        }

        $gtins = [];

        foreach($products as $product) {
            $gtin = $this->getProductGtin($product);

            if ($gtin !== '') {
                $gtins[] = ["gtin" => $gtin];
            }

            if ($gtins == []) {
                $gtins = false;
            }
        }

        return array(
            'MERCHANT_ID' => $this->getMerchantId(),
            'POSITION' => $this->getOptInPosition(),
            'LANGUAGE' => $this->getLanguage(),
            'ORDER' => array(
              'ID' => $orderId,
              'CUSTOMER_EMAIL' => $customerEmail,
              'COUNTRY_CODE' => $countryCode,
              'ESTIMATED_DELIVERY_DATE' => $deliveryDate->format('Y-m-d'),
              'PRODUCTS' => json_encode($gtins),
            ),
        );
    }

    public function getProductGtin($product)
    {
        if (trim($product['ean13']) != '') {
            return $product['ean13'];
        } elseif (trim($product['isbn']) !== '') {
            return $product['isbn'];
        } elseif (trim($product['upc'] !== '')) {
            return $product['upc'];
        } else {
            return '';
        }
    }

    public function getBadgeSmartyVariables()
    {
        $cookieClass = new Mergado\Tools\CookieClass($this->shopId);

        return array(
            'MERCHANT_ID' => $this->getMerchantId(),
            'POSITION' => $this->getBadgePosition(),
            'IS_INLINE' => $this->isPositionInline(),
            'LANGUAGE' => $this->getLanguage(),
            'ADVERTISEMENT_ENABLED' => $cookieClass->advertismentEnabled(),
        );
    }

    /******************************************************************************************************************
     * GET TEMPLATES
     ******************************************************************************************************************/

    public function getOptInTemplatePath()
    {
        return 'classes/services/Google/GoogleReviews/templates/optIn.tpl';
    }

    public function getBadgeTemplatePath()
    {
        return 'classes/services/Google/GoogleReviews/templates/badge.tpl';
    }

    /******************************************************************************************************************
     * GET FIELDS
     ******************************************************************************************************************/

    /**
     * @return false|string|null
     */
    public function getOptInActive()
    {
        if (!is_null($this->optInActive)) {
            return $this->optInActive;
        }

        $this->optInActive = SettingsClass::getSettings(self::OPT_IN_ACTIVE, $this->shopId);

        return $this->optInActive;
    }

    /**
     * @return array
     */
    public function getOptInPosition()
    {
        if (!is_null($this->optInPosition)) {
            return $this->optInPosition;
        }

        $this->optInPosition = self::OPT_IN_POSITIONS_FOR_SELECT()[SettingsClass::getSettings(self::OPT_IN_POSITION, $this->shopId)]['codePosition'];

        return $this->optInPosition;
    }

    /**
     * @return array|false|string
     */
    public function getOptInDeliveryDate()
    {
        if (!is_null($this->optInDeliveryDate)) {
            return $this->optInDeliveryDate;
        }

        $this->optInDeliveryDate = SettingsClass::getSettings(self::OPT_IN_DELIVERY_DATE, $this->shopId);

        return $this->optInDeliveryDate;
    }

    /**
     * @return false|string|null
     */
    public function getBadgeActive()
    {
        if (!is_null($this->badgeActive)) {
            return $this->badgeActive;
        }

        $this->badgeActive = SettingsClass::getSettings(self::BADGE_ACTIVE, $this->shopId);

        return $this->badgeActive;
    }

    /**
     * @return false|string|null
     */
    public function getMerchantId()
    {
        if (!is_null($this->merchantId)) {
            return $this->merchantId;
        }

        $this->merchantId = SettingsClass::getSettings(self::MERCHANT_ID, $this->shopId);

        return $this->merchantId;
    }

    /**
     * @return array
     */
    public function getBadgePosition()
    {
        if (!is_null($this->badgePosition)) {
            return $this->badgePosition;
        }

        $this->badgePosition = self::BADGE_POSITIONS_FOR_SELECT()[SettingsClass::getSettings(self::BADGE_POSITION, $this->shopId)]['codePosition'];

        return $this->badgePosition;
    }

    /**
     * @return false|string|null
     */
    public function getLanguage()
    {
        if (!is_null($this->language)) {
            return $this->language;
        }

        $this->language = self::LANGUAGES[SettingsClass::getSettings(self::LANGUAGE, $this->shopId)]['name'];

        return $this->language;
    }

    /*******************************************************************************************************************
     * TOGGLE FIELDS JSON
     ******************************************************************************************************************/

    public static function getToggleFields()
    {
        return array(
            self::OPT_IN_ACTIVE => array(
                'fields' => array(
                    self::MERCHANT_ID,
                    self::BADGE_ACTIVE,
                    self::OPT_IN_DELIVERY_DATE,
                    self::LANGUAGE,
                    self::OPT_IN_POSITION,
                ),
                'sub-check' => array (
                    self::BADGE_ACTIVE => array(
                        'fields' => array(
                            self::BADGE_POSITION
                        )
                    ),
                )
            ),
        );
    }
}