function arukeresoAddCheckbox()
{
    var el = '';

    if ($('#conditions-to-approve li').length > 0) {
        el = $('#conditions-to-approve li').clone().html();
        $('#conditions-to-approve').before('<div class="arukereso_checkbox">' + el + '</div>');
    } else {
        if ($('.customConditionsMergado').length == 0) {
            $('.content .payment-options').after('<div class="customConditionsMergado" style="padding-top: 10px;"><ul></ul></div>');
        }
        el = '<div class="float-xs-left"><span class="custom-checkbox"><input id="conditions_to_approve[terms-and-conditions]" name="conditions_to_approve[terms-and-conditions]" required="" type="checkbox" value="1" class="ps-shown-by-js" data-com.bitwarden.browser.user-edited="yes"><span><i class="material-icons rtl-no-flip checkbox-checked"></i></span></span></div><div class="condition-label"><label class="js-terms" for="conditions_to_approve[terms-and-conditions]">Souhlasím s <a href="http://ps1760.test/content/3-terms-and-conditions-of-use" id="cta-terms-and-conditions-0">obchodními podmínkami</a> a budu je bezpodmínečně dodržovat</label></div>';
        $('.customConditionsMergado ul').append('<li class="arukereso_checkbox">' + el + '</li>');
    }

    $('.arukereso_checkbox label').attr('for', 'arukereso_consent');
    $('.arukereso_checkbox input').attr('id', 'arukereso_consent').attr('name', 'arukereso_consent'). attr('class', '');
    $('.arukereso_checkbox label').html(mmp_arukereso['optText']);

    if (mmp_arukereso['checkboxChecked'] == '1') {
        $('.arukereso_checkbox input').attr('checked', 'checked');
    }

    //Send ajax to backend and set that value...
    $('.arukereso_checkbox').on('change', function () {
        if ($('#arukereso_consent:checked').length !== 0) {
            checked = 1;
        } else {
            checked = 0;
        }

        $.getJSON(mmp_arukereso['ajaxLink'], {'arukeresoData' : checked}, function (data) {
            if(typeof data.status !== "undefined") {

            }
        });
    })
}

$(document).ready(function () {
    if($('body#checkout').length > 0) {
        arukeresoAddCheckbox()
    }
});
