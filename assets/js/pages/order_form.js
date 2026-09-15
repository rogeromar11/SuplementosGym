$(document).ready(function () {
    var C = window.sgmsOrderForm || {};

    function money(v) {
        v = Number(v) || 0;
        return (C.currency || '₡') + ' ' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function fmtQty(v) {
        v = Number(v) || 0;
        return String(Math.round(v));
    }

    function integerQty(v) {
        return Math.max(0, Math.round(Number(v) || 0));
    }

    // Inventario disponible por producto y cantidades reservadas al editar
    var stockByPid = {};
    var reservedByPid = {};

    function rowProductId($row) {
        return parseInt($row.find('select.product-select').val() || $row.find('input[name="product_id[]"]').val(), 10) || 0;
    }

    function currentQtyByPid() {
        var m = {};
        $('#itemsTable tbody .item-row').each(function () {
            var pid = rowProductId($(this));
            if (!pid) return;
            var q = integerQty($(this).find('.item-qty').val());
            m[pid] = (m[pid] || 0) + q;
        });
        return m;
    }

    function availableFor(pid) {
        if (typeof stockByPid[pid] !== 'number') return null;
        return stockByPid[pid] + (reservedByPid[pid] || 0);
    }

    function updateStockHints() {
        var totals = currentQtyByPid();
        $('#itemsTable tbody .item-row').each(function () {
            var pid = rowProductId($(this));
            var $qty = $(this).find('.item-qty');
            var $hint = $(this).find('.item-stock-hint');
            if (!$hint.length) return;
            var avail = availableFor(pid);
            if (!pid || avail === null) {
                $hint.text('').removeClass('text-danger').hide();
                $qty.removeClass('is-invalid');
                return;
            }
            var used = totals[pid] || 0;
            var over = used > avail;
            $hint.text(over ? 'Supera el inventario (' + fmtQty(avail) + ' disponibles)' : '');
            $hint.toggleClass('text-danger', over).toggle(over);
            $qty.toggleClass('is-invalid', over);
        });
    }

    function clampStock($row) {
        var pid = rowProductId($row);
        if (!pid) return;
        var avail = availableFor(pid);
        if (avail === null) return;
        var $qty = $row.find('.item-qty');
        var v = integerQty($qty.val());
        $qty.val(v || '');
        if (v > avail) {
            if (avail > 0) $qty.val(avail);
            sgms.toast('error', 'Inventario', 'La cantidad solicitada excede el inventario disponible (' + fmtQty(Math.max(avail, 0)) + ').');
            $qty.trigger('input');
        }
    }

    function initStockValidation() {
        var ids = [];
        var reserved = {};
        $('#itemsTable tbody .item-row').each(function () {
            var pid = rowProductId($(this));
            if (!pid) return;
            var q = integerQty($(this).find('.item-qty').val());
            ids.push(pid);
            reserved[pid] = (reserved[pid] || 0) + q;
        });
        reservedByPid = reserved;
        if (!ids.length || !C.stockUrl) { updateStockHints(); return; }
        $.ajax({
            url: C.stockUrl,
            type: 'POST',
            data: { product_ids: ids },
            dataType: 'json'
        }).done(function (res) {
            if (res.success && res.data && res.data.stock) {
                for (var k in res.data.stock) stockByPid[k] = res.data.stock[k];
            }
            updateStockHints();
        });
    }

    function recomputeTotals() {
        var subtotal = 0;
        $('#itemsTable tbody .item-row').each(function () {
            var qty = integerQty($(this).find('.item-qty').val());
            var price = parseFloat($(this).find('.item-price').val()) || 0;
            var total = qty * price;
            subtotal += total;
            $(this).find('.item-line-total').text(money(total));
        });
        var discount = parseFloat($('#discount').val()) || 0;
        if (discount > subtotal) discount = subtotal;
        var total = subtotal - discount;
        $('#orderTotal').text(money(total));
        return total;
    }

    function setupSelect2($row) {
        var $select = $row.find('.product-select');
        if (!$select.length || $select.data('select2')) return;
        $select.select2({
            width: '100%',
            placeholder: 'Buscar producto...',
            ajax: {
                url: C.searchUrl,
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function (params) {
                    return { term: params.term || '' };
                },
                processResults: function (data) {
                    return data.data || { results: [] };
                },
                cache: false
            },
            minimumInputLength: 1,
            templateResult: function (data) {
                if (data.loading) return data.text;
                var text = data.text || data.id || '';
                if (typeof data.stock_qty === 'number') {
                    text += ' <small class="text-muted-2">Disp: ' + fmtQty(data.stock_qty) + '</small>';
                }
                return $('<span>').html(text);
            },
            templateSelection: function (data) {
                return data.text || data.id || '';
            }
        });
        $select.on('select2:select', function (e) {
            var d = e.params.data;
            var pid = parseInt(d.id, 10);
            $row.find('input[name="item_name[]"]').val(d.name);
            $row.find('input[name="item_sku[]"]').val(d.sku);
            $row.find('.item-price').val(d.unit_price);
            if (pid) stockByPid[pid] = parseFloat(d.stock_qty) || 0;
            recomputeTotals();
            updateStockHints();
        });
    }

    function setupRow($row) {
        $row.find('.item-qty, .item-price').on('input', recomputeTotals);
        $row.find('.item-qty').on('input', updateStockHints);
        $row.find('.item-qty').on('blur', function () { clampStock($(this).closest('tr')); });
        if ($row.find('.product-select').length && !$row.find('.item-stock-hint').length) {
            $('<div class="item-stock-hint form-text"></div>').insertAfter($row.find('.product-select'));
        }
        setupSelect2($row);
    }

    function setupCustomerSelect2() {
        var $select = $('#customer_name');
        var $hidden = $('#customer_name_value');
        if (!$select.length || $select.data('select2')) return;
        $select.select2({
            width: '100%',
            placeholder: 'Buscar cliente...',
            allowClear: true,
            tags: true,
            minimumInputLength: 1,
            ajax: {
                url: C.clientSearchUrl,
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function (params) {
                    return { term: params.term || '' };
                },
                processResults: function (data) {
                    return data.data || { results: [] };
                }
            },
            templateSelection: function (data) {
                return data.name || data.text || data.id || '';
            },
            templateResult: function (data) {
                if (data.loading) return data.text;
                if (!data.name) return data.text;
                var html = '<div>' + $('<div>').text(data.name).html();
                if (data.code) html += ' <small class="text-muted">[' + $('<div>').text(data.code).html() + ']</small>';
                if (data.phone) html += ' <small class="text-muted-2">' + $('<div>').text(data.phone).html() + '</small>';
                if (data.phone2) html += ' <small class="text-muted-2">· ' + $('<div>').text(data.phone2).html() + '</small>';
                return $(html);
            }
        });
    $select.on('select2:select', function (e) {
        var d = e.params.data;
        $hidden.val(d.name || d.text || '');
        if (d.phone) {
            $('#customer_phone').val(d.phone).trigger('blur');
        }
        if (d.phone2) {
            $('#customer_phone2').val(d.phone2).trigger('blur');
        }
        // Al seleccionar un cliente del listado se carga SIEMPRE su direccion
        // guardada (sobrescribe lo escrito), para reflejar ediciones recientes.
        if (d.address && $.trim(d.address) !== '') {
            $('#delivery_address').val($.trim(d.address));
            lastGeoAddress = '';
            geocodeAddress(false);
        }
        var ref = (d.delivery_type || d.zone || '').trim();
        if (ref && !$.trim($('#delivery_reference').val())) {
            $('#delivery_reference').val(ref);
        }
    });
        $select.on('select2:clear', function () {
            $hidden.val('');
        });
    }

    $('#itemsTable tbody .item-row').each(function () { setupRow($(this)); });
    setupCustomerSelect2();

    $('.btn-add-product-row').on('click', function () {
        var html = '<tr class="item-row" data-is-manual="0">' +
            '<td>' +
                '<select class="form-select form-select-sm product-select" name="product_id[]" data-placeholder="Buscar producto..."><option value="">Buscar producto...</option></select>' +
                '<input type="hidden" name="item_name[]" value="">' +
                '<input type="hidden" name="item_sku[]" value="">' +
                '<input type="hidden" name="is_manual[]" value="0">' +
            '</td>' +
            '<td><input type="number" step="1" min="1" class="form-control form-control-sm item-qty" name="quantity[]" value="1" required></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm item-price" name="unit_price[]" value="0" data-auto="1" ' + (C.canChangePrice ? '' : 'readonly') + ' required></td>' +
            '<td class="item-line-total fw-semibold text-end">' + money(0) + '</td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-x-lg"></i></button></td>' +
        '</tr>';
        var $row = $(html);
        $('#itemsTable tbody').append($row);
        setupRow($row);
    });

    $('.btn-add-manual-row').on('click', function () {
        var html = '<tr class="item-row" data-is-manual="1">' +
            '<td>' +
                '<input type="text" class="form-control form-control-sm manual-name" name="item_name[]" placeholder="Descripción de la línea" required>' +
                '<input type="hidden" name="product_id[]" value="">' +
                '<input type="hidden" name="item_sku[]" value="">' +
                '<input type="hidden" name="is_manual[]" value="1">' +
            '</td>' +
            '<td><input type="number" step="1" min="1" class="form-control form-control-sm item-qty" name="quantity[]" value="1" required></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm item-price" name="unit_price[]" value="0" required></td>' +
            '<td class="item-line-total fw-semibold text-end">' + money(0) + '</td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-x-lg"></i></button></td>' +
        '</tr>';
        var $row = $(html);
        $('#itemsTable tbody').append($row);
        setupRow($row);
    });

    $('#itemsTable').on('click', '.btn-remove-row', function () {
        var rows = $('#itemsTable tbody .item-row');
        if (rows.length <= 1) {
            sgms.toast('error', 'Atención', 'Debe existir al menos una línea.');
            return;
        }
        $(this).closest('tr').remove();
        recomputeTotals();
    });

    $('#discount').on('input', recomputeTotals);
    $('#includes_gifts').on('change', function () {
        $('#giftWrap').toggle($(this).is(':checked'));
    });
    $('#is_parcel').on('change', function () {
        $('#transportWrap').toggle($(this).is(':checked'));
    });

    // El comprobante de pago es obligatorio si el cliente ya pago completo
    function updatePaidReceipt() {
        var paid = $('#paid_before_delivery').is(':checked');
        $('#payment_receipt').prop('required', paid);
        $('#paidReceiptHint').toggle(paid);
        $('#paymentReceiptLabel .text-muted-2').toggle(!paid);
    }
    $('#paid_before_delivery').on('change', updatePaidReceipt);
    updatePaidReceipt();

    // --- Mapa Leaflet para la ubicacion del pedido ---
    var orderMap = null;
    var orderMarker = null;

    function placeMarker(lat, lng) {
        if (!orderMap) return;
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (isNaN(lat) || isNaN(lng)) return;
        if (orderMarker) { orderMap.removeLayer(orderMarker); }
        orderMarker = L.marker([lat, lng], { draggable: true }).addTo(orderMap);
        orderMarker.on('dragend', function () {
            var p = orderMarker.getLatLng();
            $('#latitude').val(p.lat.toFixed(7));
            $('#longitude').val(p.lng.toFixed(7));
            $('#mapCoordText').text('  (' + p.lat.toFixed(5) + ', ' + p.lng.toFixed(5) + ')');
        });
        orderMap.setView([lat, lng], Math.max(orderMap.getZoom(), 15));
        $('#latitude').val(lat.toFixed(7));
        $('#longitude').val(lng.toFixed(7));
        $('#mapCoordText').text('  (' + lat.toFixed(5) + ', ' + lng.toFixed(5) + ')');
    }

    // --- Ubicacion aproximada a partir de la direccion de entrega ---
    var lastGeoAddress = '';
    var geoDebounce = null;

    function geocodeAddress(showError) {
        var address = $.trim($('#delivery_address').val());
        if (address === '' || address === lastGeoAddress) return;
        lastGeoAddress = address;
        $.ajax({
            url: C.geocodeUrl,
            type: 'POST',
            data: { address: address },
            dataType: 'json'
        }).done(function (res) {
            if (res.success && res.data && res.data.coords) {
                placeMarker(res.data.coords[0], res.data.coords[1]);
                sgms.toast('success', 'Ubicación aproximada', 'El pin se colocó según la dirección de entrega. Puede moverlo o hacer clic en otro punto.');
            } else if (showError) {
                sgms.toast('error', 'No se pudo ubicar', 'Fije el pin manualmente haciendo clic en el mapa.');
            }
        });
    }

    $('#delivery_address').on('input', function () {
        clearTimeout(geoDebounce);
        geoDebounce = setTimeout(function () { geocodeAddress(false); }, 900);
    }).on('blur', function () {
        clearTimeout(geoDebounce);
        geocodeAddress(true);
    });

    var mapEl = document.getElementById('orderMapPicker');
    if (mapEl && window.L) {
        var center = C.mapCenter || [9.928069, -84.090725];
        var hasInitial = C.initialLat != null && C.initialLng != null;
        orderMap = L.map('orderMapPicker').setView(center, hasInitial ? 15 : 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(orderMap);

        orderMap.on('click', function (e) {
            placeMarker(e.latlng.lat, e.latlng.lng);
        });

        if (hasInitial) {
            placeMarker(C.initialLat, C.initialLng);
        }
    }

    // Parseo de enlace de mapa
    $('#map_url').on('blur', function () {
        var url = $.trim($(this).val());
        if (!url) return;
        $.ajax({
            url: C.parseMapUrl,
            type: 'POST',
            data: { map_url: url },
            dataType: 'json'
        }).done(function (res) {
            if (res.success && res.data.coords) {
                $('#latitude').val(res.data.coords[0].toFixed(7));
                $('#longitude').val(res.data.coords[1].toFixed(7));
                placeMarker(res.data.coords[0], res.data.coords[1]);
                sgms.toast('success', 'Ubicación detectada', 'Coordenadas reconocidas del enlace.');
            }
        });
    });

    // Normalizacion de telefono
    $('#customer_phone').on('blur', function () {
        var digits = $(this).val().replace(/\D/g, '');
        if (digits.length === 8) {
            $('#phoneNormalized').text('Se usara el codigo +' + (C.dialCode || '506') + ': +' + (C.dialCode || '506') + ' ' + digits);
        } else if (digits.length > 0) {
            $('#phoneNormalized').text('Número internacional: +' + digits);
        } else {
            $('#phoneNormalized').text('');
        }
    });

    $('#customer_phone2').on('blur', function () {
        var digits = $(this).val().replace(/\D/g, '');
        if (digits.length === 8) {
            $('#phone2Normalized').text('Se usara el codigo +' + (C.dialCode || '506') + ': +' + (C.dialCode || '506') + ' ' + digits);
        } else if (digits.length > 0) {
            $('#phone2Normalized').text('Número internacional: +' + digits);
        } else {
            $('#phone2Normalized').text('');
        }
    });

    $('#orderForm').on('submit', function () {
        var valid = true;
        var message = '';
        if (!$.trim($('#customer_name_value').val())) {
            valid = false;
            message = 'Indique el nombre del cliente.';
        }
        if (valid) {
            $('#itemsTable tbody .item-row').each(function () {
                var name = $.trim($(this).find('input[name="item_name[]"]').val() || $(this).find('.product-select').val() || '');
                if (!name) { valid = false; message = 'Complete las líneas de productos antes de guardar.'; }
                var qty = integerQty($(this).find('.item-qty').val());
                if (qty < 1) { valid = false; message = 'Complete las cantidades de los productos.'; }
                var isManual = $(this).data('is-manual') == 1;
                if (valid && !isManual && !rowProductId($(this))) {
                    valid = false;
                    message = 'Seleccione el producto de cada línea de catálogo antes de guardar.';
                }
                $(this).find('.item-qty').val(qty || '');
            });
        }
        if (!valid) {
            sgms.toast('error', 'Atención', message);
            return false;
        }
        // La fecha de entrega solicitada no puede ser anterior a hoy
        // (en edicion se permite conservar la fecha original del pedido)
        var requestedDate = $.trim($('#requested_delivery_date').val());
        var originalDate = $.trim($('#requested_delivery_date').data('original') || '');
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        var todayStr = yyyy + '-' + mm + '-' + dd;
        if (requestedDate !== '' && requestedDate < todayStr && requestedDate !== originalDate) {
            sgms.toast('error', 'Atención', 'La fecha de entrega no puede ser anterior a hoy.');
            return false;
        }
        // Comprobante obligatorio si el cliente ya realizo el pago completo
        if ($('#paid_before_delivery').is(':checked') && !document.getElementById('payment_receipt').files.length) {
            sgms.toast('error', 'Comprobante requerido', 'Adjunte el comprobante del pago realizado para continuar.');
            $('#payment_receipt').focus();
            return false;
        }
        // Validación de inventario: la cantidad total por producto no debe superar el disponible
        var totals = currentQtyByPid();
        for (var pid in totals) {
            var avail = availableFor(parseInt(pid, 10));
            if (avail === null) continue;
            if (totals[pid] > avail) {
                valid = false;
                message = 'La cantidad solicitada supera el inventario disponible de uno o más productos. Revise las líneas marcadas.';
                break;
            }
        }
        if (!valid) {
            sgms.toast('error', 'Atención', message);
            return false;
        }
        return true;
    });

    initStockValidation();
    recomputeTotals();
});
