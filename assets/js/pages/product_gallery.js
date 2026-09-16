(function () {
    'use strict';

    var cfg = window.sgmsProductGallery;
    if (!cfg || !cfg.productId) { return; }

    var grid = document.getElementById('galleryGrid');
    var empty = document.getElementById('galleryEmpty');
    var input = document.getElementById('galleryInput');
    var pick = document.getElementById('galleryPick');
    var progress = document.getElementById('galleryProgress');
    if (!grid) { return; }

    function render(images) {
        grid.innerHTML = '';
        if (!images || !images.length) {
            if (empty) { empty.style.display = ''; }
            return;
        }
        if (empty) { empty.style.display = 'none'; }
        images.forEach(function (im) {
            var col = document.createElement('div');
            col.className = 'col-4 col-md-3';
            var star = im.is_main ? 'bi-star-fill' : 'bi-star';
            var mainBtn = im.is_main ? 'btn-brand' : 'btn-outline-brand';
            col.innerHTML =
                '<div class="border rounded p-2 text-center" style="border-color:' + (im.is_main ? '#7C3AED' : '#e5e7eb') + ';">' +
                    '<img src="' + im.url + '" alt="" style="width:100%;height:110px;object-fit:cover;border-radius:8px;">' +
                    '<div class="d-flex justify-content-center gap-1 mt-2">' +
                        '<button type="button" class="btn btn-sm ' + mainBtn + ' btn-set-main" data-id="' + im.id + '" title="Marcar como principal"><i class="bi ' + star + '"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger btn-del-img" data-id="' + im.id + '" title="Eliminar imagen"><i class="bi bi-trash"></i></button>' +
                    '</div>' +
                    (im.is_main ? '<span class="badge text-bg-primary mt-2">Principal</span>' : '') +
                '</div>';
            grid.appendChild(col);
        });
    }

    function toastError(message) {
        if (window.sgms && sgms.toast) {
            sgms.toast('error', 'Error', message || 'No fue posible completar la acción.');
        } else {
            alert(message || 'Error');
        }
    }

    function send(url) {
        $.ajax({ url: url, type: 'POST', dataType: 'json' })
            .done(function (res) {
                if (res && res.success && res.data && res.data.images) {
                    render(res.data.images);
                } else {
                    toastError(res && res.message);
                }
            })
            .fail(function (xhr) {
                toastError(xhr.responseJSON && xhr.responseJSON.message);
            });
    }

    if (pick && input) {
        pick.addEventListener('click', function () { input.click(); });
    }

    if (input) {
        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) { return; }
            var fd = new FormData();
            for (var i = 0; i < input.files.length; i++) {
                fd.append('images[]', input.files[i]);
            }
            if (progress) { progress.style.display = ''; }
            if (pick) { pick.disabled = true; }

            $.ajax({
                url: cfg.uploadUrl,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function (res) {
                if (res && res.success && res.data && res.data.images) {
                    render(res.data.images);
                } else {
                    toastError(res && res.message);
                }
            }).fail(function (xhr) {
                toastError(xhr.responseJSON && xhr.responseJSON.message);
            }).always(function () {
                input.value = '';
                if (progress) { progress.style.display = 'none'; }
                if (pick) { pick.disabled = false; }
            });
        });
    }

    grid.addEventListener('click', function (e) {
        var del = e.target.closest ? e.target.closest('.btn-del-img') : null;
        if (del) {
            if (confirm('¿Eliminar esta imagen?')) {
                send(cfg.deleteUrlBase + del.getAttribute('data-id'));
            }
            return;
        }
        var main = e.target.closest ? e.target.closest('.btn-set-main') : null;
        if (main) {
            send(cfg.mainUrlBase + main.getAttribute('data-id'));
        }
    });

    render(cfg.images || []);
})();
