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
    $('#mergadoController .mergado-tab').stop().first().show();
    $('#mergadoController .tabControl a').first().addClass('active');

    $('#mergadoController .tabControl a').on('click', function (e) {
        e.preventDefault();

        var tabId = $(this).attr('data-tab');
        $('#mergadoController .tabControl a').removeClass('active');
        $('#mergadoController .mergado-tab').stop().hide();

        $('#mergadoController .tabControl a[data-tab=' + tabId + ']').addClass('active');
        $('#mergadoController .mergado-tab[data-tab=' + tabId + ']').stop().fadeIn();

        return false;
    });
});