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

function heurekaAddCheckbox()
{
    var el = '';

    if ($('#conditions-to-approve li').length > 0) {
        el = $('#conditions-to-approve li').clone().html();
        $('#conditions-to-approve').before('<div class="heureka_checkbox">' + el + '</div>');
    } else {
        if ($('.customConditionsMergado').length == 0) {
            $('.content .payment-options').after('<div class="customConditionsMergado" style="padding-top: 10px;"><ul></ul></div>');
        }
        el = '<div class="float-xs-left"><span class="custom-checkbox"><input id="conditions_to_approve[terms-and-conditions]" name="conditions_to_approve[terms-and-conditions]" required="" type="checkbox" value="1" class="ps-shown-by-js" data-com.bitwarden.browser.user-edited="yes"><span><i class="material-icons rtl-no-flip checkbox-checked"></i></span></span></div><div class="condition-label"><label class="js-terms" for="conditions_to_approve[terms-and-conditions]">Souhlasím s <a href="http://ps1760.test/content/3-terms-and-conditions-of-use" id="cta-terms-and-conditions-0">obchodními podmínkami</a> a budu je bezpodmínečně dodržovat</label></div>';
        $('.customConditionsMergado ul').append('<li class="heureka_checkbox">' + el + '</li>');
    }


    $('.heureka_checkbox label').attr('for', 'heureka_consent');
    $('.heureka_checkbox input').attr('id', 'heureka_consent').attr('name', 'heureka_consent'). attr('class', '');
    $('.heureka_checkbox label').html(mmp_heureka['optText']);


    if (mmp_heureka['checkboxChecked'] == '1') {
        $('.heureka_checkbox input').attr('checked', 'checked');
    }

    //Send ajax to backend and set that value...
    $('.heureka_checkbox').on('change', function () {
        if ($('#heureka_consent:checked').length !== 0) {
            checked = 1;
        } else {
            checked = 0;
        }

        $.getJSON(mmp_heureka['ajaxLink'], {'heurekaData' : checked}, function (data) {
            if(typeof data.status !== "undefined") {

            }
        });
    })
}

$(document).ready(function () {
    if($('body#checkout').length > 0) {
        heurekaAddCheckbox()
    }
});
