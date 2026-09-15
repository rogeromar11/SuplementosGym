$(document).ready(function () {
    $('#savePermissions').on('click', function () {
        var url = $(this).data('url');
        var perms = [];
        $('.perm-check:checked').each(function () { perms.push($(this).val()); });
        sgms.loading('Guardando permisos...');
        $.ajax({
            url: url,
            type: 'POST',
            data: { permissions: perms },
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                sgms.toast('success', 'Correcto', res.message);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function () {
            sgms.stopLoading();
            sgms.toast('error', 'Error', 'No se pudieron guardar los permisos.');
        });
    });
});
