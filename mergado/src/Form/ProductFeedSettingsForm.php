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
use Mergado\Service\Feed\ProductFeed;
use Mergado\Traits\SingletonTrait;
use Shop;

class ProductFeedSettingsForm extends AbstractForm
{
    use SingletonTrait;

    protected function getDefaultFieldValues(): array
    {
        return ['clrCheckboxesProduct' => 1,
            'page' => 'feeds-product',
            'mmp-tab' => 'settings',
            'id_shop' => ShopHelper::getId(),
        ];
    }

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {
        $options = [
            [
                'id_option' => 'both',
                'name' => $translateFunction('Everywhere', 'feeds-product')
            ],
            [
                'id_option' => 'catalog',
                'name' => $translateFunction('Catalog', 'feeds-product')
            ],
            [
                'id_option' => 'search',
                'name' => $translateFunction('Search', 'feeds-product')
            ],
            [
                'id_option' => 'none',
                'name' => $translateFunction('Nowhere', 'feeds-product')
            ]
        ];

        $fields_form[0]['form'] = [
            'input' => [
                [
                    'type' => 'checkbox',
                    'label' => $translateFunction('Export cost elements?', 'feeds-product'),
                    'name' => 'm_export',
                    'values' => [
                        'query' =>
                            [
                                [
                                    'id_option' => 'wholesale_prices',
                                    'name' => $translateFunction('Yes', 'feeds-product')
                                ],
                            ],
                        'id' => 'id_option',
                        'name' => 'name'
                    ],
                    'hint' => $translateFunction('Choose whether to export COST and COST_VAT elements to the product feed.', 'feeds-product')
                ],
                [
                    'type' => 'checkbox',
                    'label' => $translateFunction('Export by visibility', 'feeds-product'),
                    'name' => 'what_to_export',
                    'values' => [
                        'query' => $options,
                        'id' => 'id_option',
                        'name' => 'name'],
                    'hint' => $translateFunction('Choose which products will be exported by visibility.', 'feeds-product')
                ],
            ]
        ];


        $fields_form[1]['form'] = [
            'input' => [
                [
                    'type' => 'checkbox',
                    'label' => $translateFunction('Export products with denied orders in Product feeds', 'feeds-product'),
                    'name' => 'mmp_export',
                    'values' => [
                        'query' =>
                            [
                                [
                                    'id_option' => 'denied_products',
                                    'name' => $translateFunction('Yes', 'feeds-product')
                                ],
                            ],
                        'id' => 'id_option',
                        'name' => 'name'
                    ],
                    'hint' => $translateFunction('By default, the module generates only products with allowed orders. By enabling this option, the module will also generate products with denied orders', 'feeds-product')
                ],
            ]
        ];

        $fields_form[2]['form'] = [
            'input' => [
                [
                    'label' => $translateFunction('Delivery days', 'feeds-product'),
                    'type' => 'text',
                    'name' => 'delivery_days',
                    'hint' => $translateFunction('In how many days can you delivery the product when it is out of stock', 'feeds-product'),
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('If not filled in, the value from the field "Label of out-of-stock products with allowed backorders"', 'feeds-product'),
                    'visibility' => Shop::CONTEXT_ALL
                ]
            ],
        ];

        $fields_form[3]['form'] = [
            'input' => [
                [
                    'label' => $translateFunction('Change the number of products per batch (Change only if advised by our support team)', 'feeds-product'),
                    'name' => ProductFeed::USER_ITEM_COUNT_PER_STEP_DB_NAME,
                    'validation' => 'isInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => $translateFunction('Leave blank to generate the entire XML feed at once.', 'feeds-product'),
                    'hint' => $translateFunction('Changing the batch size could seriously effect the performance of your website. We advice against changing the batch size if you are unsure about its effects!<br><br>Default number is set to 1500 items per batch step.', 'feeds-product')
                ],
            ],
        ];

        $fields_form[4]['form'] = [
            'input' => [
                [
                    'type' => 'hidden',
                    'name' => 'page'
                ],
                [
                    'type' => 'hidden',
                    'name' => 'mmp-tab',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'id_shop'
                ],
                [
                    'type' => 'hidden',
                    'name' => 'clrCheckboxesProduct'
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save', 'feeds-product'),
                'name' => 'submit' . $moduleName
            ]
        ];

        return $fields_form;
    }
}
