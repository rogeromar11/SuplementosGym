<?php defined('BASEPATH') OR exit('No direct script access allowed');
$r = $route;
$canModify = in_array($r->status, array('borrador', 'planificada'), true);
$canStart = in_array($r->status, array('planificada', 'borrador'), true) && $r->courier_user_id;
?>
<div class="sg-page-header">
    <div>
        <h1>Ruta <code><?php echo html_escape($r->route_number); ?></code></h1>
        <p><?php echo html_escape($r->shift_name); ?> · <?php echo fmt_date($r->route_date); ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php echo route_status_badge($r->status); ?>
        <a href="<?php echo back_url('routes'); ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Regresar</a>
        <?php if ($canModify && (int)$summary->total_orders === 0 && has_permission('rutas.editar')): ?>
            <button type="button" class="btn btn-outline-danger btn-delete-route" data-url="<?php echo base_url('routes/delete/' . $r->id); ?>" data-redirect="<?php echo back_url('routes'); ?>"><i class="bi bi-trash me-1"></i>Eliminar</button>
        <?php endif; ?>
        <?php if ($canStart): ?>
            <button type="button" class="btn btn-brand btn-route-start" data-url="<?php echo base_url('routes/start/' . $r->id); ?>"><i class="bi bi-play-fill me-1"></i>Iniciar ruta</button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md">
        <div class="card sg-card"><div class="card-body">
            <div class="sg-stat-label">Pedidos</div>
            <div class="sg-stat-value fs-4"><?php echo (int)$summary->total_orders; ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card" style="border-left-color:#16A34A;"><div class="card-body">
            <div class="sg-stat-label">Entregados</div>
            <div class="sg-stat-value fs-4 text-success"><?php echo (int)$summary->delivered; ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card" style="border-left-color:#DC2626;"><div class="card-body">
            <div class="sg-stat-label">Monto esperado</div>
            <div class="sg-stat-value fs-4"><?php echo money($summary->expected_amount); ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card" style="border-left-color:#F59E0B;"><div class="card-body">
            <div class="sg-stat-label">Cobrado</div>
            <div class="sg-stat-value fs-4"><?php echo money($summary->collected_amount); ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card" style="border-left-color:#DC2626;"><div class="card-body">
            <div class="sg-stat-label">Monto faltante</div>
            <div class="sg-stat-value fs-4 <?php echo (float)$summary->pending_amount > 0.009 ? 'text-danger' : 'text-success'; ?>"><?php echo money($summary->pending_amount); ?></div>
        </div></div>
    </div>
</div>

<?php if ($r->notes): ?>
<div class="alert alert-info py-2 mb-3"><i class="bi bi-chat-left-text me-1"></i><strong>Observaciones:</strong> <?php echo html_escape($r->notes); ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card sg-card mb-3">
            <div class="sg-card-header">
                <h5><i class="bi bi-list-ol me-2 text-danger"></i>Pedidos de la ruta</h5>
                <?php if ($canModify && has_permission('rutas.reordenar')): ?>
                    <span class="badge text-bg-dark">Arrastre para ordenar</span>
                <?php endif; ?>
            </div>
            <div class="sg-card-body">
                <?php if (empty($routeOrders)): ?>
                    <p class="text-muted-2 mb-0">Sin pedidos asignados.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush" id="routeOrdersList">
                        <?php foreach ($routeOrders as $idx => $ro): ?>
                            <li class="list-group-item route-order-item" data-order-id="<?php echo $ro->order_id; ?>" data-index="<?php echo $idx + 1; ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge text-bg-dark order-number"><?php echo $idx + 1; ?></span>
                                    <div class="flex-grow-1 min-w-0 route-order-main">
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="<?php echo base_url('orders/detail/' . $ro->order_id . '?back=' . urlencode('routes/detail/' . $r->id)); ?>" class="fw-semibold"><code><?php echo html_escape($ro->order_number); ?></code></a>
                                            <?php echo delivery_status_badge($ro->route_status); ?>
                                            <?php if ((int)$ro->was_reprogrammed === 1): ?>
                                                <span class="badge text-bg-warning" title="Este pedido fue reprogramado por el cliente."><i class="bi bi-arrow-repeat me-1"></i>Reprogramado</span>
                                            <?php endif; ?>
                                            <?php if ((int)$ro->is_parcel === 1): ?>
                                                <span class="badge text-bg-info">Encomienda</span>
                                            <?php endif; ?>
                                    </div>
                                        <div class="fw-semibold"><?php echo html_escape($ro->customer_name); ?></div>
                                        <div class="small"><i class="bi bi-geo-alt me-1"></i><strong>Zona:</strong> <?php echo html_escape($ro->delivery_zone ?: '—'); ?></div>
                                        <div class="small text-muted-2 text-truncate route-order-address"><?php echo html_escape($ro->delivery_address); ?></div>
                                        <?php if ($ro->route_status === 'no_entregado' && ($ro->failure_reason || $ro->failure_notes)): ?>
                                            <div class="small text-danger mt-1"><i class="bi bi-x-octagon me-1"></i><strong><?php echo html_escape($ro->failure_reason ?: 'No entregado'); ?></strong><?php echo $ro->failure_notes ? ' — ' . html_escape($ro->failure_notes) : ''; ?></div>
                                        <?php endif; ?>
                                        <?php if ($ro->route_status === 'entregado' && $ro->failure_notes): ?>
                                            <div class="small text-success mt-1"><i class="bi bi-chat-left-text me-1"></i><strong>Nota del mensajero:</strong> <?php echo html_escape($ro->failure_notes); ?></div>
                                        <?php endif; ?>
                                        <?php if ((int)$ro->is_parcel === 1): ?>
                                            <div class="small"><i class="bi bi-truck me-1 text-muted-2"></i><span class="text-muted-2">Transporte:</span> <?php echo html_escape($ro->transport_name ?: '—'); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end route-order-side">
                                        <div class="fw-bold"><?php echo money($ro->route_status === 'entregado' ? $ro->paid_amount : $ro->balance_amount); ?></div>
                                        <div class="small text-muted-2"><?php echo html_escape($ro->payment_name); ?></div>
                                    </div>
                                    <div class="d-flex gap-1 route-order-actions">
                                        <?php if ($ro->customer_phone_whatsapp): ?>
                                            <a href="https://wa.me/<?php echo html_escape($ro->customer_phone_whatsapp); ?>" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                        <?php endif; ?>
                                        <?php if ($ro->customer_phone2_whatsapp): ?>
                                            <a href="<?php echo base_url('courier/wa2/' . $ro->order_id); ?>" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp secundario"><i class="bi bi-whatsapp"></i></a>
                                        <?php endif; ?>
                                        <?php if ($canModify && has_permission('rutas.editar')): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-order" data-url="<?php echo base_url('routes/remove_order'); ?>" data-order="<?php echo $ro->order_id; ?>" title="Quitar"><i class="bi bi-x-lg"></i></button>
                                        <?php endif; ?>
                                        <?php if ($canModify && has_permission('rutas.transferir_pedido') && !empty($sameDayRoutes)): ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-transfer-order" data-order="<?php echo $ro->order_id; ?>" data-order-number="<?php echo html_escape($ro->order_number); ?>" title="Transferir"><i class="bi bi-arrow-left-right"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($canModify && has_permission('rutas.reordenar') && count($routeOrders) > 1): ?>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-brand btn-save-order" data-url="<?php echo base_url('routes/reorder'); ?>" data-route="<?php echo $r->id; ?>"><i class="bi bi-check-lg me-1"></i>Guardar orden</button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($canModify && has_permission('rutas.editar')): ?>
        <div class="card sg-card">
            <div class="sg-card-header"><h5><i class="bi bi-plus-circle me-2 text-danger"></i>Agregar pedidos a la ruta</h5></div>
            <div class="sg-card-body">
                <?php if (empty($availableOrders)): ?>
                    <p class="text-muted-2 mb-0">No hay pedidos disponibles para asignar.</p>
                <?php else: ?>
                    <div class="table-responsive" style="max-height:300px;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Zona</th>
                                    <th class="text-end">Saldo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availableOrders as $ao): ?>
                                    <tr>
                                        <td><code><?php echo html_escape($ao->order_number); ?></code> <?php if ((int)$ao->is_parcel === 1): ?><span class="badge text-bg-info">Encomienda</span><?php endif; ?> <?php if ($ao->status === 'reprogramado'): ?><span class="badge text-bg-warning">Reprogramado</span><?php endif; ?></td>
                                        <td>
                                            <div class="fw-semibold"><?php echo html_escape($ao->customer_name); ?></div>
                                            <div class="small text-muted-2"><?php echo html_escape(mb_substr($ao->delivery_address, 0, 40)); ?></div>
                                        </td>
                                        <td class="small"><?php echo html_escape($ao->delivery_zone ?: '—'); ?></td>
                                        <td class="text-end fw-semibold"><?php echo money($ao->balance_amount); ?></td>
                                        <td class="text-end">
                                            <?php if ((int)$ao->is_parcel === 1): ?>
                                                <button type="button" class="btn btn-sm btn-brand btn-assign-parcel" data-url="<?php echo base_url('routes/add_order'); ?>" data-route="<?php echo $r->id; ?>" data-order="<?php echo $ao->id; ?>" data-order-number="<?php echo html_escape($ao->order_number); ?>" data-transport="<?php echo (int)$ao->transport_id; ?>"><i class="bi bi-truck me-1"></i>Asignar</button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-brand btn-add-order" data-url="<?php echo base_url('routes/add_order'); ?>" data-route="<?php echo $r->id; ?>" data-order="<?php echo $ao->id; ?>"><i class="bi bi-plus-lg"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($r->status === 'finalizada' && !empty($releasedOrders)): ?>
        <div class="card sg-card border-warning">
            <div class="sg-card-header"><h5><i class="bi bi-arrow-repeat me-2 text-danger"></i>Pedidos sin entregar <span class="badge text-bg-warning ms-1"><?php echo count($releasedOrders); ?></span></h5></div>
            <div class="sg-card-body">
                <p class="mb-2">Estos pedidos no fueron entregados y quedaron disponibles para reprogramar o asignar a otra ruta.</p>
                <ul class="list-group">
                    <?php foreach ($releasedOrders as $ro): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <code><?php echo html_escape($ro->order_number); ?></code>
                                    <?php if ((int)$ro->is_parcel === 1): ?><span class="badge text-bg-info">Encomienda</span><?php endif; ?>
                                    <?php if ($ro->status === 'reprogramado'): ?><span class="badge text-bg-warning">Reprogramado</span><?php else: ?><span class="badge text-bg-secondary">Preparado</span><?php endif; ?>
                                    <div class="small text-muted-2"><?php echo html_escape($ro->customer_name); ?> · <?php echo html_escape($ro->customer_phone); ?></div>
                                    <?php if ($ro->failure_reason || $ro->failure_notes): ?>
                                        <div class="small text-danger mt-1"><i class="bi bi-x-octagon me-1"></i><strong><?php echo html_escape($ro->failure_reason ?: 'No entregado'); ?></strong><?php echo $ro->failure_notes ? ' — ' . html_escape($ro->failure_notes) : ''; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?php echo base_url('routes'); ?>" class="btn btn-sm btn-brand mt-3"><i class="bi bi-plus-lg me-1"></i>Planificar nueva ruta</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-5">
        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-geo-alt me-2 text-danger"></i>Recorrido</h5></div>
            <div class="sg-card-body">
                <div id="routeMap" data-route="<?php echo $r->id; ?>" style="height:380px;"></div>
            </div>
        </div>

        <div class="card sg-card">
            <div class="sg-card-header"><h5><i class="bi bi-info-circle me-2 text-danger"></i>Detalle</h5></div>
            <div class="sg-card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted-2">Mensajero</td><td class="fw-semibold"><?php echo html_escape(trim($r->courier_first_name . ' ' . $r->courier_last_name)); ?></td></tr>
                    <tr><td class="text-muted-2">Bodega</td><td><?php echo html_escape($r->warehouse_name ?: '—'); ?></td></tr>
                    <tr><td class="text-muted-2">Inicio</td><td><?php echo $r->started_at ? fmt_datetime($r->started_at) : '—'; ?></td></tr>
                    <tr><td class="text-muted-2">Fin</td><td><?php echo $r->finished_at ? fmt_datetime($r->finished_at) : '—'; ?></td></tr>
                    <tr><td class="text-muted-2">Observaciónes</td><td><?php echo html_escape($r->notes ?: '—'); ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal asignar encomienda (transporte requerido) -->
<div class="modal fade" id="assignParcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-black text-white">
                <h5 class="modal-title"><i class="bi bi-truck me-2 text-danger"></i>Asignar encomienda</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Defina el transporte para la encomienda <strong id="assignParcelLabel"></strong>:</p>
                <input type="hidden" id="assignParcelOrderId" value="">
                <div class="mb-3">
                    <label class="form-label" for="assignParcelTransport">Transporte *</label>
                    <select class="form-select" id="assignParcelTransport" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($transports as $t): ?>
                            <option value="<?php echo $t->id; ?>"><?php echo html_escape($t->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Se usará por el mensajero para entregar la encomienda.</div>
                </div>
                <button type="button" class="btn btn-brand w-100" id="assignParcelConfirm"><i class="bi bi-check-lg me-1"></i>Asignar a la ruta</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal transferencia -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-black text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2 text-danger"></i>Transferir pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Transferir <strong id="transferOrderLabel"></strong> a otra ruta del mismo dia:</p>
                <input type="hidden" id="transferOrderId" value="">
                <div class="mb-3">
                    <label class="form-label">Ruta destino</label>
                    <select class="form-select" id="transferRoute">
                        <?php foreach ($sameDayRoutes as $sr): ?>
                            <option value="<?php echo $sr->id; ?>"><?php echo html_escape($sr->route_number); ?> (<?php echo html_escape($sr->shift_id === 1 ? 'Manana' : 'Tarde'); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observación</label>
                    <textarea class="form-control" id="transferNotes" rows="2"></textarea>
                </div>
                <button type="button" class="btn btn-brand w-100" id="transferConfirm">Transferir pedido</button>
            </div>
        </div>
    </div>
</div>

<script>
window.sgmsRoute = {
    routeId: <?php echo $r->id; ?>,
    startUrl: '<?php echo base_url('routes/start/' . $r->id); ?>',
    reorderUrl: '<?php echo base_url('routes/reorder'); ?>',
    transferUrl: '<?php echo base_url('routes/transfer'); ?>',
    warehouse: { lat: <?php echo $r->warehouse_lat !== null ? $r->warehouse_lat : 'null'; ?>, lng: <?php echo $r->warehouse_lng !== null ? $r->warehouse_lng : 'null'; ?>, name: <?php echo json_encode($r->warehouse_name); ?> },
    points: <?php echo json_encode(array_map(function ($ro) {
        return array('id' => $ro->order_id, 'number' => $ro->order_number, 'name' => $ro->customer_name, 'lat' => $ro->latitude, 'lng' => $ro->longitude);
    }, $routeOrders)); ?>
};
</script>
