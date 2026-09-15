$(document).ready(function () {
    $('#saveSettings').on('click', function () {
        var data = {};
        $('#saveSettings').closest('.sg-content').find('[name]').each(function () {
            var $el = $(this);
            data[$el.attr('name')] = $el.is(':checkbox') ? ($el.is(':checked') ? '1' : '0') : $el.val();
        });
        sgms.loading('Guardando...');
        $.ajax({
            url: window.sgmsSettings && window.sgmsSettings.saveUrl,
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                sgms.toast('success', 'Correcto', res.message);
                setTimeout(function () { window.location.reload(); }, 900);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
    });
});
