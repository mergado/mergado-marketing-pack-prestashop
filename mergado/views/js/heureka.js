var $ = jQuery;

var heureka = {
    showOnMobile: false,
    screenToHide: false,

    init() {
        //Set values
        if (typeof heureka_widget_active !== 'undefined') {
            if (typeof heureka_widget_enable_mobile !== 'undefined') {
                heureka.showOnMobile = heureka_widget_enable_mobile;
            }

            if (typeof heureka_widget_hide_width !== 'undefined') {
                heureka.screenToHide = heureka_widget_hide_width;
            }

            //Call scripts
            heureka.checkWidth();

            $(window).on('resize', function() {
                heureka.checkWidth();
            })
        }
    },

    checkWidth() {
        var width = $(window).width();

        //If mobile - should be visible everytime .. no responsive
        if (heureka.isMobile.any() && heureka.showOnMobile) {
            heureka.showWidget();
            //If mobile and not shoudle be visible there then hide
        } else if (heureka.isMobile.any() && !heureka.showOnMobile) {
            heureka.hideWidget();
            //If PC
        } else {
            if (width > heureka.screenToHide || heureka.screenToHide == 0) {
                heureka.showWidget();
            } else {
                heureka.hideWidget();
            }
        }
    },

    isMobile: {
        Android: function () {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function () {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function () {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function () {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function () {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function () {
            return (heureka.isMobile.Android() || heureka.isMobile.BlackBerry() || heureka.isMobile.iOS() || heureka.isMobile.Opera() || heureka.isMobile.Windows());
        }
    },

    showWidget() {
        if($('#heurekaTableft').length > 0) {
            $('#heurekaTableft').parent().css('display','block');
        }
        if($('#heurekaTabright').length > 0) {
            $('#heurekaTabright').parent().css('display','block');
        }
    },

    hideWidget() {
        if($('#heurekaTableft').length > 0) {
            $('#heurekaTableft').parent().css('display','none');
        }
        if($('#heurekaTabright').length > 0) {
            $('#heurekaTabright').parent().css('display','none');
        }
    }
};

document.addEventListener("DOMContentLoaded", function(event) {
    heureka.init();
});
