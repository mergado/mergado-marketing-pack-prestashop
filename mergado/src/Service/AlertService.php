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


namespace Mergado\Service;

use Context;
use Mergado\Helper\ShopHelper;
use Mergado\Manager\DatabaseManager;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\ProductFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Utility\TimeUtils;

class AlertService extends AbstractBaseService
{
    public const FEED_TO_SECTION = [
        'product' => ProductFeed::ALERT_SECTION,
        'category' => CategoryFeed::ALERT_SECTION,
        'stock' => StockFeed::ALERT_SECTION,
        'static' => StaticFeed::ALERT_SECTION
    ];

    //SINGLE ALERT NAMES... in prestashop function that add blogId
    public const ALERT_NAMES = [
        'NO_FEED_UPDATE' => 'feed_not_updated',
        'ERROR_DURING_GENERATION' => 'generation_failed'
    ];

    public function getDisabledName($feedName, $alertName): string
    {
        return 'mmp_alert_disabled_' . $feedName . '_' . $alertName;
    }

    public function isAlertDisabled($feedName, $alertName)
    {
        $name = $this->getDisabledName($feedName, $alertName);

        return DatabaseManager::getSettingsFromCache($name);
    }

    public function setAlertDisabled($feedName, $alertName): bool
    {
        $name = $this->getDisabledName($feedName, $alertName);

        return DatabaseManager::saveSetting($name, 1, ShopHelper::getId());
    }

    public function getDisabledSectionName($sectionName): string
    {
        return 'mmp_alert_section_disabled' . '_' . $sectionName;
    }

    /**
     * Used in templates
     */
    public function isSectionDisabled($sectionName)
    {
        $name = $this->getDisabledSectionName($sectionName);

        return DatabaseManager::getSettingsFromCache($name);
    }


    public function setSectionDisabled($sectionName): bool
    {
        $name = $this->getDisabledSectionName($sectionName);

        return DatabaseManager::saveSetting($name, 1, ShopHelper::getId());
    }

    // ERRORS
    public function getErrorName($feedName, $sectionName, $alertName): string
    {
        return 'mmp_alert_error_' . $feedName . '_' . $sectionName . '_' . $alertName;
    }

    public function getSectionByFeed($feedName)
    {
        if (StockFeed::isStockFeed($feedName)) {
            return self::FEED_TO_SECTION['stock'];
        } else if (StaticFeed::isStaticFeed($feedName)) {
            return self::FEED_TO_SECTION['static'];
        } else if (CategoryFeed::isCategoryFeed($feedName)) {
            return self::FEED_TO_SECTION['category'];
        } else {
            return self::FEED_TO_SECTION['product'];
        }
    }

    public function setErrorInactive($feedName, $alertName): bool
    {
        $sectionName = $this->getSectionByFeed($feedName);
        $name = $this->getErrorName($feedName, $sectionName, $alertName);

        return DatabaseManager::saveSetting($name, 0, ShopHelper::getId());
    }

    public function setErrorActive($feedName, $alertName): bool
    {
        $sectionName = $this->getSectionByFeed($feedName);
        $name = $this->getErrorName($feedName, $sectionName, $alertName);

        return DatabaseManager::saveSetting($name, 1, ShopHelper::getId());
    }

    public function getFeedErrors($feedName): array
    {
        $sectionName = $this->getSectionByFeed($feedName);

        $activeErrors = [];

        foreach (self::ALERT_NAMES as $alert) {
            $alertName = $this->getErrorName($feedName, $sectionName, $alert);

            if (DatabaseManager::getSettingsFromCache($alertName) == 1) {
                $isNotHidden = !$this->isAlertDisabled($feedName, $alert);

                // Error is not hidden by user
                if ($isNotHidden) {
                    $activeErrors[] = $alert;
                }
            }
        }

        return $activeErrors;
    }

    public function checkIfErrorsShouldBeActive(): void
    {
        // Adding error if feeds exist and not updated for 24 hours
        $this->checkIfFeedsUpdated();
    }

    public function checkIfFeedsUpdated(): void
    {
        foreach (Context::getContext()->language->getLanguages(true) as $lang) {
            foreach (Context::getContext()->currency->getCurrencies(false, true, true) as $currency) {
                // Product feeds
                $name = ProductFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];

                $xmlProductFeed = new ProductFeed($name);

                if ($xmlProductFeed->isFeedExist() && TimeUtils::isTimestampOlderThan24hours($xmlProductFeed->getLastFeedChangeTimestamp())) {
                    $this->setErrorActive($name, self::ALERT_NAMES['NO_FEED_UPDATE']);
                } else {
                    $this->setErrorInactive($name, self::ALERT_NAMES['NO_FEED_UPDATE']);
                }

                // Category feeds
                $name = CategoryFeed::FEED_PREFIX . $lang['iso_code'] . '-' . $currency['iso_code'];

                $xmlCategoryFeed = new CategoryFeed($name);

                if ($xmlCategoryFeed->isFeedExist() && TimeUtils::isTimestampOlderThan24hours($xmlCategoryFeed->getLastFeedChangeTimestamp())) {
                    $this->setErrorActive($name, self::ALERT_NAMES['NO_FEED_UPDATE']);
                } else {
                    $this->setErrorInactive($name, self::ALERT_NAMES['NO_FEED_UPDATE']);
                }
            }
        }

        $xmlStockFeed = new StockFeed();
        $xmlStaticFeed = new StaticFeed();

        if ($xmlStockFeed->isFeedExist() && TimeUtils::isTimestampOlderThan24hours($xmlStockFeed->getLastFeedChangeTimestamp())) {
            $this->setErrorActive('stock', self::ALERT_NAMES['NO_FEED_UPDATE']);
        } else {
            $this->setErrorInactive('stock', self::ALERT_NAMES['NO_FEED_UPDATE']);
        }

        if ($xmlStaticFeed->isFeedExist() && TimeUtils::isTimestampOlderThan24hours($xmlStaticFeed->getLastFeedChangeTimestamp())) {
            $this->setErrorActive('static', self::ALERT_NAMES['NO_FEED_UPDATE']);
        } else {
            $this->setErrorInactive('static', self::ALERT_NAMES['NO_FEED_UPDATE']);
        }
    }
}
