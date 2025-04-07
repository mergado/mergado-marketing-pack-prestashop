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


namespace Mergado\Service\External\ArukeresoFamily\Compari;

use Mergado\Service\External\ArukeresoFamily\Compari\CompariService;
use Mergado\Service\External\ArukeresoFamily\AbstractArukeresoFamilyServiceIntegration;
use Mergado\Traits\SingletonTrait;

class CompariServiceIntegration extends AbstractArukeresoFamilyServiceIntegration
{
    use SingletonTrait;

    public function __construct()
    {
        parent::__construct(CompariService::getInstance());
    }
}
