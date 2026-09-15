$(document).ready(function () {
    $('.btn-prep-action').on('click', function () {
        var btn = $(this);
        var url = btn.data('url');
        var action = btn.data('action');
        var isPrepared = action === 'prepared';
        var confirmMsg = isPrepared ? 'Confirmar que el pedido está preparado y listo para entrega.' : 'Iniciar la preparación de este pedido.';

        var proceed = function () {
            sgms.loading('Actualizando...');
            $.ajax({ url: url, type: 'POST', dataType: 'json' })
                .done(function (res) {
                    sgms.stopLoading();
                    if (res.success) {
                        sgms.toast('success', 'Correcto', res.message);
                        setTimeout(function () { window.location.reload(); }, 900);
                    } else {
                        sgms.toast('error', 'Error', res.message);
                    }
                })
                .fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
        };

        if (isPrepared) {
            Swal.fire({
                title: 'Marcar como preparado',
                text: confirmMsg,
                icon: 'question',
                input: 'textarea',
                inputPlaceholder: 'Observaciones (opcional)',
                showCancelButton: true,
                confirmButtonText: 'Si, preparado',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#16A34A'
            }).then(function (r) {
                if (!r.isConfirmed) return;
                $.ajax({ url: url, type: 'POST', data: { notes: r.value || '' }, dataType: 'json' })
                    .done(function (res) { res.success ? (sgms.toast('success', 'Correcto', res.message), setTimeout(function () { location.reload(); }, 900)) : sgms.toast('error', 'Error', res.message); })
                    .fail(function (xhr) { sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
            });
        } else {
            sgms.confirm(confirmMsg).then(function (r) { if (r.isConfirmed) proceed(); });
        }
    });

    $('.btn-return-pending').on('click', function () {
        var url = $(this).data('url');
        Swal.fire({
            title: 'Devolver a pendiente',
            text: 'Indique la justificación:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Justificación (obligatoria)',
            showCancelButton: true,
            confirmButtonText: 'Devolver',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#F59E0B',
            inputValidator: function (v) { if (!v) return 'La justificacion es obligatoria.'; }
        }).then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Actualizando...');
            $.ajax({ url: url, type: 'POST', data: { justification: r.value }, dataType: 'json' })
                .done(function (res) { sgms.stopLoading(); res.success ? (sgms.toast('success', 'Correcto', res.message), setTimeout(function () { location.reload(); }, 900)) : sgms.toast('error', 'Error', res.message); })
                .fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
        });
    });

    $('.btn-add-observation').on('click', function () {
        var url = $(this).data('url');
        Swal.fire({
            title: 'Agregar observación',
            input: 'textarea',
            inputPlaceholder: 'Observación',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626',
            inputValidator: function (v) { if (!v) return 'Escriba una observacion.'; }
        }).then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Guardando...');
            $.ajax({ url: url, type: 'POST', data: { notes: r.value }, dataType: 'json' })
                .done(function (res) { sgms.stopLoading(); res.success ? sgms.toast('success', 'Correcto', res.message) : sgms.toast('error', 'Error', res.message); })
                .fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
        });
    });
});
