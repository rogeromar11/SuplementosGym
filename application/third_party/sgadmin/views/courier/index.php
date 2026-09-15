<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->data['courierTab'] = 'home';
?>
<div class="sg-page-header">
    <div>
        <h1>Mis rutas</h1>
        <p><?php echo fmt_date($today); ?></p>
    </div>
</div>

<?php if (!empty($staleRoutes)): ?>
<div class="alert alert-warning d-flex justify-content-between align-items-center py-2 mb-3">
    <div class="small">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Rutas pendientes de días anteriores</strong><br>
        Tienes <?php echo count($staleRoutes); ?> ruta(s) sin finalizar.
    </div>
    <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#staleRoutesBox" aria-expanded="true">Ver</button>
</div>
<div class="collapse show mb-3" id="staleRoutesBox">
    <?php foreach ($staleRoutes as $sr): ?>
        <div class="courier-delivery mb-2">
            <div class="d-head">
                <span class="d-order-num" style="background:#F59E0B;"><i class="bi bi-exclamation-lg"></i></span>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><code><?php echo html_escape($sr->route_number); ?></code></div>
                    <div class="small text-muted-2"><?php echo html_escape($sr->shift_name); ?> · <?php echo fmt_date($sr->route_date); ?> · <?php echo html_escape($sr->warehouse_name ?: '—'); ?></div>
                </div>
                <?php echo route_status_badge($sr->status); ?>
            </div>
            <div class="d-actions" style="grid-template-columns:1fr;">
                <a href="<?php echo base_url('courier/route/' . $sr->id); ?>" class="btn btn-warning w-100"><i class="bi bi-check2-square me-1"></i>Ir a finalizar</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($routes)): ?>
    <div class="courier-route-card">
        <div class="card-body text-center py-5">
            <i class="bi bi-signpost-2 fs-1 text-muted-2 d-block mb-2"></i>
            <p class="text-muted-2 mb-0">No tienes rutas activas para hoy.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($routes as $r): ?>
        <?php $s = $this->Route_model->summary($r->id); ?>
        <div class="courier-route-card">
            <div class="card-head">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-truck me-1"></i><?php echo html_escape($r->route_number); ?></h2>
                    <?php echo route_status_badge($r->status); ?>
                </div>
                <small><i class="bi bi-clock me-1"></i><?php echo html_escape($r->shift_name); ?> · <i class="bi bi-buildings me-1"></i><?php echo html_escape($r->warehouse_name ?: '—'); ?></small>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <div class="text-center flex-fill"><div class="d-value"><?php echo (int)$s->total_orders; ?></div><div class="d-label">Pedidos</div></div>
                    <div class="text-center flex-fill"><div class="d-value text-success"><?php echo (int)$s->delivered; ?></div><div class="d-label">Entregados</div></div>
                    <div class="text-center flex-fill"><div class="d-value text-danger"><?php echo (int)$s->not_delivered; ?></div><div class="d-label">No entregados</div></div>
                    <div class="text-center flex-fill"><div class="d-value"><?php echo money($s->collected_amount); ?></div><div class="d-label">Cobrado</div></div>
                </div>
                <a href="<?php echo base_url('courier/route/' . $r->id); ?>" class="btn btn-brand w-100 courier-btn"><i class="bi bi-play-fill me-1"></i>Abrir ruta</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
