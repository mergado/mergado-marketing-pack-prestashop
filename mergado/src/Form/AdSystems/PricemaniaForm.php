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

use Mergado\Service\External\Pricemania\PricemaniaService;
use Mergado\Traits\SingletonTrait;
use Shop;

class PricemaniaForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Pricemania', 'pricemania'),
                'icon' => 'icon-cogs'
            ],
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
                    'name' => PricemaniaService::FIELD_ACTIVE,
                    'label' => $translateFunction('Verified shop', 'pricemania'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_pricemania_overeny_obchod_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_pricemania_overeny_obchod_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => PricemaniaService::FIELD_SHOP_ID,
                    'label' => $translateFunction('Pricemania shop ID', 'pricemania'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('Your unique Store ID from Pricemania.', 'pricemania'),
                    'visibility' => Shop::CONTEXT_ALL,
                ]
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        return $fields_form;
    }
}
