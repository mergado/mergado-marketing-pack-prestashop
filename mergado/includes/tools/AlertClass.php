<?php

use Mergado\Tools\SettingsClass;
use Mergado\Tools\XML\XMLCategoryFeed;
use Mergado\Tools\XML\XMLProductFeed;
use Mergado\Tools\XML\XMLStaticFeed;
use Mergado\Tools\XML\XMLStockFeed;

class AlertClass
{
    const FEED_TO_SECTION = [
        'product' => XMLProductFeed::ALERT_SECTION,
        'category' => XMLCategoryFeed::ALERT_SECTION,
        'stock' => XMLStockFeed::ALERT_SECTION,
        'static' => XMLStaticFeed::ALERT_SECTION
    ];

    //SINGLE ALERT NAMES .. in prestashop function that add blogId
    const ALERT_NAMES = [
        'NO_FEED_UPDATE' => 'feed_not_updated',
        'ERROR_DURING_GENERATION' => 'generation_failed'
    ];

    // DISABLED ALERT
    public function getDisabledName($feedName, $alertName) {
        return 'mmp_alert_disabled_' . $feedName . '_' . $alertName;
    }

    public function isAlertDisabled($feedName, $alertName) {
        $name = $this->getDisabledName($feedName, $alertName);

        return SettingsClass::getSettings($name, Mergado::getShopId());
    }

    public function setAlertDisabled($feedName, $alertName) {
        $name = $this->getDisabledName($feedName, $alertName);

        return SettingsClass::saveSetting($name, 1, Mergado::getShopId());
    }


    // DISABLED SECTION
    public function getDisabledSectionName($sectionName) {
        return 'mmp_alert_section_disabled' . '_' . $sectionName;
    }

    public function isSectionDisabled($sectionName) {
        $name = $this->getDisabledSectionName($sectionName);

        return SettingsClass::getSettings($name, Mergado::getShopId());
    }


    public function setSectionDisabled($sectionName) {
        $name = $this->getDisabledSectionName($sectionName);

        return SettingsClass::saveSetting($name, 1, Mergado::getShopId());
    }

    // ERRORS
    public function getErrorName($feedName, $sectionName, $alertName) {
        return 'mmp_alert_error_' . $feedName . '_' . $sectionName . '_' . $alertName;
    }

    public function getSectionByFeed($feedName)
    {
        if (XMLStockFeed::isStockFeed($feedName)) {
            return self::FEED_TO_SECTION['stock'];
        } else if (XMLStaticFeed::isStaticFeed($feedName)){
            return self::FEED_TO_SECTION['static'];
        } else if (XMLCategoryFeed::isCategoryFeed($feedName)) {
            return self::FEED_TO_SECTION['category'];
        } else {
            return self::FEED_TO_SECTION['product'];
        }
    }

    public function setErrorInactive($feedName, $alertName)
    {
        $sectionName = $this->getSectionByFeed($feedName);
        $name = $this->getErrorName($feedName, $sectionName, $alertName);

        return SettingsClass::saveSetting($name, 0, Mergado::getShopId());
    }

    public function setErrorActive($feedName, $alertName)
    {
        $sectionName = $this->getSectionByFeed($feedName);
        $name = $this->getErrorName($feedName, $sectionName, $alertName);
//
        return SettingsClass::saveSetting($name, 1, Mergado::getShopId());
    }

    public function getFeedErrors($feedName)
    {
        $sectionName = $this->getSectionByFeed($feedName);

        $activeErrors = [];

        foreach(self::ALERT_NAMES as $alert) {
            $alertName = $this->getErrorName($feedName, $sectionName, $alert);

            if(SettingsClass::getSettings($alertName, Mergado::getShopId()) == 1) {
                $isNotHidden = !$this->isAlertDisabled($feedName, $alert);

                // Error is not hidden by user
                if ($isNotHidden) {
                    $activeErrors[] = $alert;
                }
            }
        }

        return $activeErrors;
    }

    // Theres a function that set these variables base on specific conditions
    public function getMergadoErrors()
    {
        $errors = ['total' => 0];

        foreach(self::FEED_TO_SECTION as $feedName => $sectionName) {
            if (!isset($errors[$sectionName])) {
                $errors[$sectionName] = 0;
            }

            foreach(self::ALERT_NAMES as $alert) {
                $alertName = $this->getErrorName($feedName, $sectionName, $alert);

                $hasError = SettingsClass::getSettings($alertName, Mergado::getShopId());

                // Is error active
                if ($hasError) {
                    $isNotHidden = !$this->isAlertDisabled($feedName, $alert);

                    // Error is not hidden by user
                    if ($isNotHidden) {
                        $errors['total']++;
                        $errors[$sectionName]++;
                    }
                }
            }
        }

        return $errors;
    }

    public function checkIfErrorsShouldBeActive()
    {
        // Adding error if feeds exist and not updated for 24 hours
        $this->checkIfFeedsUpdated();
    }

    public function checkIfFeedsUpdated()
    {

        foreach (Context::getContext()->language->getLanguages(true) as $lang) {
            foreach (Context::getContext()->currency->getCurrencies(false, true, true) as $currency) {
                // Product feeds
                $name = XMLProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];

                $xmlProductFeed = new XMLProductFeed($name);

                if ($xmlProductFeed->isFeedExist() && $this->isTimestampOlderThan24hours($xmlProductFeed->getLastFeedChangeTimestamp())) {
                    $this->setErrorActive($name, self::ALERT_NAMES['NO_FEED_UPDATE']);
                } else {
                    $this->setErrorInactive($name, self::ALERT_NAMES['NO_FEED_UPDATE']);
                }

                // Category feeds
                $name = XMLCategoryFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];

                $xmlCategoryFeed = new XMLCategoryFeed($name);

                if ($xmlCategoryFeed->isFeedExist() && $this->isTimestampOlderThan24hours($xmlCategoryFeed->getLastFeedChangeTimestamp())) {
                    $this->setErrorActive($name, self::ALERT_NAMES['NO_FEED_UPDATE']);
                } else {
                    $this->setErrorInactive($name, self::ALERT_NAMES['NO_FEED_UPDATE']);
                }
            }
        }

        $xmlStockFeed = new XMLStockFeed();
        $xmlStaticFeed = new XMLStaticFeed();

        if ($xmlStockFeed->isFeedExist() && $this->isTimestampOlderThan24hours($xmlStockFeed->getLastFeedChangeTimestamp())) {
            $this->setErrorActive('stock', self::ALERT_NAMES['NO_FEED_UPDATE']);
        } else {
            $this->setErrorInactive('stock', self::ALERT_NAMES['NO_FEED_UPDATE']);
        }

        if ($xmlStaticFeed->isFeedExist() && $this->isTimestampOlderThan24hours($xmlStaticFeed->getLastFeedChangeTimestamp())) {
            $this->setErrorActive('static', self::ALERT_NAMES['NO_FEED_UPDATE']);
        } else {
            $this->setErrorInactive('static', self::ALERT_NAMES['NO_FEED_UPDATE']);
        }
    }

    public function isTimestampOlderThan24hours($timestamp)
    {
        return strtotime('+1 day', $timestamp) < time();
    }
}