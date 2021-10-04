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
use Mergado;
use SimpleXMLElement;
use DbQueryCore as DbQuery;
use Db;

class NewsClass
{
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const DATE_COMPARE_FORMAT = 'Y-m-d';
    const DATE_OUTPUT_FORMAT = 'd.m.Y';

    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    /**
     * Return news from DB by lang and limit (ifset)
     *
     * @param $lang
     * @param null $limit
     * @return array|false|\mysqli_result|\PDOStatement|resource|null
     * @throws \PrestaShopDatabaseException
     */
    public static function getNews($lang, $limit = null)
    {
        $sql = self::getNewsBase($lang, $limit);

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Return news with formated date
     *
     * @param $lang
     * @param null $limit
     * @return array|false|\mysqli_result|\PDOStatement|resource|null
     * @throws \Exception
     */
    public static function getNewsWithFormatedDate($lang, $limit = null)
    {
        $sql = self::getNewsBase($lang, $limit);
        $return = Db::getInstance()->executeS($sql);

        foreach($return as $item => $val) {
            $date = new DateTime();
            $date = $date::createFromFormat('Y-m-d H:m:s',$return[$item]['pubDate']);
            $formatted = $date->format('d.m.Y H:m:s');

            $return[$item]['pubDate'] = $formatted;
        }

        return $return;
    }


    /**
     * Base query for returning news
     *
     * @param $lang
     * @param null $limit
     * @return DbQuery
     */
    private static function getNewsBase($lang, $limit = null)
    {
        $lang = self::getMergadoNewsLanguage($lang);

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(Mergado::MERGADO['TABLE_NEWS_NAME']);
        $sql->where('`language`="' . $lang . '"');
        $sql->orderBy('id DESC');

        if($limit) {
            $sql->limit($limit);
        }

        return $sql;
    }

    /**
     * Return shown/new news from DB
     *
     * @param $shown
     * @param $lang
     * @param null $category
     * @param null $limit
     * @param bool $excludeTop
     * @param string $order
     * @return array|false|\mysqli_result|\PDOStatement|resource|null
     * @throws \PrestaShopDatabaseException
     */
    public static function getNewsByStatusAndLanguageAndCategory($shown, $lang, $category = null, $limit = null, $excludeTop = false, $order = false)
    {
        $lang = self::getMergadoNewsLanguage($lang);

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(Mergado::MERGADO['TABLE_NEWS_NAME']);
        $sql->where('`language`="' . $lang . '"');
        if($shown) {
            $sql->where('`shown`="' . 1 . '"');
        } else {
            $sql->where('`shown`="' . 0 . '"');
        }

        if (($category || $category != '') && $category !== null) {
            $sql->where('`category`="' . $category . '"');
        }

        if ($excludeTop) {
            $sql->where('`category`!="top"');
        }

        if($order) {
            $sql->orderBy('`pubDate`' . $order .'');
        } else {
            $sql->orderBy('`pubDate`');
        }

        if($limit) {
            $sql->limit($limit);
        }

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param $lang
     * @return string|string
     */
    public static function getMergadoNewsLanguage($lang)
    {
        // Set default English news if language not available
        if(!in_array($lang, Mergado::LANG_AVAILABLE)) {
            $lang = Mergado::LANG_EN;
        }

        return $lang;
    }

    /*******************************************************************************************************************
     * SET
     *******************************************************************************************************************/

    /**
     * Save article to DB
     *
     * @param array $item
     * @param DateTime $date
     * @param $lang
     */
    public static function saveArticle(array $item, DateTime $date, $lang)
    {
        $lang = self::getMergadoNewsLanguage($lang);

        Db::getInstance()->insert(Mergado::MERGADO['TABLE_NEWS_NAME'], array(
            'title' => pSQL((string) $item['title']),
            'description' => pSQL(str_replace(']]>','', str_replace('<![CDATA[', '', $item['description'])), true),
            'category' => (string) $item['category'],
            'pubDate' => $date->format(self::DATE_FORMAT),
            'language' => $lang,
            'shown' => 0,
        ));
    }

    /**
     * Set Article shown by user
     *
     * @param array $ids
     */
    public static function setArticlesShown(array $ids)
    {
        Db::getInstance()->update(Mergado::MERGADO['TABLE_NEWS_NAME'], array('shown' => 1), '`id` IN (' . implode(',', $ids) . ')');
    }

    /**
     * Set Article shownbased on language
     *
     * @param $lang
     */

    public static function setArticlesShownByLanguage($lang)
    {
        $lang = self::getMergadoNewsLanguage($lang);

        Db::getInstance()->update(Mergado::MERGADO['TABLE_NEWS_NAME'],
            array('shown' => 1),
            '`language` = "' . $lang . '"');

    }

    public static function getFormattedDate($date)
    {
        $date = new DateTime($date);
        $date = $date->format(NewsClass::DATE_OUTPUT_FORMAT);
        return $date;
    }
}
