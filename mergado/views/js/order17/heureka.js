function heurekaAddCheckbox()
{
    var el = $('#conditions-to-approve li').clone().html();
    $('#conditions-to-approve').before('<div class="heureka_checkbox">' + el + '</div>')

    $('.heureka_checkbox label').attr('for', 'heureka_consent');
    $('.heureka_checkbox input').attr('id', 'heureka_consent').attr('name', 'heureka_consent'). attr('class', '');
    $('.heureka_checkbox label').html(heurekaOptText);

    //Send ajax to backend and set that value...
    $('.heureka_checkbox').on('click', function () {
        if ($('#heureka_consent:checked').length !== 0) {
            checked = 1;
        } else {
            checked = 0;
        }

        $.getJSON(heurekaAjax, {'heurekaData' : checked}, function (data) {
            if(typeof data.status !== "undefined") {
                alert('throught');
            }
        });
    })
}

$(document).ready(function () {
    if($('body#checkout').length > 0) {
        heurekaAddCheckbox()
    }
});
