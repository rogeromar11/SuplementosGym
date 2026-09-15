<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
            </main>
        </div>
    </div>

    <script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/datatables/dataTables.bootstrap5.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/select2/select2.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/sweetalert2/sweetalert2.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/sortablejs/Sortable.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/chartjs/chart.umd.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/leaflet/leaflet.js'); ?>"></script>
    <script>
        window.sgmsCsrf = { name: '<?php echo $csrf_name; ?>', token: '<?php echo $csrf_hash; ?>' };
        window.sgmsCsrfUrl = '<?php echo base_url('auth/csrf'); ?>';
        // Agrega el token CSRF a todas las peticiones AJAX POST.
        // CI3 valida el token por campo POST; la cookie CSRF es HttpOnly (no se lee via JS).
        (function ($) {
            $(document).ajaxSend(function (event, jqxhr, settings) {
                if (settings.type.toUpperCase() !== 'POST') return;
                var token = window.sgmsCsrf && window.sgmsCsrf.token;
                if (!token) return;
                var d = settings.data;
                if (typeof d === 'string' && d.length) {
                    settings.data = d + '&csrf_sgms_token=' + encodeURIComponent(token);
                } else if (typeof d === 'object' && d !== null && !(d instanceof FormData)) {
                    d.csrf_sgms_token = token;
                } else if (d instanceof FormData) {
                    d.append('csrf_sgms_token', token);
                } else {
                    settings.data = 'csrf_sgms_token=' + encodeURIComponent(token);
                    // jQuery solo fija Content-Type si habia 'data'; sin data el
                    // navegador usaria text/plain y PHP no llenaria $_POST.
                    jqxhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                }
                jqxhr.setRequestHeader('X-CSRF-TOKEN', token);
            });
        })(jQuery);
    </script>
    <script src="<?php echo asset_url('assets/js/app.js'); ?>"></script>
    <?php if (isset($pageScripts)): foreach ((array)$pageScripts as $script): ?>
        <script src="<?php echo asset_url(html_escape($script)); ?>"></script>
    <?php endforeach; endif; ?>
    <script>
        if (typeof window.dataTablesLangUrl === 'undefined') {
            window.dataTablesLangUrl = null;
        }
    </script>
    <?php if (isset($flash)): ?>
    <script>
        $(document).ready(function () {
            var f = <?php echo json_encode($flash); ?>;
            if (f && f.message) {
                sgms.toast(f.success ? 'success' : 'error', f.success ? 'Operación exitosa' : 'Atención', f.message);
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
