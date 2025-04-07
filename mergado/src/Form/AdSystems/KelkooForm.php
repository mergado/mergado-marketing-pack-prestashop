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

use Mergado\Service\External\Kelkoo\KelkooService;
use Mergado\Traits\SingletonTrait;
use Shop;

class KelkooForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Kelkoo', 'kelkoo'),
                'icon' => 'icon-cogs',
            ],
            'input' => [],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[0]['form']['input'][] = [
            'name' => KelkooService::FIELD_ACTIVE,
            'label' => $translateFunction('Module active', 'kelkoo'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => 'switch',
//    'desc' => $translateFunction('1. Your website must have HTTPS protocol at least on order confirmation page. 2. You have to set your DNS before use. More informations on: https://www.glami.cz/info/reviews/implementation/') . '<br><span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your Glami TOP API key in the Glami Administration at the Glami TOP page > Implementation > Developer Implementation Guide> Javascript Integration section.', 'kelkoo'),
            'class' => 'class-mmp-activity-check-checkbox',
            'values' => [
                [
                    'id' => 'kelkoo_active_on',
                    'value' => 1,
                    'label' => $translateFunction('Yes')
                ],
                [
                    'id' => 'kelkoo_active_off',
                    'value' => 0,
                    'label' => $translateFunction('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ];

        $fields_form[0]['form']['input'][] = [
            'name' => KelkooService::FIELD_COUNTRY,
            'label' => $translateFunction('Kelkoo country', 'kelkoo'),
            'type' => 'select',
            'options' => [
                'query' => KelkooService::COUNTRIES,
                'id' => 'id_option',
                'name' => 'name'
            ]
        ];

        $fields_form[0]['form']['input'][] = [
            'name' => KelkooService::FIELD_COM_ID,
            'label' => $translateFunction('Kelkoo merchant id', 'kelkoo'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ];

        $fields_form[0]['form']['input'][] = [
            'name' => KelkooService::FIELD_CONVERSION_VAT_INCL,
            'label' => $translateFunction('With VAT', 'kelkoo'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => 'switch',
            'values' => [
                [
                    'id' => 'kelkoo_active_on',
                    'value' => 1,
                    'label' => $translateFunction('Yes')
                ],
                [
                    'id' => 'kelkoo_active_off',
                    'value' => 0,
                    'label' => $translateFunction('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the conversion value will be sent with or without VAT.', 'kelkoo'),
            'visibility' => Shop::CONTEXT_ALL,
        ];

        return $fields_form;
    }
}
