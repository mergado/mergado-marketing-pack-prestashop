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
use Psr\Log\InvalidArgumentException;
use SimpleXMLElement;

require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/XMLClass.php';
require_once _PS_MODULE_DIR_ . 'mergado/classes/tools/NewsClass.php';

class RssClass
{
    // @TODO - correct ?? (lang mutations ??)
    const FEED_URLS = array(
        'en' => 'https://news.mergado.com/en/prestashop/rss.xml',
        'cs' => 'https://news.mergado.com/cs/prestashop/rss.xml',
        'sk' => 'https://news.mergado.com/sk/prestashop/rss.xml',
    );

    // @TODO - UPDATE CONSTANT - update ???
    const UPDATE_CATEGORY = 'UPDATE';

    public function getFeed($lang)
    {
        try {
            $lastDownload = SettingsClass::getSettings(SettingsClass::RSS_FEED, 0);

            if ($lastDownload && $lastDownload !== '') {
                $dateTime = new DateTime($lastDownload);

                // Check every half day from last check
                $dateFormatted = $dateTime->modify('+5 seconds')->format(NewsClass::DATE_FORMAT);

                $now = new DateTime();
                $date = $now->format(NewsClass::DATE_FORMAT);

                if ($dateFormatted <= $date) {
                    foreach(self::FEED_URLS as $item_lang => $val) {
                        $this->saveFeed($item_lang);
                    }

                    SettingsClass::saveSetting(SettingsClass::RSS_FEED, $date, 0);
                }
            } else {
                $now = new DateTime();
                $date = $now->format(NewsClass::DATE_FORMAT);

                foreach(self::FEED_URLS as $item_lang => $val) {
                    $this->saveFeed($item_lang);
                }

                SettingsClass::saveSetting(SettingsClass::RSS_FEED, $date, 0);
            }
        } catch (Exception $e) {

        }
    }

    /**
     * Save new RSS feed articles to database
     *
     * @param $lang
     * @return void
     */
    private function saveFeed($lang)
    {
        try {
            $dbQuery = NewsClass::getNews($lang);
            $rssFeed = $this->downloadFeed($lang);
            foreach ($rssFeed as $item) {
                $itemDatetime = new DateTime((string)$item->pubDate);
                $save = true;

                if (count($dbQuery) > 0) {
                    foreach ($dbQuery as $dbItem) {

                        // Fix different APIs ( one with time and second only date ) => Compare only based on date and title
                        $dbTime = new DateTime($dbItem['pubDate']);
                        $dbTime = $dbTime->format(NewsClass::DATE_COMPARE_FORMAT);

                        if ($itemDatetime->format(NewsClass::DATE_COMPARE_FORMAT) === $dbTime && (string)$item->title === $dbItem['title']) {
                            $save = false;
                            break;
                        }
                    }
                }

                if ($save) {
                    if((string) $item->category == self::UPDATE_CATEGORY && Mergado::checkUpdate()) {
                        NewsClass::saveArticle($item, $itemDatetime, $lang);
                    } elseif((string) $item->category != self::UPDATE_CATEGORY) {
                        NewsClass::saveArticle($item, $itemDatetime, $lang);
                    }
                }
            }
        } catch (Exception $e) {
            LogClass::log("Mergado save downloaded RSS feed ERROR:\n" . $e->getMessage());
        }
    }

    /**
     * Downlaod feed - upgraded version
     * - do not use file_get_contents (not working on HTTPS in php 5.6)
     */

    private function downloadFeed($lang)
    {
        $lang = NewsClass::getMergadoNewsLanguage($lang);

        $agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $agent); //make it act decent
        curl_setopt($ch, CURLOPT_URL, self::FEED_URLS[$lang]);
        curl_setopt($ch, CURLOPT_FAILONERROR,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //set this flag for results to the variable
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //This is required for HTTPS certs if
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //you don't have some key/password action
        $feed = curl_exec($ch);
        curl_close($ch);

        $x = new SimpleXMLElement($feed);

        $data = array();
        foreach ($x->channel->item as $item) {
            $data[] = $item;
        }

        return $data;
    }
}
