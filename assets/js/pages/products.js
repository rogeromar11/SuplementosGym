$(document).ready(function () {
    $('.btn-delete-product').on('click', function () {
        var url = $(this).data('url');
        sgms.confirm('Se desactivará este producto. Continuar?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Eliminando...');
            var data = {};
            if (window.sgmsCsrf && window.sgmsCsrf.name && window.sgmsCsrf.token) {
                data[window.sgmsCsrf.name] = window.sgmsCsrf.token;
            }
            $.ajax({ url: url, type: 'POST', data: data, dataType: 'json' })
                .done(function (res) { sgms.stopLoading(); res.success ? (sgms.toast('success', 'Correcto', res.message), setTimeout(function () { location.reload(); }, 800)) : sgms.toast('error', 'Error', res.message); })
                .fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible eliminar.'); });
        });
    });
});
