<?php defined('BASEPATH') OR exit('No direct script access allowed');
$o = $order;
$canEdit = in_array($o->status, array('registrado', 'pendiente_preparacion', 'en_preparacion', 'preparado', 'asignado_ruta', 'no_entregado', 'reprogramado')) && has_permission('pedidos.editar');
$canCancel = in_array($o->status, array('registrado', 'pendiente_preparacion', 'en_preparacion', 'preparado', 'asignado_ruta', 'no_entregado', 'reprogramado')) && has_permission('pedidos.anular');
$canDelete = in_array($o->status, array('registrado', 'pendiente_preparacion', 'en_preparacion', 'preparado', 'no_entregado', 'reprogramado', 'cancelado')) && has_permission('pedidos.eliminar');
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
            <div class="sg-card-header"><h5><i class="bi bi-person me-2 text-danger"></i>Cliente y entrega</h5></div>
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
                    <?php if ($o->notes): ?>
                        <div class="col-12">
                            <div class="small text-muted-2">Notas</div>
                            <div><?php echo html_escape($o->notes); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card sg-card mb-3">
            <div class="sg-card-header"><h5><i class="bi bi-boxes me-2 text-danger"></i>Detalle</h5></div>
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
                                <?php if ($d->received_by_name): ?>
                                    <div class="small">Recibió: <?php echo html_escape($d->received_by_name); ?></div>
                                <?php endif; ?>
                                <?php if ($d->payment_method_name): ?>
                                    <div class="small text-success"><i class="bi bi-cash-coin me-1"></i>Forma de pago: <?php echo html_escape($d->payment_method_name); ?><?php echo $d->payment_amount !== null ? ' (' . money($d->payment_amount) . ')' : ''; ?></div>
                                <?php endif; ?>
                                <?php if ($d->failure_reason_name): ?>
                                    <div class="small text-danger">Motivo: <?php echo html_escape($d->failure_reason_name); ?><?php echo $d->failure_other ? ' — ' . html_escape($d->failure_other) : ''; ?></div>
                                <?php endif; ?>
                                <?php if ($d->notes): ?>
                                    <div class="small"><i class="bi bi-chat-left-text me-1"></i><?php echo html_escape($d->notes); ?></div>
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
                                <?php if ($h->notes): ?><div class="small"><?php echo html_escape($h->notes); ?></div><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
