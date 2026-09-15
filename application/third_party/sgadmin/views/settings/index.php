<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Configuracion</h1>
        <p>Datos de la empresa y parametros del sistema</p>
    </div>
    <div>
        <button type="button" class="btn btn-brand" id="saveSettings"><i class="bi bi-check-lg me-1"></i>Guardar</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card sg-card h-100">
            <div class="sg-card-header"><h5><i class="bi bi-building me-2 text-danger"></i>Empresa</h5></div>
            <div class="sg-card-body">
                <div class="mb-3">
                    <label class="form-label" for="company_name">Nombre</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo html_escape($settings['company_name'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="company_phone">Teléfono</label>
                    <input type="text" class="form-control" id="company_phone" name="company_phone" value="<?php echo html_escape($settings['company_phone'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="company_email">Correo</label>
                    <input type="email" class="form-control" id="company_email" name="company_email" value="<?php echo html_escape($settings['company_email'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="company_address">Dirección</label>
                    <input type="text" class="form-control" id="company_address" name="company_address" value="<?php echo html_escape($settings['company_address'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="country_code">Código internacional</label>
                    <input type="text" class="form-control" id="country_code" name="country_code" value="<?php echo html_escape($settings['country_code'] ?? '506'); ?>" maxlength="4">
                    <div class="form-text">Usado para normalizar teléfonos (WhatsApp).</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="timezone">Zona horaria</label>
                    <input type="text" class="form-control" id="timezone" name="timezone" value="<?php echo html_escape($settings['timezone'] ?? 'America/Costa_Rica'); ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card sg-card h-100">
            <div class="sg-card-header"><h5><i class="bi bi-sliders me-2 text-danger"></i>Moneda y parametros</h5></div>
            <div class="sg-card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label" for="currency">Moneda</label>
                        <input type="text" class="form-control" id="currency" name="currency" value="<?php echo html_escape($settings['currency'] ?? 'CRC'); ?>">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label" for="currency_symbol">Símbolo</label>
                        <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?php echo html_escape($settings['currency_symbol'] ?? '₡'); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="evidence_max_size_mb">Tamaño máximo de evidencia (MB)</label>
                    <input type="number" min="1" max="50" class="form-control" id="evidence_max_size_mb" name="evidence_max_size_mb" value="<?php echo html_escape($settings['evidence_max_size_mb'] ?? '8'); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="orders_display_time">Formato de hora</label>
                    <select class="form-select" id="orders_display_time" name="orders_display_time">
                        <option value="12" <?php echo (($settings['orders_display_time'] ?? '12') == '12') ? 'selected' : ''; ?>>12 horas</option>
                        <option value="24" <?php echo (($settings['orders_display_time'] ?? '12') == '24') ? 'selected' : ''; ?>>24 horas</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="low_stock_threshold">Alerta de pocos productos (existencias)</label>
                    <input type="number" min="0" class="form-control" id="low_stock_threshold" name="low_stock_threshold" value="<?php echo html_escape($settings['low_stock_threshold'] ?? '5'); ?>">
                    <div class="form-text">Se alertará cuando un producto activo tenga existencias menores o iguales a este valor.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card sg-card">
            <div class="sg-card-header"><h5><i class="bi bi-shop me-2 text-danger"></i>Tienda web</h5></div>
            <div class="sg-card-body">
                <p class="text-muted-2">Estos datos se muestran en la tienda y se usan para los mensajes de los pedidos de la página web.</p>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="whatsapp_number">Número de la empresa (WhatsApp)</label>
                        <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" value="<?php echo html_escape($storeSettings['whatsapp_number'] ?? ''); ?>" placeholder="Ej: 50377777777">
                        <div class="form-text">Aquí se reciben los mensajes y pedidos de la página web. Incluye el código de país.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="contact_email">Correo de la empresa</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo html_escape($storeSettings['contact_email'] ?? ''); ?>" placeholder="ventas@empresa.com">
                        <div class="form-text">Correo donde llegan las notificaciones de la página web.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="business_hours">Horario de atención</label>
                        <input type="text" class="form-control" id="business_hours" name="business_hours" value="<?php echo html_escape($storeSettings['business_hours'] ?? ''); ?>" placeholder="Lun a Vie, 8:00 a 18:00">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="availability_min_stock">Existencias mínimas para mostrar "Disponible"</label>
                        <input type="number" min="1" class="form-control" id="availability_min_stock" name="availability_min_stock" value="<?php echo html_escape($storeSettings['availability_min_stock'] ?? '5'); ?>" style="max-width:180px;">
                        <div class="form-text">Si un producto tiene menos existencias que este valor, la tienda muestra "Pocas unidades" en vez de "Disponible". No aplica a productos sin control de inventario.</div>
                    </div>
                </div>
                <hr>
                <h6 class="mb-3">Redes sociales</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="instagram_url"><i class="bi bi-instagram me-1"></i>Instagram</label>
                        <input type="text" class="form-control" id="instagram_url" name="instagram_url" value="<?php echo html_escape($storeSettings['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/tuempresa">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="facebook_url"><i class="bi bi-facebook me-1"></i>Facebook</label>
                        <input type="text" class="form-control" id="facebook_url" name="facebook_url" value="<?php echo html_escape($storeSettings['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/tuempresa">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="tiktok_url"><i class="bi bi-tiktok me-1"></i>TikTok</label>
                        <input type="text" class="form-control" id="tiktok_url" name="tiktok_url" value="<?php echo html_escape($storeSettings['tiktok_url'] ?? ''); ?>" placeholder="https://tiktok.com/@tuempresa">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.sgmsSettings = { saveUrl: '<?php echo base_url('settings/save'); ?>' };
</script>
