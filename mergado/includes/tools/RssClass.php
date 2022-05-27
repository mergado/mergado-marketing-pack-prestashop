<?php
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

namespace Mergado\Tools;

use DateTime;
use Exception;
use Mergado;
use SimpleXMLElement;
use Symfony\Component\Config\Util\Exception\InvalidXmlException;

include_once _PS_MODULE_DIR_ . 'mergado/autoload.php';

class RssClass
{
    const FEED_URLS = [
        'en' => 'https://pack.mergado.cz/custom-platform/prestashop/en',
        'cs' => 'https://pack.mergado.cz/custom-platform/prestashop/cs',
        'sk' => 'https://pack.mergado.cz/custom-platform/prestashop/sk',
        'pl' => 'https://pack.mergado.cz/custom-platform/prestashop/pl',
        'hu' => 'https://pack.mergado.cz/custom-platform/prestashop/hu',
    ];

    const UPDATE_CATEGORY = 'update';

    public function getFeed()
    {

        $now = new DateTime();
        $date = $now->format(NewsClass::DATE_FORMAT);

        $lastDownload = SettingsClass::getSettings(SettingsClass::RSS_FEED, 0);

        $lastDate = new DateTime($lastDownload);

        if ($this->getDownloadLock() < count(self::FEED_URLS) * 3) {
            $dateFormatted = $lastDate->modify('+5 minutes')->format(NewsClass::DATE_FORMAT);
        } else {
            $dateFormatted = $lastDate->modify('+30 minutes')->format(NewsClass::DATE_FORMAT);
        }

        try {
            if ($lastDownload && $lastDownload !== '') {
                if ($dateFormatted <= $date) {
                    foreach(self::FEED_URLS as $item_lang => $val) {

                        $this->saveFeed($item_lang);
                    }

                    $this->nullDownloadLock();
                    $this->setLastDownload($date);
                }
            } else {
                foreach(self::FEED_URLS as $item_lang => $val) {
                    $this->saveFeed($item_lang);
                }

                $this->nullDownloadLock();
                $this->setLastDownload($date);
            }
        } catch (InvalidXmlException $e) {
            LogClass::log("Mergado XML parse RSS feed ERROR:\n" . $e->getMessage());
            $this->increaseDownloadLock();
            $this->setLastDownload($date);
        } catch (Exception $e) {
            LogClass::log("Mergado save downloaded RSS feed ERROR:\n" . $e->getMessage());
            $this->increaseDownloadLock();
            $this->setLastDownload($date);
        }
    }

    /**
     * Save new RSS feed articles to database
     *
     * @param $lang
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws Exception
     */
    private function saveFeed($lang)
    {
        $dbQuery = NewsClass::getNews($lang);
        $rssFeed = $this->downloadFeed($lang);
        foreach ($rssFeed as $item) {

            // Transform keys to lowercase
            $itemAr = (array)$item;
            $item = array_change_key_case($itemAr, CASE_LOWER);

            $itemDatetime = new DateTime((string)$item['pubdate']);
            $save = true;

            if (count($dbQuery) > 0) {
                foreach ($dbQuery as $dbItem) {

                    // Fix different APIs ( one with time and second only date ) => Compare only based on date and title
                    $dbTime = new DateTime($dbItem['pubDate']);
                    $dbTime = $dbTime->format(NewsClass::DATE_COMPARE_FORMAT);

                    if ($itemDatetime->format(NewsClass::DATE_COMPARE_FORMAT) === $dbTime && (string)$item['title'] === $dbItem['title']) {
                        $save = false;
                        break;
                    }
                }
            }

            if ($save) {
                NewsClass::saveArticle($item, $itemDatetime, $lang);
            }
        }
    }

    /**
     * Downlaod feed - upgraded version
     * - do not use file_get_contents (not working on HTTPS in php 5.6)
     * @param $lang
     * @return array
     */

    private function downloadFeed($lang)
    {
        $lang = NewsClass::getMergadoNewsLanguage($lang);

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
            throw new InvalidXmlException($ex);
        }

        return $data;
    }

    /**
     * Set last download based on lock
     *
     * @param $now
     */
    private function setLastDownload($now)
    {
        SettingsClass::saveSetting(SettingsClass::RSS_FEED, $now, 0);
    }

    /**
     * Set lock for few minutes, if feed is broken
     */
    private function increaseDownloadLock()
    {
        $value = $this->getDownloadLock();
        SettingsClass::saveSetting(SettingsClass::RSS_FEED_LOCK, $value + 1, 0);
    }

    /**
     * Set download lock to null
     */
    private function nullDownloadLock()
    {
        SettingsClass::saveSetting(SettingsClass::RSS_FEED_LOCK, 0, 0);
    }

    /**
     * Return current downlaod lock number
     * @return false|string|null
     */
    private function getDownloadLock()
    {
        return SettingsClass::getSettings(SettingsClass::RSS_FEED_LOCK, 0);
    }
}
