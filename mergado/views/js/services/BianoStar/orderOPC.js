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

function updateBianoStarOpc()
{
    $('#bianoStar_consent').on('change', function () {
        if ($('#bianoStar_consent:checked').length !== 0) {
            checked = 1;
        } else {
            checked = 0;
        }

        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: orderOpcUrl + '?rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType : "json",
            data: 'ajax=true&method=bianoStarConsent&bianoStarData=' + checked + '&token=' + static_token,
            success: function()
            {
            }
        });
    });
}

$(document).ready(function () {
    if($('body#order-opc').length > 0) {
        updateBianoStarOpc()
    }
});
