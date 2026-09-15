<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statusLabels = array(
    'pendiente' => 'Pendiente de confirmar',
    'recibido' => 'Recibido',
    'entregado' => 'Entregado al administrador',
    'aprobado' => 'Aprobado',
);
$statusColors = array('pendiente' => 'warning', 'recibido' => 'info', 'entregado' => 'primary', 'aprobado' => 'success');
$totalAmount = 0;
$totales = array('pendiente' => 0.0, 'recibido' => 0.0, 'entregado' => 0.0, 'aprobado' => 0.0);
foreach ($rows as $row) {
    $totalAmount += (float)$row->amount;
    if (isset($totales[$row->status])) {
        $totales[$row->status] += (float)$row->amount;
    }
}
?>
<div class="sg-page-header">
    <div>
        <h1>Reporte de depósitos</h1>
        <p>Seguimiento del efectivo depositado por los mensajeros</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (has_permission('reportes.exportar')): ?>
            <a href="<?php echo base_url('reports/export_deposits_pdf?' . http_build_query($filters)); ?>" class="btn btn-outline-dark"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="<?php echo base_url('reports/export_deposits_excel?' . http_build_query($filters)); ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
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
        <form method="get" action="<?php echo base_url('reports/deposits'); ?>" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label" for="date_from">Desde</label>
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="date_to">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="courier_user_id">Mensajero</label>
                <select class="form-select form-select-sm" id="courier_user_id" name="courier_user_id">
                    <option value="">Todos</option>
                    <?php foreach ($couriers as $c): ?>
                        <option value="<?php echo (int)$c->id; ?>" <?php echo (int)$filters['courier_user_id'] === (int)$c->id ? 'selected' : ''; ?>><?php echo html_escape(trim($c->first_name . ' ' . $c->last_name)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Estado</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">Todos</option>
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?php echo html_escape($key); ?>" <?php echo isset($filters['status']) && $filters['status'] === $key ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-brand btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php foreach ($totales as $st => $t): ?>
        <div class="col-6 col-md-3">
            <div class="card sg-card" style="border-left-color:<?php echo $st === 'aprobado' ? '#16A34A' : ($st === 'pendiente' ? '#F59E0B' : '#DC2626'); ?>;">
                <div class="card-body">
                    <div class="sg-stat-label"><?php echo html_escape($statusLabels[$st]); ?></div>
                    <div class="sg-stat-value fs-5"><?php echo money($t); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card sg-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Mensajero</th>
                    <th>Recibió</th>
                    <th>Entregado a</th>
                    <th class="text-end">Rutas</th>
                    <th class="text-end">Monto</th>
                    <th>Estado</th>
                    <th>Visto bueno</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="9" class="text-center text-muted-2 py-4">No hay depósitos en el periodo seleccionado.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $d): ?>
                        <tr>
                            <td><a href="<?php echo base_url('deposits/detail/' . $d->id . '?back=' . urlencode(current_path_query())); ?>"><code><?php echo (int)$d->id; ?></code></a></td>
                            <td><?php echo fmt_datetime($d->created_at); ?></td>
                            <td><?php echo html_escape($d->courier_name); ?></td>
                            <td><?php echo html_escape($d->receiver_name); ?></td>
                            <td><?php echo $d->admin_receiver_user_id ? html_escape($d->admin_receiver_name) : '—'; ?></td>
                            <td class="text-end"><?php echo (int)$d->route_count; ?></td>
                            <td class="text-end fw-semibold"><?php echo money($d->amount); ?></td>
                            <td><span class="badge text-bg-<?php echo isset($statusColors[$d->status]) ? $statusColors[$d->status] : 'secondary'; ?>"><?php echo html_escape(isset($statusLabels[$d->status]) ? $statusLabels[$d->status] : $d->status); ?></span></td>
                            <td><?php echo $d->confirmed_at ? html_escape($d->confirmed_by_name) . ' · ' . fmt_datetime($d->confirmed_at) : '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
                <tfoot>
                    <tr class="fw-semibold">
                        <td colspan="6" class="text-end">Total</td>
                        <td class="text-end"><?php echo money($totalAmount); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>