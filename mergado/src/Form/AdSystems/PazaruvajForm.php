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
use Mergado\Service\External\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\Traits\SingletonTrait;
use Shop;

class PazaruvajForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {
        $defaultFields = [
            'input' => [
                [
                    'name' => PazaruvajService::FIELD_ACTIVE,
                    'label' => $translateFunction('Enable Trusted Shop', 'pazaruvaj'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_pazaruvaj_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_pazaruvaj_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => PazaruvajService::FIELD_WEB_API_KEY,
                    'label' => $translateFunction('WebAPI key', 'pazaruvaj'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You will find the WebAPI key in the Arukereso portal under Megbízható Bolt Program > Csatlakozás > Árukereső WebAPI kulcs', 'pazaruvaj'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Editing consent to the questionnaire', 'pazaruvaj'),
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $translateFunction(' Here you can edit the sentence of the consent to the sending of the questionnaire, displayed on the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'pazaruvaj'),
                ],
            ]
        ];

        foreach (Language::getLanguages(true) as $key => $lang) {
            $langName = LanguageHelper::getLang(strtoupper($lang['iso_code']));

            $defaultFields['input'][] = [
                'name' => PazaruvajService::FIELD_OPT_OUT . $langName,
                'label' => $langName,
                'type' => 'text',
                'visibility' => Shop::CONTEXT_ALL,
            ];
        }

        $widgetFields = [
            [
                'name' => PazaruvajService::FIELD_WIDGET_ACTIVE,
                'label' => $translateFunction('Enable widget Trusted Shop', 'pazaruvaj'),
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'switch',
                'values' => [
                    [
                        'id' => 'mergado_pazaruvaj_widget_on',
                        'value' => 1,
                        'label' => $translateFunction('Yes')
                    ],
                    [
                        'id' => 'mergado_pazaruvaj_widget_off',
                        'value' => 0,
                        'label' => $translateFunction('No')
                    ]
                ],
                'visibility' => Shop::CONTEXT_ALL,
            ],
            [
                'name' => PazaruvajService::FIELD_WIDGET_DESKTOP_POSITION,
                'label' => $translateFunction('Widget position on desktop', 'pazaruvaj'),
                'type' => 'select',
                'options' => [
                    'query' => PazaruvajService::DESKTOP_POSITIONS($translateFunction),
                    'id' => 'id_option',
                    'name' => 'name'
                ],
            ],
            [
                'name' => PazaruvajService::FIELD_WIDGET_APPEARANCE_TYPE,
                'label' => $translateFunction('Appearance type on desktop', 'pazaruvaj'),
                'type' => 'select',
                'class' => 'w-auto-i',
                'options' => [
                    'query' => PazaruvajService::APPEARANCE_TYPES($translateFunction),
                    'id' => 'id_option',
                    'name' => 'name'
                ],
            ],
            [
                'name' => PazaruvajService::FIELD_WIDGET_MOBILE_POSITION,
                'label' => $translateFunction('Widget position on mobile', 'pazaruvaj'),
                'type' => 'select',
                'options' => [
                    'query' => PazaruvajService::MOBILE_POSITIONS($translateFunction),
                    'id' => 'id_option',
                    'name' => 'name'
                ],
            ],
            [
                'name' => PazaruvajService::FIELD_WIDGET_MOBILE_WIDTH,
                'label' => $translateFunction('Width on the mobile', 'pazaruvaj'),
                'type' => 'text',
                'suffix' => 'px',
                'visibility' => Shop::CONTEXT_ALL,
            ],
        ];

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Pazaruvaj Trusted Shop', 'pazaruvaj'),
                'icon' => 'icon-cogs'
            ],
            'input' => array_merge($defaultFields['input'], $widgetFields),
            'submit' => [
                'title' => $translateFunction('Save', 'mergado'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $translateFunction('Pazaruvaj Conversions', 'pazaruvaj'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => PazaruvajService::FIELD_CONVERSIONS_ACTIVE,
                    'label' => $translateFunction('Enable conversions', 'pazaruvaj'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'mmp-pazaruvaj-conversion-active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mmp-pazaruvaj-conversion-active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => PazaruvajService::FIELD_CONVERSIONS_API_KEY,
                    'label' => $translateFunction('API key', 'pazaruvaj'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You will find the WebAPI key in the Arukereso portal under Statisztikák > Konverziómérés', 'pazaruvaj'),
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
