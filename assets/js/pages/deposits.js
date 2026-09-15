/* Depósitos de efectivo: confirmar recepción, entrega al administrador y visto bueno. */
$(document).ready(function () {
    // Confirmar recepcion del deposito (auxiliar/admin)
    $('.btn-deposit-confirm').on('click', function () {
        var url = $(this).data('url');
        sgms.confirm('Confirma que recibió el efectivo del mensajero?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Confirmando...');
            $.post(url).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { location.reload(); }, 900);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) {
                sgms.stopLoading();
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error');
            });
        });
    });

    // Visto bueno del administrador
    $('.btn-deposit-approve').on('click', function () {
        var url = $(this).data('url');
        sgms.confirm('Da el visto bueno a este depósito? Confirmará que recibió el monto completo.').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Aprobando...');
            $.post(url).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { location.reload(); }, 900);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) {
                sgms.stopLoading();
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error');
            });
        });
    });

    // Entrega del auxiliar al administrador (modal con comprobante)
    var handoverModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('handoverModal'));
    $('.btn-deposit-handover').on('click', function () {
        $('#handoverForm').attr('action', $(this).data('url'));
        handoverModal.show();
    });

    $('#handoverForm').on('submit', function (e) {
        e.preventDefault();
        var adminId = $('#admin_receiver_user_id').val();
        if (!adminId) {
            sgms.toast('error', 'Atención', 'Indique el administrador que recibe el efectivo.');
            return;
        }
        if (!document.getElementById('aux_receipt').files.length) {
            sgms.toast('error', 'Comprobante requerido', 'Adjunte la foto del comprobante de entrega para continuar.');
            return;
        }
        var formData = new FormData(this);
        sgms.loading('Registrando entrega...');
        $.ajax({
            url: $('#handoverForm').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                handoverModal.hide();
                sgms.toast('success', 'Correcto', res.message);
                setTimeout(function () { location.reload(); }, 900);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function (xhr) {
            sgms.stopLoading();
            sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error');
        });
    });

    // Formulario movil del mensajero: total dinamico segun rutas seleccionadas
    if (window.sgmsDepositCreate) {
        function recalcTotal() {
            var total = 0;
            var count = 0;
            $('.route-check').each(function () {
                if ($(this).is(':checked')) {
                    total += parseFloat($(this).data('amount')) || 0;
                    count++;
                }
            });
            var fmt = window.moneyFmt ? window.moneyFmt(total) : total.toFixed(2);
            $('#depositTotal').text(fmt);
            $('#depositSubmit').prop('disabled', count === 0);
        }
        $(document).on('change', '.route-check', recalcTotal);
        recalcTotal();

        $('#depositForm').on('submit', function (e) {
            if (!$('#receiver_user_id').val()) {
                e.preventDefault();
                sgms.toast('error', 'Atención', 'Indique a quién entrega el depósito.');
                return;
            }
            if (!document.getElementById('deposit_receipt').files.length) {
                e.preventDefault();
                sgms.toast('error', 'Comprobante requerido', 'Adjunte la foto del comprobante del depósito para continuar.');
                return;
            }
            sgms.loading('Registrando depósito...');
        });
    }
});
