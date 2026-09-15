<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->data['courierTab'] = 'home';
$r = $route;
$progressTotal = (int)$summary->total_orders;
$progressDone = (int)$summary->delivered + (int)$summary->not_delivered;
$progressPct = $progressTotal > 0 ? round(($progressDone / $progressTotal) * 100) : 0;
$routeActive = in_array($r->status, array('planificada', 'en_progreso'), true);
// Entregas sin procesar (pendiente o en proceso): la ruta solo puede
// finalizarse cuando todas esten resueltas (entregado o no_entregado).
$pendingCount = 0;
foreach ($routeOrders as $ro) {
    if (in_array($ro->route_status, array('pendiente', 'en_proceso'), true)) {
        $pendingCount++;
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?php echo base_url('courier'); ?>" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <?php echo route_status_badge($r->status); ?>
</div>

<div class="courier-route-card">
    <div class="card-head">
        <h2><?php echo html_escape($r->route_number); ?></h2>
        <small><i class="bi bi-clock me-1"></i><?php echo html_escape($r->shift_name); ?> · <?php echo fmt_date($r->route_date); ?></small>
        <div class="d-flex align-items-center gap-2 mt-2">
            <div class="progress progress-slim flex-grow-1" role="progressbar" aria-valuenow="<?php echo $progressPct; ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width:<?php echo $progressPct; ?>%"></div>
            </div>
            <small class="text-white-50"><?php echo $progressDone; ?>/<?php echo $progressTotal; ?></small>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between mb-1">
            <div class="d-label">Bodega</div>
            <div class="d-value"><?php echo html_escape($r->warehouse_name ?: '—'); ?></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <div class="d-label">Monto esperado</div>
            <div class="d-value"><?php echo money($summary->expected_amount); ?></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <div class="d-label">Cobrado</div>
            <div class="d-value text-success"><?php echo money($summary->collected_amount); ?></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <div class="d-label">Faltante</div>
            <div class="d-value <?php echo (float)$summary->pending_amount > 0.009 ? 'text-warning' : 'text-success'; ?>"><?php echo money($summary->pending_amount); ?></div>
        </div>
        <?php if ($r->status === 'planificada'): ?>
            <button type="button" class="btn btn-brand w-100 courier-btn mt-2 btn-route-start" data-url="<?php echo base_url('courier/route/' . $r->id . '/start'); ?>"><i class="bi bi-play-fill me-1"></i>Iniciar ruta</button>
        <?php endif; ?>
        <?php if ($r->status === 'en_progreso'): ?>
            <?php if ($pendingCount === 0): ?>
                <button type="button" class="btn btn-outline-success w-100 courier-btn mt-2 btn-route-finish" data-url="<?php echo base_url('courier/route/' . $r->id . '/finish'); ?>"><i class="bi bi-check-lg me-1"></i>Finalizar ruta</button>
            <?php else: ?>
                <div class="alert alert-warning text-center py-2 mt-2 mb-0 small">
                    <i class="bi bi-info-circle me-1"></i>Quedan <strong><?php echo $pendingCount; ?></strong> entrega(s) sin procesar.
                    Debe marcar cada pedido como entregado o no entregado para finalizar la ruta.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php foreach ($routeOrders as $ro): ?>
    <div class="courier-delivery">
        <div class="d-head">
            <span class="d-order-num"><?php echo (int)$ro->delivery_order; ?></span>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-center gap-2">
                    <strong style="font-family:'Fira Code',monospace;font-size:0.85rem;"><code><?php echo html_escape($ro->order_number); ?></code></strong>
                    <?php echo delivery_status_badge($ro->route_status); ?>
                    <?php if ((int)$ro->is_parcel === 1): ?>
                        <span class="badge text-bg-info">Encomienda</span>
                    <?php endif; ?>
                </div>
                <div class="fw-semibold"><?php echo html_escape($ro->customer_name); ?></div>
                <?php if ((int)$ro->is_parcel === 1): ?>
                    <div class="small text-white-50"><i class="bi bi-truck me-1"></i>Transporte: <?php echo html_escape($ro->transport_name ?: '—'); ?></div>
                <?php endif; ?>
            </div>
            <div class="text-end">
                <div class="fw-bold" style="font-family:'Fira Code',monospace;"><?php echo money($ro->route_status === 'entregado' ? $ro->paid_amount : $ro->balance_amount); ?></div>
                <div class="d-label"><?php echo html_escape($ro->payment_name); ?></div>
            </div>
        </div>
        <div class="d-body">
            <div class="d-flex justify-content-between mb-1"><span class="d-label">Dirección</span><span class="text-end ms-2"><?php echo html_escape($ro->delivery_address); ?></span></div>
                            <div class="d-flex justify-content-between mb-1"><span class="d-label">Zona de entrega</span><span class="text-end ms-2"><?php echo html_escape($ro->delivery_zone ?: '—'); ?></span></div>
            <div class="d-flex justify-content-between"><span class="d-label">Teléfono</span><span><?php echo html_escape($ro->customer_phone); ?></span></div>
            <?php if ($ro->customer_phone2): ?>
                <div class="d-flex justify-content-between"><span class="d-label">Tel. secundario</span><span><?php echo html_escape($ro->customer_phone2); ?></span></div>
            <?php endif; ?>
            <?php if ($ro->notes): ?>
                <div class="d-flex justify-content-between"><span class="d-label">Nota del pedido</span><span class="text-end ms-2"><?php echo html_escape($ro->notes); ?></span></div>
            <?php endif; ?>
        </div>
        <?php if ($routeActive && in_array($ro->route_status, array('pendiente', 'en_proceso'), true)): ?>
            <div class="d-actions">
                <a class="btn btn-outline-dark" href="<?php echo base_url('courier/nav/' . $ro->id); ?>" target="_blank"><i class="bi bi-geo-alt"></i>Navegar</a>
                <a class="btn btn-outline-success" href="<?php echo base_url('courier/wa/' . $ro->order_id); ?>" target="_blank"><i class="bi bi-whatsapp"></i>WhatsApp</a>
                <a class="btn btn-outline-secondary" href="<?php echo base_url('courier/tel/' . $ro->order_id); ?>"><i class="bi bi-telephone"></i>Llamar</a>
                <?php if ($ro->customer_phone2): ?>
                    <a class="btn btn-outline-success" href="<?php echo base_url('courier/wa2/' . $ro->order_id); ?>" target="_blank"><i class="bi bi-whatsapp"></i>WhatsApp 2</a>
                    <a class="btn btn-outline-secondary" href="<?php echo base_url('courier/tel2/' . $ro->order_id); ?>"><i class="bi bi-telephone"></i>Llamar 2</a>
                <?php endif; ?>
                <?php if ($ro->route_status === 'pendiente'): ?>
                    <button type="button" class="btn btn-brand btn-start-delivery" data-url="<?php echo base_url('courier/start_delivery'); ?>" data-route-order="<?php echo $ro->id; ?>"><i class="bi bi-play-fill"></i>Iniciar entrega</button>
                <?php endif; ?>
                <?php if ($ro->route_status === 'en_proceso'): ?>
                    <button type="button" class="btn btn-success btn-open-deliver" data-route-order="<?php echo $ro->id; ?>" data-order-number="<?php echo html_escape($ro->order_number); ?>" data-balance="<?php echo $ro->balance_amount; ?>" data-is-parcel="<?php echo (int)$ro->is_parcel; ?>" data-payment-id="<?php echo (int)$ro->payment_method_id; ?>" data-payment-name="<?php echo html_escape($ro->initial_payment_name ?: $ro->payment_name); ?>"><i class="bi bi-check-lg"></i>Entregado</button>
                    <button type="button" class="btn btn-outline-danger btn-open-notdelivered" data-route-order="<?php echo $ro->id; ?>" data-order-number="<?php echo html_escape($ro->order_number); ?>"><i class="bi bi-x-lg"></i>No entregado</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (empty($routeOrders)): ?>
    <div class="courier-route-card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted-2 d-block mb-2"></i>
            <p class="text-muted-2 mb-0">Esta ruta no tiene entregas.</p>
        </div>
    </div>
<?php endif; ?>

<!-- Modal: entrega exitosa -->
<div class="modal fade" id="deliverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Entrega exitosa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="deliverForm">
                    <input type="hidden" name="idempotency_key" id="deliverIdem" value="">
                    <input type="hidden" name="latitude" id="deliverLat" value="">
                    <input type="hidden" name="longitude" id="deliverLng" value="">
                    <div class="mb-3">
                        <label class="form-label">Pedido</label>
                        <div class="fw-bold" id="deliverOrderLabel"></div>
                    </div>
                    <div class="alert alert-secondary py-2" id="deliverPaymentMethodInfo">
                        <i class="bi bi-credit-card me-1"></i>Forma de pago acordada: <strong id="deliverPaymentMethodLabel"></strong>
                    </div>
                    <div class="alert alert-info py-2" id="deliverParcelInfo" style="display:none;">
                        <i class="bi bi-truck me-1"></i>Encomienda: el pago ya fue realizado. Solo confirme la entrega.
                    </div>
                    <div class="alert alert-success py-2" id="deliverPrepaidInfo" style="display:none;">
                        <i class="bi bi-check-circle me-1"></i>El cliente ya realizó el pago. No debe cobrar.
                    </div>
                    <div id="deliverPaymentFields">
                        <div class="mb-3">
                            <label class="form-label" for="deliverPayment">Forma de pago recibida *</label>
                            <select class="form-select" id="deliverPayment" name="payment_method_id" required>
                                <?php foreach ($paymentMethods as $pm): ?>
                                    <option value="<?php echo $pm->id; ?>" data-code="<?php echo html_escape($pm->code); ?>"><?php echo html_escape($pm->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="deliverAmount">Monto recibido *</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo html_escape(app_setting('currency_symbol', '₡')); ?></span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="deliverAmount" name="amount" required>
                            </div>
                            <div class="form-text" id="deliverBalanceHint"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="deliverReceivedBy">Nombre de quien recibe</label>
                            <input type="text" class="form-control" id="deliverReceivedBy" name="received_by_name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="deliverNotes">Nota</label>
                        <textarea class="form-control" id="deliverNotes" name="notes" rows="2" placeholder="Ej: Pagó con billete de ₡20.000."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="deliverEvidence" id="deliverEvidenceLabel">Evidencia (foto)</label>
                        <input type="file" class="form-control" id="deliverEvidence" name="evidence" accept="image/jpeg,image/png,image/webp" data-camera>
                        <div class="form-text" id="deliverEvidenceHint">Opcional. JPG, PNG o WEBP.</div>
                        <div class="form-text text-danger fw-semibold" id="deliverCashHint" style="display:none;">
                            <i class="bi bi-cash-coin me-1"></i>Adjunte la foto del efectivo recibido para continuar.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 courier-btn">Confirmar entrega</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: no entregado -->
<div class="modal fade" id="failModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Entrega no realizada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="failForm">
                    <input type="hidden" name="latitude" id="failLat" value="">
                    <input type="hidden" name="longitude" id="failLng" value="">
                    <div class="mb-3">
                        <label class="form-label">Pedido</label>
                        <div class="fw-bold" id="failOrderLabel"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="failReason">Motivo *</label>
                        <select class="form-select" id="failReason" name="failure_reason_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($failureReasons as $fr): ?>
                                <option value="<?php echo $fr->id; ?>" data-requires-desc="<?php echo (int)$fr->requires_description; ?>"><?php echo html_escape($fr->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="failOtherWrap" style="display:none;">
                        <label class="form-label" for="failOther">Descripción del motivo *</label>
                        <textarea class="form-control" id="failOther" name="failure_other" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="failNotes">Observación</label>
                        <textarea class="form-control" id="failNotes" name="notes" rows="2"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="failReschedule" name="reschedule_requested" value="1">
                        <label class="form-check-label" for="failReschedule">Reprogramar entrega</label>
                    </div>
                    <div class="mb-3" id="failDateWrap" style="display:none;">
                        <label class="form-label" for="failDate">Nueva fecha de entrega *</label>
                        <input type="date" class="form-control" id="failDate" name="rescheduled_date" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="failEvidence">Evidencia (foto)</label>
                        <input type="file" class="form-control" id="failEvidence" name="evidence" accept="image/jpeg,image/png,image/webp" data-camera>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 courier-btn">Registrar no entrega</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.sgmsCourierRoute = {
    routeId: <?php echo $r->id; ?>,
    startUrl: '<?php echo base_url('courier/route/' . $r->id . '/start'); ?>',
    finishUrl: '<?php echo base_url('courier/route/' . $r->id . '/finish'); ?>',
    deliverUrl: '<?php echo base_url('courier/deliver/'); ?>',
    notDeliveredUrl: '<?php echo base_url('courier/not_delivered/'); ?>',
    maxEvidenceMb: <?php echo (float)app_setting('evidence_max_size_mb', 8); ?>
};
</script>
