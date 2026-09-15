<?php defined('BASEPATH') OR exit('No direct script access allowed');
$isEdit = isset($product) && $product;
$formAction = $isEdit ? base_url('products/edit/' . $product->id) : base_url('products/create');
$importPreview = isset($importPreview) ? $importPreview : null;
?>
<div class="sg-page-header">
    <div>
        <h1><?php echo $isEdit ? 'Editar producto' : 'Nuevo producto'; ?></h1>
        <p>Administra el catálogo, precios e inventario</p>
    </div>
</div>

<?php if (!$isEdit): ?>
<div class="card sg-card mb-4">
    <div class="sg-card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Importar inventario desde Excel</h2>
                <p class="text-muted-2 mb-0">Carga un archivo .xlsx. El sistema detecta automáticamente la hoja que contiene los encabezados; no es obligatorio que se llame <strong>Sheet1</strong>. Primero se mostrará una vista previa y no se guardará nada hasta confirmar.</p>
            </div>
            <a href="<?php echo base_url('products/import_template'); ?>" class="btn btn-outline-brand"><i class="bi bi-file-earmark-arrow-down me-1"></i>Descargar plantilla</a>
        </div>

        <?php if (isset($importMessage) && $importMessage): ?>
            <div class="alert alert-danger" role="alert"><?php echo html_escape($importMessage); ?></div>
        <?php endif; ?>

        <?php echo form_open_multipart(base_url('products/import_preview'), array('class' => 'row g-3 align-items-end')); ?>
            <div class="col-md-8">
                <label class="form-label" for="inventory_file">Archivo de inventario *</label>
                <input type="file" class="form-control" id="inventory_file" name="inventory_file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                <div class="form-text">Máximo 5 MB. Columnas requeridas: Producto, Cantidad disponible, Venta y Costo. Opcionales: Laboratorio, Nombre, Peso, Servidas y Sabor.</div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-brand w-100"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Revisar archivo</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php if ($importPreview): ?>
<div class="card sg-card mb-4">
    <div class="sg-card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Vista previa: <?php echo html_escape($importPreview['filename']); ?></h2>
                <p class="text-muted-2 mb-0">
                    <?php echo count($importPreview['rows']); ?> filas detectadas:
                    <strong><?php echo (int)$importPreview['new_count']; ?></strong> nuevas y
                    <strong><?php echo (int)$importPreview['update_count']; ?></strong> por actualizar.
                </p>
            </div>
            <?php if ($importPreview['valid'] && isset($importToken)): ?>
                <?php echo form_open(base_url('products/import_commit')); ?>
                    <input type="hidden" name="import_token" value="<?php echo html_escape($importToken); ?>">
                    <button type="submit" class="btn btn-brand"><i class="bi bi-cloud-arrow-up me-1"></i>Confirmar importacion</button>
                <?php echo form_close(); ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($importPreview['errors'])): ?>
            <div class="alert alert-danger" role="alert">
                <strong>No se puede importar:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($importPreview['errors'] as $error): ?>
                        <li><?php echo html_escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ((int)$importPreview['row_error_count'] > 0): ?>
            <div class="alert alert-danger" role="alert">Hay <?php echo (int)$importPreview['row_error_count']; ?> filas con errores. Corrija el Excel y vuelva a cargarlo.</div>
        <?php else: ?>
            <div class="alert alert-success" role="alert">El archivo es válido. Revise los datos y confirme para guardarlos.</div>
        <?php endif; ?>

        <?php if (!empty($importPreview['rows'])): ?>
        <div class="table-responsive" style="max-height:520px;">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light position-sticky top-0">
                    <tr>
                        <th>Fila</th>
                        <th>Estado</th>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th>Nombre</th>
                        <th>Peso</th>
                        <th>Servidas</th>
                        <th>Sabor</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Venta</th>
                        <th class="text-end">Costo</th>
                        <th>Validacion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($importPreview['rows'] as $row): ?>
                    <tr class="<?php echo !empty($row['errors']) ? 'table-danger' : ''; ?>">
                        <td><?php echo (int)$row['source_row']; ?></td>
                        <td><span class="badge <?php echo $row['status'] === 'Actualizar' ? 'text-bg-warning' : 'text-bg-success'; ?>"><?php echo html_escape($row['status']); ?></span></td>
                        <td><?php echo html_escape($row['product_type']); ?></td>
                        <td><?php echo html_escape($row['laboratory']); ?></td>
                        <td><?php echo html_escape($row['name']); ?></td>
                        <td><?php echo html_escape($row['weight']); ?></td>
                        <td><?php echo html_escape($row['servings']); ?></td>
                        <td><?php echo html_escape($row['flavor']); ?></td>
                        <td class="text-end"><?php echo $row['stock_qty'] !== null ? (int)$row['stock_qty'] : '—'; ?></td>
                        <td class="text-end"><?php echo $row['unit_price'] !== null ? money($row['unit_price']) : '—'; ?></td>
                        <td class="text-end"><?php echo $row['cost_price'] !== null ? money($row['cost_price']) : '—'; ?></td>
                        <td>
                            <?php if (empty($row['errors'])): ?>
                                <span class="text-success">Correcta</span>
                            <?php else: ?>
                                <?php echo html_escape(implode(' ', $row['errors'])); ?>
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
<?php endif; ?>

<div class="card sg-card mx-auto" style="max-width:1100px;">
    <div class="sg-card-body">
        <h2 class="h5 mb-3"><?php echo $isEdit ? 'Datos del producto' : 'Crear producto manualmente'; ?></h2>

        <?php if (isset($message) && $message): ?>
            <div class="alert alert-danger" role="alert"><?php echo html_escape(strip_tags($message)); ?></div>
        <?php endif; ?>
        <?php if (validation_errors()): ?>
            <div class="alert alert-danger" role="alert"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open_multipart($formAction); ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="sku">Número de producto</label>
                    <input type="text" class="form-control" id="sku" value="<?php echo $isEdit ? html_escape($product->sku) : 'Automático (PRO-XXXX)'; ?>" readonly>
                    <div class="form-text">Se genera automáticamente.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="product_type">Producto *</label>
                    <input type="text" class="form-control" id="product_type" name="product_type" maxlength="100" value="<?php echo set_value('product_type', $isEdit ? $product->product_type : ''); ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="laboratory">Laboratorio</label>
                    <input type="text" class="form-control" id="laboratory" name="laboratory" maxlength="120" value="<?php echo set_value('laboratory', $isEdit ? $product->laboratory : ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="name">Nombre</label>
                    <input type="text" class="form-control" id="name" name="name" maxlength="150" value="<?php echo set_value('name', $isEdit ? $product->name : ''); ?>">
                    <div class="form-text">Si queda vacio, se usara el valor de Producto.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="weight">Peso</label>
                    <input type="text" class="form-control" id="weight" name="weight" maxlength="50" value="<?php echo set_value('weight', $isEdit ? $product->weight : ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="servings">Servidas</label>
                    <input type="text" class="form-control" id="servings" name="servings" maxlength="50" value="<?php echo set_value('servings', $isEdit ? $product->servings : ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="flavor">Sabor</label>
                    <input type="text" class="form-control" id="flavor" name="flavor" maxlength="120" value="<?php echo set_value('flavor', $isEdit ? $product->flavor : ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="unit_price">Venta *</label>
                    <div class="input-group">
                        <span class="input-group-text"><?php echo html_escape(app_setting('currency_symbol', '₡')); ?></span>
                        <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" value="<?php echo set_value('unit_price', $isEdit ? $product->unit_price : ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="cost_price">Costo *</label>
                    <div class="input-group">
                        <span class="input-group-text"><?php echo html_escape(app_setting('currency_symbol', '₡')); ?></span>
                        <input type="number" step="0.01" min="0" class="form-control" id="cost_price" name="cost_price" value="<?php echo set_value('cost_price', $isEdit ? $product->cost_price : ''); ?>" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="image">Imagen del producto</label>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <?php if ($isEdit && !empty($product->image)): ?>
                            <img src="<?php echo base_url('assets/img/products/' . rawurlencode($product->image)); ?>" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:12px;border:1px solid #e5e7eb;">
                        <?php else: ?>
                            <div style="width:96px;height:96px;border-radius:12px;border:1px dashed #d1d5db;display:flex;align-items:center;justify-content:center;color:#9ca3af;"><i class="bi bi-image" style="font-size:1.6rem;"></i></div>
                        <?php endif; ?>
                        <div class="flex-grow-1" style="min-width:240px;">
                            <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG o WEBP, máximo 3 MB. Es la foto que se muestra en la tienda.</div>
                            <?php if ($isEdit && !empty($product->image)): ?>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                    <label class="form-check-label" for="remove_image">Quitar imagen actual</label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="description">Descripción</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description', $isEdit ? $product->description : ''); ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="store_description">Descripción para la tienda</label>
                    <textarea class="form-control" id="store_description" name="store_description" rows="3"><?php echo set_value('store_description', $isEdit ? $product->store_description : ''); ?></textarea>
                    <div class="form-text">Si se deja vacía, la tienda usa la Descripción general.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="stock_qty">Cantidad disponible (inventario)</label>
                    <input type="number" step="1" min="0" class="form-control" id="stock_qty" name="stock_qty" value="<?php echo set_value('stock_qty', $isEdit ? (int)$product->stock_qty : '0'); ?>" style="max-width:220px;" required>
                    <div class="form-text">Se descuenta automáticamente al registrar pedidos.</div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo set_checkbox('is_active', '1', (!$isEdit || (int)$product->is_active === 1)); ?>>
                        <label class="form-check-label" for="is_active">Producto activo</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" <?php echo set_checkbox('featured', '1', $isEdit && (int)$product->featured === 1); ?>>
                        <label class="form-check-label" for="featured">Destacar en la tienda (producto destacado)</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                    <a href="<?php echo base_url('products'); ?>" class="btn btn-light">Cancelar</a>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>
