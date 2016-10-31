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

$(document).ready(function () {
    
    $('#mergadoController .mergado-tab').hide();
    var currentTab = getUrlVars('mergadoTab');

    if (currentTab !== undefined) {
        $('#mergadoController .mergado-tab[data-tab=' + currentTab + ']').stop().show();
        $('#mergadoController .tabControl a[data-tab=' + currentTab + ']').addClass('active');

        if (currentTab == 1 || currentTab == 6) {
            checkChanges = true;
        } else {
            checkChanges = false;
        }

    } else {
        $('#mergadoController .mergado-tab').stop().first().show();
        $('#mergadoController .tabControl a').first().addClass('active');
        checkChanges = true;
    }

    
});

function getUrlVars(variable) {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,
            function (m, key, value) {
                vars[key] = value;
            });
    return vars[variable];

}

function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts = url.split('?');
    if (urlparts.length >= 2) {

        var prefix = encodeURIComponent(parameter) + '=';
        var pars = urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i = pars.length; i-- > 0; ) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }

        url = urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
}