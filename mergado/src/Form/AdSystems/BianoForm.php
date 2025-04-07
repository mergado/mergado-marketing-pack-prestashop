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

use Language;
use Mergado\Helper\LanguageHelper;
use Mergado\Service\External\Biano\Biano\BianoService;
use Mergado\Service\External\Biano\BianoStar\BianoStarService;
use Mergado\Traits\SingletonTrait;
use Shop;

class BianoForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Biano pixel', 'biano'),
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
            'name' => BianoService::FIELD_ACTIVE,
            'label' => $translateFunction('Module active', 'biano'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => 'switch',
            'class' => 'class-mmp-activity-check-checkbox',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Biano Pixel merchantId is available only for CZ, SK, HU, RO, NL languages. Other languages will be using default option.', 'biano'),
            'values' => [
                [
                    'id' => BianoService::FIELD_ACTIVE . '_on',
                    'value' => 1,
                    'label' => $translateFunction('Yes')
                ],
                [
                    'id' => BianoService::FIELD_ACTIVE . '_off',
                    'value' => 0,
                    'label' => $translateFunction('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ];

        foreach (Language::getLanguages(true) as $key => $lang) {
            $langName = LanguageHelper::getLang(strtoupper($lang['iso_code']));

            $fields_form[0]['form']['input'][] = [
                'name' => BianoService::getActiveLangFieldName($langName),
                'label' => $langName,
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'switch',
                'values' => [
                    [
                        'id' => 'biano_active_on',
                        'value' => 1,
                        'label' => $translateFunction('Yes')
                    ],
                    [
                        'id' => 'biano_active_off',
                        'value' => 0,
                        'label' => $translateFunction('No')
                    ]
                ],
                'visibility' => Shop::CONTEXT_ALL,
            ];

            if(in_array($langName, BianoService::LANG_OPTIONS)) {
                $fields_form[0]['form']['input'][] = [
                    'name' => BianoService::getMerchantIdFieldName($langName),
                    'label' => $translateFunction('Merchant ID', 'biano') . ' ' . $langName,
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('You can get your merchant ID in your Biano account > Optimization > Biano pixel.', 'biano'),
                    'visibility' => Shop::CONTEXT_ALL,
                ];
            }
        }

        $fields_form[0]['form']['input'][] = [
            'name' => BianoService::FIELD_CONVERSION_VAT_INCl,
            'label' => $translateFunction('With VAT', 'biano'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => 'switch',
            'values' => [
                [
                    'id' => 'biano_active_on',
                    'value' => 1,
                    'label' => $translateFunction('Yes')
                ],
                [
                    'id' => 'biano_active_off',
                    'value' => 0,
                    'label' => $translateFunction('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the conversion value will be sent with or without VAT.', 'biano'),
            'visibility' => Shop::CONTEXT_ALL,
        ];

        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $translateFunction('Biano Star', 'biano'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => BianoStarService::FIELD_ACTIVE,
                    'label' => $translateFunction('Active', 'biano'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => BianoStarService::FIELD_ACTIVE . 'on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => BianoStarService::FIELD_ACTIVE . 'off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Biano Star is dependent on Biano Pixel. You must first activate the Pixel function and then Biano Star.', 'biano'),
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Edit consent to the questionnaire', 'biano'),
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $translateFunction('Here you can edit the sentence of the consent to the sending of the questionnaire, displayed on the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'biano'),
                ],
                [
                    'name' => BianoStarService::FIELD_SHIPMENT_IN_STOCK,
                    'label' => $translateFunction('In stock', 'biano'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => BianoStarService::FIELD_SHIPMENT_BACKORDER,
                    'label' => $translateFunction('backorder', 'biano'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => BianoStarService::FIELD_SHIPMENT_OUT_OF_STOCK,
                    'label' => $translateFunction('out of stock', 'biano'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Edit consent to the questionnaire', 'biano'),
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $translateFunction('Here you can edit the sentence of the consent to the sending of the questionnaire, displayed on the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'biano'),
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        foreach (Language::getLanguages(true) as $key => $lang) {
            $langName = LanguageHelper::getLang(strtoupper($lang['iso_code']));

            $fields_form[1]['form']['input'][] = [
                'name' => BianoStarService::OPT_OUT . $langName,
                'label' => $langName,
                'type' => 'text',
                'visibility' => Shop::CONTEXT_ALL,
            ];
        }

        return $fields_form;
    }
}
