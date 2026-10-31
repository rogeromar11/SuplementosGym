<?php defined('BASEPATH') OR exit('No direct script access allowed');
$o = $order;
$canEdit = in_array($o->status, array('registrado', 'pendiente_preparacion', 'en_preparacion', 'preparado', 'asignado_ruta', 'no_entregado', 'reprogramado')) && has_permission('pedidos.editar');
$canCancel = in_array($o->status, array('registrado', 'pendiente_preparacion', 'en_preparacion', 'preparado', 'asignado_ruta', 'no_entregado', 'reprogramado')) && has_permission('pedidos.anular');
$canDelete = in_array($o->status, array('registrado', 'pendiente_preparacion', 'en_preparacion', 'preparado', 'no_entregado', 'reprogramado', 'cancelado')) && has_permission('pedidos.eliminar');
$canEditPaymentNote = !in_array($o->status, array('entregado', 'cancelado'), true) && has_permission('pedidos.editar');
$isAdmin = $this->ion_auth->is_admin();
$canCorrectDelivered = $o->status === 'entregado' && $isAdmin;
?>
<div class="sg-page-header">
    <div>
        <h1>Pedido <code><?php echo html_escape($o->order_number); ?></code></h1>
        <p>Registrado el <?php echo fmt_datetime($o->created_at); ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php echo order_status_badge($o->status); ?>
        <a href="<?php echo back_url('orders'); ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Regresar</a>
        <a href="<?php echo base_url('orders/print_view/' . $o->id); ?>" class="btn btn-outline-dark" target="_blank"><i class="bi bi-printer me-1"></i>Imprimir</a>
        <?php if ($canEdit): ?>
            <a href="<?php echo base_url('orders/edit/' . $o->id . back_query_string()); ?>" class="btn btn-outline-brand"><i class="bi bi-pencil me-1"></i>Editar</a>
        <?php endif; ?>
        <?php if ($o->status === 'reprogramado' && has_permission('pedidos.editar')): ?>
            <button type="button" class="btn btn-outline-warning btn-update-rescheduled-date" data-url="<?php echo base_url('orders/update_rescheduled_date/' . $o->id); ?>" data-date="<?php echo html_escape($o->rescheduled_delivery_date); ?>"><i class="bi bi-calendar-event me-1"></i>Cambiar fecha</button>
        <?php endif; ?>
        <?php if ($canCancel): ?>
            <button type="button" class="btn btn-outline-danger btn-cancel-order" data-url="<?php echo base_url('orders/cancel/' . $o->id); ?>"><i class="bi bi-x-circle me-1"></i>Anular</button>
        <?php endif; ?>
        <?php if ($canDelete): ?>
            <button type="button" class="btn btn-danger btn-delete-order" data-url="<?php echo base_url('orders/delete/' . $o->id); ?>" data-redirect="<?php echo back_url('orders'); ?>"><i class="bi bi-trash me-1"></i>Eliminar</button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card sg-card mb-3">
            <div class="sg-card-header">
                <h5><i class="bi bi-person me-2 text-danger"></i>Cliente y entrega</h5>
                <?php if ($canEditPaymentNote): ?>
                    <button type="button" class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#paymentNoteModal">
                        <i class="bi bi-pencil-square me-1"></i>Forma de pago y nota
                    </button>
                <?php elseif ($canCorrectDelivered): ?>
                    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#deliveredCorrectionModal">
                        <i class="bi bi-shield-lock me-1"></i>Corregir detalles (admin)
                    </button>
                <?php endif; ?>
            </div>
            <div class="sg-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted-2">Cliente</div>
                        <div class="fw-semibold"><?php echo html_escape($o->customer_name); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted-2">Teléfono</div>
                        <div class="fw-semibold"><?php echo html_escape($o->customer_phone); ?>
                            <?php if ($o->customer_phone_whatsapp): ?>
                                <a href="<?php echo html_escape(base_url('courier/wa/' . $o->id)); ?>" class="btn btn-sm btn-outline-success ms-1" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                <a href="<?php echo html_escape('tel:+' . $o->customer_phone_whatsapp); ?>" class="btn btn-sm btn-outline-secondary" title="Llamar"><i class="bi bi-telephone"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted-2">Teléfono secundario</div>
                        <div class="fw-semibold"><?php echo html_escape($o->customer_phone2 ?: '—'); ?>
                            <?php if ($o->customer_phone2_whatsapp): ?>
                                <a href="<?php echo html_escape(base_url('courier/wa2/' . $o->id)); ?>" class="btn btn-sm btn-outline-success ms-1" title="WhatsApp secundario"><i class="bi bi-whatsapp"></i></a>
                                <a href="<?php echo html_escape('tel:+' . $o->customer_phone2_whatsapp); ?>" class="btn btn-sm btn-outline-secondary" title="Llamar secundario"><i class="bi bi-telephone"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted-2">Correo</div>
                        <div><?php echo html_escape($o->customer_email ?: '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted-2">Bodega</div>
                        <div><?php echo html_escape($o->warehouse_name ?: '—'); ?></div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted-2">Zona de entrega</div>
                        <div><?php echo html_escape($o->delivery_zone ?: '—'); ?></div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted-2">Dirección de entrega</div>
                        <div><?php echo html_escape($o->delivery_address); ?></div>
                        <?php if ($o->delivery_reference): ?><div class="small text-muted-2 mt-1"><?php echo html_escape($o->delivery_reference); ?></div><?php endif; ?>
                    </div>
                    <?php if ($o->latitude && $o->longitude): ?>
                        <div class="col-12">
                            <div id="orderMap" data-lat="<?php echo $o->latitude; ?>" data-lng="<?php echo $o->longitude; ?>" style="height:220px;"></div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <div class="small text-muted-2">Forma de pago</div>
                        <div class="fw-semibold"><?php echo html_escape($o->payment_name ?: '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted-2">Fecha solicitada</div>
                        <div><?php echo fmt_date($o->requested_delivery_date); ?></div>
                    </div>
                    <?php if ($o->rescheduled_delivery_date): ?>
                        <div class="col-md-6">
                            <div class="small text-muted-2">Reprogramada para</div>
                            <div class="fw-semibold text-danger"><?php echo fmt_date($o->rescheduled_delivery_date); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($o->is_parcel): ?>
                        <div class="col-md-6"><span class="badge text-bg-info">Encomienda</span>
                            <?php if ($o->transport_name): ?><div class="mt-1 small text-muted-2"><i class="bi bi-truck me-1"></i><?php echo html_escape($o->transport_name); ?></div><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($o->includes_gifts): ?>
                        <div class="col-md-6"><span class="badge text-bg-warning">Incluye regalías</span> <small><?php echo html_escape($o->gift_description); ?></small></div>
                    <?php endif; ?>
                    <?php if ($o->notes || $canEditPaymentNote): ?>
                        <div class="col-12">
                            <div class="small text-muted-2">Notas</div>
                            <div><?php echo $o->notes ? nl2br(html_escape($o->notes)) : '<span class="text-muted-2">—</span>'; ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card sg-card mb-3">
            <div class="sg-card-header">
                <h5><i class="bi bi-boxes me-2 text-danger"></i>Detalle</h5>
                <?php if ($canCorrectDelivered): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#returnItemsModal">
                        <i class="bi bi-arrow-return-left me-1"></i>Devolución
                    </button>
                <?php endif; ?>
            </div>
            <div class="sg-card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cant.</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($o->items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo html_escape($item->item_name); ?></div>
                                        <?php if ($item->item_sku): ?><small class="text-muted-2"><code><?php echo html_escape($item->item_sku); ?></code></small><?php endif; ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format((int)$item->quantity); ?></td>
                                    <td class="text-end"><?php echo money($item->unit_price); ?></td>
                                    <td class="text-end fw-semibold"><?php echo money($item->line_total); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="text-end text-muted-2">Subtotal</td><td class="text-end"><?php echo money($o->subtotal); ?></td></tr>
                            <?php if ((float)$o->discount > 0): ?>
                                <tr><td colspan="3" class="text-end text-muted-2">Descuento</td><td class="text-end text-danger">- <?php echo money($o->discount); ?></td></tr>
                            <?php endif; ?>
                            <?php if ((float)$o->shipping > 0): ?>
                                <tr><td colspan="3" class="text-end text-muted-2">Envío</td><td class="text-end"><?php echo money($o->shipping); ?></td></tr>
                            <?php endif; ?>
                            <tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold fs-5"><?php echo money($o->total); ?></td></tr>
                            <tr><td colspan="3" class="text-end text-muted-2">Pagado</td><td class="text-end text-success fw-semibold"><?php echo money($o->paid_amount); ?></td></tr>
                            <tr><td colspan="3" class="text-end text-muted-2">Saldo</td><td class="text-end <?php echo (float)$o->balance_amount > 0 ? 'text-danger fw-semibold' : ''; ?>"><?php echo money($o->balance_amount); ?></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <?php
        $receipts = array();
        $evidences = array();
        foreach ((array)$o->attachments as $att) {
            if ($att->type === 'comprobante_pago') {
                $receipts[] = $att;
            } else {
                $evidences[] = $att;
            }
        }
        ?>
        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-receipt me-2 text-danger"></i>Comprobante de pago</h5></div>
            <div class="sg-card-body">
                <?php if (empty($receipts)): ?>
                    <p class="text-muted-2 mb-0">Sin comprobante de pago adjunto.</p>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($receipts as $att): ?>
                            <div class="col-6 col-md-3">
                                <?php if ($att->mime_type === 'application/pdf'): ?>
                                    <a href="<?php echo base_url('uploads/payment_receipts/' . $att->filename); ?>" target="_blank" class="btn btn-outline-dark w-100">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Ver PDF
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo base_url('uploads/payment_receipts/' . $att->filename); ?>" data-lightbox>
                                        <img src="<?php echo base_url('uploads/payment_receipts/' . $att->filename); ?>" class="img-fluid rounded border" alt="Comprobante" style="height:120px;width:100%;object-fit:cover;">
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($o->status === 'entregado' && $this->ion_auth->is_admin()): ?>
                    <hr class="my-3">
                    <input type="file" id="receiptFileInput" class="d-none" accept="image/jpeg,image/png,image/webp,application/pdf">
                    <button type="button" class="btn btn-sm btn-outline-brand btn-add-receipt" data-url="<?php echo base_url('orders/add_receipt/' . $o->id); ?>">
                        <i class="bi bi-plus-lg me-1"></i><?php echo empty($receipts) ? 'Adjuntar comprobante' : 'Agregar otro comprobante'; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-camera me-2 text-danger"></i>Evidencias</h5></div>
            <div class="sg-card-body">
                <?php if (empty($evidences)): ?>
                    <p class="text-muted-2 mb-0">Sin evidencias de entrega.</p>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($evidences as $att): ?>
                            <div class="col-6 col-md-3">
                                <a href="<?php echo base_url('uploads/delivery_evidence/' . $att->filename); ?>" data-lightbox>
                                    <img src="<?php echo base_url('uploads/delivery_evidence/' . $att->filename); ?>" class="img-fluid rounded border" alt="Evidencia" style="height:120px;width:100%;object-fit:cover;">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-cash-coin me-2 text-danger"></i>Pagos</h5></div>
            <div class="sg-card-body">
                <?php if (empty($o->payments)): ?>
                    <p class="text-muted-2 mb-0">Sin pagos registrados.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($o->payments as $p): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?php echo money($p->amount); ?></div>
                                    <div class="small text-muted-2"><?php echo html_escape($p->method_name ?: '—'); ?> · <?php echo fmt_datetime($p->received_at); ?></div>
                                </div>
                                <span class="badge text-bg-dark"><?php echo html_escape($p->reference ?: 'Pago'); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-truck me-2 text-danger"></i>Seguimiento de entrega</h5></div>
            <div class="sg-card-body">
                <?php if (empty($o->deliveries)): ?>
                    <p class="text-muted-2 mb-0">Sin intentos de entrega registrados.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($o->deliveries as $d): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semibold"><?php echo html_escape(delivery_status_label($d->status)); ?></div>
                                    <small class="text-muted-2"><?php echo fmt_datetime($d->attempted_at ?: $d->created_at); ?></small>
                                </div>
                                <?php if ( ! empty($d->courier_first_name)): ?>
                                    <div class="small text-muted-2"><i class="bi bi-person-badge me-1"></i><?php echo html_escape(trim($d->courier_first_name . ' ' . $d->courier_last_name)); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($d->route_number)): ?>
                                    <div class="small text-muted-2"><i class="bi bi-signpost-split me-1"></i>Ruta: <?php echo html_escape($d->route_number); ?></div>
                                <?php endif; ?>
                                <?php if ($d->received_by_name): ?>
                                    <div class="small">Recibió: <?php echo html_escape($d->received_by_name); ?></div>
                                <?php endif; ?>
                                <?php if ($d->payment_method_name): ?>
                                    <div class="small text-success"><i class="bi bi-cash-coin me-1"></i>Forma de pago: <?php echo html_escape($d->payment_method_name); ?><?php echo $d->payment_amount !== null ? ' (' . money($d->payment_amount) . ')' : ''; ?></div>
                                <?php endif; ?>
                                <?php if ((float)$d->extra_expense > 0): ?>
                                    <div class="small text-danger"><i class="bi bi-cash-coin me-1"></i>Otros gastos: <?php echo money($d->extra_expense); ?><?php echo $d->extra_expense_detail ? ' — ' . nl2br(html_escape($d->extra_expense_detail)) : ''; ?></div>
                                <?php endif; ?>
                                <?php if ($d->failure_reason_name): ?>
                                    <div class="small text-danger">Motivo: <?php echo html_escape($d->failure_reason_name); ?><?php echo $d->failure_other ? ' — ' . html_escape($d->failure_other) : ''; ?></div>
                                <?php endif; ?>
                                <?php if ($d->notes): ?>
                                    <div class="small"><i class="bi bi-chat-left-text me-1"></i><?php echo nl2br(html_escape($d->notes)); ?></div>
                                <?php endif; ?>
                                <?php if ($d->reschedule_requested && $d->rescheduled_date): ?>
                                    <div class="small text-warning">Reprogramar para: <?php echo fmt_date($d->rescheduled_date); ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card sg-card">
            <div class="sg-card-header"><h5><i class="bi bi-clock-history me-2 text-danger"></i>Historial de estados</h5></div>
            <div class="sg-card-body">
                <?php if (empty($o->history)): ?>
                    <p class="text-muted-2 mb-0">Sin cambios de estado.</p>
                <?php else: ?>
                    <ul class="list-unstyled m-0" style="border-left:2px solid #E4E4E7;padding-left:16px;">
                        <?php foreach ($o->history as $h): ?>
                            <li class="mb-3 position-relative" style="padding-left:16px;">
                                <span class="position-absolute" style="left:-25px;top:5px;width:10px;height:10px;border-radius:50%;background:#DC2626;"></span>
                                <div class="fw-semibold">
                                    <?php echo $h->from_status ? order_status_label($h->from_status) . ' → ' : ''; ?><?php echo order_status_label($h->to_status); ?>
                                </div>
                                <div class="small text-muted-2"><?php echo fmt_datetime($h->created_at); ?></div>
                                <?php if ($h->notes): ?><div class="small"><?php echo nl2br(html_escape($h->notes)); ?></div><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($canEditPaymentNote): ?>
<div class="modal fade" id="paymentNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="paymentNoteForm" data-url="<?php echo base_url('orders/update_payment_note/' . $o->id); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Forma de pago y nota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="paymentNoteMethod">Forma de pago</label>
                        <select class="form-select" id="paymentNoteMethod" name="payment_method_id" required>
                            <?php foreach ($paymentMethods as $pm): ?>
                                <option value="<?php echo (int)$pm->id; ?>" <?php echo (int)$o->payment_method_id === (int)$pm->id ? 'selected' : ''; ?>><?php echo html_escape($pm->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="paymentNoteNotes">Nota del pedido</label>
                        <textarea class="form-control" id="paymentNoteNotes" name="notes" rows="4" placeholder="Nota visible para el equipo y el mensajero."><?php echo html_escape($o->notes); ?></textarea>
                    </div>
                    <div class="alert alert-info py-2 mt-3 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>Puedes modificar estos datos hasta antes de confirmar la entrega. Una vez entregado el pedido, quedarán bloqueados.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-brand">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canCorrectDelivered): ?>
<div class="modal fade" id="deliveredCorrectionModal" tabindex="-1" aria-hidden="true" data-currency="<?php echo html_escape(app_setting('currency_symbol', '₡')); ?>">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="deliveredCorrectionForm" class="d-flex flex-column" style="min-height:0;" data-url="<?php echo base_url('orders/admin_correct_delivered/' . $o->id); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-shield-lock me-2"></i>Corregir pedido entregado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>Solo el administrador puede corregir un pedido entregado. Los totales y el saldo se recalculan automáticamente.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="correctionPaymentMethod">Forma de pago</label>
                            <select class="form-select" id="correctionPaymentMethod" name="payment_method_id" required>
                                <?php foreach ($paymentMethods as $pm): ?>
                                    <option value="<?php echo (int)$pm->id; ?>" <?php echo (int)$o->payment_method_id === (int)$pm->id ? 'selected' : ''; ?>><?php echo html_escape($pm->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="correctionDiscount">Descuento general</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo html_escape(app_setting('currency_symbol', '₡')); ?></span>
                                <input type="number" step="0.01" min="0" class="form-control" id="correctionDiscount" name="discount" value="<?php echo number_format((float)$o->discount, 2, '.', ''); ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="correctionNotes">Nota del pedido</label>
                            <textarea class="form-control" id="correctionNotes" name="notes" rows="3"><?php echo html_escape($o->notes); ?></textarea>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end" style="width:80px;">Cant.</th>
                                    <th class="text-end" style="width:150px;">Precio unitario</th>
                                    <th class="text-end" style="width:130px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($o->items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo html_escape($item->item_name); ?></div>
                                            <?php if ($item->item_sku): ?><small class="text-muted-2"><code><?php echo html_escape($item->item_sku); ?></code></small><?php endif; ?>
                                        </td>
                                        <td class="text-end"><?php echo number_format((int)$item->quantity); ?></td>
                                        <td class="text-end">
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end correction-price" name="price[<?php echo (int)$item->id; ?>]" value="<?php echo number_format((float)$item->unit_price, 2, '.', ''); ?>" data-qty="<?php echo max(1, (int)$item->quantity); ?>">
                                        </td>
                                        <td class="text-end correction-line-total"><?php echo money($item->line_total); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between"><span class="text-muted-2">Subtotal</span><span id="correctionSubtotal"><?php echo money($o->subtotal); ?></span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted-2">Descuento</span><span id="correctionDiscountOut"><?php echo money($o->discount); ?></span></div>
                        <?php if ((float)$o->shipping > 0): ?>
                            <div class="d-flex justify-content-between"><span class="text-muted-2">Envío</span><span><?php echo money($o->shipping); ?></span></div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between fw-bold"><span>Total</span><span id="correctionTotal"><?php echo money($o->total); ?></span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted-2">Pagado</span>
                            <div class="input-group input-group-sm" style="max-width:180px;width:100%;">
                                <span class="input-group-text"><?php echo html_escape(app_setting('currency_symbol', '₡')); ?></span>
                                <input type="number" step="0.01" min="0" class="form-control text-end" id="correctionPaid" name="paid_amount" value="<?php echo number_format((float)$o->paid_amount, 2, '.', ''); ?>">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between"><span class="text-muted-2">Saldo</span><span id="correctionBalance"><?php echo money($o->balance_amount); ?></span></div>
                        <div class="form-text mt-1">Ajusta el pagado si hubo una devolución o una corrección de precio.</div>
                    </div>
                    <input type="hidden" id="correctionShipping" value="<?php echo number_format((float)$o->shipping, 2, '.', ''); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-brand">Guardar corrección</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canCorrectDelivered): ?>
<div class="modal fade" id="returnItemsModal" tabindex="-1" aria-hidden="true" data-currency="<?php echo html_escape(app_setting('currency_symbol', '₡')); ?>">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="returnItemsForm" class="d-flex flex-column" style="min-height:0;" data-url="<?php echo base_url('orders/admin_return_items/' . $o->id); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i>Devolución de productos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>Solo el administrador puede registrar devoluciones. Las unidades devueltas se eliminan del pedido y se reponen en el inventario.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end" style="width:80px;">Cant.</th>
                                    <th class="text-end" style="width:120px;">Precio</th>
                                    <th class="text-end" style="width:120px;">Devolver</th>
                                    <th class="text-end" style="width:110px;">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($o->items as $item): ?>
                                    <tr data-price="<?php echo (float)$item->unit_price; ?>">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input return-item-check" type="checkbox" id="return_<?php echo (int)$item->id; ?>" data-qty="<?php echo max(1, (int)$item->quantity); ?>" data-target="return_qty_<?php echo (int)$item->id; ?>">
                                                <label class="form-check-label fw-semibold" for="return_<?php echo (int)$item->id; ?>"><?php echo html_escape($item->item_name); ?></label>
                                            </div>
                                            <?php if ($item->item_sku): ?><small class="text-muted-2 ms-4"><code><?php echo html_escape($item->item_sku); ?></code></small><?php endif; ?>
                                        </td>
                                        <td class="text-end"><?php echo number_format((int)$item->quantity); ?></td>
                                        <td class="text-end"><?php echo money($item->unit_price); ?></td>
                                        <td class="text-end">
                                            <input type="number" class="form-control form-control-sm text-end return-item-qty" id="return_qty_<?php echo (int)$item->id; ?>" name="return_qty[<?php echo (int)$item->id; ?>]" min="1" max="<?php echo max(1, (int)$item->quantity); ?>" value="<?php echo max(1, (int)$item->quantity); ?>" disabled>
                                        </td>
                                        <td class="text-end return-item-amount"><?php echo money(0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <span class="text-muted-2">Total a devolver</span>
                        <span class="fw-bold" id="returnItemsTotal"><?php echo money(0); ?></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Registrar devolución</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
