<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?php echo base_url('deposits/mine'); ?>" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h5 mb-0">Nuevo depósito</h1>
    <span></span>
</div>

<?php echo form_open_multipart(base_url('deposits/store'), array('id' => 'depositForm')); ?>
    <div class="courier-route-card mb-3">
        <div class="card-head">
            <small>Efectivo a depositar (rutas seleccionadas)</small>
            <h2 id="depositTotal"><?php echo money($pendingTotal); ?></h2>
        </div>
        <div class="card-body">
            <div class="form-text text-muted-2 mb-2">Desmarque las rutas que no incluirá en este depósito. Puede depositar varias rutas a la vez.</div>
            <?php foreach ($pendingRoutes as $pr): ?>
                <div class="form-check form-check-lg d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #E4E4E7;">
                    <div>
                        <input class="form-check-input route-check" type="checkbox" name="route_ids[]" value="<?php echo (int)$pr->id; ?>" data-amount="<?php echo (float)$pr->cash_amount; ?>" checked id="route_<?php echo (int)$pr->id; ?>">
                        <label class="form-check-label" for="route_<?php echo (int)$pr->id; ?>">
                            <code><?php echo html_escape($pr->route_number); ?></code>
                            <div class="small text-muted-2"><?php echo html_escape($pr->shift_name); ?> · <?php echo fmt_date($pr->route_date); ?></div>
                        </label>
                    </div>
                    <div class="fw-bold"><?php echo money($pr->cash_amount); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="courier-delivery mb-3">
        <div class="d-body py-3">
            <div class="mb-3">
                <label class="d-label form-label" for="receiver_user_id">Entregar el efectivo a *</label>
                <select class="form-select" id="receiver_user_id" name="receiver_user_id" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($receivers as $rcv): ?>
                        <option value="<?php echo (int)$rcv->id; ?>">
                            <?php echo html_escape(trim($rcv->first_name . ' ' . $rcv->last_name)); ?> (<?php echo $rcv->group_name === 'admin' ? 'Administrador' : 'Auxiliar Admin'; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="d-label form-label" for="deposit_receipt">Comprobante del depósito *</label>
                <input type="file" class="form-control" id="deposit_receipt" name="deposit_receipt" data-camera accept="image/jpeg,image/png,image/webp,application/pdf" required>
                <div class="form-text text-muted-2">Foto o PDF del comprobante. Máximo <?php echo (int)$maxMb; ?> MB.</div>
            </div>
            <div class="mb-2">
                <label class="d-label form-label" for="notes">Nota (opcional)</label>
                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Ej: Depositado en SINPE al auxiliar."></textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-brand w-100 courier-btn mb-4"><i class="bi bi-cloud-arrow-up me-1"></i>Registrar depósito</button>
<?php echo form_close(); ?>

<script>
window.sgmsDepositCreate = true;
</script>