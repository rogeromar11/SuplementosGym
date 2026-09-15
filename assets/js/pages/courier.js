$(document).ready(function () {
    var C = window.sgmsCourierRoute || {};

    function captureLocation($lat, $lng) {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(function (pos) {
            $lat.val(pos.coords.latitude.toFixed(7));
            $lng.val(pos.coords.longitude.toFixed(7));
        }, function () {}, { enableHighAccuracy: true, timeout: 8000 });
    }

    function newIdemKey() {
        return 'idem-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10);
    }

    // Iniciar entrega
    $('.btn-start-delivery').on('click', function () {
        var btn = $(this);
        sgms.confirm('Iniciar la entrega de este pedido?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Iniciando...');
            $.ajax({
                url: btn.data('url'),
                type: 'POST',
                data: { route_order_id: btn.data('route-order') },
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { window.location.reload(); }, 900);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error (' + xhr.status + ')', xhr.responseJSON ? xhr.responseJSON.message : 'Error de conexión. Verifique su internet e intente de nuevo.'); });
        });
    });

    // Modal entrega exitosa
    var deliverModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deliverModal'));
    var deliverBtn = null;
    var deliverSkipCollection = false;

    // El efectivo exige foto del dinero recibido para continuar
    function isCashSelected() {
        var opt = $('#deliverPayment').find('option:selected');
        return opt.data('code') === 'efectivo';
    }

    function updateCashEvidence() {
        var cashRequired = !deliverSkipCollection && isCashSelected();
        $('#deliverEvidence').prop('required', cashRequired);
        $('#deliverCashHint').toggle(cashRequired);
        if (cashRequired) {
            $('#deliverEvidenceLabel').text('Evidencia (foto del efectivo) *');
        } else if (deliverBtn && deliverBtn.data('is-parcel') === 1) {
            $('#deliverEvidenceLabel').text('Evidencia (foto de la guía)');
        } else {
            $('#deliverEvidenceLabel').text('Evidencia (foto)');
        }
    }

    $('#deliverPayment').on('change', updateCashEvidence);

    $('.btn-open-deliver').on('click', function () {
        deliverBtn = $(this);
        var balance = parseFloat($(this).data('balance'));
        var isParcel = $(this).data('is-parcel') === 1;
        var isPrepaid = balance <= 0.01;
        var skipCollection = isParcel || isPrepaid;
        deliverSkipCollection = skipCollection;
        var paymentName = $(this).data('payment-name') || 'No especificada';
        var paymentId = $(this).data('payment-id');
        $('#deliverOrderLabel').text($(this).data('order-number'));
        $('#deliverPaymentMethodLabel').text(paymentName);
        if (paymentId) $('#deliverPayment').val(paymentId);
        $('#deliverIdem').val(newIdemKey());
        $('#deliverParcelInfo').toggle(isParcel);
        $('#deliverPrepaidInfo').toggle(!isParcel && isPrepaid);
        $('#deliverPaymentFields').toggle(!skipCollection);
        $('#deliverEvidenceLabel').text(isParcel ? 'Evidencia (foto de la guía)' : 'Evidencia (foto)');
        $('#deliverPayment').prop('disabled', skipCollection);
        $('#deliverAmount').prop('disabled', skipCollection);
        $('#deliverReceivedBy').prop('disabled', skipCollection);
        if (skipCollection) {
            $('#deliverAmount').val('');
        } else {
            $('#deliverAmount').val(balance > 0 ? balance.toFixed(2) : '');
            $('#deliverBalanceHint').text('Saldo pendiente: ' + (window.moneyFmt ? window.moneyFmt(balance) : balance.toFixed(2)));
        }
        $('#deliverNotes').val('');
        $('#deliverEvidence').val('');
        updateCashEvidence();
        captureLocation($('#deliverLat'), $('#deliverLng'));
        deliverModal.show();
    });

    // Tamaño maximo de evidencia configurado en el servidor
    function evidenceTooBig(inputId) {
        var maxMb = parseFloat((window.sgmsCourierRoute && window.sgmsCourierRoute.maxEvidenceMb) || 8);
        var input = document.getElementById(inputId);
        var f = input && input.files && input.files[0];
        if (f && f.size > maxMb * 1024 * 1024) {
            sgms.toast('error', 'Foto muy grande', 'La foto supera ' + maxMb + ' MB. Tome la foto con la cámara o reduzca su tamaño.');
            return true;
        }
        return false;
    }

    $('#deliverForm').on('submit', function (e) {
        e.preventDefault();
        if (!deliverSkipCollection && isCashSelected() && !document.getElementById('deliverEvidence').files.length) {
            sgms.toast('error', 'Evidencia requerida', 'Adjunte la foto del efectivo recibido para continuar.');
            $('#deliverEvidence').focus();
            return;
        }
        if (evidenceTooBig('deliverEvidence')) return;
        var formData = new FormData(this);
        sgms.loading('Registrando entrega...');
        $.ajax({
            url: C.deliverUrl + deliverBtn.data('route-order'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                deliverModal.hide();
                sgms.toast('success', 'Correcto', res.message);
                setTimeout(function () { window.location.reload(); }, 1000);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error (' + xhr.status + ')', xhr.responseJSON ? xhr.responseJSON.message : 'Error de conexión. Verifique su internet e intente de nuevo.'); });
    });

    // Modal no entregado
    var failModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('failModal'));
    $('.btn-open-notdelivered').on('click', function () {
        $('#failOrderLabel').text($(this).data('order-number'));
        $('#failOther').val('');
        $('#failNotes').val('');
        $('#failReschedule').prop('checked', false);
        $('#failDate').val('');
        $('#failDateWrap').hide();
        $('#failEvidence').val('');
        captureLocation($('#failLat'), $('#failLng'));
        failModal.show();
    });

    $('#failReason').on('change', function () {
        var requiresDesc = $(this).find(':selected').data('requires-desc');
        $('#failOtherWrap').toggle(requiresDesc === 1);
    });

    $('#failReschedule').on('change', function () {
        $('#failDateWrap').toggle($(this).is(':checked'));
    });

    $('#failForm').on('submit', function (e) {
        e.preventDefault();
        if (evidenceTooBig('failEvidence')) return;
        var formData = new FormData(this);
        sgms.loading('Registrando...');
        $.ajax({
            url: C.notDeliveredUrl + $('.btn-open-notdelivered').data('route-order'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                failModal.hide();
                sgms.toast('success', 'Correcto', res.message);
                setTimeout(function () { window.location.reload(); }, 1000);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error (' + xhr.status + ')', xhr.responseJSON ? xhr.responseJSON.message : 'Error de conexión. Verifique su internet e intente de nuevo.'); });
    });

    // Iniciar / finalizar ruta
    $('.btn-route-start').on('click', function () {
        sgms.confirm('Iniciar la ruta? Los pedidos pasarán a "en ruta".').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Iniciando...');
            $.ajax({ url: C.startUrl, type: 'POST', dataType: 'json' })
                .done(function (res) { sgms.stopLoading(); res.success ? (sgms.toast('success', 'Correcto', res.message), setTimeout(function () { location.reload(); }, 900)) : sgms.toast('error', 'Error', res.message); })
                .fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error (' + xhr.status + ')', xhr.responseJSON ? xhr.responseJSON.message : 'Error de conexión. Verifique su internet e intente de nuevo.'); });
        });
    });

    $('.btn-route-finish').on('click', function () {
        sgms.confirm('Finalizar la ruta?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Finalizando...');
            $.ajax({ url: C.finishUrl, type: 'POST', dataType: 'json' })
                .done(function (res) { sgms.stopLoading(); res.success ? (sgms.toast('success', 'Correcto', res.message), setTimeout(function () { location.reload(); }, 900)) : sgms.toast('error', 'Error', res.message); })
                .fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error (' + xhr.status + ')', xhr.responseJSON ? xhr.responseJSON.message : 'Error de conexión. Verifique su internet e intente de nuevo.'); });
        });
    });
});

