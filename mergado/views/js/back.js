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
        $('.mmp-header-bot').show();
        checkChanges = currentTab === 1 || currentTab === 6;

    } else {
        $('.mmp-header-bot').hide();
        mergadoTab.stop().first().show();
        $('#mergadoController .tabControl a').first().addClass('active');
        checkChanges = true;
    }

    generateCron();
    tabControl();
    toggleFieldsInit();
    initFormChangeChecker();
    closeCronPopup();
    copyToClipboard();
    initRangeScript();
    setSettings();
    tab1FormSend();
    confirmMessage();
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
                    controller: 'AdminMergado',
                    action: $(this).attr('data-generate'),
                    ajax: true,
                    feedBase: $(this).attr('data-cron'),
                }, beforeSend: function() {
                    $('.mmp-popup').addClass('active');
                    $('.mmp-popup__button').addClass('disabled');
                    $('.mmp-popup__loader').show();
                }, success: function(jsonData) {
                    $('.mmp-popup__loader').hide();
                    if (jsonData) {
                        if (jsonData === 'running') {
                            $('.mmp-popup__output').html(admin_mergado_back_running);
                        } else if ($(el).hasClass('last')) {
                            $('.mmp-popup__output').html(admin_mergado_back_merged);
                        } else if ($(el).attr('data-generate') === 'import_prices') {
                            $('.mmp-popup__output').html(admin_mergado_prices_imported);
                        } else {
                            $('.mmp-popup__output').html(admin_mergado_back_success);
                        }
                    } else {
                        $('.mmp-popup__output').html(admin_mergado_back_error);
                    }
                },
                error: function() {
                    $('.mmp-popup__loader').hide();
                    $('.mmp-popup__output').html($('.mmp-popup').attr('data-500'));
                    locker = false;
                },
                complete: function() {
                    $('.mmp-popup__button').removeClass('disabled');
                    locker = false;
                }
            });
        });
    });
}

function setSettings() {
    var locker = false;

    $('[data-cookie]').each(function() {
        $(this).on('click', function (e) {

            $(this).closest('.panel').hide();

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
                    controller: 'AdminMergado',
                    action: $(this).attr('data-cookie'),
                    ajax: true,
                }
            }).complete(function() {
                locker = false;
            });
        });
    });
}

function closeCronPopup() {
    $('.mmp-popup__button').on('click', function(e) {
        e.preventDefault();
        if(!$(this).hasClass('disabled')) {
            $('.mmp-popup').removeClass('active');
            $('.mmp-popup__output').html('');
            window.location.reload(); // Partially ajax .. meh
        }
    });
}

function tabControl() {
    // Tab control
    $('.mmp_tabs__menu li').on('click', function(e) {
        e.preventDefault();
        $('.mmp_tabs li.active').removeClass('active');
        $('.mmp_tabs__tab.active').removeClass('active');
        $(this).addClass('active');
        $('[data-mmp-tab="' + $(this).children('a').attr('data-mmp-tab-button') + '"]').addClass('active');
    });
}

function toggleFieldsInit()
{
    var toggleJSON = JSON.parse($('[data-toggle-fields-json]').attr('data-toggle-fields-json'));

    var fieldStatusSetter = function setFieldStatus(values, mainStatus)
    {
        if(typeof values['fields'] !== "undefined") {
            Object.keys(values['fields']).forEach(function (key) {

                var field = $('[name="' + values['fields'][key] + '"]');

                if (mainStatus) {
                    field.attr('disabled', false);
                } else {
                    field.attr('disabled', true);
                }
            });
        }

        if(typeof values['sub-check'] !== "undefined") {
            Object.keys(values['sub-check']).forEach(function(k) {
                var field = $('[name="' + k + '"]');

                if(mainStatus) {
                    fieldStatusSetter(values['sub-check'][k], getSwitchStatus(field));
                } else {
                    field.attr('disabled', true);
                    fieldStatusSetter(values['sub-check'][k], false);
                }
            });
        }

        if(typeof values['sub-check-two'] !== "undefined") {
            Object.keys(values['sub-check-two']).forEach(function(k) {
                var field = $('[name="' + k + '"]');

                // Check main status of subchecktwo and status of subcheck main field
                if(mainStatus && getSwitchStatus($('[name="' + Object.keys(values['sub-check'])[0] + '"]'))) {
                    return fieldStatusSetter(values['sub-check-two'][k], getSwitchStatus(field));
                } else {
                    field.attr('disabled', true);
                    return fieldStatusSetter(values['sub-check-two'][k], false);
                }
            });
        }
    };

    toggleFieldsMonstrosity();

    $('form input[type="radio"]').on('click', function() {
        toggleFieldsMonstrosity();
    });

    function toggleFieldsMonstrosity() {
        Object.keys(toggleJSON).forEach(function(key) {
            var main = $('[name="' + key + '"]');
            var values = toggleJSON[key];

            fieldStatusSetter(values, getSwitchStatus(main));
        });
    }

    function getSwitchStatus(main)
    {
        if (main && main.attr('checked') == 'checked') {
            return true;
        } else {
            return false;
        }
    }
}

// On change of form set changed
function initFormChangeChecker()
{
    clickedSubmit = false;

    $("form :input").change(function() {
        $(this).closest('form').data('changed', true);
    });

    $('button[type="submit"]').on('click', function() {
        clickedSubmit = true;
    });

    $(window).bind('beforeunload', function() {
        var changed = false;
        $('form').each(function() {
            if ($(this).data('changed') && !clickedSubmit) {
                changed = true;
                return false;
            }
        });

        if(changed) {
            return false;
        }
    });
}

function copyToClipboard()
{
    // Copy to clipboard
    $('[data-copy-stash]').on('click', function (e) {
        e.preventDefault();
        var stash = $(this).attr('data-copy-stash');
        copyToClipboard(stash);
    });

    function copyToClipboard(text) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(text).select();
        document.execCommand("copy");
        $temp.remove();
    }
}

function initRangeScript()
{
    // Range slider init
   $('[class*="rangeSlider-"]').each(function() {
        $(this).append('<style>.rangeSlider-' + $(this).attr('data-range-index') + '::before{width: ' + $(this).attr('data-percentage') + '%;}</style>');
   });
}

function confirmMessage() {
    $('[data-confirm-message]').on('click', function() {
        var r = confirm($(this).attr('data-confirm-message'));
        if (r != true) {
            return false;
        }
    });
}

function tab1FormSend()
{
    var locker = false;

    $('.mergado-tab form').submit(function(e) {
        var tab = $(this).closest('.mergado-tab').attr('data-tab');

        if(tab === '6' || tab === '1') {
            e.preventDefault();

            if(locker) {
                return;
            } else {
                locker = true;
            }

            var pageSet = false;
            var shopIdSet = false;
            var formData = [];

            $('.mergado-tab[data-tab="' + tab + '"] form').each(function() {
                var disabled = $(this).find(':input:disabled').removeAttr('disabled');
                formData.push($(this).serializeArray());
                disabled.attr('disabled','disabled');
            });

            var outputItems = [];

            formData.forEach(function(i) {
                i.forEach(function(item) {
                    if((item['name'] === 'page' || (item['name'] === 'id_shop'))) {
                        if((item['name'] === 'page' && !pageSet)) {
                            outputItems.push(item);
                            pageSet = true;
                        }

                        if((item['name'] === 'id_shop' && !shopIdSet)) {
                            outputItems.push(item);
                            shopIdSet = true;
                        }
                    } else {
                        outputItems.push(item);
                    }
                });
            });

            jQuery.ajax({
                type: 'POST',
                data: outputItems,
                url: e.currentTarget.action,
                dataType: 'json'
            }).complete(function() {
                locker = false;
                window.location.reload();
            });
        }
    });
}
