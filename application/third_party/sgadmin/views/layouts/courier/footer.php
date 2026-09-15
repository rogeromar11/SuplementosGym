<?php defined('BASEPATH') OR exit('No direct script access allowed');
$activeTab = isset($courierTab) ? $courierTab : 'home';
?>
        </main>
    </div>

    <nav class="courier-bottombar" aria-label="Navegacion">
        <a href="<?php echo base_url('courier'); ?>" class="<?php echo $activeTab === 'home' ? 'active' : ''; ?>">
            <i class="bi bi-house-door"></i>Mis rutas
        </a>
        <a href="<?php echo base_url('deposits/mine'); ?>" class="<?php echo $activeTab === 'depositos' ? 'active' : ''; ?>">
            <i class="bi bi-cash-stack"></i>Depósitos
        </a>
        <a href="tel:<?php echo html_escape(app_setting('company_phone', '')); ?>" class="<?php echo $activeTab === 'call' ? 'active' : ''; ?>">
            <i class="bi bi-telephone"></i>Llamar
        </a>
        <a href="<?php echo base_url('auth/logout'); ?>">
            <i class="bi bi-box-arrow-right"></i>Salir
        </a>
    </nav>

    <script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/sweetalert2/sweetalert2.min.js'); ?>"></script>
    <script>
        window.sgmsCsrf = { name: '<?php echo $csrf_name; ?>', token: '<?php echo $csrf_hash; ?>' };
        window.sgmsCsrfUrl = '<?php echo base_url('auth/csrf'); ?>';
        // Agrega el token CSRF a todas las peticiones AJAX POST.
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
</body>
</html>
