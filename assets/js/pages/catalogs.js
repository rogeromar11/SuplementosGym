$(document).ready(function () {
    var cfg = window.sgmsCatalog;
    if (!cfg) return;

    var modalEl = document.getElementById('catalogModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    function buildField(f) {
        var req = f.required ? 'required' : '';
        var readonly = f.readonly ? 'readonly' : '';
        var id = 'f_' + f.name;
        var label = '<label class="form-label" for="' + id + '">' + f.label + (req ? ' *' : '') + '</label>';
        var control = '';
        switch (f.type) {
            case 'checkbox':
                control = '<div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="' + id + '" name="' + f.name + '" value="1" ' + ((f.default || f.default === 1) ? 'checked' : '') + '><label class="form-check-label" for="' + id + '">' + f.label + '</label></div>';
                break;
            case 'number':
                control = '<input type="number" class="form-control" id="' + id + '" name="' + f.name + '" value="' + (f.default || 0) + '" ' + req + '>';
                break;
            case 'time':
                control = '<input type="time" class="form-control" id="' + id + '" name="' + f.name + '">';
                break;
            case 'select':
                var opts = '<option value="">Seleccione...</option>';
                (f.options || []).forEach(function (o) {
                    opts += '<option value="' + o.value + '">' + o.label + '</option>';
                });
                control = '<select class="form-select" id="' + id + '" name="' + f.name + '" ' + req + '>' + opts + '</select>';
                break;
            case 'textarea':
                control = '<textarea class="form-control" id="' + id + '" name="' + f.name + '" rows="3" placeholder="' + (f.placeholder || '') + '" ' + req + '></textarea>';
                break;
            default:
                control = '<input type="text" class="form-control" id="' + id + '" name="' + f.name + '" placeholder="' + (f.placeholder || '') + '" ' + readonly + ' ' + req + '>';
        }
        var hint = '';
        if (f.foreignNumber) {
            hint = '<div class="form-text foreign-number-hint d-none" id="hint_' + f.name + '"><i class="bi bi-globe me-1"></i>Número extranjero: se usará tal cual, sin agregar el código local.</div>';
        }
        return '<div class="col-md-' + (f.col || 12) + '">' + (f.type === 'checkbox' ? control : label + control + hint) + '</div>';
    }

    function updateForeignHints() {
        cfg.fields.forEach(function (f) {
            if (!f.foreignNumber) return;
            var $hint = $('#hint_' + f.name);
            if (!$hint.length) return;
            var v = $.trim($('#f_' + f.name).val() || '');
            $hint.toggleClass('d-none', v.charAt(0) !== '+');
        });
    }

    function bindForeignHints() {
        cfg.fields.forEach(function (f) {
            if (!f.foreignNumber) return;
            $('#f_' + f.name).on('input change', updateForeignHints);
        });
    }

    function buildFields() {
        $('#catalogFields').html(cfg.fields.map(buildField).join(''));
    }

    function fillForm(data) {
        cfg.fields.forEach(function (f) {
            var $el = $('#f_' + f.name);
            var val = data ? data[f.name] : (f.default !== undefined ? f.default : '');
            if (f.type === 'checkbox') {
                $el.prop('checked', val === 1 || val === '1' || val === true);
            } else if (f.type === 'select') {
                $el.val(val === null || val === '' ? (f.default !== undefined ? f.default : '') : val);
            } else {
                $el.val(val === null ? '' : val);
            }
        });
        updateForeignHints();
    }

    // Construir los campos del modal una vez
    buildFields();
    bindForeignHints();

    $('.btn-add-item').on('click', function () {
        $('#catalogId').val('');
        $('#catalogModalTitle').text('Nuevo');
        fillForm(null);
        modal.show();
    });

    $('.btn-edit-item').on('click', function () {
        var data = $(this).closest('tr').data('json');
        $('#catalogId').val(data.id);
        $('#catalogModalTitle').text('Editar');
        fillForm(data);
        modal.show();
    });

    $('#catalogForm').on('submit', function (e) {
        e.preventDefault();
        sgms.loading('Guardando...');
        var data = $(this).serialize();
        $.ajax({
            url: cfg.saveEndpoint,
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function (res) {
            sgms.stopLoading();
            if (res.success) {
                modal.hide();
                sgms.toast('success', 'Correcto', res.message);
                setTimeout(function () { window.location.reload(); }, 800);
            } else {
                sgms.toast('error', 'Error', res.message);
            }
        }).fail(function (xhr) {
            sgms.stopLoading();
            sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible guardar.');
        });
    });

    $('.btn-delete-item').on('click', function () {
        var url = $(this).data('url');
        sgms.confirm('Se eliminará este registro. Continuar?').then(function (r) {
            if (!r.isConfirmed) return;
            sgms.loading('Eliminando...');
            $.ajax({ url: url, type: 'POST', dataType: 'json' })
                .done(function (res) {
                    sgms.stopLoading();
                    if (res.success) {
                        sgms.toast('success', 'Correcto', res.message);
                        setTimeout(function () { window.location.reload(); }, 800);
                    } else {
                        sgms.toast('error', 'Error', res.message);
                    }
                })
                .fail(function (xhr) {
                    sgms.stopLoading();
                    if (xhr.status === 403 && !xhr.responseJSON) return; // CSRF: lo gestiona app.js (recarga)
                    sgms.toast('error', 'Error', xhr.responseJSON ? xhr.responseJSON.message : 'No fue posible eliminar. (' + (xhr.status || '') + ')');
                });
        });
    });
});
