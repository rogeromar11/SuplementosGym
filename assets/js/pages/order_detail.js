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

    // Forma de pago y nota del pedido (editable solo hasta antes de la entrega)
    var paymentNoteForm = document.getElementById('paymentNoteForm');
    if (paymentNoteForm) {
        paymentNoteForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var method = $('#paymentNoteMethod').val();
            if (!method) {
                sgms.toast('error', 'Atención', 'Seleccione una forma de pago.');
                return;
            }
            sgms.loading('Guardando...');
            $.ajax({
                url: $(paymentNoteForm).data('url'),
                type: 'POST',
                data: {
                    payment_method_id: method,
                    notes: $('#paymentNoteNotes').val()
                },
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    var modalEl = document.getElementById('paymentNoteModal');
                    if (modalEl) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { window.location.reload(); }, 700);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) {
                sgms.stopLoading();
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible guardar los cambios.');
            });
        });
    }

    // Corrección de pedidos entregados (solo administrador)
    var correctionForm = document.getElementById('deliveredCorrectionForm');
    if (correctionForm) {
        var correctionModalEl = document.getElementById('deliveredCorrectionModal');
        var correctionCurrency = correctionModalEl ? (correctionModalEl.getAttribute('data-currency') || '') : '';

        function correctionMoney(value) {
            var n = Number(value) || 0;
            return correctionCurrency + ' ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function correctionTotals() {
            var subtotal = 0;
            $(correctionForm).find('.correction-price').each(function () {
                var price = parseFloat($(this).val());
                if (isNaN(price) || price < 0) { price = 0; }
                var qty = parseInt($(this).attr('data-qty'), 10) || 0;
                var line = price * qty;
                subtotal += line;
                $(this).closest('tr').find('.correction-line-total').text(correctionMoney(line));
            });
            var discount = parseFloat($('#correctionDiscount').val());
            if (isNaN(discount) || discount < 0) { discount = 0; }
            var shipping = parseFloat($('#correctionShipping').val()) || 0;
            var total = Math.max(0, subtotal + shipping - discount);
            var paid = parseFloat($('#correctionPaid').val());
            if (isNaN(paid) || paid < 0) { paid = 0; }
            var balance = Math.max(0, total - paid);
            $('#correctionSubtotal').text(correctionMoney(subtotal));
            $('#correctionDiscountOut').text(correctionMoney(discount));
            $('#correctionTotal').text(correctionMoney(total));
            $('#correctionBalance').text(correctionMoney(balance));
            return { subtotal: subtotal, discount: discount };
        }

        if (correctionModalEl) {
            $(correctionModalEl).on('shown.bs.modal', correctionTotals);
        }
        $(correctionForm).on('input change', '.correction-price, #correctionDiscount, #correctionPaid', correctionTotals);

        correctionForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var invalid = false;
            $(correctionForm).find('.correction-price').each(function () {
                var v = $(this).val();
                if (v === '' || isNaN(parseFloat(v)) || parseFloat(v) < 0) { invalid = true; }
            });
            if (invalid) {
                sgms.toast('error', 'Atención', 'Indique un precio válido para todos los productos.');
                return;
            }
            var paidVal = $('#correctionPaid').val();
            if (paidVal !== '' && (isNaN(parseFloat(paidVal)) || parseFloat(paidVal) < 0)) {
                sgms.toast('error', 'Atención', 'Indique un monto pagado válido.');
                return;
            }
            var totals = correctionTotals();
            if (totals.discount > totals.subtotal + 0.001) {
                sgms.toast('error', 'Atención', 'El descuento no puede superar el subtotal.');
                return;
            }
            sgms.loading('Guardando corrección...');
            $.ajax({
                url: $(correctionForm).data('url'),
                type: 'POST',
                data: $(correctionForm).serialize(),
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    if (correctionModalEl) {
                        bootstrap.Modal.getOrCreateInstance(correctionModalEl).hide();
                    }
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { window.location.reload(); }, 700);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) {
                sgms.stopLoading();
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible guardar la corrección.');
            });
        });
    }

    // Devolución de productos en pedidos entregados (solo administrador)
    var returnForm = document.getElementById('returnItemsForm');
    if (returnForm) {
        var returnModalEl = document.getElementById('returnItemsModal');
        var returnCurrency = returnModalEl ? (returnModalEl.getAttribute('data-currency') || '') : '';

        function returnMoney(value) {
            var n = Number(value) || 0;
            return returnCurrency + ' ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function updateReturnAmounts() {
            var total = 0;
            $(returnForm).find('.return-item-check').each(function () {
                var check = $(this);
                var row = check.closest('tr');
                var price = parseFloat(row.attr('data-price')) || 0;
                var amount = 0;
                if (check.is(':checked')) {
                    var qtyInput = document.getElementById(check.attr('data-target'));
                    var max = parseInt(check.attr('data-qty'), 10) || 0;
                    var qty = qtyInput ? (parseInt(qtyInput.value, 10) || 0) : 0;
                    if (qty > max) {
                        qty = max;
                        if (qtyInput) { qtyInput.value = max; }
                    }
                    if (qty < 1) { qty = 0; }
                    amount = price * qty;
                }
                row.find('.return-item-amount').text(returnMoney(amount));
                total += amount;
            });
            $('#returnItemsTotal').text(returnMoney(total));
        }

        function toggleReturnRow(check) {
            var target = document.getElementById(check.attr('data-target'));
            var max = parseInt(check.attr('data-qty'), 10) || 0;
            if (target) {
                target.disabled = !check.is(':checked');
                if (check.is(':checked')) {
                    var value = parseInt(target.value, 10);
                    if (!value || value < 1 || value > max) {
                        target.value = max;
                    }
                }
            }
            updateReturnAmounts();
        }

        $(returnForm).on('change', '.return-item-check', function () {
            toggleReturnRow($(this));
        });
        $(returnForm).on('input change', '.return-item-qty', function () {
            var max = parseInt($(this).attr('max'), 10) || 0;
            var value = parseInt($(this).val(), 10);
            if (value > max) {
                $(this).val(max);
            }
            updateReturnAmounts();
        });

        if (returnModalEl) {
            $(returnModalEl).on('shown.bs.modal', function () {
                $(returnForm).find('.return-item-check').each(function () {
                    var check = $(this);
                    check.prop('checked', false);
                    var target = document.getElementById(check.attr('data-target'));
                    if (target) {
                        target.disabled = true;
                        target.value = check.attr('data-qty');
                    }
                });
                updateReturnAmounts();
            });
        }

        returnForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var checked = $(returnForm).find('.return-item-check:checked');
            if (!checked.length) {
                sgms.toast('error', 'Atención', 'Seleccione al menos un producto para devolver.');
                return;
            }
            var invalid = false;
            checked.each(function () {
                var check = $(this);
                var target = document.getElementById(check.attr('data-target'));
                var max = parseInt(check.attr('data-qty'), 10) || 0;
                var qty = target ? parseInt(target.value, 10) : 0;
                if (!qty || qty < 1 || qty > max) { invalid = true; }
            });
            if (invalid) {
                sgms.toast('error', 'Atención', 'Indique una cantidad válida para cada producto seleccionado.');
                return;
            }
            updateReturnAmounts();
            sgms.loading('Registrando devolución...');
            $.ajax({
                url: $(returnForm).data('url'),
                type: 'POST',
                data: $(returnForm).serialize(),
                dataType: 'json'
            }).done(function (res) {
                sgms.stopLoading();
                if (res.success) {
                    if (returnModalEl) {
                        bootstrap.Modal.getOrCreateInstance(returnModalEl).hide();
                    }
                    sgms.toast('success', 'Correcto', res.message);
                    setTimeout(function () { window.location.reload(); }, 800);
                } else {
                    sgms.toast('error', 'Error', res.message);
                }
            }).fail(function (xhr) {
                sgms.stopLoading();
                sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible registrar la devolución.');
            });
        });
    }
});
