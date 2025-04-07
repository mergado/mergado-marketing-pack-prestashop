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


namespace Mergado\Service\External\Etarget;

use Mergado;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\CookieService;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;

class EtargetServiceIntegration extends AbstractBaseService
{
    /**
     * @var EtargetService
     */
    private $etargetService;

    /**
     * @var CookieService
     */
    private $cookieService;

    public const TEMPLATES_PATH = 'views/templates/services/Etarget/';

    protected function __construct()
    {
        $this->etargetService = EtargetService::getInstance();
        $this->cookieService = CookieService::getInstance();

        parent::__construct();
    }

    public function etargetRetarget(Mergado $module, $smarty): string
    {
        try {
            if (!$this->etargetService->isActive()) {
                return '';
            }

            if (!$this->cookieService->advertismentEnabled()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'retarget.tpl',
                $smarty,
                [
                    'etargetData' => [
                        'id' => $this->etargetService->getId(),
                        'hash' => $this->etargetService->getHash(),
                    ],
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }
}
