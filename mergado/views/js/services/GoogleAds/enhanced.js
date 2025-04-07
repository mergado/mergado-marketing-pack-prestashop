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
        mergado_helper_gads.init();
    }
});

/***********************************************************************************************************************
 * MAIN FUNCTIONS
 **********************************************************************************************************************/

var mergado_helper_gads = {
    init: function () {
        //Add to cart
        mergado_gtag_functions_ps16.initAddToCartPs16(mergado_gads_settings.sendTo, mergado_gads_settings.withVat, mergado_gads_settings.remarketingType);
        mergado_gtag_functions_ps17.initAddToCartPs17(mergado_gads_settings.sendTo, mergado_gads_settings.withVat, mergado_gads_settings.remarketingType);

        //List view
        if(typeof prestashop != 'undefined') {
            mergado_gtag_functions_ps17.initViewListPs17(mergado_gads_settings.sendTo, mergado_gads_settings.remarketingType);
        } else {
            mergado_gtag_functions_ps16.initViewListPs16(mergado_gads_settings.sendTo, mergado_gads_settings.remarketingType);
        }

        //Detail of product
        if ($('body#product').length > 0) {
            mergado_gtag_functions_shared.initDetailViewed(mergado_gads_settings.sendTo, mergado_gads_settings.remarketingType);
        }
    },
};
