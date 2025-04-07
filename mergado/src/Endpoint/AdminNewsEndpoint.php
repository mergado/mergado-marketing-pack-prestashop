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


namespace Mergado\Endpoint;

use Mergado\Service\News\NewsBannerService;
use Mergado\Service\News\NewsService;
use Mergado\Traits\SingletonTrait;
use Mergado\Utility\JsonResponse;

class AdminNewsEndpoint implements ParametrizedEndpointInterface
{
    use SingletonTrait;

    /**
     * @var NewsBannerService
     */
    private $newsBannerService;

    /**
     * @var NewsService
     */
    private $newsService;

    public function __construct()
    {
        $this->newsBannerService = NewsBannerService::getInstance();
        $this->newsService = NewsService::getInstance();
    }

    protected function dismissNewsBarForTwoWeeks(): void
    {
        if (isset($_POST['action']) && $_POST['action'] === 'mmp-cookie-news') {
            $this->newsBannerService->setNextBannerVisibility('+14 days');
            exit;
        }
    }

    protected function getNews(): void
    {
        if(isset($_POST['action']) && $_POST['action'] === 'mmp-get-news') {
            JsonResponse::send_json_success($this->newsService->getNewsByStatusAndCategory(false));
        }
    }

    protected function setNewsRead(): void
    {
        if(isset($_POST['action']) && $_POST['action'] === 'mmp-set-readed') {
            $this->newsService->markArticlesShownByIds($_POST['ids']);
            exit;
        }
    }

    public function initEndpoints($controller, $context): void
    {
        $this->dismissNewsBarForTwoWeeks();
        $this->getNews();
        $this->setNewsRead();
    }
}
