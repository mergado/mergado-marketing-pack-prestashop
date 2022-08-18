/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   LICENSE.txt
 */

// PS 1.7 - start on document ready when jQuery is already loaded
document.addEventListener("DOMContentLoaded", function (event) {
    if (typeof gtag !== 'undefined') {
        m_GTAG.init();
    }
});

/***********************************************************************************************************************
 * MAIN FUNCTIONS
 **********************************************************************************************************************/

var m_GTAG = {
    couponCookie: 'mergado_gtag_ps_coupons',
    init: function () {
        // if ((typeof mergado.GoogleAds.remarketingActive !== 'undefined' && mergado.GoogleAds.remarketingActive ) || (typeof mergado.Gtag.enhancedActive !== 'undefined' && mergado.Gtag.enhancedActive)) {
        if (mergado.GoogleAds.remarketingActive || mergado.Gtag.enhancedActive) {
            //Add to cart
            this.initAddToCartPs16();
            this.initAddToCartPs17();

            //List view
            if(typeof prestashop != 'undefined') {
                this.initViewListPs17();
            } else {
                this.initViewListPs16();
            }

            //Detail of product
            if ($('body#product').length > 0) {
                this.initDetailViewed();
            }
        }

        if (mergado.Gtag.enhancedActive) {
            this.initRemoveFromCartPs16();
            this.initRemoveFromCartPs17();

            //PS 1.6
            //Standard checkout page and one page checkout
            if ($('body#order').length > 0 || $('body#order-opc').length > 0) {
                // Init - carrier and payment select options
                this.initCarrierSetPs16();
                this.initPaymentSetPs16();

                if ($('[data-mscd]').length > 0 && $('[data-mscd-cart-id]').length > 0) {
                    // Init - start of checkout, add of coupon to cart and remove of coupon from cart
                    this.initCheckoutStarted16();
                    this.initCheckoutReviewStepPs16();
                    this.initCheckoutLoginStepPs16();
                    this.initCheckoutAddressStepPs16();
                    this.initCheckoutDeliveryStepPs16();
                    this.initCheckoutPaymentStepPs16();
                    this.initCouponAdded();
                    this.initCouponRemoved();
                }
            }

            //PS 1.7
            if($('body#cart').length > 0) {
                //Checkout step 1 - cart click on button
                this.initCheckoutStarted17();

                if ($('[data-mscd]').length > 0 && $('[data-mscd-cart-id]').length > 0) {
                    this.initCouponChangePs17();
                }
            }

            //PS 1.7
            if($('body#checkout').length > 0) {
                //Checkout steps
                //2 - selected/create address page
                this.initCheckoutAddressStep17();

                //3 - delivery page
                this.initCheckoutDeliveryStep17();
                this.initCarrierSetPs17();

                //4 - payment page
                this.initCheckoutPaymentStep17();
                this.initPaymentSetPs17();
            }
        }
    },
    initAddToCartPs16: function () {
        //PS 1.6
        $('.ajax_add_to_cart_button').on('click', function () {
            var $_id = $(this).attr('data-id-product');
            var $_name = $(this).closest('li').find('.product-name').text().replace(/\t/g, '').trim();
            var $_quantity = 1;
            var $_category = '';
            var $_price = '';
            var $_currency = currency.iso_code;

            if ($(this).attr('data-id-product-attribute') && $(this).attr('data-id-product-attribute') !== '' && $(this).attr('data-id-product-attribute') !== '0') {
                $_id = $_id + '-' + $(this).attr('data-id-product-attribute');
            }

            m_GTAG_events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency);
        });

        //PS 1.6
        $('#add_to_cart button').on('click', function () {
            var buyBlock = $(this).closest('form');

            var $_id = buyBlock.find('#product_page_product_id').val();
            var $_name = $('h1[itemprop="name"]').text();
            var $_quantity = buyBlock.find('#quantity_wanted').val();
            var $_category = '';
            var $_price = buyBlock.find('[itemprop="price"]').attr('content');
            var $_currency = currency.iso_code;

            if (buyBlock.find('#idCombination').length > 0 && buyBlock.find('#idCombination').val() !== '' && buyBlock.find('#idCombination').val() !== '0') {
                $_id = $_id + '-' + buyBlock.find('#idCombination').val();
            }

            m_GTAG_events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency);
        });
    },
    initAddToCartPs17: function () {
        // PS 1.7
        $('body').on('click', '.add-to-cart', function () {
            addEvents($(this))
        });

        function addEvents(target) {
            var $_id, $_name, $_price, $_category, $_quantity, $_currency, $_modal, $_id2;

            if(target.closest('.product-add-to-cart').find('#quantity_wanted').length > 0) {
                $_quantity = target.closest('.product-add-to-cart').find('#quantity_wanted').val();
            } else {
                $_quantity = 1;
            }

            if ($('#product-details[data-product]').length > 0) {
                var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
                $_id = productJSON.id;
                $_name = productJSON.name;
                $_price = productJSON.price_amount;
                $_category = productJSON.category_name;
                $_currency = prestashop.currency.iso_code;

                if (productJSON.id_product_attribute !== "" && productJSON.id_product_attribute !== "0") {
                    $_id = $_id + '-' + productJSON.id_product_attribute;
                }
            } else {
                $_modal = target.closest('[id*="quickview-modal-"]').clone().html();

                //Can't get real attr ID because no hook or data on that place :=/
                $_id = $($_modal).find('#product_page_product_id').val();
                $_id2 = $($_modal).find('[data-product-attribute]:checked').attr('data-product-attribute')

                if ($_id2 !== '' && $_id2 && $_id2 !== '0') {
                    $_id = $_id + '-' + $_id2;
                } else if ($(this).closest('form').find('#idCombination').length > 0) {
                    $_id = $_id + '-' + target.closest('form').find('#idCombination').val();
                }

                $_name = $('h1[itemprop="name"]').text();
                $_price = $('.product-price').find('[itemprop="price"]').attr('content');
                $_category = '';

                if ($_name === '') {
                    $_name = $('.modal-body h1').text();
                }
            }

            m_GTAG_events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency);
        }
    },
    initRemoveFromCartPs16: function() {
        //PS 1.6
        $('.ajax_cart_block_remove_link, .cart_quantity_delete').on('click', function () {
            var urlParams = new URLSearchParams($(this).attr('href'));

            var $_id = urlParams.get('id_product');
            var $_ipa = urlParams.get('ipa');
            var $_currency = currency.iso_code;

            if($_ipa != null && $_ipa != 0) {
                $_id += "-" + $_ipa;
            }

            m_GTAG_events.sendRemoveFromCart($_id, $_currency);
        });
    },
    initRemoveFromCartPs17: function() {
        //PS 1.7
        $('body').on('click', '.remove-from-cart[data-link-action="delete-from-cart"]', function () {
            var urlParams = new URLSearchParams($(this).attr('href'));

            var $_id = urlParams.get('id_product');
            var $_ipa = urlParams.get('id_product_attribute');

            var $_currency = prestashop.currency.iso_code;

            if($_ipa != null && $_ipa != 0) {
                $_id += "-" + $_ipa;
            }

            m_GTAG_events.sendRemoveFromCart($_id, $_currency);
        });
    },
    initDetailViewed: function() {
        var $_id, $_name, $_currency;

        if ($('#product-details[data-product]').length > 0) {
            var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
            $_id = productJSON.id;
            $_name = productJSON.name;
            $_currency = prestashop.currency.iso_code;

            if (productJSON.id_product_attribute !== "" && productJSON.id_product_attribute !== "0") {
                $_id = $_id + '-' + productJSON.id_product_attribute;
            }
        } else {
            var baseBlock = $('#add_to_cart').closest('form');

            $_id = baseBlock.find('#product_page_product_id').val();
            $_name = $('h1[itemprop="name"]').text();
            $_currency = currency.iso_code;

            if ($_name === '') {
                $_name = $('.modal-body h1').text();
            }

            if (baseBlock.find('#idCombination').length > 0 && baseBlock.find('#idCombination').val() !== '' && baseBlock.find('#idCombination').val() !== '0') {
                $_id = $_id + '-' + baseBlock.find('#idCombination').val();
            }
        }

        m_GTAG_events.sendViewItem($_id, $_name, $_currency);
    },
    initPaymentSetPs16: function () {
        //PS 1.6
        //Multistep
        if($('body#order').length > 0) {
            $('.payment_module a').on('click', function () {
                var value = $(this).attr('title');

                m_GTAG_events.sendCheckoutOptionSelected('payment method', 4, value);
            });
        }

        //One page checkout
        if($('body#order-opc').length > 0) {
            $('.payment_module a').on('click', function () {
                var value = $(this).attr('title');
                var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
                var $_currency = currency.iso_code;

                m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency);
                m_GTAG_events.sendCheckoutOptionSelected('payment method', 3, value);
            });
        }
    },
    initPaymentSetPs17: function () {
        //PS 1.7
        $('.payment-options input').on('click', function () {
            // var value = $(this).closest('.payment-option').find('label span').text();
            var value = $(this).attr('id');
            var label = $('label[for="' + value + '"] span').text();

            m_GTAG_events.sendCheckoutOptionSelected('payment method', 4, label);
        });
    },
    initCarrierSetPs16: function () {
        //Mutlipage checkout

        if($('body#order')) {
            var lock = false;

            $('body').on('click', '.delivery_option_radio', function () {
                if(!lock) {
                    lock = true;

                    var value = $(this).val().replace(',', '');

                    m_GTAG_events.sendCheckoutOptionSelected('shipping method', 3 ,value);

                    setTimeout(function () {
                        lock = false;
                    }, 500)
                }
            });
        }

        //One page checkout
        if($('body#order-opc')) {
            var lock = false;

            $('body').on('click', '.delivery_option_radio', function () {
                if(!lock) {
                    lock = true;
                        var $_currency = currency.iso_code;
                        var value = $(this).val().replace(',', '');
                        var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
                        m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency)
                        m_GTAG_events.sendCheckoutOptionSelected('shipping method', 2, value);

                    setTimeout(function () {
                        lock = false;
                    }, 500)
                }
            });
        }
    },
    initCarrierSetPs17: function () {
        $('.delivery-option input').on('click', function () {
            // var value = $(this).closest('.delivery-option').find('.carrier-name').text();
            var value = $(this).attr('id');
            var label = $('label[for="' + value + '"]').find('.carrier-name').text();

            m_GTAG_events.sendCheckoutOptionSelected('payment method', 3, label);
        });
    },
    initCheckoutStarted16: function () {
        //Multistep
        $('body#order .cart_navigation .standard-checkout').on('click', function () {
            if (typeof $(this).attr('name') == 'undefined' || $(this).attr('name') === '') {
                var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
                var $_currency = currency.iso_code;

                m_GTAG_events.sendBeginCheckout(items, m_GTAG.getCoupon(), $_currency);
            }
        });

        //OnePageCheckout
        if($('body#order-opc').length > 0) {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = currency.iso_code;

            m_GTAG_events.sendBeginCheckout(items, m_GTAG.getCoupon(), $_currency);
        }
    },
    initCheckoutLoginStepPs16: function () {
        if($('body#authentication').length > 0) {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = currency.iso_code;
            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency);
        }
    },
    initCheckoutAddressStepPs16: function () {
        if($('body#order [name="processAddress"]').length > 0) {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = currency.iso_code;

            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency);
        }
    },
    initCheckoutDeliveryStepPs16: function () {
        if($('body#order [name="processCarrier"]').length > 0) {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = currency.iso_code;

            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency);
        }
    },
    initCheckoutPaymentStepPs16: function () {
        if($('body#order .payment_module a').length > 0) {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = currency.iso_code;

            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency);
        }
    },
    initCheckoutReviewStepPs16: function () {
        //Multistep
        $('body#order .payment_module a').on('click', function () {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = currency.iso_code;

            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency);
        });

        //One page checkout payment/review page before confirm order
        $('body#order-opc #opc_payment_methods a').on('click', function () {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = currency.iso_code;

            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency);
        });
    },
    initCheckoutStarted17: function () {
        var orderUrl = $('[data-morder-url]').attr('data-morder-url');
        $('a[href="' + orderUrl + '"]').on('click', function () {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = prestashop.currency.iso_code;
            m_GTAG_events.sendBeginCheckout(items, m_GTAG.getCoupon(), $_currency)
        });

        // Triggering this can be skipped if already logged in .. so button probably better
        // if($('#checkout-personal-information-step').hasClass('-current') || $('#checkout-personal-information-step').hasClass('js-currenct-step')) {
        //     var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
        //     var cartId = $('[data-mscd-cart-id]').attr('data-mscd-cart-id');
        //     m_GTAG_events.sendBeginCheckout(items, m_GTAG.getCouponFromCookie(cartId))
        // }
    },
    initCheckoutAddressStep17: function () {
        if($('#checkout-addresses-step').hasClass('-current') || $('#checkout-addresses-step').hasClass('js-currenct-step')) {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var currency = prestashop.currency.iso_code;
            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), currency)
        }
    },
    initCheckoutDeliveryStep17: function () {
        if($('#checkout-delivery-step').hasClass('-current') || $('#checkout-delivery-step').hasClass('js-currenct-step')) {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var currency = prestashop.currency.iso_code;
            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), currency)
        }
    },
    initCheckoutPaymentStep17: function () {
        if($('#checkout-payment-step').hasClass('-current') || $('#checkout-payment-step').hasClass('js-currenct-step')) {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var currency = prestashop.currency.iso_code;
            m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), currency)
        }
    },
    initCouponChangePs17: function() {
        //One method for add/remove in ps1.7
        if(typeof prestashop !== 'undefined') {
            prestashop.on(
                'updatedCart',
                function() {
                    var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
                    var currency = prestashop.currency.iso_code;

                    m_GTAG_events.sendDiscountRemoved(items, m_GTAG.getCoupon(), currency);
                }
            );
        }
    },
    initCouponAdded: function () {
        //Only for ps 1.6

        //Checkout coupon
        var urlParams = new URLSearchParams(window.location.search);
        var couponAdded = urlParams.get('addingCartRule');
        var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
        var $_currency = currency.iso_code;

        if (couponAdded) {
                m_GTAG_events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency);
        }
    },
    initCouponRemoved: function () {
        //Only for ps 1.6

        $('.price_discount_delete').on('click', function () {
            var items = JSON.parse($('[data-mscd]').attr('data-mscd'));
            var $_currency = currency.iso_code;

            //Send new discount change
            m_GTAG_events.sendDiscountRemoved(items, m_GTAG.getCoupon(), $_currency);
        });
    },
    getCoupon: function () {
        return $('[data-mcoupons]').attr('data-mcoupons');
    },
    initViewListPs16: function () {
        var products = $('.ajax_block_product');
        var $_currency = currency.iso_code;

        if(products.length > 0) {
            var items = getProductsData(products);
            m_GTAG_events.sendViewList(items, $_currency);
        }

        $(document).ajaxComplete(function() {
            var currentProducts = $('.ajax_block_product');
            var newProducts = getProductsData(currentProducts)
            var $_currency = currency.iso_code;

            if(items !== newProducts && currentProducts.length > 0) {
                m_GTAG_events.sendViewList(newProducts, $_currency);
            }
        });

        function getProductsData(products)
        {
            var items = [];

            if(products.length > 0) {
                products.each(function (key, value) {
                    var $_id = $(this).find('[data-id-product]').attr('data-id-product');
                    var $_name = $(this).find('.product-name').text().replace(/\t/g, '').trim();

                    var id_attr = $(this).find('[data-id-product-attribute]');

                    if (id_attr.length > 0 && id_attr.attr('data-id-product-attribute') !== '' && id_attr.attr('data-id-product-attribute') !== '0') {
                        $_id = $_id + '-' + id_attr.attr('data-id-product-attribute');
                    }

                    var list = '';

                    if ($('body#category').length > 0) {
                        list = $('.category-name').text();
                    } else if ($('body#search').length > 0) {
                        list = 'search';
                    }

                    var item = {};
                    item['id'] = $_id;
                    item['name'] = $_name;

                    if (list !== '') {
                        item['list_name'] = list.trim();
                    }

                    item['position'] = key;

                    items.push(item);
                });
            }

            return items;
        }
    },
    initViewListPs17: function () {
        if(typeof prestashop !== 'undefined') {
            var products = $('.product-miniature[data-id-product]');
            var currency = prestashop.currency.iso_code;

            if (products.length > 0) {
                var items = getProductsData(products);
                m_GTAG_events.sendViewList(items, currency);
            }

            $(document).ajaxComplete(function () {
                var currentProducts = $('.product-miniature[data-id-product]');
                var newProducts = getProductsData(currentProducts)

                if (items !== newProducts && currentProducts.length > 0) {
                    m_GTAG_events.sendViewList(newProducts, currency);
                }
            });

            function getProductsData(products) {
                var items = [];

                if (products.length > 0) {
                    products.each(function (key, value) {
                        var $_id = $(this).attr('data-id-product');
                        var $_name = $(this).find('.product-title a').text().replace(/\t/g, '').trim();
                        var id_attr = $(this).attr('data-id-product-attribute');

                        if (id_attr && id_attr != 0) {
                            $_id = $_id + '-' + id_attr;
                        }

                        var list = '';

                        if ($('body#category').length > 0) {
                            list = $('title').text();
                        } else if ($('body#search').length > 0) {
                            list = 'search';
                        }

                        var item = {};
                        item['id'] = $_id;
                        item['name'] = $_name;

                        if (list !== '') {
                            item['list_name'] = list.trim();
                        }

                        item['position'] = key;
                        item['google_business_vertical'] = mergado.GoogleAds.remarketingType;

                        items.push(item);
                    });
                }

                return items;
            }
        }
    },
};

/***********************************************************************************************************************
 * GTAG FUNCTIONS
 **********************************************************************************************************************/

var m_GTAG_events = {
    sendAddToCart: function (id, name, category, price, quantity, currency) {

        gtag('event', 'add_to_cart', {
            "currency" : currency,
            "items": [
                {
                    "id": id,
                    "name": name,
                    // "list_name": "Search Results",
                    // "brand": "Google",
                    "category": category,
                    // "variant": "Black",
                    // "list_position": 1,
                    "quantity": quantity,
                    "price": price,
                    'google_business_vertical': mergado.GoogleAds.remarketingType
                }
            ],
            "send_to": window.mergado.GtagAndGads.send_to,
        });
    },
    sendRemoveFromCart: function(id, currency) {
        gtag('event', 'remove_from_cart', {
            "currency" : currency,
            "items": [
                {
                    "id": id,
                    // "name": name,
                    // "list_name": "Search Results",
                    // "brand": "Google",
                    // "category": $_category,
                    // "variant": "Black",
                    // "list_position": 1,
                    // "quantity": 1,
                    // "price": $_price
                }
            ]
        });
    },
    sendViewItem: function(id, name, currency) {
        gtag('event', 'view_item', {
            "currency" : currency,
            "items": [
                {
                    "id": id,
                    "name": name,
                    // "list_name": "Search Results",
                    // "brand": "Google",
                    // "category": "Apparel/T-Shirts",
                    // "variant": "Black",
                    // "list_position": 1,
                    // "quantity": 2,
                    // "price": '2.0',
                    'google_business_vertical': mergado.GoogleAds.remarketingType
                }
            ],
            "send_to": window.mergado.GtagAndGads.send_to,
        });
    },
    sendCheckoutOptionSelected: function (option, step, value) {
        gtag('event', 'set_checkout_option', {
            "checkout_step": step,
            "checkout_option": option,
            "value": value
        });
    },
    sendBeginCheckout: function(items, coupon, currency) {
        gtag('event', 'begin_checkout', {
            "currency" : currency,
            "items": items,
            "coupon": coupon,
        });
    },
    sendCheckoutProgress: function(items, coupon, currency) {
        gtag('event', 'checkout_progress', {
            "currency" : currency,
            "items": items,
            "coupon": coupon,
        });
    },
    sendDiscountRemoved: function (items, coupon, currency) {
        gtag('event', 'checkout_progress', {
            "currency" : currency,
            "items": items,
            "coupon": coupon,
        });
    },
    sendViewList: function (items, currency) {
        gtag('event', 'view_item_list', {
            "currency" : currency,
            "items": items,
            "send_to": window.mergado.GtagAndGads.send_to,
        });
    }
};

/***********************************************************************************************************************
 * UTILITIES
 **********************************************************************************************************************/

var m_cookieManagment = {
    setCookie: function (cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    },
    getCookie: function (cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
};
