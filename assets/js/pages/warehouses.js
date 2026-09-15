$(document).ready(function () {
    var map = null;

    $('.btn-view-map').on('click', function () {
        var lat = parseFloat($(this).data('lat'));
        var lng = parseFloat($(this).data('lng'));
        $('#mapModalTitle').text($(this).data('name'));
        var modalEl = document.getElementById('mapModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        setTimeout(function () {
            if (map) { map.remove(); }
            map = L.map('warehouseMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            L.marker([lat, lng]).addTo(map);
            setTimeout(function () { map.invalidateSize(); }, 250);
        }, 350);
    });

    $('.btn-set-default').on('click', function () {
        var url = $(this).data('url');
        sgms.confirm('Se cambiará la bodega predeterminada. Continuar?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Actualizando...');
            $.ajax({ url: url, type: 'POST', dataType: 'json' })
                .done(function (res) { sgms.stopLoading(); res.success ? (sgms.toast('success', 'Correcto', res.message), setTimeout(function () { location.reload(); }, 800)) : sgms.toast('error', 'Error', res.message); })
                .fail(function () { sgms.stopLoading(); sgms.toast('error', 'Error', 'No fue posible actualizar.'); });
        });
    });

    $('.btn-delete-warehouse').on('click', function () {
        var url = $(this).data('url');
        sgms.confirm('Se desactivará esta bodega. Continuar?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Eliminando...');
            $.ajax({ url: url, type: 'POST', dataType: 'json' })
                .done(function (res) { sgms.stopLoading(); res.success ? (sgms.toast('success', 'Correcto', res.message), setTimeout(function () { location.reload(); }, 800)) : sgms.toast('error', 'Error', res.message); })
                .fail(function (xhr) { sgms.stopLoading(); sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible eliminar.'); });
        });
    });
});
