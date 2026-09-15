<?php defined('BASEPATH') OR exit('No direct script access allowed');
$isEdit = isset($order) && $order;
$o = $isEdit ? $order : null;
$rowTpl = array('product_id' => '', 'item_sku' => '', 'item_name' => '', 'quantity' => 1, 'unit_price' => '', 'is_manual' => 0);
$items = $isEdit && !empty($o->items) ? $o->items : array($rowTpl);
foreach ($items as $k => $item) {
    $items[$k] = (object)$item;
}
unset($item);
?>
<div class="sg-page-header">
    <div>
        <h1><?php echo $isEdit ? 'Editar pedido ' . $o->order_number : 'Nuevo pedido'; ?></h1>
        <p><?php echo $isEdit ? 'Modifica el detalle del pedido' : 'Registra un nuevo pedido para entrega'; ?></p>
    </div>
    <?php if ($isEdit): ?>
        <a href="<?php echo base_url('orders/detail/' . $o->id . back_query_string()); ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Volver al detalle</a>
    <?php else: ?>
        <a href="<?php echo back_url('orders'); ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Regresar</a>
    <?php endif; ?>
</div>

<?php echo form_open('', array('id' => 'orderForm', 'enctype' => 'multipart/form-data')); ?>
    <input type="hidden" name="back" value="<?php echo html_escape(isset($_GET['back']) ? $_GET['back'] : (isset($_POST['back']) ? $_POST['back'] : '')); ?>">
    <div class="row g-3">
        <!-- Cliente -->
        <div class="col-lg-6">
            <div class="card sg-card h-100">
                <div class="sg-card-header"><h5><i class="bi bi-person me-2 text-danger"></i>Cliente</h5></div>
                <div class="sg-card-body">
                    <?php if (isset($message) && $message): ?>
                        <div class="alert alert-danger" role="alert"><?php echo html_escape(strip_tags($message)); ?></div>
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="customer_name">Nombre del cliente *</label>
                            <select class="form-select" id="customer_name" data-placeholder="Buscar cliente..." aria-describedby="customerNameHint">
                                <option value="">Buscar cliente...</option>
                                <?php if ($isEdit && $o->customer_name): ?>
                                    <option value="<?php echo html_escape($o->customer_name); ?>" selected><?php echo html_escape($o->customer_name); ?></option>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="customer_name" id="customer_name_value" value="<?php echo set_value('customer_name', $isEdit ? $o->customer_name : ''); ?>">
                            <div class="form-text" id="customerNameHint">Si el cliente existe en el catálogo se sugieren sus datos.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="customer_phone">Teléfono *</label>
                            <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="<?php echo set_value('customer_phone', $isEdit ? $o->customer_phone : ''); ?>" placeholder="8888 8888" required>
                            <div class="form-text" id="phoneNormalized"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="customer_phone2">Teléfono secundario <span class="text-muted-2">(opcional)</span></label>
                            <input type="text" class="form-control" id="customer_phone2" name="customer_phone2" value="<?php echo set_value('customer_phone2', $isEdit ? $o->customer_phone2 : ''); ?>" placeholder="8888 8888">
                            <div class="form-text" id="phone2Normalized"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="customer_email">Correo</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" value="<?php echo set_value('customer_email', $isEdit ? $o->customer_email : ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="warehouse_id">Bodega responsable *</label>
                            <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($warehouses as $w): ?>
                                    <option value="<?php echo $w->id; ?>" <?php echo set_select('warehouse_id', $w->id, $isEdit ? (int)$o->warehouse_id === (int)$w->id : isset($defaultWarehouse) && $defaultWarehouse && (int)$defaultWarehouse->id === (int)$w->id); ?>><?php echo html_escape($w->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="delivery_zone">Zona de entrega *</label>
                            <input type="text" class="form-control" id="delivery_zone" name="delivery_zone" maxlength="100" value="<?php echo set_value('delivery_zone', $isEdit ? $o->delivery_zone : ''); ?>" required>
                            <div class="form-text">Provincia</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="delivery_address">Dirección de entrega *</label>
                            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="2" required><?php echo set_value('delivery_address', $isEdit ? $o->delivery_address : ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="delivery_reference">Referencias adicionales</label>
                            <input type="text" class="form-control" id="delivery_reference" name="delivery_reference" value="<?php echo set_value('delivery_reference', $isEdit ? $o->delivery_reference : ''); ?>" placeholder="Casa blanca, porton negro, etc.">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="map_url">Enlace Waze o Google Maps</label>
                            <input type="url" class="form-control" id="map_url" name="map_url" value="<?php echo set_value('map_url', $isEdit ? $o->map_url : ''); ?>" placeholder="https://waze.com/ul?ll=...">
                            <div class="form-text">Se intenta reconocer las coordenadas automáticamente.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ubicación en el mapa</label>
                            <div id="orderMapPicker" style="height:260px;border-radius:12px;border:1px solid #E4E4E7;z-index:0;"></div>
                            <div class="form-text mt-2">
                                <i class="bi bi-geo-alt me-1 text-danger"></i>Haz clic en el mapa para fijar el punto de entrega
                                <span id="mapCoordText" class="text-muted-2"></span>
                            </div>
                            <input type="hidden" name="latitude" id="latitude" value="<?php echo set_value('latitude', $isEdit ? $o->latitude : ''); ?>">
                            <input type="hidden" name="longitude" id="longitude" value="<?php echo set_value('longitude', $isEdit ? $o->longitude : ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div class="col-lg-6">
            <div class="card sg-card h-100">
                <div class="sg-card-header">
                    <h5><i class="bi bi-boxes me-2 text-danger"></i>Productos</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-brand btn-add-product-row"><i class="bi bi-plus-lg me-1"></i>Producto</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-add-manual-row"><i class="bi bi-pencil me-1"></i>Linea manual</button>
                    </div>
                </div>
                <div class="sg-card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width:34%;">Producto</th>
                                    <th style="width:12%;">Cant.</th>
                                    <th style="width:20%;">Precio</th>
                                    <th style="width:20%;">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $idx => $item): ?>
                                    <tr class="item-row" data-is-manual="<?php echo (int)$item->is_manual; ?>">
                                        <td>
                                            <?php if ($item->is_manual): ?>
                                                <input type="text" class="form-control form-control-sm manual-name" name="item_name[]" value="<?php echo html_escape($item->item_name); ?>" placeholder="Descripción de la línea">
                                                <input type="hidden" name="product_id[]" value="">
                                                <input type="hidden" name="item_sku[]" value="">
                                            <?php else: ?>
                                                <select class="form-select form-select-sm product-select" name="product_id[]" data-placeholder="Buscar producto...">
                                                    <option value="">Buscar producto...</option>
                                                    <?php if ($isEdit && $item->product_id): ?>
                                                        <option value="<?php echo $item->product_id; ?>" selected><?php echo html_escape($item->item_name); ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <input type="hidden" name="item_name[]" value="<?php echo html_escape($item->item_name); ?>">
                                                <input type="hidden" name="item_sku[]" value="<?php echo html_escape($item->item_sku); ?>">
                                            <?php endif; ?>
                                            <input type="hidden" name="is_manual[]" value="<?php echo (int)$item->is_manual; ?>">
                                        </td>
                                        <td><input type="number" step="1" min="1" class="form-control form-control-sm item-qty" name="quantity[]" value="<?php echo max(1, (int)$item->quantity); ?>" required></td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm item-price" name="unit_price[]" value="<?php echo html_escape($item->unit_price); ?>" <?php echo $canChangePrice ? '' : 'readonly'; ?> <?php echo $item->is_manual ? '' : 'data-auto="1"'; ?> required>
                                        </td>
                                        <td class="item-line-total fw-semibold text-end"><?php echo money((float)$item->quantity * (float)$item->unit_price); ?></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-x-lg"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-3">
                    <div class="row g-2">
                        <?php if ($canDiscount): ?>
                        <div class="col-md-6">
                            <label class="form-label" for="discount">Descuento</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo html_escape(app_setting('currency_symbol', '₡')); ?></span>
                                <input type="number" step="0.01" min="0" class="form-control" id="discount" name="discount" value="<?php echo set_value('discount', $isEdit ? $o->discount : '0'); ?>">
                            </div>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="discount" value="0">
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label class="form-label" for="payment_method_id">Forma de pago</label>
                            <select class="form-select" id="payment_method_id" name="payment_method_id">
                                <?php foreach ($paymentMethods as $pm): ?>
                                    <option value="<?php echo $pm->id; ?>" <?php echo set_select('payment_method_id', $pm->id, $isEdit && (int)$o->payment_method_id === (int)$pm->id); ?>><?php echo html_escape($pm->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="paid_before_delivery" name="paid_before_delivery" value="1" <?php echo set_checkbox('paid_before_delivery', '1', $isEdit && (float)$o->balance_amount <= 0.01); ?>>
                                <label class="form-check-label" for="paid_before_delivery">El cliente ya realizó el pago completo</label>
                            </div>
                            <div class="form-text">Se registra el saldo pendiente con la forma de pago seleccionada. El mensajero verá que no debe cobrar.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="payment_receipt" id="paymentReceiptLabel">Comprobante de pago <span class="text-muted-2">(opcional)</span></label>
                            <input type="file" class="form-control" id="payment_receipt" name="payment_receipt" data-camera accept="image/jpeg,image/png,image/webp,application/pdf">
                            <div class="form-text text-danger fw-semibold" id="paidReceiptHint" style="display:none;">Adjunte el comprobante del pago realizado para continuar.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="requested_delivery_date">Fecha solicitada de entrega</label>
                            <input type="date" class="form-control" id="requested_delivery_date" name="requested_delivery_date" min="<?php echo date('Y-m-d'); ?>" data-original="<?php echo html_escape($isEdit ? (string)$o->requested_delivery_date : ''); ?>" value="<?php echo set_value('requested_delivery_date', $isEdit ? $o->requested_delivery_date : date('Y-m-d')); ?>">
                        </div>
                        <div class="col-12 d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includes_gifts" name="includes_gifts" value="1" <?php echo set_checkbox('includes_gifts', '1', $isEdit && (int)$o->includes_gifts === 1); ?>>
                                <label class="form-check-label" for="includes_gifts">Incluye regalías</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_parcel" name="is_parcel" value="1" <?php echo set_checkbox('is_parcel', '1', $isEdit && (int)$o->is_parcel === 1); ?>>
                                <label class="form-check-label" for="is_parcel">Es encomienda</label>
                            </div>
                        </div>
                        <div class="col-12" id="giftWrap" style="<?php echo ($isEdit && (int)$o->includes_gifts === 1) ? '' : 'display:none;'; ?>">
                            <label class="form-label" for="gift_description">Descripción de regalías</label>
                            <input type="text" class="form-control" id="gift_description" name="gift_description" value="<?php echo set_value('gift_description', $isEdit ? $o->gift_description : ''); ?>">
                        </div>
                        <div class="col-md-6" id="transportWrap" style="<?php echo ($isEdit && (int)$o->is_parcel === 1) ? '' : 'display:none;'; ?>">
                            <label class="form-label" for="transport_id">Transporte de encomienda</label>
                            <select class="form-select" id="transport_id" name="transport_id">
                                <option value="">Seleccione...</option>
                                <?php foreach ($transports as $t): ?>
                                    <option value="<?php echo $t->id; ?>" <?php echo set_select('transport_id', $t->id, $isEdit && (int)$o->transport_id === (int)$t->id); ?>><?php echo html_escape($t->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Opcional. También puede definirse al planificar la ruta.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="notes">Notas</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo set_value('notes', $isEdit ? $o->notes : ''); ?></textarea>
                        </div>
                    </div>

                    <div class="bg-black text-white rounded p-3 mt-3 d-flex justify-content-between align-items-center">
                        <span class="text-white-50 small">Total del pedido</span>
                        <span class="fs-4 fw-bold" id="orderTotal" style="font-family:'Fira Code',monospace;"><?php echo money($isEdit ? $o->total : 0); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-brand btn-lg px-4"><i class="bi bi-check-lg me-1"></i><?php echo $isEdit ? 'Guardar cambios' : 'Registrar pedido'; ?></button>
            <a href="<?php echo base_url('orders'); ?>" class="btn btn-light btn-lg">Cancelar</a>
        </div>
    </div>
<?php echo form_close(); ?>

<script>
window.sgmsOrderForm = {
    searchUrl: '<?php echo base_url('orders/product_search'); ?>',
    clientSearchUrl: '<?php echo base_url('orders/client_search'); ?>',
    stockUrl: '<?php echo base_url('orders/product_stock'); ?>',
    parseMapUrl: '<?php echo base_url('orders/parse_map'); ?>',
    geocodeUrl: '<?php echo base_url('orders/geocode_address'); ?>',
    canChangePrice: <?php echo $canChangePrice ? 'true' : 'false'; ?>,
    currency: '<?php echo html_escape(app_setting('currency_symbol', '₡')); ?>',
    mapCenter: <?php $c = current_country(); echo json_encode(array($c && $c->code === 'SV' ? 13.69294 : 9.928069, $c && $c->code === 'SV' ? -89.218191 : -84.090725)); ?>,
    countryCode: '<?php echo html_escape($c && $c->code ? $c->code : ''); ?>',
    dialCode: '<?php echo html_escape(app_setting('country_code', '506')); ?>',
    initialLat: <?php echo $isEdit && $o->latitude ? (float)$o->latitude : 'null'; ?>,
    initialLng: <?php echo $isEdit && $o->longitude ? (float)$o->longitude : 'null'; ?>
};
</script>
