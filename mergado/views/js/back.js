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
    var mergadoTab = $('#mergadoController .mergado-tab');

    mergadoTab.hide();
    var currentTab = getUrlVars('mergadoTab');

    if (currentTab !== undefined) {
        $('#mergadoController .mergado-tab[data-tab=' + currentTab + ']').stop().show();
        $('#mergadoController .tabControl a[data-tab=' + currentTab + ']').addClass('active');

        checkChanges = currentTab === 1 || currentTab === 6;

    } else {
        mergadoTab.stop().first().show();
        $('#mergadoController .tabControl a').first().addClass('active');
        checkChanges = true;
    }

    generateCron();
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
        for (var i = pars.length; i-- > 0;) {
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

function generateCron()
{
    var locker = false;

    $('.mergado-manual-cron').each(function() {
        $(this).on('click', function (e) {

            $(this).addClass('disabled');
            $(this).html(admin_mergado_back_process);

            if(locker) {
                return;
            } else {
                locker = true;
            }

            var el = $(this);
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: admin_mergado_ajax_url,
                data: {
                    controller : 'AdminMergado',
                    action : $(this).attr('data-generate'),
                    ajax : true,
                    feedBase: $(this).attr('data-cron'),
                }, success: function(jsonData) {
                    if(jsonData) {
                        if(jsonData === 'running') {
                            alert(admin_mergado_back_running);
                        } else if ($(el).hasClass('last')) {
                            alert(admin_mergado_back_merged);
                        } else if($(el).attr('data-generate') === 'import_prices') {
                            alert(admin_mergado_prices_imported);
                        } else {
                            alert(admin_mergado_back_success);
                        }
                        window.location.reload();
                    } else {
                            alert(admin_mergado_back_error);
                    }
                }
            });
        });
    });
}