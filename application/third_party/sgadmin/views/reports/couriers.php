<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Reporte de mensajeros</h1>
        <p>Desempeno por mensajero en el periodo</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (has_permission('reportes.exportar')): ?>
            <a href="<?php echo base_url('reports/export_couriers_pdf?' . http_build_query($filters)); ?>" class="btn btn-outline-dark"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="<?php echo base_url('reports/export_couriers_excel?' . http_build_query($filters)); ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <?php endif; ?>
    </div>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'general') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports'); ?>">General</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'couriers') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/couriers'); ?>">Mensajeros</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'products') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/products'); ?>">Productos</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'deposits') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/deposits'); ?>">Depósitos</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'sellers') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/sellers'); ?>">Vendedores</a></li>
</ul>

<div class="card sg-card mb-3">
    <div class="sg-card-body">
        <form method="get" action="<?php echo base_url('reports/couriers'); ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="date_from">Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="date_to">Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="courier_user_id">Mensajero</label>
                <select class="form-select" id="courier_user_id" name="courier_user_id">
                    <option value="">Todos</option>
                    <?php foreach ($couriers as $cu): ?>
                        <option value="<?php echo $cu->id; ?>" <?php echo ($filters['courier_user_id'] == $cu->id) ? 'selected' : ''; ?>><?php echo html_escape(trim($cu->first_name . ' ' . $cu->last_name)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-brand"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card sg-card">
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table data-table-no-search" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>">
                <thead>
                    <tr>
                        <th>Mensajero</th>
                        <th class="text-center">Rutas</th>
                        <th class="text-center">Pedidos</th>
                        <th class="text-center">Entregados</th>
                        <th class="text-center">No entregados</th>
                        <th class="text-center">% Éxito</th>
                        <th class="text-end">Esperado</th>
                        <th class="text-end">Cobrado</th>
                        <th class="text-end">Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td class="fw-semibold"><?php echo html_escape($row->courier_name); ?></td>
                            <td class="text-center"><?php echo (int)$row->routes; ?></td>
                            <td class="text-center"><?php echo (int)$row->total_orders; ?></td>
                            <td class="text-center text-success fw-semibold"><?php echo (int)$row->delivered; ?></td>
                            <td class="text-center text-danger fw-semibold"><?php echo (int)$row->not_delivered; ?></td>
                            <td class="text-center">
                                <div class="progress" style="height:8px;" role="progressbar" aria-valuenow="<?php echo round((float)$row->success_rate); ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-success" style="width:<?php echo round((float)$row->success_rate); ?>%"></div>
                                </div>
                                <small><?php echo round((float)$row->success_rate, 1); ?>%</small>
                            </td>
                            <td class="text-end"><?php echo money($row->expected_amount); ?></td>
                            <td class="text-end fw-semibold"><?php echo money($row->collected_amount); ?></td>
                            <td class="text-end text-danger"><?php echo money($row->pending_amount); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($rows)): ?><p class="text-muted-2 mb-0">Sin resultados para los filtros.</p><?php endif; ?>
    </div>
</div>
