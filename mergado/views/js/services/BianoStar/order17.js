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

function bianoStarAddCheckbox()
{
    var el = '';

    if ($('#conditions-to-approve li').length > 0) {
        el = $('#conditions-to-approve li').clone().html();
        $('#conditions-to-approve').before('<div class="bianoStar_checkbox">' + el + '</div>');
    } else {
        if ($('.customConditionsMergado').length == 0) {
            $('.content .payment-options').after('<div class="customConditionsMergado" style="padding-top: 10px;"><ul></ul></div>');
        }
        el = '<div class="float-xs-left"><span class="custom-checkbox"><input id="conditions_to_approve[terms-and-conditions]" name="conditions_to_approve[terms-and-conditions]" required="" type="checkbox" value="1" class="ps-shown-by-js" data-com.bitwarden.browser.user-edited="yes"><span><i class="material-icons rtl-no-flip checkbox-checked"></i></span></span></div><div class="condition-label"><label class="js-terms" for="conditions_to_approve[terms-and-conditions]">Souhlasím s <a href="http://ps1760.test/content/3-terms-and-conditions-of-use" id="cta-terms-and-conditions-0">obchodními podmínkami</a> a budu je bezpodmínečně dodržovat</label></div>';
        $('.customConditionsMergado ul').append('<li class="bianoStar_checkbox">' + el + '</li>');
    }

    $('.bianoStar_checkbox label').attr('for', 'bianoStar_consent');
    $('.bianoStar_checkbox input').attr('id', 'bianoStar_consent').attr('name', 'bianoStar_consent'). attr('class', '');
    $('.bianoStar_checkbox label').html(mmp_bianoStar['optText']);

    if (mmp_bianoStar['checkboxChecked'] == '1') {
        $('.bianoStar_checkbox input').attr('checked', 'checked');
    }

    //Send ajax to backend and set that value...
    $('.bianoStar_checkbox').on('change', function () {
        if ($('#bianoStar_consent:checked').length !== 0) {
            checked = 1;
        } else {
            checked = 0;
        }

        $.getJSON(mmp_bianoStar['ajaxLink'], {'bianoStarData' : checked}, function (data) {
            if(typeof data.status !== "undefined") {

            }
        });
    })
}

$(document).ready(function () {
    if($('body#checkout').length > 0) {
        bianoStarAddCheckbox()
    }
});
