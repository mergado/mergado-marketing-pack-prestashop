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

use Mergado\Service\External\Glami\GlamiService;
use Mergado\Traits\SingletonTrait;
use Shop;

class GlamiForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Glami pixel', 'glami'),
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
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[0]['form']['input'][] = [
            'name' => GlamiService::FIELD_PIXEL_ACTIVE,
            'label' => $translateFunction('Module active', 'glami'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => 'switch',
            'class' => 'class-mmp-activity-check-checkbox',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your piXel in the Glami Administration at Glami piXel page > Implementing Glami piXel for Developers > Glami piXel Code section for YOUR ESHOP', 'glami'),
            'values' => [
                [
                    'id' => 'glami_active_on',
                    'value' => 1,
                    'label' => $translateFunction('Yes')
                ],
                [
                    'id' => 'glami_active_off',
                    'value' => 0,
                    'label' => $translateFunction('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ];

        $fields_form[0]['form']['input'][] = [
            'name' => GlamiService::FIELD_PIXEL_CONVERSION_VAT_INCLUDED,
            'label' => $translateFunction('With VAT'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => 'switch',
            'values' => [
                [
                    'id' => 'glam_conv_active_on',
                    'value' => 1,
                    'label' => $translateFunction('Yes')
                ],
                [
                    'id' => 'glam_conv_active_off',
                    'value' => 0,
                    'label' => $translateFunction('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the conversion value will be sent with or without VAT.', 'glami'),
            'visibility' => Shop::CONTEXT_ALL,
        ];

        foreach (GlamiService::PIXEL_LANGUAGES as $key => $lang) {
            $fields_form[0]['form']['input'][] = [
                'name' => $lang,
                'label' => $key,
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'switch',
                'values' => [
                    [
                        'id' => 'glami_active_on',
                        'value' => 1,
                        'label' => $translateFunction('Yes')
                    ],
                    [
                        'id' => 'glami_active_off',
                        'value' => 0,
                        'label' => $translateFunction('No')
                    ]
                ],
                'visibility' => Shop::CONTEXT_ALL,
            ];

            $fields_form[0]['form']['input'][] = [
                'name' => GlamiService::FIELD_PIXEL_CODE_PREFIX . '-' . $key,
                'label' => $translateFunction('Glami Pixel', 'glami') . ' ' . $key,
                'type' => 'text',
                'visibility' => Shop::CONTEXT_ALL,
            ];
        }

        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $translateFunction('Glami TOP', 'glami'),
                'icon' => 'icon-cogs',
            ],
            'input' => [],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[1]['form']['input'][] = [
            'name' => GlamiService::FIELD_TOP_ACTIVE,
            'label' => $translateFunction('Module active', 'glami'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => 'switch',
            'desc' => $translateFunction('1. Your website must have HTTPS protocol at least on order confirmation page. 2. You have to set your DNS before use. More informations on: https://www.glami.cz/info/reviews/implementation/', 'glami') . '<br><span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your Glami TOP API key in the Glami Administration at the Glami TOP page > Implementation > Developer Implementation Guide> Javascript Integration section.', 'glami'),
            'class' => 'class-mmp-activity-check-checkbox',
            'values' => [
                [
                    'id' => 'glami_top_active_on',
                    'value' => 1,
                    'label' => $translateFunction('Yes')
                ],
                [
                    'id' => 'glami_top_active_off',
                    'value' => 0,
                    'label' => $translateFunction('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ];

        $fields_form[1]['form']['input'][] = [
            'name' => GlamiService::FIELD_TOP_SELECTION,
            'label' => $translateFunction('Glami website', 'glami'),
            'type' => 'select',
            'options' => [
                'query' => GlamiService::PIXEL_TOP_LANGUAGES,
                'id' => 'id_option',
                'name' => 'name'
            ]
        ];

        $fields_form[1]['form']['input'][] = [
            'name' => GlamiService::FIELD_TOP_CODE,
            'label' => $translateFunction('Glami TOP', 'glami'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ];

        return $fields_form;
    }
}
