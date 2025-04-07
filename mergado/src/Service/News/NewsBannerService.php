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


namespace Mergado\Service\News;

use DateTime;
use Mergado\Service\AbstractBaseService;
use Mergado\Manager\DatabaseManager;
use Throwable;

class NewsBannerService extends AbstractBaseService
{
    private const NEXT_BANNER_VISIBILITY_DB_NAME = 'mmp-cookie-news';

    public function setNextBannerVisibility(string $modifier) : void
    {
        $now = new DateTime();
        $date = $now->modify($modifier)->format(NewsService::DATE_FORMAT);

        DatabaseManager::saveSetting(self::NEXT_BANNER_VISIBILITY_DB_NAME, $date, 0);
    }

    public function isBannerVisible(): bool
    {
        try {
            $newsHiddenUntilTimestamp = DatabaseManager::getSettingsFromCache(self::NEXT_BANNER_VISIBILITY_DB_NAME, false, 0);

            if($newsHiddenUntilTimestamp !== '0' && $newsHiddenUntilTimestamp !== false) {
                $newsHiddenUntil = new DateTime($newsHiddenUntilTimestamp);
                return $newsHiddenUntil > new DateTime();
            }
        } catch (Throwable $e) {
            $this->logger->error('New banner visibility function failed', ['exception' => $e]);
        }

        return true;
    }
}
