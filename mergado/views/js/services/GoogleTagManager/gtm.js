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
 *  @license   license.txt
 */

/***********************************************************************************************************************
 * MAIN FUNCTIONS
 **********************************************************************************************************************/

var m_GTM = {
    init: function () {
        this.initAddToCartPs16();
        this.initAddToCartPs17();
        this.initRemoveFromCartPs16();
        this.initRemoveFromCartPs17();

        if(typeof prestashop != 'undefined') {
            this.initViewListPs17();
        } else {
            this.initViewListPs16();
        }

        //Detail of product
        if($('body#product').length > 0) {
            this.initDetailViewed();
        }

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
            }
        }

        //PS 1.7
        if($('body#cart').length > 0) {
            //Checkout step 1 - cart click on button
            this.initCheckoutStarted17();
        }

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
    },
    initAddToCartPs16: function () {
        //PS 1.6
        $('.ajax_add_to_cart_button').on('click', function () {
            var $_currency = $(this).closest('li').find('[itemprop="priceCurrency"]').attr('content');
            var $_id = $(this).attr('data-id-product');
            var $_name = $(this).closest('li').find('.product-name').text().replace(/\t/g, '').trim();
            var $_quantity = 1;
            var $_category = '';
            var $_price = '';

            if ($(this).attr('data-id-product-attribute') && $(this).attr('data-id-product-attribute') !== '' && $(this).attr('data-id-product-attribute') !== '0') {
                $_id = $_id + '-' + $(this).attr('data-id-product-attribute');
            }

            const dataItem = $(this).closest('li').find('.mergado-product-list-item-data[data-product]');

            if (dataItem.length > 0) {
                const productData = JSON.parse(dataItem.attr('data-product'));
                $_price = mmp_GTM_helpers.functions.getProductPrices(productData);
            }

            mmp_GTM_helpers.events.sendAddToCart($_id, $_name, $_category, $_price, $_currency, $_quantity);
        });

        //PS 1.6
        $('#add_to_cart button').on('click', function () {
            var buyBlock = $(this).closest('form');

            var $_currency = buyBlock.find('[itemprop="priceCurrency"]').attr('content');
            var $_id = buyBlock.find('#product_page_product_id').val();
            var $_name = $('h1[itemprop="name"]').text();
            var $_quantity = buyBlock.find('#quantity_wanted').val();
            var $_category = '';
            var $_price = buyBlock.find('[itemprop="price"]').attr('content');

            if (buyBlock.find('#idCombination').length > 0 && buyBlock.find('#idCombination').val() !== '' && buyBlock.find('#idCombination').val() !== '0') {
                $_id = $_id + '-' + buyBlock.find('#idCombination').val();
            }

            const productData = mmp_GTM_helpers.functions.getProductObjectAttribute($_id);

            if (productData) {
                $_price = productData.prices.price;
            }

            mmp_GTM_helpers.events.sendAddToCart($_id, $_name, $_category, $_price, $_currency, $_quantity)
        });
    },
    initAddToCartPs17: function () {
        // PS 1.7
        $('body').on('click', '.add-to-cart', function () {
            addEvents($(this))
        });

        function addEvents(target) {
            var $_id, $_name, $_price, $_category, $_currency, $_quantity, $_modal, $_id2;

            if(target.closest('.product-add-to-cart').find('#quantity_wanted').length > 0) {
                $_quantity = target.closest('.product-add-to-cart').find('#quantity_wanted').val();
            } else {
                $_quantity = 1;
            }

            // VAT changes
            let productData = mmp_GTM_helpers.functions.getProductObject('#mergado-product-informations.mergado-product-data[data-product]');

            if (!productData) {
                // If someone modified PS 1.7 and added add to cart on product list page
                productData = mmp_GTM_helpers.functions.getProductObjectFromTarget($(target).closest('.product-item, .product-miniature'), '#mergado-product-informations.mergado-product-list-item-data[data-product]');
            }

            if ($('#product-details[data-product]').length > 0) {
                var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
                $_id = productJSON.id;
                $_name = productJSON.name;
                $_price = productData.prices.price;
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
                $_price = productData.prices.price;
                $_category = '';
                $_currency = prestashop.currency.iso_code;

                if ($_name === '') {
                    $_name = $('.modal-body h1').text();
                }
            }

            mmp_GTM_helpers.events.sendAddToCart($_id, $_name, $_category, $_price, $_currency, $_quantity);
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

            mmp_GTM_helpers.events.sendRemoveFromCart($_id, $_currency);
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

            mmp_GTM_helpers.events.sendRemoveFromCart($_id, $_currency);
        });
    },
    initDetailViewed: function() {
        var $_id, $_name, $_category, $_currency;

        if ($('#product-details[data-product]').length > 0) {
            var productJSON = JSON.parse($('#product-details[data-product]').attr('data-product'));
            $_id = productJSON.id;
            $_name = productJSON.name;
            $_category = productJSON.category_name;
            $_currency = prestashop.currency.iso_code;

            if (productJSON.id_product_attribute !== "" && productJSON.id_product_attribute !== "0") {
                $_id = $_id + '-' + productJSON.id_product_attribute;
            }
        } else {
            var baseBlock = $('#add_to_cart').closest('form');

            $_id = baseBlock.find('#product_page_product_id').val();
            $_name = $('h1[itemprop="name"]').text();
            $_category = '';
            $_currency = currency.iso_code;

            if ($_name === '') {
                $_name = $('.modal-body h1').text();
            }

            if (baseBlock.find('#idCombination').length > 0) {
                $_id = $_id + '-' + baseBlock.find('#idCombination').val();
            }
        }

        mmp_GTM_helpers.events.sendViewItem($_id, $_name, $_category, $_currency);
    },
    initPaymentSetPs16: function () {
        //PS 1.6
        //Multistep

        if($('body#order').length > 0) {
            $('.payment_module a').on('click', function () {
                var value = $(this).attr('title');

                mmp_GTM_helpers.events.sendCheckoutOptionSelected(4, value);
            });
        }

        //One page checkout
        if($('body#order-opc').length > 0) {
            $('.payment_module a').on('click', function () {
                var value = $(this).attr('title');
                var items = mmp_GTM_helpers.functions.getMscdData();
                var $_currency = currency.iso_code;

                mmp_GTM_helpers.events.sendCheckoutProgress(3, items, $_currency);
                mmp_GTM_helpers.events.sendCheckoutOptionSelected(3, value);
            });
        }
    },
    initPaymentSetPs17: function () {
        //PS 1.7
        $('.payment-options input').on('click', function () {
            // var value = $(this).closest('.payment-option').find('label span').text();
            var value = $(this).attr('id');
            var label = $('label[for="' + value + '"] span').text();

            mmp_GTM_helpers.events.sendCheckoutOptionSelected(4, label);
        });
    },
    initCarrierSetPs16: function () {
        //Multipage checkout
        if($('body#order')) {
            var lock = false;

            $('body').on('click', '.delivery_option_radio', function () {
                if(!lock) {
                    lock = true;

                    var value = $(this).val().replace(',', '');

                    mmp_GTM_helpers.events.sendCheckoutOptionSelected(4, value);

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
                    var value = $(this).val().replace(',', '');
                    var items = mmp_GTM_helpers.functions.getMscdData();
                    var $_currency = currency.iso_code;

                    //Cant get text so sending carrier value
                    mmp_GTM_helpers.events.sendCheckoutProgress(2, items, $_currency);
                    mmp_GTM_helpers.events.sendCheckoutOptionSelected(2, value);

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

            mmp_GTM_helpers.events.sendCheckoutOptionSelected(3, label);
        });
    },
    initCheckoutStarted16: function () {
        $('body#order .cart_navigation .standard-checkout').on('click', function () {
            if (typeof $(this).attr('name') == 'undefined' || $(this).attr('name') === '') {
                var items = mmp_GTM_helpers.functions.getMscdData();
                var $_currency = currency.iso_code;

                mmp_GTM_helpers.events.sendCheckoutProgress(1, items, $_currency)
            }
        });

        //OnePageCheckout
        if($('body#order-opc').length > 0) {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GTM_helpers.events.sendCheckoutProgress(1, items, $_currency)
        }
    },
    initCheckoutLoginStepPs16: function () {
        if($('body#authentication').length > 0) {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GTM_helpers.events.sendCheckoutProgress(2, items, $_currency);
        }
    },
    initCheckoutAddressStepPs16: function () {
        if($('body#order [name="processAddress"]').length > 0) {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GTM_helpers.events.sendCheckoutProgress(3, items, $_currency);
        }
    },
    initCheckoutDeliveryStepPs16: function () {
        if($('body#order [name="processCarrier"]').length > 0) {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GTM_helpers.events.sendCheckoutProgress(4, items, $_currency);
        }
    },
    initCheckoutPaymentStepPs16: function () {
        if($('body#order .payment_module a').length > 0) {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GTM_helpers.events.sendCheckoutProgress(5, items, $_currency);
        }
    },
    initCheckoutReviewStepPs16: function () {
        //Multistep
        $('body#order .payment_module a').on('click', function () {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GTM_helpers.events.sendCheckoutProgress(4, items, $_currency);
        });

        //One page checkout payment/review page before confirm order
        $('body#order-opc #opc_payment_methods a').on('click', function () {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var $_currency = currency.iso_code;

            mmp_GTM_helpers.events.sendCheckoutProgress(4, items, $_currency)
        });
    },
    initCheckoutStarted17: function () {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var currency = prestashop.currency.iso_code;
            mmp_GTM_helpers.events.sendCheckoutProgress(1, items, currency)
    },
    initCheckoutAddressStep17: function () {
        if($('#checkout-addresses-step').hasClass('-current') || $('#checkout-addresses-step').hasClass('js-currenct-step')) {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var currency = prestashop.currency.iso_code;

            mmp_GTM_helpers.events.sendCheckoutProgress(2, items, currency)
        }
    },
    initCheckoutDeliveryStep17: function () {
        if($('#checkout-delivery-step').hasClass('-current') || $('#checkout-delivery-step').hasClass('js-currenct-step')) {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var currency = prestashop.currency.iso_code;
            mmp_GTM_helpers.events.sendCheckoutProgress(3, items, currency)
        }
    },
    initCheckoutPaymentStep17: function () {
        if($('#checkout-payment-step').hasClass('-current') || $('#checkout-payment-step').hasClass('js-currenct-step')) {
            var items = mmp_GTM_helpers.functions.getMscdData();
            var currency = prestashop.currency.iso_code;
            mmp_GTM_helpers.events.sendCheckoutProgress(4, items, currency)
        }
    },
    initViewListPs16: function () {
        var products = $('.ajax_block_product');
        var currency = $('[itemprop="priceCurrency"]').attr('content');

        if(products.length > 0) {
            var items = getProductsData(products);
            mmp_GTM_helpers.events.sendViewList(currency, items);
        }

        $(document).ajaxComplete(function() {
            var currentProducts = $('.ajax_block_product');
            var newProducts = getProductsData(currentProducts)
            var currency = $('[itemprop="priceCurrency"]').attr('content');

            if(items !== newProducts && currentProducts.length > 0) {
                mmp_GTM_helpers.events.sendViewList(currency, newProducts);
            }
        });

        function getProductsData(products)
        {
            var items = [];

            products.each(function (key, value) {
                var $_id = $(this).find('[data-id-product]').attr('data-id-product');
                var $_name = $(this).find('.product-name').text().replace(/\t/g, '').trim();
                var id_attr = $(this).find('[data-id-product-attribute]');

                if(id_attr.length > 0 && id_attr.attr('data-id-product-attribute') !== '' && id_attr.attr('data-id-product-attribute') !== '0') {
                    $_id = $_id + '-' + id_attr.attr('data-id-product-attribute');
                }

                var list = '';

                if($('body#category').length > 0) {
                    list = $('.category-name').text();
                } else if ($('body#search').length > 0) {
                    list = 'search';
                }

                var item = {};
                item['id'] = $_id;
                item['name'] = $_name;

                if(list !== '') {
                    item['list'] = list.trim();
                } else {
                    item['list'] = 'Overview';
                }

                item['variant'] = id_attr.attr('data-id-product-attribute');
                item['position'] = key;

                items.push(item);

                if ((key + 1) == window.mergado_GTM_settings.maxViewListItems) {
                    return false;
                }
            });

            return items;
        }
    },
    initViewListPs17: function () {
        if(typeof prestashop !== 'undefined') {
            var products = $('.product-miniature[data-id-product]');
            var currency = prestashop.currency.iso_code;

            if(products.length > 0) {
                var items = getProductsData(products);
                mmp_GTM_helpers.events.sendViewList(currency, items);
            }

            prestashop.on('updateProductList', function (e) {
                var currentProducts = $('.product-miniature[data-id-product]');
                var newProducts = getProductsData(currentProducts)

                if (items !== newProducts && currentProducts.length > 0) {
                    mmp_GTM_helpers.events.sendViewList(currency, items);
                }
            });

            function getProductsData(products)
            {
                var items = [];

                products.each(function (key, value) {
                    var $_id = $(this).attr('data-id-product');
                    var $_name = $(this).find('.product-title a').text().replace(/\t/g, '').trim();
                    var id_attr = $(this).attr('data-id-product-attribute');

                    if(id_attr && id_attr != 0) {
                        $_id = $_id + '-' + id_attr;
                    }

                    var list = '';

                    if($('body#category').length > 0) {
                        list = $('title').text();
                    } else if ($('body#search').length > 0) {
                        list = 'search';
                    }

                    var item = {};
                    item['id'] = $_id;
                    item['name'] = $_name;

                    if(list !== '') {
                        item['list'] = list.trim();
                    } else {
                        item['list'] = 'Overview';
                    }

                    item['variant'] = id_attr;
                    item['position'] = key;

                    items.push(item);

                    if ((key + 1) == window.mergado_GTM_settings.maxViewListItems) {
                        return false;
                    }
                });

                return items;
            }
        }
    },
};

// PS 1.7 - start on document ready when jQuery is already loaded
document.addEventListener("DOMContentLoaded", function (event) {
    window.dataLayer = window.dataLayer || [];
  // if (typeof dataLayer !== 'undefined') {
    m_GTM.init();
  // }
});
