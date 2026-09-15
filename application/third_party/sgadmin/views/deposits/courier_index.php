<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statusLabels = array(
    'pendiente' => 'Pendiente de confirmar',
    'recibido' => 'Recibido',
    'entregado' => 'Entregado al admin',
    'aprobado' => 'Aprobado',
);
$statusColors = array('pendiente' => 'warning', 'recibido' => 'info', 'entregado' => 'primary', 'aprobado' => 'success');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Depósitos por ruta</h1>
    <?php if (!empty($pendingRoutes)): ?>
        <a href="<?php echo base_url('deposits/create'); ?>" class="btn btn-brand btn-sm"><i class="bi bi-cash-coin me-1"></i>Depositar</a>
    <?php endif; ?>
</div>
<div class="text-muted-2 small mb-3">Efectivo cobrado en rutas finalizadas que aún no ha depositado.</div>

<div class="courier-route-card mb-3">
    <div class="card-head">
        <small>Total pendiente de depositar</small>
        <h2><?php echo money($pendingTotal); ?></h2>
        <small><?php echo count($pendingRoutes); ?> ruta(s) pendiente(s)</small>
    </div>
    <div class="card-body py-2">
        <?php if (empty($pendingRoutes)): ?>
            <div class="text-center text-muted-2 py-2 small">No tiene rutas con efectivo pendiente. ¡Todo depositado!</div>
        <?php else: ?>
            <?php foreach ($pendingRoutes as $pr): ?>
                <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #E4E4E7;">
                    <div>
                        <code><?php echo html_escape($pr->route_number); ?></code>
                        <div class="small text-muted-2"><?php echo html_escape($pr->shift_name); ?> · <?php echo fmt_date($pr->route_date); ?></div>
                    </div>
                    <div class="fw-bold"><?php echo money($pr->cash_amount); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<h2 class="d-label mb-2">Mis depósitos</h2>
<?php if (empty($deposits)): ?>
    <div class="courier-route-card"><div class="card-body text-center py-4 text-muted-2">Aún no ha registrado depósitos.</div></div>
<?php else: ?>
    <?php foreach ($deposits as $d): ?>
        <a href="<?php echo base_url('deposits/my_detail/' . $d->id); ?>" class="text-decoration-none text-reset">
            <div class="courier-delivery mb-2">
                <div class="d-head">
                    <span class="d-order-num"><i class="bi bi-cash-stack"></i></span>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">Depósito #<?php echo (int)$d->id; ?> · <?php echo fmt_date($d->created_at); ?></div>
                        <div class="small text-muted-2"><?php echo (int)$d->route_count; ?> ruta(s) · Recibió: <?php echo html_escape($d->receiver_name); ?></div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold"><?php echo money($d->amount); ?></div>
                        <span class="badge text-bg-<?php echo isset($statusColors[$d->status]) ? $statusColors[$d->status] : 'secondary'; ?>"><?php echo html_escape(isset($statusLabels[$d->status]) ? $statusLabels[$d->status] : $d->status); ?></span>
                    </div>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>