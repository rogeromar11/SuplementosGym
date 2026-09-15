<?php defined('BASEPATH') OR exit('No direct script access allowed');
$totals = array('orders' => 0, 'delivered' => 0, 'not_delivered' => 0, 'expected' => 0, 'collected' => 0);
foreach ($rows as $row) {
    $totals['orders'] += (int)$row->total_orders;
    $totals['delivered'] += (int)$row->delivered;
    $totals['not_delivered'] += (int)$row->not_delivered;
    $totals['expected'] += (float)$row->expected_amount;
    $totals['collected'] += (float)$row->collected_amount;
}
?>
<div class="sg-page-header">
    <div>
        <h1>Reporte general</h1>
        <p>Rutas, entregas y montos por periodo</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (has_permission('reportes.exportar')): ?>
            <a href="<?php echo base_url('reports/export_general_pdf?' . http_build_query($filters)); ?>" class="btn btn-outline-dark"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="<?php echo base_url('reports/export_general_excel?' . http_build_query($filters)); ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
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
        <form method="get" action="<?php echo base_url('reports'); ?>" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label" for="date_from">Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="date_to">Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="courier_user_id">Mensajero</label>
                <select class="form-select" id="courier_user_id" name="courier_user_id">
                    <option value="">Todos</option>
                    <?php foreach ($couriers as $cu): ?>
                        <option value="<?php echo $cu->id; ?>" <?php echo ($filters['courier_user_id'] == $cu->id) ? 'selected' : ''; ?>><?php echo html_escape(trim($cu->first_name . ' ' . $cu->last_name)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="warehouse_id">Bodega</label>
                <select class="form-select" id="warehouse_id" name="warehouse_id">
                    <option value="">Todas</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?php echo $w->id; ?>" <?php echo ($filters['warehouse_id'] == $w->id) ? 'selected' : ''; ?>><?php echo html_escape($w->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="shift_id">Turno</label>
                <select class="form-select" id="shift_id" name="shift_id">
                    <option value="">Todos</option>
                    <?php foreach ($shifts as $s): ?>
                        <option value="<?php echo $s->id; ?>" <?php echo ($filters['shift_id'] == $s->id) ? 'selected' : ''; ?>><?php echo html_escape($s->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-brand"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card sg-card"><div class="card-body"><div class="sg-stat-label">Pedidos</div><div class="sg-stat-value fs-4"><?php echo $totals['orders']; ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card sg-card" style="border-left-color:#16A34A;"><div class="card-body"><div class="sg-stat-label">Entregados</div><div class="sg-stat-value fs-4 text-success"><?php echo $totals['delivered']; ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card sg-card" style="border-left-color:#DC2626;"><div class="card-body"><div class="sg-stat-label">No entregados</div><div class="sg-stat-value fs-4 text-danger"><?php echo $totals['not_delivered']; ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card sg-card" style="border-left-color:#F59E0B;"><div class="card-body"><div class="sg-stat-label">Cobrado</div><div class="sg-stat-value fs-4"><?php echo money($totals['collected']); ?></div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card sg-card">
            <div class="sg-card-header"><h5>Rutas en el periodo</h5></div>
            <div class="sg-card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle data-table data-table-no-search" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Ruta</th>
                                <th>Turno</th>
                                <th>Mensajero</th>
                                <th class="text-center">Ped.</th>
                                <th class="text-center">Ent.</th>
                                <th class="text-center">No ent.</th>
                                <th class="text-end">Esperado</th>
                                <th class="text-end">Cobrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo fmt_date($row->route_date); ?></td>
                                    <td><a href="<?php echo base_url('routes/detail/' . $row->route_id . '?back=' . urlencode(current_path_query())); ?>"><code><?php echo html_escape($row->route_number); ?></code></a></td>
                                    <td><?php echo html_escape($row->shift_name); ?></td>
                                    <td><?php echo html_escape($row->courier_name); ?></td>
                                    <td class="text-center"><?php echo (int)$row->total_orders; ?></td>
                                    <td class="text-center text-success fw-semibold"><?php echo (int)$row->delivered; ?></td>
                                    <td class="text-center text-danger fw-semibold"><?php echo (int)$row->not_delivered; ?></td>
                                    <td class="text-end"><?php echo money($row->expected_amount); ?></td>
                                    <td class="text-end fw-semibold"><?php echo money($row->collected_amount); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($rows)): ?><p class="text-muted-2 mb-0">Sin resultados para los filtros.</p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sg-card">
            <div class="sg-card-header"><h5>Formas de pago</h5></div>
            <div class="sg-card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($paymentBreakdown as $pb): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-semibold"><?php echo html_escape($pb->payment_name); ?></div>
                                <div class="small text-muted-2"><?php echo (int)$pb->total_payments; ?> pagos</div>
                            </div>
                            <span class="fw-bold"><?php echo money($pb->total); ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($paymentBreakdown)): ?><li class="list-group-item px-0 text-muted-2">Sin pagos registrados.</li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
