<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Bodegas</h1>
        <p>Puntos de salida de las rutas</p>
    </div>
    <div>
        <?php if (has_permission('bodegas.crear')): ?>
            <a href="<?php echo base_url('warehouses/create'); ?>" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Nueva bodega</a>
        <?php endif; ?>
    </div>
</div>

<div class="card sg-card">
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($warehouses as $w): ?>
                        <tr>
                            <td><code><?php echo html_escape($w->code); ?></code></td>
                            <td>
                                <div class="fw-semibold"><?php echo html_escape($w->name); ?></div>
                                <?php if ((int)$w->is_default === 1): ?><span class="badge text-bg-dark mt-1">Predeterminada</span><?php endif; ?>
                            </td>
                            <td><?php echo html_escape($w->address); ?></td>
                            <td><?php echo html_escape($w->phone); ?></td>
                            <td>
                                <?php if ((int)$w->is_active === 1): ?>
                                    <span class="badge text-bg-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-view-map" data-name="<?php echo html_escape($w->name); ?>" data-lat="<?php echo $w->latitude; ?>" data-lng="<?php echo $w->longitude; ?>" <?php echo ($w->latitude === null || $w->longitude === null) ? 'disabled' : ''; ?> title="Ver en mapa"><i class="bi bi-map"></i></button>
                                <?php if ((int)$w->is_default !== 1 && has_permission('bodegas.editar')): ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-set-default" data-url="<?php echo base_url('warehouses/set_default/' . $w->id); ?>" title="Establecer como predeterminada"><i class="bi bi-star"></i></button>
                                <?php endif; ?>
                                <?php if (has_permission('bodegas.editar')): ?>
                                    <a href="<?php echo base_url('warehouses/edit/' . $w->id); ?>" class="btn btn-sm btn-outline-brand" title="Editar"><i class="bi bi-pencil"></i></a>
                                <?php endif; ?>
                                <?php if (has_permission('bodegas.eliminar')): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-warehouse" data-url="<?php echo base_url('warehouses/delete/' . $w->id); ?>" title="Eliminar"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-black text-white">
                <h5 class="modal-title"><i class="bi bi-geo-alt me-2 text-danger"></i><span id="mapModalTitle">Bodega</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="warehouseMap" style="height:420px;"></div>
            </div>
        </div>
    </div>
</div>
