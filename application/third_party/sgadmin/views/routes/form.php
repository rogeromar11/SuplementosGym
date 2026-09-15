<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Nueva ruta</h1>
        <p>Planifica una ruta diaria de entrega</p>
    </div>
</div>

<div class="card sg-card mx-auto" style="max-width:680px;">
    <div class="sg-card-body">
        <?php if (isset($message) && $message): ?>
            <div class="alert alert-danger" role="alert"><?php echo html_escape(strip_tags($message)); ?></div>
        <?php endif; ?>

        <?php echo form_open('routes/create'); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="route_date">Fecha *</label>
                    <input type="date" class="form-control" id="route_date" name="route_date" value="<?php echo set_value('route_date', date('Y-m-d')); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="shift_id">Turno *</label>
                    <select class="form-select" id="shift_id" name="shift_id" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($shifts as $s): ?>
                            <option value="<?php echo $s->id; ?>" <?php echo set_select('shift_id', $s->id); ?>><?php echo html_escape($s->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="warehouse_id">Bodega de salida</label>
                    <select class="form-select select2" id="warehouse_id" name="warehouse_id">
                        <option value="">Seleccione...</option>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?php echo $w->id; ?>" <?php echo set_select('warehouse_id', $w->id); ?>><?php echo html_escape($w->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="courier_user_id">Mensajero *</label>
                    <select class="form-select select2" id="courier_user_id" name="courier_user_id" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($couriers as $cu): ?>
                            <option value="<?php echo $cu->id; ?>" <?php echo set_select('courier_user_id', $cu->id); ?>><?php echo html_escape(trim($cu->first_name . ' ' . $cu->last_name)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="status">Estado inicial</label>
                    <select class="form-select" id="status" name="status">
                        <option value="planificada" <?php echo set_select('status', 'planificada', true); ?>>Planificada</option>
                        <option value="borrador" <?php echo set_select('status', 'borrador'); ?>>Borrador</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="notes">Observaciones</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo set_value('notes'); ?></textarea>
                </div>
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Crear ruta</button>
                    <a href="<?php echo base_url('routes'); ?>" class="btn btn-light">Cancelar</a>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>
