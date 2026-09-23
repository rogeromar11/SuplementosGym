<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card sg-card mb-4">
    <div class="sg-card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Importar clientes desde Excel</h2>
                <p class="text-muted-2 mb-0">Carga un archivo .xlsx. El sistema detecta automáticamente la hoja que contiene los encabezados; no es obligatorio que se llame <strong>Lista de Clientes</strong>. Se mostrará una vista previa antes de guardar.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo base_url('clients/export'); ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Exportar clientes</a>
                <a href="<?php echo base_url('clients/import_template'); ?>" class="btn btn-outline-brand"><i class="bi bi-file-earmark-arrow-down me-1"></i>Descargar plantilla</a>
            </div>
        </div>

        <?php if (isset($importMessage) && $importMessage): ?>
            <div class="alert alert-danger" role="alert"><?php echo html_escape($importMessage); ?></div>
        <?php endif; ?>

        <?php echo form_open_multipart(base_url('clients/import_preview'), array('class' => 'row g-3 align-items-end')); ?>
            <div class="col-md-8">
                <label class="form-label" for="client_file">Archivo de clientes *</label>
                <input type="file" class="form-control" id="client_file" name="client_file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                <div class="form-text">Máximo 5 MB. Se importan cliente, celular, zona, dirección, tipo de entrega y notas. El número de cliente se genera automáticamente en secuencia.</div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-brand w-100"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Revisar archivo</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php if (!empty($importPreview)): ?>
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
                <?php echo form_open(base_url('clients/import_commit')); ?>
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
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Zona</th>
                        <th>Dirección</th>
                        <th>Tipo de entrega</th>
                        <th>Notas</th>
                        <th>Validacion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($importPreview['rows'] as $row): ?>
                    <tr class="<?php echo !empty($row['errors']) ? 'table-danger' : ''; ?>">
                        <td><?php echo (int)$row['source_row']; ?></td>
                        <td><span class="badge <?php echo $row['status'] === 'Actualizar' ? 'text-bg-warning' : 'text-bg-success'; ?>"><?php echo html_escape($row['status']); ?></span></td>
                        <td><?php echo html_escape($row['name']); ?></td>
                        <td><?php echo html_escape($row['phone']); ?></td>
                        <td><?php echo html_escape($row['zone']); ?></td>
                        <td style="min-width:280px;"><?php echo html_escape($row['address']); ?></td>
                        <td><?php echo html_escape($row['delivery_type']); ?></td>
                        <td><?php echo html_escape($row['notes']); ?></td>
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
