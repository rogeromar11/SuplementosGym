/* ==========================================================================
   SGMensajeria - JS global
   ========================================================================== */
(function ($) {
    'use strict';

    // Nota: el manejo CSRF para AJAX se registra en el footer del layout
    // (script inline renderizado en cada pagina) para no depender de cache.

    // --- Utilidades ---------------------------------------------------------
    window.sgms = window.sgms || {};

	// CI3 puede regenerar el token despues de cada POST. Conserva el nuevo
	// valor devuelto por las respuestas JSON para la siguiente peticion AJAX.
	$(document).ajaxSuccess(function (event, xhr) {
		var response = xhr.responseJSON;
		if (response && response.csrf && response.csrf.name && response.csrf.token) {
			window.sgmsCsrf = response.csrf;
		}
	});

	// Si una peticion POST falla con 403 y respuesta HTML (CSRF de CI3 con
	// token/cookie caducados), renueva el token en silencio y reintenta una vez.
	// Se envuelve $.ajax para que los .done/.fail que las paginas ya registraron
	// se ejecuten con la respuesta del reintento (sin recargar ni perder datos).
	(function () {
		var _ajax = $.ajax;
		$.ajax = function (url, options) {
			var settings = (typeof url === 'object' && url !== null) ? url : (options || {});
			if (typeof url === 'string') settings.url = url;
			var xhr = _ajax.call($, settings);
			return xhr.then(null, function (jqxhr, textStatus, errorThrown) {
				if ((settings.type || '').toUpperCase() !== 'POST') return $.Deferred().reject(jqxhr, textStatus, errorThrown).promise();
				if (jqxhr.status !== 403 || jqxhr.responseJSON) return $.Deferred().reject(jqxhr, textStatus, errorThrown).promise();
				sgms.stopLoading();
				return $.get(window.sgmsCsrfUrl || 'auth/csrf').then(function (res) {
					if (!res || !res.success || !res.data || !res.data.token) {
						return $.Deferred().reject(jqxhr, textStatus, errorThrown).promise();
					}
					if (window.sgmsCsrf) window.sgmsCsrf.token = res.data.token;
					var d = settings.data;
					if (typeof d === 'string' && d.length) {
						d = d.replace(/csrf_sgms_token=[^&]*/, 'csrf_sgms_token=' + encodeURIComponent(res.data.token));
					} else if (d instanceof FormData) {
						d.set('csrf_sgms_token', res.data.token);
					} else if (d && typeof d === 'object') {
						d.csrf_sgms_token = res.data.token;
					}
					settings.data = d;
					return _ajax.call($, settings);
				}, function () {
					Swal.fire({
						icon: 'warning',
						title: 'Sesión de seguridad caducada',
						text: 'No se pudo renovar la protección. Se recargará la página.',
						confirmButtonText: 'Recargar',
						confirmButtonColor: '#DC2626'
					}).then(function () { window.location.reload(); });
					return $.Deferred().reject(jqxhr, textStatus, errorThrown).promise();
				});
			});
		};
	})();

    sgms.toast = function (icon, title, text) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text || '',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2800,
            timerProgressBar: true
        });
    };

    sgms.confirm = function (message, options) {
        options = options || {};
        return Swal.fire({
            title: options.title || 'Confirmar acción',
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'Si, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#71717A'
        });
    };

    sgms.ajax = function (url, data, opts) {
        opts = opts || {};
        return $.ajax({
            url: url,
            type: opts.method || 'POST',
            data: data,
            dataType: 'json'
        });
    };

    sgms.loading = function (message) {
        Swal.fire({
            title: message || 'Procesando...',
            allowOutsideClick: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });
    };

    sgms.stopLoading = function () {
        if (Swal.isLoading()) {
            Swal.close();
        }
    };

    // --- Inicializadores automaticos ----------------------------------------
    $(document).ready(function () {
        $('.select2').each(function () {
            var opts = {
                width: '100%',
                allowClear: $(this).hasClass('select2-allow-clear')
            };
            if ($(this).hasClass('select2-searchable') === false) {
                opts.minimumResultsForSearch = Infinity;
            }
            $(this).select2(opts);
        });

        $('.data-table').each(function () {
            var dt = $(this);
            var opts = {
                language: {
                    url: dt.data('lang-url')
                }
            };
            var order = dt.data('order');
            if (order) {
                opts.order = order;
            }
            if ($(this).hasClass('data-table-no-search')) {
                opts.dom = 'lrtip';
            }
            $(this).DataTable(opts);
        });
    });

    // --- Sidebar movil ------------------------------------------------------
    $(document).on('click', '[data-sidebar-toggle]', function () {
        $('.sg-sidebar').toggleClass('show');
        if ($('.sg-sidebar').hasClass('show')) {
            if ($('.sg-sidebar-backdrop').length === 0) {
                $('<div class="sg-sidebar-backdrop"></div>').appendTo('body');
            }
        } else {
            $('.sg-sidebar-backdrop').remove();
        }
    });

    // --- Boton "Tomar foto" para inputs de evidencia/comprobante (movil) ---
    // Usa el atributo capture de HTML5: en el telefono abre directamente la
    // camara trasera; en escritorio el boton se oculta por CSS. La foto pasa
    // por el mismo input y las validaciones del servidor (MIME y limite).
    $(function () {
        $('input[type="file"][data-camera]').each(function () {
            var input = this;
            if (!input.id || $(input).data('cameraWired')) return;
            $(input).data('cameraWired', true);
            $('<button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-1 btn-take-photo" data-camera-target="' + input.id + '"><i class="bi bi-camera me-1"></i>Tomar foto</button>').insertAfter(input);
        });
    });

    $(document).on('click', '.btn-take-photo', function () {
        var target = document.getElementById($(this).data('cameraTarget'));
        if (!target) return;
        target.setAttribute('capture', 'environment');
        $(target).off('change.sgmscam').on('change.sgmscam', function () {
            // Al elegir la foto se retira capture para que el boton "Elegir
            // archivo" vuelva a abrir la galeria/selector normal.
            target.removeAttribute('capture');
        });
        target.click();
    });

    $(document).on('click', '.sg-sidebar-backdrop', function () {
        $('.sg-sidebar').removeClass('show');
        $(this).remove();
    });

    // --- Previsualizacion de imagenes (lightbox) ---------------------------
    // Cualquier <a data-lightbox href="imagen"> abre la imagen en grande en la
    // misma pagina, sin abrir otra ventana.
    var lightbox = null;
    function closeLightbox() {
        if (!lightbox) { return; }
        lightbox.removeClass('is-open');
        $('body').removeClass('sg-lightbox-open');
    }
    function openLightbox(src) {
        if (!lightbox) {
            lightbox = $(
                '<div class="sg-lightbox" role="dialog" aria-modal="true" aria-label="Vista previa de imagen">' +
                '<button type="button" class="sg-lightbox-close" aria-label="Cerrar">&times;</button>' +
                '<img alt="Vista previa">' +
                '</div>'
            );
            $('body').append(lightbox);
            lightbox.on('click', function (e) {
                if (e.target === this || $(e.target).hasClass('sg-lightbox-close')) {
                    closeLightbox();
                }
            });
            $(document).on('keydown.sglightbox', function (e) {
                if (e.key === 'Escape') { closeLightbox(); }
            });
        }
        lightbox.find('img').attr('src', src);
        lightbox.addClass('is-open');
        $('body').addClass('sg-lightbox-open');
    }
    $(document).on('click', 'a[data-lightbox]', function (e) {
        var href = $(this).attr('href');
        if (!href) { return; }
        e.preventDefault();
        openLightbox(href);
    });
})(jQuery);
