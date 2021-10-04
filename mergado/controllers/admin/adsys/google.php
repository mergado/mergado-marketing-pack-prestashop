<?php

use Mergado\Google\GaRefundClass;
use Mergado\Google\GoogleAdsClass;
use Mergado\Google\GoogleReviewsClass;
use Mergado\Google\GoogleTagManagerClass;
use Mergado\Tools\SettingsClass;

$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('GoogleAds', 'google'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'type' => 'hidden',
            'name' => 'page'
        ),
        array(
            'type' => 'hidden',
            'name' => 'id_shop'
        ),
        array(
            'name' => GoogleAdsClass::CONVERSIONS_ACTIVE,
            'label' => $this->module->l('GoogleAds conversions', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_adwords_conversion_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_adwords_conversion_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GoogleAdsClass::REMARKETING_ACTIVE,
            'label' => $this->module->l('GoogleAds remarketing', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'adwords_remarketing_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'adwords_remarketing_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can get a Remarketing ID in your Google Ads account administration > Tools & Settings > Shared library > Audience Manager > Audience Sources > Set Google Ads Tag. Create a new tag, then click Install Tag Yourself. The code is located in the "Global Site Tag" section and has the form AW-123456789.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GoogleAdsClass::REMARKETING_TYPE,
            'label' => $this->module->l('Select Business type', 'google'),
            'type' => 'select',
            'class' => 'w-auto-i',
            'options' => array(
                'query' => GoogleAdsClass::REMARKETING_TYPES,
                'id' => 'id_option',
                'name' => 'name'
            ),
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Mergado Pack supports tag implementations for ecommerce solutions only. If your business type is not ecommerce, select Custom.', 'google'),
        ),
        array(
            'name' => GoogleAdsClass::CONVERSIONS_CODE,
            'label' => $this->module->l('GoogleAds code', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Get the Conversion code in your Google Ads Account Administration > Tools & Settings > MEASUREMENT - Conversions > Add Conversion > Website. Create a new conversion, then click Install the tag yourself. The code is located in the “Global Site Tag” section and takes the form of AW-123456789.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GoogleAdsClass::CONVERSIONS_LABEL,
            'label' => $this->module->l('GoogleAds conversion label', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find the Conversion Label on the same page as the conversion code. The label is located in the “Event fragment” section of the send_to element, after the slash. For example, it has the form of /SqrGHAdS-MerfQC.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

$fields_form[1]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('gtag.js', 'google'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'type' => 'hidden',
            'name' => 'page'
        ),
        array(
            'type' => 'hidden',
            'name' => 'id_shop'
        ),
        array(
            'name' => SettingsClass::GOOGLE_GTAGJS['ACTIVE'],
            'label' => $this->module->l('Module active', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_gtagjs_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_gtagjs_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_GTAGJS['CODE'],
            'label' => $this->module->l('Google Analytics tracking ID', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your tracking ID in Google Analytics property > Admin > Property Settings, formatted as "UA-XXXXXXXXX-X".', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_GTAGJS['TRACKING'],
            'label' => $this->module->l('Add Global Site Tracking Code \'gtag.js\'', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Basic tracking code for page view tracking (necessary for Ecommerce and Enhanced Ecommerce tracking).', 'google'),
            'values' => array(
                array(
                    'id' => 'mergado_gtagjs_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_gtagjs_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_GTAGJS['ECOMMERCE'],
            'label' => $this->module->l('Ecommerce tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Measurement of ecommerce transactions/purchases.', 'google'),
            'values' => array(
                array(
                    'id' => 'mergado_gtagjs_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_gtagjs_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_GTAGJS['ECOMMERCE_ENHANCED'],
            'label' => $this->module->l('Enhanced Ecommerce Tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Enhanced tracking of customer actions.', 'google'),
            'values' => array(
                array(
                    'id' => 'mergado_gtagjs_enchanced_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_gtagjs_enchanced_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => SettingsClass::GOOGLE_GTAGJS['CONVERSION_VAT_INCL'],
            'label' => $this->module->l('Products prices with VAT', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'gtm_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'gtm_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the price of the products will be sent with or without VAT.
This setting does not affect total revenue. The total revenue of the transaction is calculated including taxes and shipping costs according to the Google Analytics specification..', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

$fields_form[2]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Google Tag Manager', 'google'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'type' => 'hidden',
            'name' => 'page'
        ),
        array(
            'type' => 'hidden',
            'name' => 'id_shop'
        ),
        array(
            'name' => GoogleTagManagerClass::ACTIVE,
            'label' => $this->module->l('Module active', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_google_tag_manager_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_google_tag_manager_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GoogleTagManagerClass::CODE,
            'label' => $this->module->l('Google Tag Manager container ID', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('You can find your container ID in Tag Manager > Workspace. Near the top of the window, find your container ID, formatted as "GTM-XXXXXX".', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GoogleTagManagerClass::ECOMMERCE_ACTIVE,
            'label' => $this->module->l('Ecommerce tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Measurement of ecommerce transactions/purchases.', 'google'),
            'values' => array(
                array(
                    'id' => 'mergado_google_tag_manager_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_google_tag_manager_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GoogleTagManagerClass::ECOMMERCE_ENHANCED_ACTIVE,
            'label' => $this->module->l('Enhanced Ecommerce Tracking', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Enhanced tracking of customer actions.', 'google'),
            'values' => array(
                array(
                    'id' => 'mergado_google_tag_manager_enchanced_ecommerce_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_google_tag_manager_enchanced_ecommerce_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GoogleTagManagerClass::VIEW_LIST_ITEMS_COUNT,
            'label' => $this->module->l('Max view_list_item', 'google'),
            'type' => 'text',
            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Set maximum of products sent in view_list_item event. Set 0 if you want to send all products on page.".', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GoogleTagManagerClass::CONVERSION_VAT_INCL,
            'label' => $this->module->l('Products prices with VAT', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'gtm_active_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'gtm_active_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Choose whether the price of the products will be sent with or without VAT.
This setting does not affect total revenue. The total revenue of the transaction is calculated including taxes and shipping costs according to the Google Analytics specification.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

$fields_form[3]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Google Analytics - refunds', 'google'),
        'icon' => 'icon-cogs'

    ),
    'input' => array(
        array(
            'type' => 'hidden',
            'name' => 'page'
        ),
        array(
            'type' => 'hidden',
            'name' => 'id_shop'
        ),
        array(
            'name' => GaRefundClass::ACTIVE,
            'label' => $this->module->l('Module active', 'google'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => array(
                array(
                    'id' => 'mergado_gtm_refund_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ),
                array(
                    'id' => 'mergado_gtm_refund_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                )
            ),
            'desc' => '<span class="mmp-tag mmp-tag--info"></span>' . $this->module->l('Whenever you make a refund for entire products or an entire order, the module sends a refund information to Google Analytics. Regardless of the status of the order.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => GaRefundClass::CODE,
            'label' => $this->module->l('Google Analytics code', 'google'),
            'type' => 'text',
//            'desc' => '<span class="mmp-tag mmp-tag--question"></span>' . $this->module->l('Konverzní kód získáte v administraci Google Ads účtu > Nástroje a nastavení > Měření – konverze > Přidat konverzi > Webová stránka. Vytvořte novou konverzi a poté klikněte na Nainstalovat značku sami. Kód se nachází v sekci “Globální značka webu” a má tuto podobu AW-123456789.', 'google'),
            'visibility' => Shop::CONTEXT_ALL,
        ),
        array(
            'name' => 'mergado_fake_field',
            'label' => $this->module->l('Order refund status', 'google'),
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('Select the order statuses at which the entire order will be refunded. When order status will change to the selected one, refund information will be send to Google Analytics.', 'google'),
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

global $cookie;
$orderStates = new OrderStateCore();
$states = $orderStates->getOrderStates($cookie->id_lang);

foreach ($states as $state) {
    $fields_form[3]['form']['input'][] = array(
        'name' => GaRefundClass::STATUS . $state['id_order_state'],
        'label' => '<span style="font-weight: 600; font-size: 12px;">' . $state['name'] . '</span>',
        'validation' => 'isBool',
        'cast' => 'intval',
        'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
        'class' => 'switch15',
        'values' => array(
            array(
                'id' => 'mergado_refund_on_' . $state['id_order_state'],
                'value' => 1,
                'label' => $this->module->l('Yes')
            ),
            array(
                'id' => 'mergado_refund_off_' . $state['id_order_state'],
                'value' => 0,
                'label' => $this->module->l('No')
            )
        ),
        'visibility' => Shop::CONTEXT_ALL,
    );

}

$fields_form[4]['form'] = array(
    'legend' => array(
        'title' => $this->module->l('Google Customer Reviews', 'google'),
        'icon' => 'icon-cogs'
    ),
    'input' => array(
        array(
            'type' => 'switch',
            'label' => $this->module->l('Module active', 'google'),
            'name' => GoogleReviewsClass::OPT_IN_ACTIVE,
            'is_bool' => true,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Show google merchant opt-in on checkout page.
To active Customer Reviews log into your Merchant Center > Growth > Manage programs > enable Reviews card.', 'google'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->module->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->module->l('Disabled')
                )
            ),
        ),
        array(
            'type' => 'text',
            'name' => GoogleReviewsClass::MERCHANT_ID,
            'label' => $this->module->l('MerchantId', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--question"></span>' . $this->module->l('You can get this value from the Google Merchant Center. It\'s the same as your Google Merchant ID', 'google'),
        ),
        array(
            'type' => 'text',
            'name' => GoogleReviewsClass::OPT_IN_DELIVERY_DATE,
            'label' => $this->module->l('Days to send', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Number of days after ordering, when the email will be send to customers. Only numbers are accepted!', 'google'),
        ),
        array(
            'type' => 'select',
            'name' => GoogleReviewsClass::OPT_IN_POSITION,
            'label' => $this->module->l('Opt-In position', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . 'Select opt-in position.',
            'options' => array(
                'query' => GoogleReviewsClass::OPT_IN_POSITIONS_FOR_SELECT($this->module),
                'id' => 'id',
                'name' => 'name'
            )
        ),
        array(
            'type' => 'switch',
            'label' => $this->module->l('Show badge', 'google'),
            'name' => GoogleReviewsClass::BADGE_ACTIVE,
            'is_bool' => true,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' .$this->module->l('Show review rating badge on prefered location.', 'google'),
            'values' => array(
                array(
                    'id' => 'badge_active_on',
                    'value' => true,
                    'label' => $this->module->l('Enabled')
                ),
                array(
                    'id' => 'badge_active_off',
                    'value' => false,
                    'label' => $this->module->l('Disabled')
                )
            ),
        ),
        array(
            'type' => 'select',
            'name' => GoogleReviewsClass::BADGE_POSITION,
            'label' => $this->module->l('Badge position', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Select badge position on page.', 'google'),
            'options' => array(
                'query' => GoogleReviewsClass::BADGE_POSITIONS_FOR_SELECT(),
                'id' => 'id',
                'name' => 'name'
            )
        ),
        array(
            'name' => 'mergado_fake_field',
            'label' => '',
            'type' => 'text',
            'class' => 'mff-d-none',
            'visibility' => Shop::CONTEXT_ALL,
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Paste this line in your HTML at the location on the page where you would like the badge to appear.', 'google'),
        ),
        array(
            'type' => 'select',
            'name' => GoogleReviewsClass::LANGUAGE,
            'label' => $this->module->l('Language', 'google'),
            'desc' => '<span class="mmp-tag mmp-tag--field mmp-tag--info"></span>' . $this->module->l('Select language for opt-in form and badge', 'google'),
            'options' => array(
                'query' => GoogleReviewsClass::LANGUAGES,
                'id' => 'id',
                'name' => 'name'
            )
        ),
    ),
    'submit' => array(
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    )
);

include __DIR__ . '/partials/helperForm.php';
