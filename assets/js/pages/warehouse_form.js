$(document).ready(function () {
    var C = window.sgmsWarehouseForm || {};
    var mapEl = document.getElementById('mapPicker');
    if (!mapEl || !window.L) return;

    var lat = (C.initialLat != null) ? Number(C.initialLat) : NaN;
    var lng = (C.initialLng != null) ? Number(C.initialLng) : NaN;
    var center = (isNaN(lat) || isNaN(lng)) ? (C.mapCenter || [9.928069, -84.090725]) : [lat, lng];
    var zoom = (isNaN(lat) || isNaN(lng)) ? 12 : 15;

    var map = L.map('mapPicker').setView(center, zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var marker = null;
    if (!isNaN(lat) && !isNaN(lng)) {
        marker = L.marker([lat, lng]).addTo(map);
    }

    map.on('click', function (e) {
        if (marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        $('#latitude').val(e.latlng.lat.toFixed(7));
        $('#longitude').val(e.latlng.lng.toFixed(7));
    });
});