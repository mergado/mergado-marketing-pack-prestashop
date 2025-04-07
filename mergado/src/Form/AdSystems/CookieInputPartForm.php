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


namespace Mergado\Form\AdSystems;

use Mergado\Service\CookieService;
use Mergado\Traits\SingletonTrait;
use Shop;

class CookieInputPartForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {
        $fields_form[0]['form'] = [
            'input' => [
                [
                    'type' => 'hidden',
                    'name' => 'page'
                ],
                [
                    'type' => 'hidden',
                    'name' => 'id_shop'
                ],
                [
                    'name' => CookieService::FIELD_ANALYTICAL_USER,
                    'label' => $translateFunction('Analytics', 'cookies'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => CookieService::FIELD_ADVERTISEMENT_USER,
                    'label' => $translateFunction('Advertisement', 'cookies'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => CookieService::FIELD_FUNCTIONAL_USER,
                    'label' => $translateFunction('Functional', 'cookies'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        return $fields_form;
    }
}
