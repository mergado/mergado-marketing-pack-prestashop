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
use Mergado\Service\External\Sklik\SklikService;
use Mergado\Service\External\Zbozi\ZboziService;
use Mergado\Traits\SingletonTrait;
use Shop;

class SeznamForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getDefaultFieldValues(): array
    {
        $defaultValues = [];

        $defaultValues[ZboziService::FIELD_VAT_INCL] = ZboziService::getInstance()->getConversionVatIncluded();

        return array_merge($defaultValues, parent::getDefaultFieldValues());
    }

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('Sklik', 'seznam'),
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
                    'name' => SklikService::FIELD_CONVERSIONS_ACTIVE,
                    'label' => $translateFunction('Sklik track conversions', 'seznam'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'mergado_sklik_konverze_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_sklik_konverze_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => SklikService::FIELD_CONVERSIONS_CODE,
                    'label' => $translateFunction('Sklik conversion code', 'seznam'),
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find the code in Sklik → Tools → Conversion Tracking → Conversion Detail / Create New Conversion. The code is in the generated HTML conversion code after: id: ', 'seznam'),
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => SklikService::FIELD_CONVERSIONS_VALUE,
                    'label' => $translateFunction('Sklik value', 'seznam'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Leave blank to fill the order value automatically. Total price excluding VAT and shipping is calculated.', 'seznam'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => SklikService::FIELD_CONVERSION_VAT_INCL,
                    'label' => $translateFunction('Sklik conversions with VAT', 'seznam'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'sklik_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'sklik_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the conversion value will be sent with or without VAT. Note: In the specification of conversion tracking, Sklik recommends the conversion value to be excluding VAT.', 'seznam'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => SklikService::FIELD_RETARGETING_ACTIVE,
                    'label' => $translateFunction('Sklik retargting', 'seznam'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'seznam_retargeting_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'seznam_retargeting_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => SklikService::FIELD_RETARGETING_ID,
                    'label' => $translateFunction('Sklik retargeting ID', 'seznam'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('The code can be found in Sklik → Tools → Retargeting → View retargeting code. The code is in the generated script after: var list_retargeting_id = RETARGETING CODE', 'seznam'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ],
        ];

        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $translateFunction('Zbozi.cz', 'seznam'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'name' => ZboziService::FIELD_ACTIVE,
                    'label' => $translateFunction('Zbozi track conversions', 'seznam'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_zbozi_konverze_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_zbozi_konverze_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => ZboziService::FIELD_ADVANCED_ACTIVE,
                    'label' => $translateFunction('Standard conversion measuring', 'seznam'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'mergado_zbozi_advanced_konverze_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_zbozi_advanced_konverze_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Unlike limited tracking, Standard Conversion Tracking allows you to keep track of the number and value of conversions, as well as conversion rate, cost per conversion, direct conversions, units sold, etc', 'seznam'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => ZboziService::FIELD_SHOP_ID,
                    'label' => $translateFunction('Zbozi.cz store ID', 'seznam'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your unique store ID in admin page zbozi.cz > Branches > ESHOP > Conversion Tracking > Store ID', 'seznam'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => ZboziService::FIELD_KEY,
                    'label' => $translateFunction('Secret key', 'seznam'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your unique Secret Key in admin page zbozi.cz > Branches > ESHOP > Conversion Tracking > Your unique Secret Key.', 'seznam'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => ZboziService::FIELD_VAT_INCL,
                    'label' => $translateFunction('With VAT', 'seznam'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'zbozi_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'zbozi_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the conversion value will be sent with or without VAT. Note: In the specification of conversion tracking, Zboží.cz recommends the price of the order and shipping to be including VAT.', 'seznam'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Edit text of consent', 'seznam'),
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $translateFunction('Here you can edit the text of the sentence of consent to the sending of the questionnaire, displayed in the checkout page. This is an opt-out consent, ie the customer must confirm that he does not want to be involved in the program.', 'seznam'),
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
                'name' => ZboziService::FIELD_OPT_OUT . $langName,
                'label' => $translateFunction('Editing consent to the questionnaire', 'seznam') . ' ' . $langName,
                'type' => 'text',
                'visibility' => Shop::CONTEXT_ALL,
            ];
        }

        return $fields_form;
    }
}
