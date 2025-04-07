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
use Exception;
use Mergado\Exception\InvalidXmlException;
use Mergado\Service\AbstractBaseService;
use Mergado\Manager\DatabaseManager;
use SimpleXMLElement;

class NewsFeedService extends AbstractBaseService
{
    // RSS feed
    public const RSS_FEED = 'last_rss_feed_download';
    public const RSS_FEED_LOCK = 'unfinished_rss_downloads';

    public const FEED_URLS = [
        'en' => 'https://pack.mergado.cz/custom-platform/prestashop/en',
        'cs' => 'https://pack.mergado.cz/custom-platform/prestashop/cs',
        'sk' => 'https://pack.mergado.cz/custom-platform/prestashop/sk',
        'pl' => 'https://pack.mergado.cz/custom-platform/prestashop/pl',
        'hu' => 'https://pack.mergado.cz/custom-platform/prestashop/hu',
    ];

    /**
     * @var NewsService
     */
    private $newsService;

    public function __construct()
    {
        $this->newsService = NewsService::getInstance();

        parent::__construct();
    }

    public function downloadNews(): void
    {
        $now = new DateTime();

        try {
            $lastDownload = DatabaseManager::getSettingsFromCache(self::RSS_FEED, false, 0);

            if ($lastDownload) {
                $lastDownloadDateTime = new DateTime($lastDownload);

                if ($this->getDownloadLock() === 0) {
                    $dateFormatted = (clone $lastDownloadDateTime)->modify('+60 minutes');
                } else {
                    $minutes = 120 * $this->getDownloadLock();
                    $dateFormatted = (clone $lastDownloadDateTime)->modify('+' . $minutes . ' minutes');
                }

                if ($dateFormatted <= $now) {
                    foreach(self::FEED_URLS as $item_lang => $val) {
                        $this->saveFeed($item_lang);
                    }

                    // Set lock on null and download time to now
                    $this->nullDownloadLock();
                    $this->setFeedLastDownloadTime($now);
                }
            } else {
                foreach(self::FEED_URLS as $item_lang => $val) {
                    $this->saveFeed($item_lang);
                }

                // Set lock on null and download time to now
                $this->nullDownloadLock();
                $this->setFeedLastDownloadTime($now);
            }
        } catch (InvalidXmlException $e) {
            $this->logger->info("RSS - XML parse failed", ['exception' => $e]);
            $this->increaseDownloadLock();
            $this->setFeedLastDownloadTime($now);
        } catch (Exception $e) {
            $this->logger->info("RSS - Save of downloaded feed failed", ['exception' => $e]);
            $this->increaseDownloadLock();
            $this->setFeedLastDownloadTime($now);
        }
    }

    /**
     * @throws InvalidXmlException
     * @throws Exception
     */
    private function saveFeed($lang): void
    {
        $newsInDatabase = $this->newsService->getNews($lang);
        $downloadedNews = $this->downloadFeed($lang);

        foreach ($downloadedNews as $article) {
            $itemAr = (array)$article;
            $article = array_change_key_case($itemAr, CASE_LOWER);

            $articlePubDate = new DateTime((string)$article['pubdate']);
            $save = true;

            // Check if article is in database already
            if (count($newsInDatabase) > 0) {
                foreach ($newsInDatabase as $existingArticle) {

                    // Fix different APIs ( one with time and second only date ) => Compare only based on date and title
                    $existingArticlePubDate = new DateTime($existingArticle['pubDate']);
                    $existingArticlePubDate = $existingArticlePubDate->format(NewsService::DATE_COMPARE_FORMAT);

                    if ((string)$article['title'] === $existingArticle['title'] && $articlePubDate->format(NewsService::DATE_COMPARE_FORMAT) === $existingArticlePubDate) {
                        $save = false;
                        break;
                    }
                }
            }

            if ($save) {
                $this->newsService->saveArticleByLang($article, $articlePubDate, $lang);
            }
        }
    }

    /**
     * @throws InvalidXmlException
     * @throws Exception
     */
    private function downloadFeed($lang): array
    {
        $agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, self::FEED_URLS[$lang]);
        curl_setopt($ch, CURLOPT_FAILONERROR,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $feed = curl_exec($ch);

        $errorCount = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($feed === false || $errorCount > 0) {
            throw new Exception('Curl error: ' . $error);
        }

        try {
            $x = new SimpleXMLElement($feed, LIBXML_NOERROR);

            $data = [];
            foreach ($x->item as $item) {
                $data[] = $item;
            }
        } catch (Exception $ex) {
            throw new InvalidXmlException($ex->getMessage());
        }

        return $data;
    }

    private function setFeedLastDownloadTime(DateTime $time) : bool
    {
        return DatabaseManager::saveSetting(self::RSS_FEED, $time->format(NewsService::DATE_FORMAT), 0);
    }

    private function increaseDownloadLock(): bool
    {
        return DatabaseManager::saveSetting(self::RSS_FEED_LOCK, $this->getDownloadLock() + 1, 0);
    }

    private function nullDownloadLock(): bool
    {
        return DatabaseManager::saveSetting(self::RSS_FEED_LOCK, 0, 0);
    }

    private function getDownloadLock() : int
    {
        return (int) DatabaseManager::getSettingsFromCache(self::RSS_FEED_LOCK, 0, 0);
    }
}
