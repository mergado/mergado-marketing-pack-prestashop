<?php

use Mergado\Google\GaRefundClass;
use Mergado\includes\services\Google\GoogleAds\GoogleAdsService;
use Mergado\Google\GoogleReviewsClass;
use Mergado\includes\services\Google\GoogleAnalytics4\GoogleAnalytics4Service;
use Mergado\includes\services\Google\GoogleTagManager\GoogleTagManagerService;
use Mergado\includes\services\Google\GoogleUniversalAnalytics\GoogleUniversalAnalyticsService;

$fields_form[0]['form'] = [
    'legend' => [
        'title' => $this->module->l('GoogleAds', 'google'),
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
            'name' => GoogleAdsService::CONVERSIONS_ACTIVE,
            'label' => $this->module->l('GoogleAds conversions', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_adwords_conversion_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_adwords_conversion_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleAdsService::REMARKETING_ACTIVE,
            'label' => $this->module->l('GoogleAds remarketing', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'adwords_remarketing_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'adwords_remarketing_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can get a Remarketing ID in your Google Ads account administration > Tools & Settings > Shared library > Audience Manager > Audience Sources > Set Google Ads Tag. Create a new tag, then click Install Tag Yourself. The code is located in the "Global Site Tag" section and has the form AW-123456789.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleAdsService::REMARKETING_TYPE,
            'label' => $this->module->l('Select Business type', 'google'),
            'type' => 'select',
            'class' => 'w-auto-i',
            'options' => [
                'query' => GoogleAdsService::REMARKETING_TYPES,
                'id' => 'id_option',
                'name' => 'name'
            ],
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Mergado Pack supports tag implementations for ecommerce solutions only. If your business type is not ecommerce, select Custom.', 'google'),
        ],
        [
            'name' => GoogleAdsService::CONVERSIONS_CODE,
            'label' => $this->module->l('GoogleAds code', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Get the Conversion code in your Google Ads Account Administration > Tools & Settings > MEASUREMENT - Conversions > Add Conversion > Website. Create a new conversion, then click Install the tag yourself. The code is located in the “Global Site Tag” section and takes the form of AW-123456789.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleAdsService::CONVERSIONS_LABEL,
            'label' => $this->module->l('GoogleAds conversion label', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find the Conversion Label on the same page as the conversion code. The label is located in the “Event fragment” section of the send_to element, after the slash. For example, it has the form of /SqrGHAdS-MerfQC.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

$fields_form[1]['form'] = [
    'legend' => [
        'title' => $this->module->l('Google Universal analytics - gtag.js', 'google'),
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
            'name' => GoogleUniversalAnalyticsService::ACTIVE,
            'label' => $this->module->l('Module active', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_gtagjs_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_gtagjs_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleUniversalAnalyticsService::CODE,
            'label' => $this->module->l('Google Analytics tracking ID', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your tracking ID in Google Analytics property > Admin > Property Settings, formatted as "UA-XXXXXXXXX-X".', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleUniversalAnalyticsService::ECOMMERCE,
            'label' => $this->module->l('Ecommerce tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Measurement of ecommerce transactions/purchases.', 'google'),
            'values' => [
                [
                    'id' => 'mergado_gtagjs_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_gtagjs_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleUniversalAnalyticsService::ECOMMERCE_ENHANCED,
            'label' => $this->module->l('Enhanced Ecommerce Tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Enhanced tracking of customer actions.', 'google'),
            'values' => [
                [
                    'id' => 'mergado_gtagjs_enchanced_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_gtagjs_enchanced_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleUniversalAnalyticsService::CONVERSION_VAT_INCL,
            'label' => $this->module->l('Products prices with VAT', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'gtm_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'gtm_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the price of the products will be sent with or without VAT.
This setting does not affect total revenue. The total revenue of the transaction is calculated including taxes and shipping costs according to the Google Analytics specification..', 'google'),
            'visibility' => ShopCore::CONTEXT_ALL,
        ],
        [
            'name' => 'mergado_fake_field',
            'label' => $this->module->l('Order refund status', 'google'),
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => ShopCore::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('Select the order statuses at which the entire order will be refunded. When order status will change to the selected one, refund information will be send to Google Analytics.', 'google'),
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

global $cookie;
$orderStates = new OrderStateCore();
$states = $orderStates->getOrderStates($cookie->id_lang);

foreach ($states as $state) {
    $fields_form[1]['form']['input'][] = [
        'name' => GaRefundClass::STATUS . $state['id_order_state'],
        'label' => '<span style="font-weight: 600; font-size: 12px;">' . $state['name'] . '</span>',
        'validation' => 'isBool',
        'cast' => 'intval',
        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
        'class' => 'switch15',
        'values' => [
            [
                'id' => 'mergado_refund_on_' . $state['id_order_state'],
                'value' => 1,
                'label' => $this->module->l('Yes')
            ],
            [
                'id' => 'mergado_refund_off_' . $state['id_order_state'],
                'value' => 0,
                'label' => $this->module->l('No')
            ]
        ],
        'visibility' => Shop::CONTEXT_ALL,
    ];
}

$fields_form[2]['form'] = [
    'legend' => [
        'title' => $this->module->l('Google analytics 4 - gtag.js', 'google') . '<span class="label--beta">Beta</span>',
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
            'name' => GoogleAnalytics4Service::ACTIVE,
            'label' => $this->module->l('Module active', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_gtagjs_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_gtagjs_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleAnalytics4Service::CODE,
            'label' => $this->module->l('Google Analytics 4 tracking ID', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your tracking ID in Google Analytics 4 property > Admin > Property Settings, formatted as "G-XXXXXXXXX-X".', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleAnalytics4Service::ECOMMERCE,
            'label' => $this->module->l('Ecommerce tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Measurement of ecommerce transactions/purchases.', 'google'),
            'values' => [
                [
                    'id' => 'mergado_gtagjs_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_gtagjs_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleAnalytics4Service::SHIPPING_PRICE_INCL,
            'label' => $this->module->l('Values with shipping price', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'ga4_shipping_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'ga4_shipping_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the value of view_cart, begin_checkout, add_payment_info, add_shipping_info and purchase will be with or without shipping.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleAnalytics4Service::CONVERSION_VAT_INCL,
            'label' => $this->module->l('Products prices with VAT', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'gtm_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'gtm_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the price of the products will be sent with or without VAT.
This setting does not affect total revenue. The total revenue of the transaction is calculated including taxes and shipping costs according to the Google Analytics specification..', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleAnalytics4Service::REFUND_API_SECRET,
            'label' => $this->module->l('Refunds - API Secret', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can create your API secret in Google Analytics 4 property > Admin > Data Streams".', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => 'mergado_fake_field',
            'label' => $this->module->l('Order refund status', 'google'),
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('Select the order statuses at which the entire order will be refunded. When order status will change to the selected one, refund information will be send to Google Analytics.', 'google'),
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

global $cookie;
$orderStates = new OrderStateCore();
$states = $orderStates->getOrderStates($cookie->id_lang);

foreach ($states as $state) {
    $fields_form[2]['form']['input'][] = [
        'name' => GoogleAnalytics4Service::REFUND_STATUS . $state['id_order_state'],
        'label' => '<span style="font-weight: 600; font-size: 12px;">' . $state['name'] . '</span>',
        'validation' => 'isBool',
        'cast' => 'intval',
        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
        'class' => 'switch15',
        'values' => [
            [
                'id' => 'mergado_ga4_refund_on_' . $state['id_order_state'],
                'value' => 1,
                'label' => $this->module->l('Yes')
            ],
            [
                'id' => 'mergado_ga_4refund_off_' . $state['id_order_state'],
                'value' => 0,
                'label' => $this->module->l('No')
            ]
        ],
        'visibility' => Shop::CONTEXT_ALL,
    ];
}

$fields_form[3]['form'] = [
    'legend' => [
        'title' => $this->module->l('Google Tag Manager', 'google'),
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
            'name' => GoogleTagManagerService::ACTIVE,
            'label' => $this->module->l('Module active', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_google_tag_manager_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_google_tag_manager_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleTagManagerService::CODE,
            'label' => $this->module->l('Google Tag Manager container ID', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your container ID in Tag Manager > Workspace. Near the top of the window, find your container ID, formatted as "GTM-XXXXXX".', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleTagManagerService::ECOMMERCE_ACTIVE,
            'label' => $this->module->l('Ecommerce tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Measurement of ecommerce transactions/purchases.', 'google'),
            'values' => [
                [
                    'id' => 'mergado_google_tag_manager_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_google_tag_manager_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleTagManagerService::ECOMMERCE_ENHANCED_ACTIVE,
            'label' => $this->module->l('Enhanced Ecommerce Tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Enhanced tracking of customer actions.', 'google'),
            'values' => [
                [
                    'id' => 'mergado_google_tag_manager_enchanced_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_google_tag_manager_enchanced_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleTagManagerService::VIEW_LIST_ITEMS_COUNT,
            'label' => $this->module->l('Max view_list_item', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Set maximum of products sent in view_list_item event. Set 0 if you want to send all products on page.".', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => GoogleTagManagerService::CONVERSION_VAT_INCL,
            'label' => $this->module->l('Products prices with VAT', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'gtm_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'gtm_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the price of the products will be sent with or without VAT.
This setting does not affect total revenue. The total revenue of the transaction is calculated including taxes and shipping costs according to the Google Analytics specification.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

$fields_form[4]['form'] = [
    'legend' => [
        'title' => $this->module->l('Google Customer Reviews', 'google'),
        'icon' => 'icon-cogs'
    ],
    'input' => [
        [
            'type' => 'switch',
            'label' => $this->module->l('Module active', 'google'),
            'name' => GoogleReviewsClass::OPT_IN_ACTIVE,
            'is_bool' => true,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Show google merchant opt-in on checkout page.
To active Customer Reviews log into your Merchant Center > Growth > Manage programs > enable Reviews card.', 'google'),
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->module->l('Enabled')
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->module->l('Disabled')
                ]
            ],
        ],
        [
            'type' => 'text',
            'name' => GoogleReviewsClass::MERCHANT_ID,
            'label' => $this->module->l('MerchantId', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('You can get this value from the Google Merchant Center. It\'s the same as your Google Merchant ID', 'google'),
        ],
        [
            'type' => 'text',
            'name' => GoogleReviewsClass::OPT_IN_DELIVERY_DATE,
            'label' => $this->module->l('Days to send', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Number of days after ordering, when the email will be send to customers. Only numbers are accepted!', 'google'),
        ],
        [
            'type' => 'select',
            'name' => GoogleReviewsClass::OPT_IN_POSITION,
            'label' => $this->module->l('Opt-In position', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . 'Select opt-in position.',
            'options' => [
                'query' => GoogleReviewsClass::OPT_IN_POSITIONS_FOR_SELECT($this->module),
                'id' => 'id',
                'name' => 'name'
            ]
        ],
        [
            'type' => 'switch',
            'label' => $this->module->l('Show badge', 'google'),
            'name' => GoogleReviewsClass::BADGE_ACTIVE,
            'is_bool' => true,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' .$this->module->l('Show review rating badge on prefered location.', 'google'),
            'values' => [
                [
                    'id' => 'badge_active_on',
                    'value' => true,
                    'label' => $this->module->l('Enabled')
                ],
                [
                    'id' => 'badge_active_off',
                    'value' => false,
                    'label' => $this->module->l('Disabled')
                ]
            ],
        ],
        [
            'type' => 'select',
            'name' => GoogleReviewsClass::BADGE_POSITION,
            'label' => $this->module->l('Badge position', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Select badge position on page.', 'google'),
            'options' => [
                'query' => GoogleReviewsClass::BADGE_POSITIONS_FOR_SELECT(),
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
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Paste this line in your HTML at the location on the page where you would like the badge to appear.', 'google'),
        ],
        [
            'type' => 'select',
            'name' => GoogleReviewsClass::LANGUAGE,
            'label' => $this->module->l('Language', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Select language for opt-in form and badge', 'google'),
            'options' => [
                'query' => GoogleReviewsClass::LANGUAGES,
                'id' => 'id',
                'name' => 'name'
            ]
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
