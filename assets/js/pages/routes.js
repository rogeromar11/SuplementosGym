$(document).ready(function () {
    var C = window.sgmsRoute;

    // Eliminar ruta (funciona en lista y detalle, sin depender de sgmsRoute)
    $('.btn-delete-route').on('click', function () {
        var btn = $(this);
        var url = btn.data('url');
        var redirect = btn.data('redirect');
        sgms.confirm('Se eliminará esta ruta. Esta acción no se puede deshacer. Continuar?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Eliminando...');
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    if (redirect) { setTimeout(function () { window.location.href = redirect; }, 800); }
                    else { setTimeout(function () { window.location.reload(); }, 800); }
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible eliminar la ruta.'); });
        });
    });

    if (!C) return;

    var routeMap = null;

    function renderMap() {
        var el = document.getElementById('routeMap');
        if (!el) return;
        var points = C.points.filter(function (p) { return p.lat && p.lng; });
        var markers = [];
        var bounds = [];

        if (routeMap) routeMap.remove();
        routeMap = L.map('routeMap');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(routeMap);

        if (C.warehouse && C.warehouse.lat && C.warehouse.lng) {
            var wh = L.marker([C.warehouse.lat, C.warehouse.lng], { icon: L.divIcon({ className: 'text-danger', html: '<i class="bi bi-buildings fs-4"></i>', iconSize: [24, 24] }) }).addTo(routeMap);
            wh.bindPopup('<strong>' + C.warehouse.name + '</strong>');
            bounds.push([C.warehouse.lat, C.warehouse.lng]);
        }

        points.forEach(function (p) {
            var m = L.marker([p.lat, p.lng]).addTo(routeMap);
            m.bindPopup('<strong>' + p.number + '</strong><br>' + p.name);
            markers.push(m);
            bounds.push([p.lat, p.lng]);
        });

        if (markers.length > 1) {
            L.polyline(markers.map(function (m) { return m.getLatLng(); }), { color: '#DC2626', weight: 3 }).addTo(routeMap);
        }

        if (bounds.length) {
            routeMap.fitBounds(bounds, { padding: [30, 30] });
        } else {
            routeMap.setView([9.928069, -84.090725], 12);
        }
        setTimeout(function () { routeMap.invalidateSize(); }, 250);
    }

    renderMap();

    // Orden arrastrable
    var listEl = document.getElementById('routeOrdersList');
    if (listEl) {
        new Sortable(listEl, {
            animation: 180,
            handle: '.route-order-item',
            onEnd: function () {
                $('.route-order-item').each(function (i) {
                    $(this).find('.order-number').text(i + 1);
                    $(this).data('index', i + 1);
                });
            }
        });

        $('.btn-save-order').on('click', function () {
            var orderIds = $('.route-order-item').map(function () { return $(this).data('order-id'); }).get();
            sgms.loading('Guardando orden...');
            $.ajax({
                url: C.reorderUrl,
                type: 'POST',
                data: { route_id: C.routeId, order_ids: orderIds },
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { window.location.reload(); }, 800);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function () { sgms.stopLoading(); sgms.toast('error', 'Error', 'No fue posible guardar el orden.'); });
        });
    }

    // Agregar pedido
    $('.btn-add-order').on('click', function () {
        var btn = $(this);
        sgms.loading('Asignando...');
        $.ajax({
            url: btn.data('url'),
            type: 'POST',
            data: { route_id: btn.data('route'), order_id: btn.data('order') },
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                sgms.toast('success', 'Correcto', res.message);
                setTimeout(function () { window.location.reload(); }, 800);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
    });

    // Asignar encomienda (transporte requerido)
    var assignParcelModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('assignParcelModal'));
    var assignParcelBtn = null;
    $('.btn-assign-parcel').on('click', function () {
        assignParcelBtn = $(this);
        $('#assignParcelLabel').text($(this).data('order-number'));
        $('#assignParcelOrderId').val($(this).data('order'));
        $('#assignParcelTransport').val($(this).data('transport') || '');
        assignParcelModal.show();
    });

    $('#assignParcelConfirm').on('click', function () {
        var transportId = $('#assignParcelTransport').val();
        if (!transportId) {
            sgms.toast('error', 'Atención', 'Debe indicar el transporte de la encomienda.');
            return;
        }
        sgms.loading('Asignando...');
        $.ajax({
            url: assignParcelBtn.data('url'),
            type: 'POST',
            data: {
                route_id: assignParcelBtn.data('route'),
                order_id: $('#assignParcelOrderId').val(),
                transport_id: transportId
            },
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                assignParcelModal.hide();
                sgms.toast('success', 'Correcto', res.message);
                setTimeout(function () { window.location.reload(); }, 800);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
    });

    // Quitar pedido
    $('.btn-remove-order').on('click', function () {
        var btn = $(this);
        sgms.confirm('Se retirará este pedido de la ruta y quedará preparado. Continuar?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Retirando...');
            $.ajax({
                url: btn.data('url'),
                type: 'POST',
                data: { route_id: C.routeId, order_id: btn.data('order') },
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { window.location.reload(); }, 800);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
        });
    });

    // Transferencia
    var transferModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('transferModal'));
    $('.btn-transfer-order').on('click', function () {
        $('#transferOrderId').val($(this).data('order'));
        $('#transferOrderLabel').text($(this).data('order-number'));
        transferModal.show();
    });

    $('#transferConfirm').on('click', function () {
        sgms.loading('Transfiriendo...');
        $.ajax({
            url: C.transferUrl,
            type: 'POST',
            data: {
                order_id: $('#transferOrderId').val(),
                from_route_id: C.routeId,
                to_route_id: $('#transferRoute').val(),
                notes: $('#transferNotes').val()
            },
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                transferModal.hide();
                sgms.toast('success', 'Correcto', res.message);
                setTimeout(function () { window.location.reload(); }, 800);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
    });

    // Iniciar ruta (la finalizacion la realiza el mensajero desde la vista courier)
    $('.btn-route-start').on('click', function () {
        sgms.confirm('Se iniciará la ruta y los pedidos pasarán a estado "en ruta". Continuar?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Iniciando...');
            $.ajax({ url: C.startUrl, type: 'POST', dataType: 'json' })
                .done(function (res) { sgms.stopLoading(); res.success ? (sgms.toast('success', 'Correcto', res.message), setTimeout(function () { location.reload(); }, 900)) : sgms.toast('error', 'Error', res.message); })
                .fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error'); });
        });
    });
});
