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

use Mergado\Service\External\Facebook\FacebookService;
use Mergado\Traits\SingletonTrait;
use Shop;

class FacebookForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Facebook pixel', 'facebook'),
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
                    'name' => FacebookService::FIELD_ACTIVE,
                    'label' => $translateFunction('Facebook pixel', 'facebook'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'fb_pixel_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'fb_pixel_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => FacebookService::FIELD_CODE,
                    'label' => $translateFunction('Facebook pixel ID', 'facebook'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('Pixel ID can be found in your Facebook Business Manager. Go to Events Manager > Add new data feed > Facebook pixel. Pixel ID is displayed below the title on the Overview page at the top left.', 'facebook'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => FacebookService::FIELD_CONVERSION_VAT_INCL,
                    'label' => $translateFunction('With VAT', 'facebook'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'fbpixel_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'fbpixel_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the conversion value will be sent with or without VAT.', 'facebook'),
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
