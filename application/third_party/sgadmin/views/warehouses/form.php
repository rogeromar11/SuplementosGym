<?php defined('BASEPATH') OR exit('No direct script access allowed');
$isEdit = isset($warehouse) && $warehouse;
$formAction = $isEdit ? base_url('warehouses/edit/' . $warehouse->id) : base_url('warehouses/create');
$lat = $isEdit ? $warehouse->latitude : '';
$lng = $isEdit ? $warehouse->longitude : '';
?>
<div class="sg-page-header">
    <div>
        <h1><?php echo $isEdit ? 'Editar bodega' : 'Nueva bodega'; ?></h1>
        <p>Registra el punto de salida de las rutas</p>
    </div>
</div>

<div class="card sg-card">
    <div class="sg-card-body">
        <?php if (isset($message) && $message): ?>
            <div class="alert alert-danger" role="alert"><?php echo html_escape(strip_tags($message)); ?></div>
        <?php endif; ?>

        <?php echo form_open($formAction); ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="code">Código *</label>
                    <input type="text" class="form-control" id="code" name="code" value="<?php echo set_value('code', $isEdit ? $warehouse->code : ''); ?>" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label" for="name">Nombre *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $isEdit ? $warehouse->name : ''); ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="address">Dirección</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo set_value('address', $isEdit ? $warehouse->address : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="phone">Teléfono</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo set_value('phone', $isEdit ? $warehouse->phone : ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="directions">Indicaciones de salida</label>
                    <textarea class="form-control" id="directions" name="directions" rows="2"><?php echo set_value('directions', $isEdit ? $warehouse->directions : ''); ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Ubicacion en el mapa</label>
                    <div id="mapPicker" style="height:300px;border-radius:12px;border:1px solid #E4E4E7;"></div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <label class="form-label" for="latitude">Latitud</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" value="<?php echo html_escape($lat); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="longitude">Longitud</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" value="<?php echo html_escape($lng); ?>">
                        </div>
                    </div>
                    <div class="form-text">Haz clic en el mapa para fijar el punto.</div>
                </div>
                <div class="col-md-6 d-flex align-items-center gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo set_checkbox('is_active', '1', (!$isEdit || (int)$warehouse->is_active === 1)); ?>>
                        <label class="form-check-label" for="is_active">Bodega activa</label>
                    </div>
                    <?php if (!$isEdit || (int)$warehouse->is_default !== 1): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="set_default" name="set_default" value="1">
                        <label class="form-check-label" for="set_default">Establecer como predeterminada</label>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                    <a href="<?php echo base_url('warehouses'); ?>" class="btn btn-light">Cancelar</a>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script>
window.sgmsWarehouseForm = {
    initialLat: <?php echo $isEdit && $lat !== '' && $lat !== null ? (float)$lat : 'null'; ?>,
    initialLng: <?php echo $isEdit && $lng !== '' && $lng !== null ? (float)$lng : 'null'; ?>,
    mapCenter: <?php $c = current_country(); echo json_encode(array($c && $c->code === 'SV' ? 13.69294 : 9.928069, $c && $c->code === 'SV' ? -89.218191 : -84.090725)); ?>
};
</script>
