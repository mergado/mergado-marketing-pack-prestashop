<?php

namespace Mergado\Tools;

class CookieClass {

    // CookieLaw
    const COOKIE_LAW_ADVERTISEMENT = 'DmCookiesMarketing';
    const COOKIE_LAW_ANALYTICAL = 'DmCookiesAnalytics';
    const COOKIE_LAW_FUNCTIONAL = 'DmCookiesFunctional'; // not yet implemented

    //Cookie form
    const FIELD_COOKIES_ENABLE = 'form-cookie-enable-always';
    const FIELD_ADVERTISEMENT_USER = 'form-cookie-advertisement';
    const FIELD_ANALYTICAL_USER = 'form-cookie-analytical';
    const FIELD_FUNCTIONAL_USER = 'form-cookie-functional';

    // Main settings variables
    private $multistoreShopId;

    private $advertisementName;
    private $analyticalName;
    private $functionalName;

    public function __construct($multistoreShopId)
    {
        $this->multistoreShopId = $multistoreShopId;
    }

    /**
     * Google Analytics (gtag.js)
     *
     * @return bool
     */
	public function analyticalEnabled(): bool {
        if ( $this->isCookieBlockingEnabled() ) {
            if ( self::isCookieActive( self::COOKIE_LAW_ANALYTICAL ) ) {
                return true;
            } else {
                $cookieName = $this->getAnalyticalCustomName();

                if ($cookieName !== '' && $this->isCookieActive($cookieName)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
	}

    /**
     * Glami Pixel, Biano Pixel, etarget, Sklik, Kelkoo, Heureka order confirmation
     *
     * @return bool
     */
	public function advertismentEnabled(): bool {
        if ( $this->isCookieBlockingEnabled() ) {
            if ( self::isCookieActive( self::COOKIE_LAW_ADVERTISEMENT ) ) {
                return true;
            } else {
                $cookieName = $this->getAdvertisementCustomName();

                if ($cookieName !== '' && $this->isCookieActive($cookieName)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
	}

    /**
     * Heureka widget, Google reviews?
     *
     * @return bool
     */
    public function functionalEnabled(): bool {
        if ( $this->isCookieBlockingEnabled() ) {
            if ( self::isCookieActive( self::COOKIE_LAW_FUNCTIONAL ) ) {
                return true;
            } else {
                $cookieName = $this->getFunctionalCustomName();

                if ($cookieName !== '' && $this->isCookieActive($cookieName)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
    }

    // HELPERS
    public function isCookieActive( $cookieName ) {
        if ( isset( $_COOKIE[ $cookieName ] ) && filter_var( $_COOKIE[ $cookieName ], FILTER_VALIDATE_BOOLEAN ) ) {
            return true;
        } else {
            return false;
        }
    }

    // ADMIN FORM VALUES
    public function isCookieBlockingEnabled() {
        $val = SettingsClass::getSettings(self::FIELD_COOKIES_ENABLE, $this->multistoreShopId);

        if ( $val === '1') {
            return true;
        } else {
            return false;
        }
    }

    public function getAdvertisementCustomName() {
        if (!is_null($this->advertisementName)) {
            return $this->advertisementName;
        }

        $this->advertisementName = SettingsClass::getSettings(self::FIELD_ADVERTISEMENT_USER, $this->multistoreShopId);

        if ( trim( $this->advertisementName ) !== '' ) {
            return $this->advertisementName;
        } else {
            return '';
        }
    }

    public function getAnalyticalCustomName() {
        if (!is_null($this->analyticalName)) {
            return $this->analyticalName;
        }

        $this->analyticalName = SettingsClass::getSettings(self::FIELD_ANALYTICAL_USER, $this->multistoreShopId);


        if ( trim( $this->analyticalName ) !== '' ) {
            return $this->analyticalName;
        } else {
            return '';
        }
    }

    public function getFunctionalCustomName() {

        if (!is_null($this->functionalName)) {
            return $this->functionalName;
        }

        $this->functionalName = SettingsClass::getSettings(self::FIELD_FUNCTIONAL_USER, $this->multistoreShopId);

        if ( trim($this->functionalName) !== '' ) {
            return $this->functionalName;
        } else {
            return '';
        }
    }

    /*******************************************************************************************************************
     * Javascript
     ******************************************************************************************************************/

    public function createJsVariables()
    {
        $this->jsAddCustomerVariableNames();
    }

    public function jsAddCustomerVariableNames()
    {
        $analyticalNames = implode('", "',array_filter([self::COOKIE_LAW_ANALYTICAL, $this->getAnalyticalCustomName()]));
        $advertisementNames = implode('", "',array_filter([self::COOKIE_LAW_ADVERTISEMENT, $this->getAdvertisementCustomName()]));
        $functionalNames = implode('", "',array_filter([self::COOKIE_LAW_FUNCTIONAL, $this->getFunctionalCustomName()]));

        ?>
        <script>
          window.mmp.cookies = {
            functions: {},
            sections: {
              functional: {
                onloadStatus: <?php echo (int) $this->functionalEnabled() ?>,
                functions: {},
                names: {}
              },
              analytical: {
                onloadStatus: <?php echo (int) $this->analyticalEnabled() ?>,
                functions: {},
                names: {}
              },
              advertisement: {
                onloadStatus: <?php echo (int) $this->advertismentEnabled() ?>,
                functions: {},
                names: {}
              }
            }
          };

          window.mmp.cookies.sections.functional.names = ["<?php echo $functionalNames ?>"];
          window.mmp.cookies.sections.advertisement.names = ["<?php echo $advertisementNames ?>"];
          window.mmp.cookies.sections.analytical.names = ["<?php echo $analyticalNames ?>"];
        </script>
        <?php
    }
}