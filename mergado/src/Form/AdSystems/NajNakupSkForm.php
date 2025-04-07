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

use Mergado\Service\External\NajNakup\NajNakupService;
use Mergado\Traits\SingletonTrait;
use Shop;

class NajNakupSkForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Najnakup.sk', 'najnakupsk'),
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
                    'name' => NajNakupService::FIELD_CONVERSIONS,
                    'label' => $translateFunction('Najnakup track conversions', 'najnakupsk'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_najnakup_konverze_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_najnakup_konverze_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => NajNakupService::FIELD_SHOP_ID,
                    'label' => $translateFunction('Najnakup shop ID', 'najnakupsk'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('Your unique store ID for Najnakup.sk', 'najnakupsk'),
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
