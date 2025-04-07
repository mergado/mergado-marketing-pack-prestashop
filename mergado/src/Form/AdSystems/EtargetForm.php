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

use Mergado\Service\External\Etarget\EtargetService;
use Mergado\Traits\SingletonTrait;
use Shop;

class EtargetForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Etarget', 'etarget'),
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
                    'name' => EtargetService::FIELD_ACTIVE,
                    'label' => $translateFunction('ETARGET', 'etarget'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'etarget_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'etarget_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => EtargetService::FIELD_ID,
                    'label' => $translateFunction('ETARGET ID', 'etarget'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => EtargetService::FIELD_HASH,
                    'label' => $translateFunction('Hash', 'etarget'),
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
