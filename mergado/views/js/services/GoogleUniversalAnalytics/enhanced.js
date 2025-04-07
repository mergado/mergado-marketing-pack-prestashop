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

// PS 1.7 - start on document ready when jQuery is already loaded
document.addEventListener("DOMContentLoaded", function (event) {
    if (typeof gtag !== 'undefined') {
        mergado_helper_gua.init();
    }
});

/***********************************************************************************************************************
 * MAIN FUNCTIONS
 **********************************************************************************************************************/

var mergado_helper_gua = {
    init: function () {
        //Add to cart
        mergado_gtag_functions_ps16.initAddToCartPs16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
        mergado_gtag_functions_ps17.initAddToCartPs17(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);

        //List view
        if(typeof prestashop != 'undefined') {
            mergado_gtag_functions_ps17.initViewListPs17(mergado_gua_settings.sendTo);
        } else {
            mergado_gtag_functions_ps16.initViewListPs16(mergado_gua_settings.sendTo);
        }

        //Detail of product
        if ($('body#product').length > 0) {
            mergado_gtag_functions_shared.initDetailViewed(mergado_gua_settings.sendTo);
        }

        mergado_gtag_functions_ps16.initRemoveFromCartPs16(mergado_gua_settings.sendTo);
        mergado_gtag_functions_ps17.initRemoveFromCartPs17(mergado_gua_settings.sendTo);

        //PS 1.6
        //Standard checkout page and one page checkout
        if ($('body#order').length > 0 || $('body#order-opc').length > 0) {
            // Init - carrier and payment select options
            mergado_gtag_functions_ps16.initCarrierSetPs16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
            mergado_gtag_functions_ps16.initPaymentSetPs16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);

            if ($('[data-mscd]').length > 0 && $('[data-mscd-cart-id]').length > 0) {
                // Init - start of checkout, add of coupon to cart and remove of coupon from cart
                mergado_gtag_functions_ps16.initCheckoutStarted16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
                mergado_gtag_functions_ps16.initCheckoutReviewStepPs16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
                mergado_gtag_functions_ps16.initCheckoutLoginStepPs16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
                mergado_gtag_functions_ps16.initCheckoutAddressStepPs16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
                mergado_gtag_functions_ps16.initCheckoutDeliveryStepPs16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
                mergado_gtag_functions_ps16.initCheckoutPaymentStepPs16(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
                mergado_gtag_functions_ps16.initCouponAdded(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
                mergado_gtag_functions_ps16.initCouponRemoved(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
            }
        }

        //PS 1.7
        if($('body#cart').length > 0) {
            //Checkout step 1 - cart click on button
            mergado_gtag_functions_ps17.initCheckoutStarted17(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);

            if ($('[data-mscd]').length > 0 && $('[data-mscd-cart-id]').length > 0) {
                mergado_gtag_functions_ps17.initCouponChangePs17(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
            }
        }

        //PS 1.7
        if($('body#checkout').length > 0) {
            //Checkout steps
            //2 - selected/create address page
            mergado_gtag_functions_ps17.initCheckoutAddressStep17(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);

            //3 - delivery page
            mergado_gtag_functions_ps17.initCheckoutDeliveryStep17(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
            mergado_gtag_functions_ps17.initCarrierSetPs17(mergado_gua_settings.sendTo);

            //4 - payment page
            mergado_gtag_functions_ps17.initCheckoutPaymentStep17(mergado_gua_settings.sendTo, mergado_gua_settings.withVat);
            mergado_gtag_functions_ps17.initPaymentSetPs17(mergado_gua_settings.sendTo);
        }
    },
};
