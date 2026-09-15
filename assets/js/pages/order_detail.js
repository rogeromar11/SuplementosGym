$(document).ready(function () {
    // Mapa en detalle
    var mapEl = document.getElementById('orderMap');
    if (mapEl) {
        var lat = parseFloat(mapEl.dataset.lat);
        var lng = parseFloat(mapEl.dataset.lng);
        var map = L.map('orderMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        L.marker([lat, lng]).addTo(map);
    }

    $('.btn-cancel-order').on('click', function () {
        var url = $(this).data('url');
        Swal.fire({
            title: 'Anular pedido',
            text: 'El pedido quedará cancelado. Escriba el motivo:',
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Motivo de la anulación',
            showCancelButton: true,
            confirmButtonText: 'Anular pedido',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626'
        }).then(function (result) {
            if (!result.isConfirmed || !result.value) return;
            sgms.loading('Anulando...');
            $.ajax({
                url: url,
                type: 'POST',
                data: { reason: result.value },
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
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible anular.');
            });
        });
    });

    $('.btn-update-rescheduled-date').on('click', function () {
        var btn = $(this);
        Swal.fire({
            title: 'Cambiar fecha de entrega',
            text: 'Actualice la fecha acordada con el cliente.',
            icon: 'question',
            input: 'date',
            inputValue: btn.data('date') || '',
            inputAttributes: { min: new Date().toISOString().slice(0, 10) },
            showCancelButton: true,
            confirmButtonText: 'Actualizar fecha',
            cancelButtonText: 'Cancelar',
            preConfirm: function (value) {
                if (!value) {
                    Swal.showValidationMessage('Seleccione una fecha.');
                    return false;
                }
                return value;
            }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            sgms.loading('Actualizando fecha...');
            $.ajax({
                url: btn.data('url'),
                type: 'POST',
                data: { rescheduled_delivery_date: result.value },
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { window.location.reload(); }, 700);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) {
                sgms.stopLoading();
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible actualizar la fecha.');
            });
        });
    });

    $('.btn-delete-order').on('click', function () {
        var btn = $(this);
        var url = btn.data('url');
        var redirect = btn.data('redirect');
        sgms.confirm('Se eliminará este pedido permanentemente. Esta acción no se puede deshacer. Continuar?').then(function (result) {
            if (!result.isConfirmed) return;
            sgms.loading('Eliminando...');
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    if (redirect) { setTimeout(function () { window.location.href = redirect; }, 900); }
                    else { setTimeout(function () { window.location.reload(); }, 900); }
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) {
                sgms.stopLoading();
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible eliminar.');
            });
        });
    });

    // Comprobante de pago en pedidos entregados (solo administrador)
    $('.btn-add-receipt').on('click', function () {
        $('#receiptFileInput').trigger('click');
    });

    $('#receiptFileInput').on('change', function () {
        var input = this;
        if (!input.files || !input.files.length) return;
        var file = input.files[0];
        var formData = new FormData();
        formData.append('payment_receipt', file);
        sgms.loading('Adjuntando comprobante...');
        $.ajax({
            url: $('.btn-add-receipt').data('url'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
            sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible adjuntar el comprobante.');
        });
    });
});
