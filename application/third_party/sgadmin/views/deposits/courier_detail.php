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
    <a href="<?php echo base_url('deposits/mine'); ?>" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <?php echo '<span class="badge text-bg-' . (isset($statusColors[$deposit->status]) ? $statusColors[$deposit->status] : 'secondary') . '">' . html_escape(isset($statusLabels[$deposit->status]) ? $statusLabels[$deposit->status] : $deposit->status) . '</span>'; ?>
</div>

<div class="courier-route-card mb-3">
    <div class="card-head">
        <small>Depósito #<?php echo (int)$deposit->id; ?> · <?php echo fmt_datetime($deposit->created_at); ?></small>
        <h2><?php echo money($deposit->amount); ?></h2>
        <small><?php echo (int)$deposit->route_count; ?> ruta(s) · Recibió: <?php echo html_escape($deposit->receiver_name); ?></small>
        <?php if ((float)$deposit->expenses_total > 0): ?>
            <small class="text-danger">Otros gastos: <?php echo money($deposit->expenses_total); ?></small>
        <?php endif; ?>
    </div>
    <div class="card-body py-2">
        <?php foreach ($deposit->routes as $r): ?>
            <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid #E4E4E7;">
                <div><code><?php echo html_escape($r->route_number); ?></code></div>
                <div class="fw-semibold text-end"><?php echo money($r->amount); ?><?php if ((float)$r->expenses_total > 0): ?><br><small class="text-danger">Gastos: <?php echo money($r->expenses_total); ?></small><?php endif; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ((float)$deposit->expenses_total > 0): ?>
<div class="courier-delivery mb-3">
    <div class="d-body py-3">
        <div class="d-label mb-1">Detalle de gastos adicionales</div>
        <?php foreach ($deposit->routes as $r): ?>
            <?php foreach ($r->expenses as $e): ?>
                <div class="d-flex justify-content-between mb-1">
                    <span class="small"><?php echo html_escape($e->order_number ?: 'Pedido'); ?></span>
                    <span class="text-end small"><?php echo money($e->extra_expense); ?><?php echo $e->extra_expense_detail ? '<br><small class="text-muted">' . nl2br(html_escape($e->extra_expense_detail)) . '</small>' : ''; ?></span>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="courier-delivery mb-3">
    <div class="d-body py-3">
        <div class="d-flex justify-content-between mb-1"><span class="d-label">Comprobante del depósito</span>
            <?php if ($deposit->courier_receipt_file): ?>
                <a href="<?php echo base_url('uploads/deposit_receipts/' . $deposit->courier_receipt_file); ?>" target="_blank">Ver archivo</a>
            <?php else: ?><span class="text-muted-2">—</span><?php endif; ?>
        </div>
        <?php if ($deposit->status !== 'pendiente'): ?>
            <div class="d-flex justify-content-between mb-1"><span class="d-label">Confirmado por</span><span><?php echo html_escape($deposit->receiver_name); ?> · <?php echo fmt_datetime($deposit->aux_confirmed_at); ?></span></div>
        <?php endif; ?>
        <?php if ($deposit->status === 'entregado' || $deposit->status === 'aprobado'): ?>
            <div class="d-flex justify-content-between mb-1"><span class="d-label">Entregado al admin</span><span><?php echo html_escape($deposit->admin_receiver_name); ?> · <?php echo fmt_datetime($deposit->aux_receipt_at); ?></span></div>
            <div class="d-flex justify-content-between mb-1"><span class="d-label">Comprobante de entrega</span>
                <?php if ($deposit->aux_receipt_file): ?>
                    <a href="<?php echo base_url('uploads/deposit_receipts/' . $deposit->aux_receipt_file); ?>" target="_blank">Ver archivo</a>
                <?php else: ?><span class="text-muted-2">—</span><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($deposit->status === 'aprobado'): ?>
            <div class="d-flex justify-content-between"><span class="d-label">Visto bueno</span><span class="text-success"><?php echo html_escape($deposit->confirmed_by_name); ?> · <?php echo fmt_datetime($deposit->confirmed_at); ?></span></div>
        <?php endif; ?>
        <?php if ($deposit->notes): ?>
            <div class="d-flex justify-content-between mt-2"><span class="d-label">Nota</span><span class="text-end"><?php echo html_escape($deposit->notes); ?></span></div>
        <?php endif; ?>
    </div>
</div>