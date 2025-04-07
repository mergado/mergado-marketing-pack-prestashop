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

use Context;
use DateTime;
use Db;
use DbQuery;
use Mergado;
use Mergado\Service\AbstractBaseService;
use PrestaShopDatabaseException;

class NewsService extends AbstractBaseService
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';
    public const DATE_COMPARE_FORMAT = 'Y-m-d';
    public const DATE_OUTPUT_FORMAT = 'd.m.Y';

    /**
     * @var string
     */
    private $newsLang;

    public function __construct()
    {
        $this->newsLang = $this->getNewsLanguage(Context::getContext()->language->iso_code);
        parent::__construct();
    }

    /*******************************************************************************************************************
     * GET
     *******************************************************************************************************************/

    /**
     * @throws PrestaShopDatabaseException
     */
    public function getNews(string $lang = null, int $limit = null)
    {
        $sql = $this->getNewsBaseQuery($lang, $limit);

        return Db::getInstance()->executeS($sql);
    }

    public function getNewsWithFormattedDate(int $limit = null, string $lang = null)
    {
        try {
            $sql = $this->getNewsBaseQuery($lang, $limit);
            $return = Db::getInstance()->executeS($sql);

            foreach($return as &$val) {
                $date = new DateTime();
                $date = $date::createFromFormat('Y-m-d H:i:s', $val['pubDate']);
                $formatted = $date->format('d.m.Y H:i:s');

                $val['pubDate'] = $formatted;
            }

            return $return;
        } catch (PrestaShopDatabaseException $e) {
            $this->logger->error('Failed to get news with formatted date', ['exception' => $e]);
        }

        return [];
    }

    private function getNewsBaseQuery($lang = null, $limit = null): DbQuery
    {
        if ($lang === null) {
            $lang = $this->newsLang;
        }

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

    public function getNewsByStatusAndCategory(bool $shown, string $category = null, int $limit = null, bool $excludeTop = false, $order = false)
    {
        try {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from(Mergado::MERGADO['TABLE_NEWS_NAME']);
            $sql->where('`language`="' . $this->newsLang . '"');

            if($shown) {
                $sql->where('`shown`="' . 1 . '"');
            } else {
                $sql->where('`shown`="' . 0 . '"');
            }

            if ($category !== '' && $category !== null) {
                $sql->where('`category`="' . $category . '"');
            }

            if ($excludeTop) {
                $sql->where('`category`!="top"');
            }

            if($order) {
                $sql->orderBy('`pubDate`' . $order);
            } else {
                $sql->orderBy('`pubDate`');
            }

            if($limit) {
                $sql->limit($limit);
            }

            return Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $this->logger->error('Failed to get news by category and status',['exception' => $e]);
        }

        return [];
    }

    public function getNewsLanguage(string $lang = null) : string
    {
        // Default language is english
        if(!in_array($lang, Mergado::LANG_AVAILABLE, true)) {
            return Mergado::LANG_EN;
        }

        return $lang;
    }

    /**
     * @throws PrestaShopDatabaseException
     */
    public function saveArticleByLang(array $item, DateTime $date, string $lang): bool
    {
        return Db::getInstance()->insert(Mergado::MERGADO['TABLE_NEWS_NAME'], [
            'title' => pSQL((string) $item['title']),
            'description' => pSQL(str_replace(']]>','', str_replace('<![CDATA[', '', $item['description'])), true),
            'category' => (string) $item['category'],
            'pubDate' => $date->format(self::DATE_FORMAT),
            'language' => $lang,
            'link' => pSQL(str_replace(']]>','', str_replace('<![CDATA[', '', $item['link']))),
            'shown' => 0,
        ]);
    }

    public function markArticlesShownByIds(array $ids): bool
    {
        return Db::getInstance()->update(Mergado::MERGADO['TABLE_NEWS_NAME'], ['shown' => 1], '`id` IN (' . implode(',', $ids) . ')');
    }

    public function markArticlesShownByLanguage(string $lang): bool
    {
        return Db::getInstance()->update(Mergado::MERGADO['TABLE_NEWS_NAME'],
            ['shown' => 1],
            '`language` = "' . $lang . '"');

    }
}
