/**
 * NOTICE OF LICENSE.
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

if(typeof(jQuery) != 'undefined' && jQuery) {
    $(window).on('load', function() {
        if (typeof admin_mergado_ajax_url !== 'undefined') {
            notifications();
        }
    });
}

function notifications()
{
    if(!psv_new) {
    var html = '<li id="mergado_notif" class="dropdown" data-type="mergado_notifications">' +
        '<a href="javascript:void(0);" class="dropdown-toggle notifs" data-toggle="dropdown">' +
        '<img id="mergado_logoimage" src="' + m_logoPath + '"/>' +
        '<span id="customer_messages_notif_number_wrapper" class="notifs_badge">' +
        // '<span id="customer_messages_notif_value">0</span>' +
        '</span>' +
        '</a>' +
        '<div class="dropdown-menu notifs_dropdown">' +
        '<section id="customer_messages_notif_wrapper" class="notifs_panel">' +
        '<div class="notifs_panel_header">' +
        '<h3>' + admin_mergado_show_messages + '</h3>' +
        '</div>' +
        '<div id="list_customer_messages_notif" class="list_notif">' +
        '<span class="no_notifs">' + admin_mergado_no_new + '</span>' +
        '</div>' +
        '<div class="notifs_panel_footer">' +
        '<a href="' + admin_mergado_all_messages_url + '&page=' + admin_mergado_all_messages_id_tab + '">' + admin_mergado_show_more_message + '</a>' +
        '</div>' +
        '</li>';
    } else {
        var html = '<li id="mergado_notif" class="dropdown" data-type="mergado_notifications">' +
            '<a href="javascript:void(0);" class="dropdown-toggle notifs" data-toggle="dropdown">' +
            '<img id="mergado_logoimage" src="' + m_logoPath + '"/>' +
            '<span id="customer_messages_notif_number_wrapper" class="notifs_badge">' +
            // '<span id="customer_messages_notif_value">0</span>' +
            '</span>' +
            '</a>' +
            '<div class="dropdown-menu notifs_dropdown">' +
            '<section class="notifs_panel">' +
            '<header class="notifs_panel_header">' +
            '<h3>' + admin_mergado_show_messages + '</h3>' +
            '</header>' +
            '<div id="list_customer_messages_notif" class="tab-pane">' +
            '<span class="no_notifs">' + admin_mergado_no_new + '</span>' +
            '</div>' +
            '<footer class="panel-footer">' +
            '<a href="' + admin_mergado_all_messages_url + '&page=' + admin_mergado_all_messages_id_tab + '">' + admin_mergado_show_more_message + '</a>' +
            '</footer>' +
            '</section>' +
            '</li>';
    }

    mergadoInsertOnBackOfficeDOM(html);
    $.ajax({
        type: 'POST',
        url: admin_mergado_ajax_url,
        dataType: 'json',
        data: {
            controller : 'AdminMergado',
            action : 'mmp-get-news',
            ajax : true,
        },
        success: function(jsonData)
        {
            if(jsonData.length > 0) {
                $('#mergado_notif .no_notifs').remove();
                ids = [];

                $(jsonData).each(function(i) {
                    if(jsonData[i]['description'].length >= 251) {
                        jsonData[i]['description'] = jsonData[i]['description'].substring(0, 250) + '...';
                        var more = '<a class="mergado__read_more" href="' + admin_mergado_all_messages_url + '&page=' + admin_mergado_all_messages_id_tab + '">' + admin_mergado_read_more + '</a>'
                    } else {
                        var more = '';
                    }

                    $('#mergado_notif #list_customer_messages_notif')
                        .append(
                            '<span class="mergado_notifs">' +
                                '<h4>' + jsonData[i]['title'] + '</h4>' +
                                '<p class="">'+ jsonData[i]['pubDate'] + '</p>' +
                                '<span>' + jsonData[i]['description'] + '</span>' +
                                more +
                            '</span>'
                        );

                    ids.push(jsonData[i]['id']);
                });

                $('#mergado_notif .dropdown-toggle, #mergado_notif .mergado__news').on('click', function() {
                    disableMergadoNotification(ids);
                });

                $('#mergado_notif').prepend('<span class="mergado__news">' + admin_mergado_news + '</span>');

                $('#customer_messages_notif_number_wrapper').html('<span id="customer_messages_notif_value">' + jsonData.length + '</span>');
            }
        },
    });
}

function mergadoInsertOnBackOfficeDOM(html)
{
    $('#mergado_notif').remove();
    // Before PrestaShop 1.7
    if (0 < $('#header_notifs_icon_wrapper').length) {
        $('#header_notifs_icon_wrapper').append(html);
    } else if (0 < $('#notification').length) {
        // PrestaShop 1.7 - Default theme
        $(html).insertAfter('#notification');
    } else if (0 < $('.notification-center').length) {
        // PrestaShop 1.7 - New theme
        $('.mergado-component').remove();
        html = '<div class="component pull-md-right mergado-component"><ul>'+html+'</ul></div>';

        $(html).insertAfter($('.notification-center').closest('.component'));
    } else {
        console.error('Could not find proper place to add the mergado notification center. x_x');
    }
}

function disableMergadoNotification(ids)
{
    $.ajax({
        type: 'POST',
        url: admin_mergado_ajax_url,
        data: {
            controller : 'AdminMergado',
            action : 'mmp-set-readed',
            ajax : true,
            ids: ids,
        }
    });
}
