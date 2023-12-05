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
    init: function () {
        if (mergado_GUA_settings.enhancedActive) {
            //Add to cart
            this.initAddToCartPs16(mergado_GUA_settings.sendTo);
            this.initAddToCartPs17(mergado_GUA_settings.sendTo);

            //List view
            if(typeof prestashop != 'undefined') {
                this.initViewListPs17(mergado_GUA_settings.sendTo);
            } else {
                this.initViewListPs16(mergado_GUA_settings.sendTo);
            }

            //Detail of product
            if ($('body#product').length > 0) {
                this.initDetailViewed(mergado_GUA_settings.sendTo);
            }
        }

        if (mergado_GAds_settings.remarketingActive) {
            //Add to cart
            this.initAddToCartPs16(mergado_GAds_settings.sendTo);
            this.initAddToCartPs17(mergado_GAds_settings.sendTo);

            //List view
            if(typeof prestashop != 'undefined') {
                this.initViewListPs17(mergado_GAds_settings.sendTo);
            } else {
                this.initViewListPs16(mergado_GAds_settings.sendTo);
            }

            //Detail of product
            if ($('body#product').length > 0) {
                this.initDetailViewed(mergado_GAds_settings.sendTo);
            }
        }

        if (mergado_GUA_settings.enhancedActive) {
            this.initRemoveFromCartPs16(mergado_GUA_settings.sendTo);
            this.initRemoveFromCartPs17(mergado_GUA_settings.sendTo);

            //PS 1.6
            //Standard checkout page and one page checkout
            if ($('body#order').length > 0 || $('body#order-opc').length > 0) {
                // Init - carrier and payment select options
                this.initCarrierSetPs16(mergado_GUA_settings.sendTo);
                this.initPaymentSetPs16(mergado_GUA_settings.sendTo);

                if ($('[data-mscd]').length > 0 && $('[data-mscd-cart-id]').length > 0) {
                    // Init - start of checkout, add of coupon to cart and remove of coupon from cart
                    this.initCheckoutStarted16(mergado_GUA_settings.sendTo);
                    this.initCheckoutReviewStepPs16(mergado_GUA_settings.sendTo);
                    this.initCheckoutLoginStepPs16(mergado_GUA_settings.sendTo);
                    this.initCheckoutAddressStepPs16(mergado_GUA_settings.sendTo);
                    this.initCheckoutDeliveryStepPs16(mergado_GUA_settings.sendTo);
                    this.initCheckoutPaymentStepPs16(mergado_GUA_settings.sendTo);
                    this.initCouponAdded(mergado_GUA_settings.sendTo);
                    this.initCouponRemoved(mergado_GUA_settings.sendTo);
                }
            }

            //PS 1.7
            if($('body#cart').length > 0) {
                //Checkout step 1 - cart click on button
                this.initCheckoutStarted17(mergado_GUA_settings.sendTo);

                if ($('[data-mscd]').length > 0 && $('[data-mscd-cart-id]').length > 0) {
                    this.initCouponChangePs17(mergado_GUA_settings.sendTo);
                }
            }

            //PS 1.7
            if($('body#checkout').length > 0) {
                //Checkout steps
                //2 - selected/create address page
                this.initCheckoutAddressStep17(mergado_GUA_settings.sendTo);

                //3 - delivery page
                this.initCheckoutDeliveryStep17(mergado_GUA_settings.sendTo);
                this.initCarrierSetPs17(mergado_GUA_settings.sendTo);

                //4 - payment page
                this.initCheckoutPaymentStep17(mergado_GUA_settings.sendTo);
                this.initPaymentSetPs17(mergado_GUA_settings.sendTo);
            }
        }
    },
    initAddToCartPs16: function (sendTo) {
        //PS 1.6 AJAX
        $('.ajax_add_to_cart_button').on('click', function () {
            var $_id = $(this).attr('data-id-product');
            var $_name = $(this).closest('li').find('.product-name').text().replace(/\t/g, '').trim();
            var $_quantity = 1;
            var $_category = '';
            var $_price = '';
            var $_currency = currency.iso_code;

            const dataItem = element.closest('li').find('.mergado-product-list-item-data[data-product]');
            if (dataItem.length > 0) {
                const productData = JSON.parse(dataItem.attr('data-product'));
                $_price = mmp_GUA_helpers.functions.getProductPrice(productData);
            }

            if ($(this).attr('data-id-product-attribute') && $(this).attr('data-id-product-attribute') !== '' && $(this).attr('data-id-product-attribute') !== '0') {
                $_id = $_id + '-' + $(this).attr('data-id-product-attribute');
            }

            mmp_GUA_helpers.events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency, sendTo);
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

            const productData = mmp_GUA_helpers.functions.getProductObjectAttribute($_id);

            if (productData) {
                $_price = productData.price;
            }

            mmp_GUA_helpers.events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency, sendTo);
        });
    },
    initAddToCartPs17: function (sendTo) {
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

            // VAT changes
            let productData = mmp_GUA_helpers.functions.getProductObject('#mergado-product-informations.mergado-product-data[data-product]');

            if (!productData) {
                // If someone modified PS 1.7 and added "add to cart" on product list page
                productData = mmp_GUA_helpers.functions.getProductObjectFromTarget($(target).closest('.js-product-miniature'), '#mergado-product-informations.mergado-product-list-item-data[data-product]');
            }

            if ($('#product-details[data-product]').length > 0) {

                let productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
                $_id = productJSON.id;
                $_name = productJSON.name;
                $_price = productData.price;
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
                // $_price = $('.product-price').find('[itemprop="price"]').attr('content');
                $_price = productData.price;
                $_category = '';

                if ($_name === '') {
                    $_name = $('.modal-body h1').text();
                }
            }

            mmp_GUA_helpers.events.sendAddToCart($_id, $_name, $_category, $_price, $_quantity, $_currency, sendTo);
        }
    },
    initRemoveFromCartPs16: function(sendTo) {
        //PS 1.6
        $('.ajax_cart_block_remove_link, .cart_quantity_delete').on('click', function () {
            var urlParams = new URLSearchParams($(this).attr('href'));

            var $_id = urlParams.get('id_product');
            var $_ipa = urlParams.get('ipa');
            var $_currency = currency.iso_code;

            if($_ipa != null && $_ipa != 0) {
                $_id += "-" + $_ipa;
            }

            mmp_GUA_helpers.events.sendRemoveFromCart($_id, $_currency, sendTo);
        });
    },
    initRemoveFromCartPs17: function(sendTo) {
        //PS 1.7
        $('body').on('click', '.remove-from-cart[data-link-action="delete-from-cart"]', function () {
            var urlParams = new URLSearchParams($(this).attr('href'));

            var $_id = urlParams.get('id_product');
            var $_ipa = urlParams.get('id_product_attribute');

            var $_currency = prestashop.currency.iso_code;

            if($_ipa != null && $_ipa != 0) {
                $_id += "-" + $_ipa;
            }

            mmp_GUA_helpers.events.sendRemoveFromCart($_id, $_currency, sendTo);
        });
    },
    initDetailViewed: function(sendTo) {
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

        mmp_GUA_helpers.events.sendViewItem($_id, $_name, $_currency, sendTo);
    },
    initPaymentSetPs16: function (sendTo) {
        //PS 1.6
        //Multistep
        if($('body#order').length > 0) {
            $('.payment_module a').on('click', function () {
                var value = $(this).attr('title');

                mmp_GUA_helpers.events.sendCheckoutOptionSelected('payment method', 4, value, sendTo);
            });
        }

        //One page checkout
        if($('body#order-opc').length > 0) {
            $('.payment_module a').on('click', function () {
                var value = $(this).attr('title');
                var items = mmp_GUA_helpers.functions.getMscdData();
                var $_currency = currency.iso_code;

                mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
                mmp_GUA_helpers.events.sendCheckoutOptionSelected('payment method', 3, value, sendTo);
            });
        }
    },
    initPaymentSetPs17: function (sendTo) {
        //PS 1.7
        $('.payment-options input').on('click', function () {
            // var value = $(this).closest('.payment-option').find('label span').text();
            var value = $(this).attr('id');
            var label = $('label[for="' + value + '"] span').text();

            mmp_GUA_helpers.events.sendCheckoutOptionSelected('payment method', 4, label, sendTo);
        });
    },
    initCarrierSetPs16: function (sendTo) {
        //Mutlipage checkout

        if($('body#order')) {
            var lock = false;

            $('body').on('click', '.delivery_option_radio', function () {
                if(!lock) {
                    lock = true;

                    var value = $(this).val().replace(',', '');

                    mmp_GUA_helpers.events.sendCheckoutOptionSelected('shipping method', 3 ,value, sendTo);

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
                        var items = mmp_GUA_helpers.functions.getMscdData();
                        mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
                        mmp_GUA_helpers.events.sendCheckoutOptionSelected('shipping method', 2, value, sendTo);

                    setTimeout(function () {
                        lock = false;
                    }, 500)
                }
            });
        }
    },
    initCarrierSetPs17: function (sendTo) {
        $('.delivery-option input').on('click', function () {
            // var value = $(this).closest('.delivery-option').find('.carrier-name').text();
            var value = $(this).attr('id');
            var label = $('label[for="' + value + '"]').find('.carrier-name').text();

            mmp_GUA_helpers.events.sendCheckoutOptionSelected('payment method', 3, label, sendTo);
        });
    },
    initCheckoutStarted16: function (sendTo) {
        //Multistep
        $('body#order .cart_navigation .standard-checkout').on('click', function () {
            if (typeof $(this).attr('name') == 'undefined' || $(this).attr('name') === '') {
                var items = mmp_GUA_helpers.functions.getMscdData();
                var $_currency = currency.iso_code;

                mmp_GUA_helpers.events.sendBeginCheckout(items, m_GTAG.getCoupon(), $_currency, sendTo);
            }
        });

        //OnePageCheckout
        if($('body#order-opc').length > 0) {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GUA_helpers.events.sendBeginCheckout(items, m_GTAG.getCoupon(), $_currency, sendTo);
        }
    },
    initCheckoutLoginStepPs16: function (sendTo) {
        if($('body#authentication').length > 0) {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;
            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
        }
    },
    initCheckoutAddressStepPs16: function (sendTo) {
        if($('body#order [name="processAddress"]').length > 0) {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
        }
    },
    initCheckoutDeliveryStepPs16: function (sendTo) {
        if($('body#order [name="processCarrier"]').length > 0) {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
        }
    },
    initCheckoutPaymentStepPs16: function (sendTo) {
        if($('body#order .payment_module a').length > 0) {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
        }
    },
    initCheckoutReviewStepPs16: function (sendTo) {
        //Multistep
        $('body#order .payment_module a').on('click', function () {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
        });

        //One page checkout payment/review page before confirm order
        $('body#order-opc #opc_payment_methods a').on('click', function () {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
        });
    },
    initCheckoutStarted17: function (sendTo) {
        var orderUrl = $('[data-morder-url]').attr('data-morder-url');
        $('a[href="' + orderUrl + '"]').on('click', function () {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = prestashop.currency.iso_code;
            mmp_GUA_helpers.events.sendBeginCheckout(items, m_GTAG.getCoupon(), $_currency, sendTo);
        });
    },
    initCheckoutAddressStep17: function (sendTo) {
        if($('#checkout-addresses-step').hasClass('-current') || $('#checkout-addresses-step').hasClass('js-currenct-step')) {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var currency = prestashop.currency.iso_code;
            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), currency, sendTo);
        }
    },
    initCheckoutDeliveryStep17: function (sendTo) {
        if($('#checkout-delivery-step').hasClass('-current') || $('#checkout-delivery-step').hasClass('js-currenct-step')) {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var currency = prestashop.currency.iso_code;
            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), currency, sendTo);
        }
    },
    initCheckoutPaymentStep17: function (sendTo) {
        if($('#checkout-payment-step').hasClass('-current') || $('#checkout-payment-step').hasClass('js-currenct-step')) {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var currency = prestashop.currency.iso_code;
            mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), currency, sendTo);
        }
    },
    initCouponChangePs17: function(sendTo) {
        //One method for add/remove in ps1.7
        if(typeof prestashop !== 'undefined') {
            prestashop.on(
                'updatedCart',
                function() {
                    var items = mmp_GUA_helpers.functions.getMscdData();
                    var currency = prestashop.currency.iso_code;

                    mmp_GUA_helpers.events.sendDiscountRemoved(items, m_GTAG.getCoupon(), currency, sendTo);
                }
            );
        }
    },
    initCouponAdded: function (sendTo) {
        //Only for ps 1.6

        //Checkout coupon
        var urlParams = new URLSearchParams(window.location.search);
        var couponAdded = urlParams.get('addingCartRule');
        var items = mmp_GUA_helpers.functions.getMscdData();
        var $_currency = currency.iso_code;

        if (couponAdded) {
                mmp_GUA_helpers.events.sendCheckoutProgress(items, m_GTAG.getCoupon(), $_currency, sendTo);
        }
    },
    initCouponRemoved: function (sendTo) {
        //Only for ps 1.6

        $('.price_discount_delete').on('click', function () {
            var items = mmp_GUA_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            //Send new discount change
            mmp_GUA_helpers.events.sendDiscountRemoved(items, m_GTAG.getCoupon(), $_currency, sendTo);
        });
    },
    getCoupon: function () {
        return $('[data-mcoupons]').attr('data-mcoupons');
    },
    initViewListPs16: function (sendTo) {
        var products = $('.ajax_block_product');
        var $_currency = currency.iso_code;

        if(products.length > 0) {
            var items = getProductsData(products);
            mmp_GUA_helpers.events.sendViewList(items, $_currency, sendTo);
        }

        $(document).ajaxComplete(function() {
            var currentProducts = $('.ajax_block_product');
            var newProducts = getProductsData(currentProducts)
            var $_currency = currency.iso_code;

            if(items !== newProducts && currentProducts.length > 0) {
                mmp_GUA_helpers.events.sendViewList(newProducts, $_currency, sendTo);
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
    initViewListPs17: function (sendTo) {
        if(typeof prestashop !== 'undefined') {
            var products = $('.product-miniature[data-id-product]');
            var currency = prestashop.currency.iso_code;

            if (products.length > 0) {
                var items = getProductsData(products);
                mmp_GUA_helpers.events.sendViewList(items, currency, sendTo);
            }

            prestashop.on('updateProductList', function (e) {
                var currentProducts = $('.product-miniature[data-id-product]');
                var newProducts = getProductsData(currentProducts)

                if (items !== newProducts && currentProducts.length > 0) {
                    mmp_GUA_helpers.events.sendViewList(newProducts, currency, sendTo);
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
                        item['google_business_vertical'] = mergado_GUA_settings.remarketingType;

                        items.push(item);
                    });
                }

                return items;
            }
        }
    },
};
