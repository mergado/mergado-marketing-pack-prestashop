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
use Mergado\Service\External\Heureka\AbstractBaseHeurekaService;
use Mergado\Service\External\Heureka\HeurekaCZService;
use Mergado\Service\External\Heureka\HeurekaSKService;
use Mergado\Traits\SingletonTrait;
use Shop;

class HeurekaForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getDefaultFieldValues(): array
    {
        $defaultValues = [];

        // CZ
        $defaultValues[HeurekaCZService::FIELD_VERIFIED_WITH_ITEMS] = HeurekaCZService::getInstance()->getVerifiedWithItems();
        $defaultValues[HeurekaCZService::FIELD_LEGACY_CONVERSION_VAT_INCL] = HeurekaCZService::getInstance()->getLegacyConversionsVatIncluded();

        // SK
        $defaultValues[HeurekaSKService::FIELD_VERIFIED_WITH_ITEMS] = HeurekaSKService::getInstance()->getVerifiedWithItems();
        $defaultValues[HeurekaSKService::FIELD_LEGACY_CONVERSION_VAT_INCL] = HeurekaSKService::getInstance()->getLegacyConversionsVatIncluded();

        return array_merge($defaultValues, parent::getDefaultFieldValues());
    }

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        // Heureka.cz - VERIFIED
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Heureka.cz : Verified by customers', 'heureka'),
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
                    'name' => HeurekaCZService::FIELD_VERIFIED,
                    'label' => $translateFunction('Heureka.cz verified by users', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_heureka_overeno_zakazniky_cz_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_heureka_overeno_zakazniky_cz_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL
                ],
                [
                    'name' => HeurekaCZService::FIELD_VERIFIED_CODE,
                    'label' => $translateFunction('Heureka.cz verified by users code', 'heureka'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your store key in the Heureka account administration under Verified customers > Settings and questionnaire data > Secret Key for verified customers.', 'heureka')
                ],
                [
                    'name' => HeurekaCZService::FIELD_VERIFIED_WITH_ITEMS,
                    'label' => $translateFunction('Send items', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'heureka_send_items_cz_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'heureka_send_items_cz_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the items  will be sent in questionnaire email.', 'heureka'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Text of the customer\'s consent to sending the questionnaire', 'heureka'),
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<a href="#consent-text" class="mmp_btn__blue mmp_btn__blue--small" style="font-style: normal;">' . $translateFunction('Edit consent text', 'heureka') . '</a>',
                ],
                [
                    'name' => HeurekaCZService::FIELD_WIDGET,
                    'label' => $translateFunction('Heureka.cz - widget', 'heureka'),
                    'hint' => $translateFunction('You need conversion code to enable this feature', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_heureka_widget_cz_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_heureka_widget_cz_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => HeurekaCZService::FIELD_WIDGET_ID,
                    'label' => $translateFunction('Widget Id', 'heureka'),
                    'type' => 'text',
                    'placeholder' => 'Insert Widget Id',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('The ID is the same as the Public Key for conversion tracking. Or you can find the key of your widget in the Heureka account administration under the tab Verified customers > Settings and questionnaire data > Certificate icons Verified customers. The numeric code is in the embed code. It takes the form "... setKey\',\'330BD_YOUR_WIDGET_KEY_2A80\']); _ hwq.push\' ..."', 'heureka')
                ],
                [
                    'name' => HeurekaCZService::FIELD_WIDGET_POSITION,
                    'label' => $translateFunction('Widget position', 'heureka'),
                    'type' => 'select',
                    'options' => [
                        'query' => [
                            ['id_option' => AbstractBaseHeurekaService::POSITION_LEFT, 'name' => 'Left'],
                            ['id_option' => AbstractBaseHeurekaService::POSITION_RIGHT, 'name' => 'Right'],
                        ],
                        'id' => 'id_option',
                        'name' => 'name'
                    ],
                ],
                [
                    'name' => HeurekaCZService::FIELD_WIDGET_TOP_MARGIN,
                    'label' => $translateFunction('Widget top margin', 'heureka'),
                    'type' => 'text',
                    'placeholder' => '60',
                    'suffix' => 'px',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        // Heureka.cz - COVNERSIONS
        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $translateFunction('Heureka.cz - conversions', 'arukereso'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => HeurekaCZService::FIELD_CONVERSIONS_ACTIVE,
                    'label' => $translateFunction('Enable conversions', 'arukereso'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => HeurekaCZService::FIELD_CONVERSIONS_ACTIVE . '_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => HeurekaCZService::FIELD_CONVERSIONS_ACTIVE . '_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => HeurekaCZService::FIELD_CONVERSIONS_API_KEY,
                    'label' => $translateFunction('API key', 'arukereso'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You will find the WebAPI key in the Arukereso portal under Statisztikák > Konverziómérés', 'arukereso'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[2]['form'] = [
            'legend' => [
                'title' => $translateFunction('Heureka.cz : Conversions tracking - legacy', 'heureka'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => HeurekaCZService::FIELD_LEGACY_CONVERSIONS,
                    'label' => $translateFunction('Heureka.cz track conversions', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_heureka_konverze_cz_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_heureka_konverze_cz_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => HeurekaCZService::FIELD_LEGACY_CONVERSIONS_CODE,
                    'label' => $translateFunction('Heureka.cz conversion code', 'heureka'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your store conversion tracking key in the Heureka account administration under the Statistics and Reports > Conversion Tracking > Public Key for Conversion Tracking Code.', 'heureka')
                ],
                [
                    'name' => HeurekaCZService::FIELD_LEGACY_CONVERSION_VAT_INCL,
                    'label' => $translateFunction('With VAT', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'heureka_conv_cz_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'heureka_conv_cz_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the conversion value will be sent with or without VAT. Note: In the specification of conversion tracking, Heureka recommends the price of the order and shipping to be including VAT.', 'heureka'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        // Heureka.sk - VERIFIED
        $fields_form[3]['form'] = [
            'legend' => [
                'title' => $translateFunction('Heureka.sk : Verified by customers', 'heureka'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => HeurekaSKService::FIELD_VERIFIED,
                    'label' => $translateFunction('Heureka.sk verified by users', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_heureka_overeno_zakazniky_sk_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_heureka_overeno_zakazniky_sk_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => HeurekaSKService::FIELD_VERIFIED_CODE,
                    'label' => $translateFunction('Heureka.sk verified by users code', 'heureka'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your store key in the Heureka account administration under Verified customers > Settings and questionnaire data > Secret Key for verified customers.', 'heureka')
                ],
                [
                    'name' => HeurekaSKService::FIELD_VERIFIED_WITH_ITEMS,
                    'label' => $translateFunction('Send items', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'heureka_send_items_sk_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'heureka_send_items_sk_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the items  will be sent in questionnaire email.', 'heureka'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => HeurekaSKService::FIELD_WIDGET,
                    'label' => $translateFunction('Heureka.sk - widget', 'heureka'),
                    'hint' => $translateFunction('You need conversion code to enable this feature', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_heureka_widget_sk_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_heureka_widget_sk_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Text of the customer\'s consent to sending the questionnaire', 'heureka'),
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<a href="#consent-text" class="mmp_btn__blue mmp_btn__blue--small" style="font-style: normal;">' . $translateFunction('Edit consent text', 'heureka') . '</a>',
                ],
                [
                    'name' => HeurekaSKService::FIELD_WIDGET_ID,
                    'label' => $translateFunction('Widget Id', 'heureka'),
                    'type' => 'text',
                    'placeholder' => 'Insert Widget Id',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('The ID is the same as the Public Key for conversion tracking. Or you can find the key of your widget in the Heureka account administration under the tab Verified customers > Settings and questionnaire data > Certificate icons Verified customers. The numeric code is in the embed code. It takes the form "... setKey\',\'330BD_YOUR_WIDGET_KEY_2A80\']); _ hwq.push\' ..."', 'heureka')
                ],
                [
                    'name' => HeurekaSKService::FIELD_WIDGET_POSITION,
                    'label' => $translateFunction('Widget position', 'heureka'),
                    'type' => 'select',
                    'suffix' => 'px',
                    'options' => [
                        'query' => [
                            ['id_option' => 21, 'name' => 'Left'],
                            ['id_option' => 22, 'name' => 'Right'],
                        ],
                        'id' => 'id_option',
                        'name' => 'name'
                    ],
                ],
                [
                    'name' => HeurekaSKService::FIELD_WIDGET_TOP_MARGIN,
                    'label' => $translateFunction('Widget top margin', 'heureka'),
                    'type' => 'text',
                    'placeholder' => '60',
                    'suffix' => 'px',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[4]['form'] = [
            'legend' => [
                'title' => $translateFunction('Heureka.sk - conversions', 'arukereso'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => HeurekaSKService::FIELD_CONVERSIONS_ACTIVE,
                    'label' => $translateFunction('Enable conversions', 'arukereso'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => HeurekaSKService::FIELD_CONVERSIONS_ACTIVE . '_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => HeurekaSKService::FIELD_CONVERSIONS_ACTIVE . '_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => HeurekaSKService::FIELD_CONVERSIONS_API_KEY,
                    'label' => $translateFunction('API key', 'arukereso'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You will find the WebAPI key in the Arukereso portal under Statisztikák > Konverziómérés', 'arukereso'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[5]['form'] = [
            'legend' => [
                'title' => $translateFunction('Heureka.sk : Conversions tracking', 'heureka'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => HeurekaSKService::FIELD_LEGACY_CONVERSIONS,
                    'label' => $translateFunction('Heureka.sk track conversions - legacy', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_heureka_konverze_sk_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_heureka_konverze_sk_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => HeurekaSKService::FIELD_LEGACY_CONVERSIONS_CODE,
                    'label' => $translateFunction('Heureka.sk conversion code', 'heureka'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your store conversion tracking key in the Heureka account administration under the Statistics and Reports > Conversion Tracking > Public Key for Conversion Tracking Code.', 'heureka')
                ],
                [
                    'name' => HeurekaSKService::FIELD_LEGACY_CONVERSION_VAT_INCL,
                    'label' => $translateFunction('With VAT', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'heureka_conv_sk_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'heureka_conv_sk_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the conversion value will be sent with or without VAT. Note: In the specification of conversion tracking, Heureka recommends the price of the order and shipping to be including VAT.', 'heureka'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[6]['form'] = [
            'legend' => [
                'title' => $translateFunction('Heureka : Other settings', 'heureka'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => 'mergado_heureka_dostupnostni_feed',
                    'label' => $translateFunction('Heureka stock feed', 'heureka'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'mergado_heureka_dostupnostni_feed_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_heureka_dostupnostni_feed_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('After activation, the Heureka availability feed will be available in the XML feed tab.', 'heureka'),
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Edit text of consent', 'heureka'),
                    'type' => 'text',
                    'id' => 'consent-text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $translateFunction('Here you can edit the text of the sentence of consent to the sending of the questionnaire, displayed in the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'heureka'),
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        foreach (Language::getLanguages(true) as $key => $lang) {
            $langName = LanguageHelper::getLang(strtoupper($lang['iso_code']));

            $fields_form[6]['form']['input'][] = [
                'name' => 'mergado_heureka_opt_out_text' . '-' . $langName,
                'label' => $translateFunction('Editing consent to the questionnaire', 'heureka') . ' ' . $langName,
                'type' => 'text',
                'visibility' => Shop::CONTEXT_ALL,
            ];
        }

        return $fields_form;
    }
}
