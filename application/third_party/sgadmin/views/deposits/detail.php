<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statusLabels = array(
    'pendiente' => 'Pendiente de confirmar',
    'recibido' => 'Recibido (pendiente de entregar)',
    'entregado' => 'Entregado al administrador',
    'aprobado' => 'Aprobado',
);
$statusColors = array('pendiente' => 'warning', 'recibido' => 'info', 'entregado' => 'primary', 'aprobado' => 'success');
$isImage = function ($mime) {
    return strpos((string)$mime, 'image/') === 0;
};
?>
<div class="sg-page-header">
    <div>
        <h1>Depósito #<?php echo (int)$deposit->id; ?></h1>
        <p>Registrado el <?php echo fmt_datetime($deposit->created_at); ?> por el mensajero <?php echo html_escape($deposit->courier_name); ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php echo '<span class="badge text-bg-' . (isset($statusColors[$deposit->status]) ? $statusColors[$deposit->status] : 'secondary') . ' fs-6">' . html_escape(isset($statusLabels[$deposit->status]) ? $statusLabels[$deposit->status] : $deposit->status) . '</span>'; ?>
        <a href="<?php echo back_url('deposits'); ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Regresar</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md">
        <div class="card sg-card"><div class="card-body">
            <div class="sg-stat-label">Monto depositado</div>
            <div class="sg-stat-value fs-4"><?php echo money($deposit->amount); ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card"><div class="card-body">
            <div class="sg-stat-label">Rutas incluidas</div>
            <div class="sg-stat-value fs-4"><?php echo (int)$deposit->route_count; ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card"><div class="card-body">
            <div class="sg-stat-label">Recibió del mensajero</div>
            <div class="sg-stat-value fs-6"><?php echo html_escape($deposit->receiver_name); ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card"><div class="card-body">
            <div class="sg-stat-label">Entregado al administrador</div>
            <div class="sg-stat-value fs-6"><?php echo $deposit->admin_receiver_user_id ? html_escape($deposit->admin_receiver_name) : '—'; ?></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-signpost-split me-2 text-danger"></i>Rutas incluidas en el depósito</h5></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Ruta</th><th>Fecha</th><th>Turno</th><th class="text-end">Efectivo</th></tr></thead>
                    <tbody>
                        <?php foreach ($deposit->routes as $r): ?>
                            <tr>
                                <td><a href="<?php echo base_url('routes/detail/' . $r->route_id . '?back=' . urlencode('deposits/detail/' . $deposit->id)); ?>"><code><?php echo html_escape($r->route_number); ?></code></a></td>
                                <td><?php echo fmt_date($r->route_date); ?></td>
                                <td><?php echo html_escape($r->shift_name); ?></td>
                                <td class="text-end fw-semibold"><?php echo money($r->amount); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-semibold"><td colspan="3" class="text-end">Total</td><td class="text-end"><?php echo money($deposit->amount); ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-clock-history me-2 text-danger"></i>Seguimiento</h5></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-cash-coin text-danger me-2"></i><strong>Depósito del mensajero:</strong> <?php echo fmt_datetime($deposit->courier_receipt_at); ?></li>
                    <li class="mb-2"><i class="bi bi-check-circle text-info me-2"></i><strong>Recepción confirmada:</strong>
                        <?php echo $deposit->aux_confirmed_at ? html_escape($deposit->receiver_name) . ' · ' . fmt_datetime($deposit->aux_confirmed_at) : 'Pendiente'; ?>
                    </li>
                    <li class="mb-2"><i class="bi bi-box-arrow-right text-primary me-2"></i><strong>Entrega al administrador:</strong>
                        <?php echo $deposit->aux_receipt_at ? html_escape($deposit->admin_receiver_name) . ' · ' . fmt_datetime($deposit->aux_receipt_at) : 'Pendiente'; ?>
                    </li>
                    <li><i class="bi bi-patch-check text-success me-2"></i><strong>Visto bueno:</strong>
                        <?php echo $deposit->confirmed_at ? html_escape($deposit->confirmed_by_name) . ' · ' . fmt_datetime($deposit->confirmed_at) : 'Pendiente'; ?>
                    </li>
                </ul>
                <?php if ($deposit->notes): ?>
                    <div class="alert alert-secondary py-2 mt-2 mb-0"><i class="bi bi-chat-left-text me-1"></i><?php echo html_escape($deposit->notes); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-receipt me-2 text-danger"></i>Comprobantes</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="fw-semibold mb-1"><i class="bi bi-person-badge me-1"></i>Depósito del mensajero (<?php echo html_escape($deposit->courier_name); ?>)</div>
                    <?php if ($deposit->courier_receipt_file): ?>
                        <?php if ($isImage($deposit->courier_receipt_mime)): ?>
                            <a href="<?php echo base_url('uploads/deposit_receipts/' . $deposit->courier_receipt_file); ?>" target="_blank">
                                <img src="<?php echo base_url('uploads/deposit_receipts/' . $deposit->courier_receipt_file); ?>" class="img-fluid rounded border" style="max-height:260px;" alt="Comprobante del mensajero">
                            </a>
                        <?php else: ?>
                            <a href="<?php echo base_url('uploads/deposit_receipts/' . $deposit->courier_receipt_file); ?>" target="_blank" class="btn btn-outline-brand btn-sm"><i class="bi bi-file-pdf me-1"></i>Ver comprobante (PDF)</a>
                        <?php endif; ?>
                        <div class="form-text"><?php echo html_escape($deposit->courier_receipt_original); ?> · <?php echo fmt_datetime($deposit->courier_receipt_at); ?></div>
                    <?php else: ?>
                        <div class="text-muted-2">Sin archivo.</div>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <div class="fw-semibold mb-1"><i class="bi bi-person-check me-1"></i>Entrega al administrador<?php echo $deposit->admin_receiver_user_id ? ' (' . html_escape($deposit->admin_receiver_name) . ')' : ''; ?></div>
                    <?php if ($deposit->aux_receipt_file): ?>
                        <?php if ($isImage($deposit->aux_receipt_mime)): ?>
                            <a href="<?php echo base_url('uploads/deposit_receipts/' . $deposit->aux_receipt_file); ?>" target="_blank">
                                <img src="<?php echo base_url('uploads/deposit_receipts/' . $deposit->aux_receipt_file); ?>" class="img-fluid rounded border" style="max-height:260px;" alt="Comprobante de entrega">
                            </a>
                        <?php else: ?>
                            <a href="<?php echo base_url('uploads/deposit_receipts/' . $deposit->aux_receipt_file); ?>" target="_blank" class="btn btn-outline-brand btn-sm"><i class="bi bi-file-pdf me-1"></i>Ver comprobante (PDF)</a>
                        <?php endif; ?>
                        <div class="form-text"><?php echo html_escape($deposit->aux_receipt_original); ?> · <?php echo fmt_datetime($deposit->aux_receipt_at); ?></div>
                    <?php else: ?>
                        <div class="text-muted-2">Pendiente (solo aplica cuando el receptor es el Auxiliar Admin).</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card sg-card">
            <div class="card-body d-grid gap-2">
                <?php if ($deposit->status === 'pendiente' && $canConfirm && ($isAdmin || (int)$deposit->receiver_user_id === (int)$currentUser->id)): ?>
                    <button type="button" class="btn btn-success btn-deposit-confirm" data-url="<?php echo base_url('deposits/confirm/' . $deposit->id); ?>"><i class="bi bi-check-circle me-1"></i>Confirmar que recibí el efectivo</button>
                <?php endif; ?>
                <?php if ($deposit->status === 'recibido' && $canHandover): ?>
                    <button type="button" class="btn btn-primary btn-deposit-handover" data-url="<?php echo base_url('deposits/handover/' . $deposit->id); ?>"><i class="bi bi-box-arrow-right me-1"></i>Entregar al administrador</button>
                <?php endif; ?>
                <?php if ($deposit->status === 'entregado' && $canApprove): ?>
                    <button type="button" class="btn btn-outline-success btn-deposit-approve" data-url="<?php echo base_url('deposits/approve/' . $deposit->id); ?>"><i class="bi bi-patch-check me-1"></i>Dar visto bueno (confirmar depósito)</button>
                <?php endif; ?>
                <?php if ($deposit->status === 'aprobado'): ?>
                    <div class="alert alert-success py-2 mb-0 text-center"><i class="bi bi-patch-check-fill me-1"></i>Depósito aprobado por <?php echo html_escape($deposit->confirmed_by_name); ?> el <?php echo fmt_datetime($deposit->confirmed_at); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: entrega del auxiliar al administrador -->
<div class="modal fade" id="handoverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="handoverForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i>Entregar efectivo al administrador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">Monto: <strong><?php echo money($deposit->amount); ?></strong></div>
                    <div class="mb-3">
                        <label class="form-label" for="admin_receiver_user_id">Administrador que recibe *</label>
                        <select class="form-select" id="admin_receiver_user_id" name="admin_receiver_user_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($admins as $adm): ?>
                                <option value="<?php echo (int)$adm->id; ?>"><?php echo html_escape(trim($adm->first_name . ' ' . $adm->last_name)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="aux_receipt">Comprobante de entrega *</label>
                        <input type="file" class="form-control" id="aux_receipt" name="aux_receipt" data-camera accept="image/jpeg,image/png,image/webp,application/pdf" required>
                        <div class="form-text">Foto o PDF del comprobante.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>