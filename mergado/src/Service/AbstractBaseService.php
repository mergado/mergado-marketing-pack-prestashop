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

use Mergado\Traits\SingletonTrait;

abstract class AbstractBaseService
{
    use SingletonTrait;

    /**
     * @var LogService
     */
    protected $logger;

    protected function __construct()
    {
        $this->logger = LogService::getInstance();
    }
}
