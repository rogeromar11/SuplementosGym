$(document).ready(function () {
    $('.btn-toggle-user').on('click', function () {
        var btn = $(this);
        var active = btn.data('active') === 1;
        var msg = active ? 'Se desactivará el acceso de este usuario. Continuar?' : 'Se reactivará el acceso de este usuario. Continuar?';
        sgms.confirm(msg).then(function (result) {
            if (!result.isConfirmed) return;
            sgms.loading('Actualizando...');
            $.ajax({
                url: btn.data('url'),
                type: 'POST',
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { window.location.reload(); }, 900);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) {
                sgms.stopLoading();
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible actualizar.');
            });
        });
    });
});
