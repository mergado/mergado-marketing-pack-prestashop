function updateZboziOpc()
{
    $('#zbozi_consent').on('change', function () {
        if ($('#zbozi_consent:checked').length !== 0) {
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
            data: 'ajax=true&method=zboziConsent&zboziData=' + checked + '&token=' + static_token,
            success: function()
            {
            }
        });
    });
}

$(document).ready(function () {
    if($('body#order-opc').length > 0) {
        updateZboziOpc()
    }
});
