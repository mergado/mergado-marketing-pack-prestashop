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

use Mergado\Service\External\Google\GaRefundService;
use Mergado\Service\External\Google\GoogleAds\GoogleAdsService;
use Mergado\Service\External\Google\GoogleAnalytics4\GoogleAnalytics4Service;
use Mergado\Service\External\Google\GoogleReviews\GoogleReviewsService;
use Mergado\Service\External\Google\GoogleTagManager\GoogleTagManagerService;
use Mergado\Service\External\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService;
use Mergado\Traits\SingletonTrait;
use OrderState;
use Shop;

class GoogleForm extends AbstractAdSystemsForm
{
    use SingletonTrait;

    protected function getDefaultFieldValues(): array
    {
        $defaultValues = [];

        // GTM
        $defaultValues[GoogleTagManagerService::FIELD_CONVERSION_VAT_INCL] = GoogleTagManagerService::getInstance()->getConversionVatIncluded();

        // GUA
        $defaultValues[GoogleUniversalAnalyticsService::FIELD_CONVERSION_VAT_INCL] = GoogleUniversalAnalyticsService::getInstance()->getConversionVatIncluded();

        // GA4
        $defaultValues[GoogleAnalytics4Service::FIELD_SHIPPING_PRICE_INCL] = GoogleAnalytics4Service::getInstance()->getShippingPriceIncluded();
        $defaultValues[GoogleAnalytics4Service::FIELD_CONVERSION_VAT_INCL] = GoogleAnalytics4Service::getInstance()->getConversionVatIncluded();

        // GADS
        $defaultValues[GoogleAdsService::FIELD_CONVERSIONS_VAT_INCLUDED] = GoogleAdsService::getInstance()->getConversionVatIncluded();
        $defaultValues[GoogleAdsService::FIELD_CONVERSIONS_SHIPPING_PRICE_INCLUDED] = GoogleAdsService::getInstance()->getConversionShippingPriceIncluded();

        return array_merge($defaultValues, parent::getDefaultFieldValues());
    }

    protected function getFormDefinition($module, $moduleName, $translateFunction): array
    {

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $translateFunction('GoogleAds', 'google'),
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
                    'name' => GoogleAdsService::FIELD_CONVERSIONS_ACTIVE,
                    'label' => $translateFunction('GoogleAds conversions', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_adwords_conversion_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_adwords_conversion_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAdsService::FIELD_ENHANCED_CONVERSION_ACTIVE,
                    'label' => $translateFunction('GoogleAds enhanced conversions', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'mergado_adwords_enhanced_conversion_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_adwords_enhanced_conversion_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAdsService::FIELD_CONVERSIONS_SHIPPING_PRICE_INCLUDED,
                    'label' => $translateFunction('Conversion value with shipping price', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'gads_shipping_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'gads_shipping_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the value of purchase will be with or without shipping.', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAdsService::FIELD_CONVERSIONS_VAT_INCLUDED,
                    'label' => $translateFunction('Products prices with VAT', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'gads_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'gads_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the price of the products will be sent with or without VAT.', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAdsService::FIELD_REMARKETING_ACTIVE,
                    'label' => $translateFunction('GoogleAds remarketing', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'adwords_remarketing_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'adwords_remarketing_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can get a Remarketing ID in your Google Ads account administration > Tools & Settings > Shared library > Audience Manager > Audience Sources > Set Google Ads Tag. Create a new tag, then click Install Tag Yourself. The code is located in the "Global Site Tag" section and has the form AW-123456789.', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAdsService::FIELD_REMARKETING_TYPE,
                    'label' => $translateFunction('Select Business type', 'google'),
                    'type' => 'select',
                    'class' => 'w-auto-i',
                    'options' => [
                        'query' => GoogleAdsService::REMARKETING_TYPES,
                        'id' => 'id_option',
                        'name' => 'name'
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('Mergado Pack supports tag implementations for ecommerce solutions only. If your business type is not ecommerce, select Custom.', 'google'),
                ],
                [
                    'name' => GoogleAdsService::FIELD_CONVERSIONS_CODE,
                    'label' => $translateFunction('GoogleAds code', 'google'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('Get the Conversion code in your Google Ads Account Administration > Tools & Settings > MEASUREMENT - Conversions > Add Conversion > Website. Create a new conversion, then click Install the tag yourself. The code is located in the “Global Site Tag” section and takes the form of AW-123456789.', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAdsService::FIELD_CONVERSIONS_LABEL,
                    'label' => $translateFunction('GoogleAds conversion label', 'google'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find the Conversion Label on the same page as the conversion code. The label is located in the “Event fragment” section of the send_to element, after the slash. For example, it has the form of /SqrGHAdS-MerfQC.', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $translateFunction('Google Universal analytics - gtag.js', 'google'),
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
                    'name' => GoogleUniversalAnalyticsService::FIELD_ACTIVE,
                    'label' => $translateFunction('Module active', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_gtagjs_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_gtagjs_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleUniversalAnalyticsService::FIELD_CODE,
                    'label' => $translateFunction('Google Analytics tracking ID', 'google'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your tracking ID in Google Analytics property > Admin > Property Settings, formatted as "UA-XXXXXXXXX-X".', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleUniversalAnalyticsService::FIELD_ECOMMERCE,
                    'label' => $translateFunction('Ecommerce tracking', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Measurement of ecommerce transactions/purchases.', 'google'),
                    'values' => [
                        [
                            'id' => 'mergado_gtagjs_ecommerce_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_gtagjs_ecommerce_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleUniversalAnalyticsService::FIELD_ECOMMERCE_ENHANCED,
                    'label' => $translateFunction('Enhanced Ecommerce Tracking', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Enhanced tracking of customer actions.', 'google'),
                    'values' => [
                        [
                            'id' => 'mergado_gtagjs_enchanced_ecommerce_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_gtagjs_enchanced_ecommerce_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleUniversalAnalyticsService::FIELD_CONVERSION_VAT_INCL,
                    'label' => $translateFunction('Products prices with VAT', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'gtm_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'gtm_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the price of the products will be sent with or without VAT.
This setting does not affect total revenue. The total revenue of the transaction is calculated including taxes and shipping costs according to the Google Analytics specification..', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Order refund status', 'google'),
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $translateFunction('Select the order statuses at which the entire order will be refunded. When order status will change to the selected one, refund information will be send to Google Analytics.', 'google'),
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        global $cookie;
        $orderStates = new OrderState();
        $states = $orderStates->getOrderStates($cookie->id_lang);

        foreach ($states as $state) {
            $fields_form[1]['form']['input'][] = [
                'name' => GaRefundService::STATUS . $state['id_order_state'],
                'label' => '<span style="font-weight: 600; font-size: 12px;">' . $state['name'] . '</span>',
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'switch',
                'values' => [
                    [
                        'id' => 'mergado_refund_on_' . $state['id_order_state'],
                        'value' => 1,
                        'label' => $translateFunction('Yes')
                    ],
                    [
                        'id' => 'mergado_refund_off_' . $state['id_order_state'],
                        'value' => 0,
                        'label' => $translateFunction('No')
                    ]
                ],
                'visibility' => Shop::CONTEXT_ALL,
            ];
        }

        $fields_form[2]['form'] = [
            'legend' => [
                'title' => $translateFunction('Google analytics 4 - gtag.js', 'google') . '<span class="label--beta">Beta</span>',
                'icon' => 'icon-cogs',
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
                    'name' => GoogleAnalytics4Service::FIELD_ACTIVE,
                    'label' => $translateFunction('Module active', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_gtagjs_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_gtagjs_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAnalytics4Service::FIELD_CODE,
                    'label' => $translateFunction('Google Analytics 4 tracking ID', 'google'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your tracking ID in Google Analytics 4 property > Admin > Property Settings, formatted as "G-XXXXXXXXX-X".', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAnalytics4Service::FIELD_ECOMMERCE,
                    'label' => $translateFunction('Ecommerce tracking', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Measurement of ecommerce transactions/purchases.', 'google'),
                    'values' => [
                        [
                            'id' => 'mergado_gtagjs_ecommerce_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_gtagjs_ecommerce_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAnalytics4Service::FIELD_SHIPPING_PRICE_INCL,
                    'label' => $translateFunction('Values with shipping price', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'ga4_shipping_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'ga4_shipping_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the value of view_cart, begin_checkout, add_payment_info, add_shipping_info and purchase will be with or without shipping.', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAnalytics4Service::FIELD_CONVERSION_VAT_INCL,
                    'label' => $translateFunction('Products prices with VAT', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'gtm_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'gtm_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the price of the products will be sent with or without VAT.
This setting does not affect total revenue. The total revenue of the transaction is calculated including taxes and shipping costs according to the Google Analytics specification..', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleAnalytics4Service::FIELD_REFUND_API_SECRET,
                    'label' => $translateFunction('Refunds - API Secret', 'google'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can create your API secret in Google Analytics 4 property > Admin > Data Streams".', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => $translateFunction('Order refund status', 'google'),
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $translateFunction('Select the order statuses at which the entire order will be refunded. When order status will change to the selected one, refund information will be send to Google Analytics.', 'google'),
                ],
            ],
            'submit' => [
                'title' => $translateFunction('Save'),
                'name' => 'submit' . $moduleName
            ]
        ];

        global $cookie;
        $orderStates = new OrderState();
        $states = $orderStates->getOrderStates($cookie->id_lang);

        foreach ($states as $state) {
            $fields_form[2]['form']['input'][] = [
                'name' => GoogleAnalytics4Service::FIELD_REFUND_STATUS . $state['id_order_state'],
                'label' => '<span style="font-weight: 600; font-size: 12px;">' . $state['name'] . '</span>',
                'validation' => 'isBool',
                'cast' => 'intval',
                'type' => 'switch',
                'values' => [
                    [
                        'id' => 'mergado_ga4_refund_on_' . $state['id_order_state'],
                        'value' => 1,
                        'label' => $translateFunction('Yes')
                    ],
                    [
                        'id' => 'mergado_ga_4refund_off_' . $state['id_order_state'],
                        'value' => 0,
                        'label' => $translateFunction('No')
                    ]
                ],
                'visibility' => Shop::CONTEXT_ALL,
            ];
        }

        $fields_form[3]['form'] = [
            'legend' => [
                'title' => $translateFunction('Google Tag Manager', 'google'),
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
                    'name' => GoogleTagManagerService::FIELD_ACTIVE,
                    'label' => $translateFunction('Module active', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'class' => 'class-mmp-activity-check-checkbox',
                    'values' => [
                        [
                            'id' => 'mergado_google_tag_manager_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_google_tag_manager_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleTagManagerService::FIELD_CODE,
                    'label' => $translateFunction('Google Tag Manager container ID', 'google'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('You can find your container ID in Tag Manager > Workspace. Near the top of the window, find your container ID, formatted as "GTM-XXXXXX".', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleTagManagerService::FIELD_ECOMMERCE_ACTIVE,
                    'label' => $translateFunction('Ecommerce tracking', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Measurement of ecommerce transactions/purchases.', 'google'),
                    'values' => [
                        [
                            'id' => 'mergado_google_tag_manager_ecommerce_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_google_tag_manager_ecommerce_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleTagManagerService::FIELD_ECOMMERCE_ENHANCED_ACTIVE,
                    'label' => $translateFunction('Enhanced Ecommerce Tracking', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Enhanced tracking of customer actions.', 'google'),
                    'values' => [
                        [
                            'id' => 'mergado_google_tag_manager_enchanced_ecommerce_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_google_tag_manager_enchanced_ecommerce_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleTagManagerService::FIELD_SEND_CUSTOMER_DATA_ACTIVE,
                    'label' => $translateFunction('Send customer data gtag event', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Event gtag(\'set\', \'user_data\', {...}) will be automatically sent, when some service that use gtag wil be active in GTM.', 'google'),
                    'values' => [
                        [
                            'id' => 'mergado_google_tag_manager_send_user_data_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'mergado_google_tag_manager_send_user_data_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleTagManagerService::FIELD_VIEW_LIST_ITEMS_COUNT,
                    'label' => $translateFunction('Max view_list_item', 'google'),
                    'type' => 'text',
                    'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $translateFunction('Set maximum of products sent in view_list_item event. Set 0 if you want to send all products on page.".', 'google'),
                    'visibility' => Shop::CONTEXT_ALL,
                ],
                [
                    'name' => GoogleTagManagerService::FIELD_CONVERSION_VAT_INCL,
                    'label' => $translateFunction('Products prices with VAT', 'google'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'switch',
                    'values' => [
                        [
                            'id' => 'gtm_active_on',
                            'value' => 1,
                            'label' => $translateFunction('Yes')
                        ],
                        [
                            'id' => 'gtm_active_off',
                            'value' => 0,
                            'label' => $translateFunction('No')
                        ]
                    ],
                    'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $translateFunction('Choose whether the price of the products will be sent with or without VAT.
This setting does not affect total revenue. The total revenue of the transaction is calculated including taxes and shipping costs according to the Google Analytics specification.', 'google'),
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
                'title' => $translateFunction('Google Customer Reviews', 'google'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $translateFunction('Module active', 'google'),
                    'name' => GoogleReviewsService::FIELD_OPT_IN_ACTIVE,
                    'is_bool' => true,
                    'class' => 'class-mmp-activity-check-checkbox',
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $translateFunction('Show google merchant opt-in on checkout page.
To active Customer Reviews log into your Merchant Center > Growth > Manage programs > enable Reviews card.', 'google'),
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $translateFunction('Enabled')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $translateFunction('Disabled')
                        ]
                    ],
                ],
                [
                    'type' => 'text',
                    'name' => GoogleReviewsService::FIELD_MERCHANT_ID,
                    'label' => $translateFunction('MerchantId', 'google'),
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $translateFunction('You can get this value from the Google Merchant Center. It\'s the same as your Google Merchant ID', 'google'),
                ],
                [
                    'type' => 'text',
                    'name' => GoogleReviewsService::FIELD_OPT_IN_DELIVERY_DATE,
                    'label' => $translateFunction('Days to send', 'google'),
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $translateFunction('Number of days after ordering, when the email will be send to customers. Only numbers are accepted!', 'google'),
                ],
                [
                    'type' => 'select',
                    'name' => GoogleReviewsService::FIELD_OPT_IN_POSITION,
                    'label' => $translateFunction('Opt-In position', 'google'),
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . 'Select opt-in position.',
                    'options' => [
                        'query' => GoogleReviewsService::OPT_IN_POSITIONS_FOR_SELECT($translateFunction),
                        'id' => 'id',
                        'name' => 'name'
                    ]
                ],
                [
                    'type' => 'switch',
                    'label' => $translateFunction('Show badge', 'google'),
                    'name' => GoogleReviewsService::FIELD_BADGE_ACTIVE,
                    'is_bool' => true,
                    'class' => 'class-mmp-activity-check-checkbox',
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' .$translateFunction('Show review rating badge on prefered location.', 'google'),
                    'values' => [
                        [
                            'id' => 'badge_active_on',
                            'value' => true,
                            'label' => $translateFunction('Enabled')
                        ],
                        [
                            'id' => 'badge_active_off',
                            'value' => false,
                            'label' => $translateFunction('Disabled')
                        ]
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => GoogleReviewsService::FIELD_BADGE_POSITION,
                    'label' => $translateFunction('Badge position', 'google'),
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $translateFunction('Select badge position on page.', 'google'),
                    'options' => [
                        'query' => GoogleReviewsService::BADGE_POSITIONS_FOR_SELECT($translateFunction),
                        'id' => 'id',
                        'name' => 'name'
                    ]
                ],
                [
                    'name' => 'mergado_fake_field',
                    'label' => '',
                    'type' => 'text',
                    'class' => 'mff-d-none',
                    'visibility' => Shop::CONTEXT_ALL,
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $translateFunction('Paste this line in your HTML at the location on the page where you would like the badge to appear.', 'google'),
                ],
                [
                    'type' => 'select',
                    'name' => GoogleReviewsService::FIELD_LANGUAGE,
                    'label' => $translateFunction('Language', 'google'),
                    'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $translateFunction('Select language for opt-in form and badge', 'google'),
                    'options' => [
                        'query' => GoogleReviewsService::LANGUAGES,
                        'id' => 'id',
                        'name' => 'name'
                    ]
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
