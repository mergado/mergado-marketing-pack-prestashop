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


namespace Mergado\Form;

use Mergado\Helper\ShopHelper;
use Mergado\Service\Feed\CategoryFeed;
use Mergado\Service\Feed\StaticFeed;
use Mergado\Service\Feed\StockFeed;
use Mergado\Service\ProductPriceImportService;
use Mergado\Traits\SingletonTrait;
use Shop;

class OtherFeedsSettingsForm extends AbstractForm
{
    use SingletonTrait;

    protected function getDefaultFieldValues(): array
    {
        return [
            'page' => 'feeds-other',
            'id_shop' => ShopHelper::getId(),
            'clrCheckboxesOther' => 1,
            'mmp-tab' => 'settings'
        ];
    }

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {
        $fields_form[0]['form'] = [
            'input' => [
                [
                    'label' => $translateFunction('Number of categories for Category feed', 'feeds-other'),
                    'name' => CategoryFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME,
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => $translateFunction('Leave blank to generate the entire XML feed at once.', 'feeds-other'),
                    'hint' => $translateFunction('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects! Default number is set to 3000 items per batch step.', 'feeds-other')
                ],
            ],
        ];

        $fields_form[1]['form'] = [
            'input' => [
                [
                    'label' => $translateFunction('Number of products for Analytical feed', 'feeds-other'),
                    'name' => StockFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME,
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => $translateFunction('Leave blank to generate the entire XML feed at once.', 'feeds-other'),
                    'hint' => $translateFunction('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects! Default number is set to 5000 items per batch step.', 'feeds-other')
                ],
            ],
        ];

        $fields_form[2]['form'] = [
            'input' => [
                [
                    'label' => $translateFunction('Number of products for Heureka Availability feed', 'feeds-other'),
                    'name' => StaticFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME,
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => $translateFunction('Leave blank to generate the entire XML feed at once.', 'feeds-other'),
                    'hint' => $translateFunction('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects! Default number is set to 5000 items per batch step.', 'feeds-other')
                ],
            ],
        ];

        $fields_form[3]['form'] = [
            'input' => [
                [
                    'label' => $translateFunction('Number of products imported in one cron run', 'feeds-other'),
                    'name' => ProductPriceImportService::USER_ITEM_COUNT_PER_STEP_DB_NAME,
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => $translateFunction('Leave blank to import the entire XML feed at once.', 'feeds-other'),
                    'hint' => $translateFunction('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects! Default number is set to 3000 items per batch step.', 'feeds-other')
                ],
            ],
        ];

        $fields_form[4]['form'] = [
            'input' => [
                [
                    'type' => 'checkbox',
                    'label' => $translateFunction('Export products with denied orders in Other feeds', 'feeds-other'),
                    'name' => 'mmp_export',
                    'values' => [
                        'query' =>
                            [
                                [
                                    'id_option' => 'denied_products_other',
                                    'name' => $translateFunction('Yes', 'feeds-other')
                                ],
                            ],
                        'id' => 'id_option',
                        'name' => 'name'
                    ],
                    'hint' => $translateFunction('By default, the module generates only products with allowed orders. By enabling this option, the module will also generate products with denied orders', 'feeds-other')
                ],
            ]
        ];

        $fields_form[5]['form'] = [
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
                    'type' => 'hidden',
                    'name' => 'mmp-tab',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'clrCheckboxesOther'
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save', 'feeds-other'),
                'name' => 'submit' . $moduleName
            ]
        ];

        return $fields_form;
    }
}
