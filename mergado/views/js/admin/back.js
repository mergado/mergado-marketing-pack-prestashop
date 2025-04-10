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

$(document).ready(function () {
    var page = $('#mergadoController .mergado-page');

    // page.hide();
    var currentTab = getUrlVars('page');

    if (currentTab !== undefined) {

        $('#mergadoController .pageControl a[data-page=' + currentTab + ']').addClass('active');

        if (currentTab === 'cookies') {
            currentTab = 6;
        }

        $('#mergadoController .mergado-page[data-page=' + currentTab + ']').stop().show();
        // $('#mergadoController .pageControl a[data-page=' + currentTab + ']').addClass('active');
        // $('.mmp-header-bot').show();
        checkChanges = currentTab === 1 || currentTab === 6;

    } else {
        // $('.mmp-header-bot').hide();
        page.stop().first().show();
        // $('#mergadoController .pageControl a').first().addClass('active');
        checkChanges = true;
    }

    generateCron();
    pageControl();
    toggleFieldsInit();
    initFormChangeChecker();
    closeCronPopup();
    copyToClipboard();
    // initRangeScript();
    setSettings();
    tab1FormSend();
    confirmMessage();
    generateInlineCodeForGoogleReviews();

    $('.mmp_feedBox__toggler').on('click', function () {
        toggleFeedBox($(this));
    });

    function toggleFeedBox(element)
    {
        element.closest('.mmp_feedBox').toggleClass('mmp_feedBox--opened');
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

function pageControl() {
    // Tab control
    $('.mmp-tabs__menu li').on('click', function(e) {
        e.preventDefault();
        $('.mmp-tabs li.active').removeClass('active');
        $('.mmp-tabs__tab.active').removeClass('active');
        $(this).addClass('active');
        $('[data-mmp-tab="' + $(this).children('a').attr('data-mmp-tab-button') + '"]').addClass('active');
    });

    $('[data-mmp-tab-button]').on('click', function () {
        var urlParams = new URLSearchParams(window.location.search);
        urlParams.set('mmp-tab', $(this).attr('data-mmp-tab-button'));
        window.history.pushState('', '',  'index.php?' + urlParams);
    });
}

function toggleFieldsInit()
{
    if ($('[data-toggle-fields-json]').length > 0) {
        var toggleJSON = JSON.parse($('[data-toggle-fields-json]').attr('data-toggle-fields-json'));

        var fieldStatusSetter = function(values, mainStatus) {
            if (typeof values['fields'] !== "undefined") {
                Object.keys(values['fields']).forEach(function (key) {

                    var field = $('[name="' + values['fields'][key] + '"]');

                    if (mainStatus) {
                        field.attr('disabled', false);
                    } else {
                        field.attr('disabled', true);
                    }
                });
            }

            if (typeof values['sub-check'] !== "undefined") {
                Object.keys(values['sub-check']).forEach(function (k) {
                    var field = $('[name="' + k + '"]');

                    if (mainStatus) {
                        fieldStatusSetter(values['sub-check'][k], getSwitchStatus(field));
                    } else {
                        field.attr('disabled', true);
                        fieldStatusSetter(values['sub-check'][k], false);
                    }
                });
            }

            if (typeof values['sub-check-two'] !== "undefined") {
                Object.keys(values['sub-check-two']).forEach(function (k) {
                    const field = $('[name="' + k + '"]');

                    // Check main status of subchecktwo and status of subcheck main field
                    if (mainStatus && getSwitchStatus($('[name="' + Object.keys(values['sub-check'])[0] + '"]'))) {
                        return fieldStatusSetter(values['sub-check-two'][k], getSwitchStatus(field));
                    } else {
                        field.attr('disabled', true);
                        return fieldStatusSetter(values['sub-check-two'][k], false);
                    }
                });
            }
        };

        toggleFieldsMonstrosity();

        $('form input[type="radio"]').on('click', function () {
            toggleFieldsMonstrosity();
        });

        function toggleFieldsMonstrosity() {
            Object.keys(toggleJSON).forEach(function (tabName) {
                Object.keys(toggleJSON[tabName]).forEach(function (key) {
                    var main = $('[name="' + key + '"]');
                    var values = toggleJSON[tabName][key];

                    fieldStatusSetter(values, window.getSwitchStatus(main));
                });

                window.recalculateActiveServices(tabName, toggleJSON[tabName]);
            });
        }
    }
}

function getSwitchStatus(main) {
    if (main && main.prop('checked')) {
        return true;
    } else {
        return false;
    }
}

function recalculateActiveServices(tabName, fields)
{
    let activeCount = 0;

    if (Object.keys(fields).length > 0) {
        Object.keys(fields).forEach((key) => {
            const items = fields[key];

            const mainActive = getSwitchStatus($('[name="' + key + '"]'));

            // Main not active === disabled
            if (mainActive) {
                ++activeCount;

                if (typeof items['sub-check'] !== 'undefined') {
                    const subItems = items['sub-check'];

                    Object.keys(subItems).forEach((subKey) => {
                        const activityStatus = window.getSwitchStatus($('[name="' + subKey + '"]'));

                        if (activityStatus) {
                            ++activeCount;
                        }
                    });
                }
            }

            const countElement = $(`[data-mmp-tab-button="${tabName}"]`).find('.mmp-tabs__active-count');

            if (activeCount === 0) {
                countElement.attr('data-count-active', 'false');
                countElement.html('');
            } else {
                // Activate
                countElement.attr('data-count-active', 'true');
                countElement.html(activeCount);
            }
        });
    }
}

// On change of form set changed
function initFormChangeChecker()
{
    clickedSubmit = false;

    $(".mergado-page form :input").change(function() {
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

// function initRangeScript()
// {
//     // Range slider init
//    $('[class*="rangeSlider-"]').each(function() {
//         $(this).append('<style>.rangeSlider-' + $(this).attr('data-range-index') + '::before{width: ' + $(this).attr('data-percentage') + '%;}</style>');
//    });
// }

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

    $('.mergado-page form').submit(function(e) {
        var tab = $(this).closest('.mergado-page').attr('data-page');

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

            $('.mergado-page[data-page="' + tab + '"] form').each(function() {
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

function generateInlineCodeForGoogleReviews()
{
    addGoogleReviewsInlineCode();

    // $('#gr_badge_position').on('change', function () {
    //    if ($(this).val() == '2') {
    //        addGoogleReviewsInlineCode();
    //    }  else {
    //        removeGoogleReviewsInlineCode();
    //    }
    // });

    $('#gr_merchant_id').on('input', function () {
        removeGoogleReviewsInlineCode();
        addGoogleReviewsInlineCode();
    });
}

function addGoogleReviewsInlineCode()
{
    var merchantId = $('#gr_merchant_id').val();
    $('#gr_badge_position').parent().append('<div id="gr_badge_position_inline_code">' +
        '<p><strong>Inline code:</strong></p>' +
        '<code>' +
        '&lt;g:ratingbadge merchant_id=' + merchantId + '&gt;&lt;/g:ratingbadge&gt;' +
        '</code>' +
        '</div>'
    );
}

function removeGoogleReviewsInlineCode()
{
    $('#gr_badge_position_inline_code').remove();
}
