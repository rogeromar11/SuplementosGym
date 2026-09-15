<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statusLabels = array(
    'pendiente' => 'Pendiente de confirmar',
    'recibido' => 'Recibido',
    'entregado' => 'Entregado al administrador',
    'aprobado' => 'Aprobado',
);
$statusColors = array('pendiente' => 'warning', 'recibido' => 'info', 'entregado' => 'primary', 'aprobado' => 'success');
?>
<div class="sg-page-header">
    <div>
        <h1>Depósitos de efectivo</h1>
        <p>Seguimiento del efectivo depositado por los mensajeros al Auxiliar Admin o al Administrador.</p>
    </div>
</div>

<?php $visibleStatuses = !empty($showApproved) ? array('pendiente', 'recibido', 'entregado', 'aprobado') : array('pendiente', 'recibido', 'entregado'); ?>
<div class="row g-3 mb-3">
    <?php foreach ($visibleStatuses as $st): ?>
        <div class="col-6 col-md">
            <div class="card sg-card" style="border-left-color:<?php echo $st === 'aprobado' ? '#16A34A' : ($st === 'pendiente' ? '#F59E0B' : '#DC2626'); ?>;">
                <div class="card-body">
                    <div class="sg-stat-label"><?php echo html_escape($statusLabels[$st]); ?></div>
                    <div class="sg-stat-value fs-5"><?php echo (int)$counts->{$st}['count']; ?></div>
                    <div class="small text-muted-2"><?php echo money($counts->{$st}['total']); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($pendingByCourier)): ?>
<div class="card sg-card mb-3">
    <div class="sg-card-header">
        <h5><i class="bi bi-hourglass-split me-2 text-danger"></i>Efectivo pendiente de depositar por mensajero</h5>
        <span class="badge text-bg-warning"><?php echo count($pendingByCourier); ?> mensajero(s)</span>
    </div>
    <div class="card-body py-3">
        <div class="row g-3">
            <?php foreach ($pendingByCourier as $pc): ?>
                <div class="col-12 col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-semibold"><i class="bi bi-person-badge me-1"></i><?php echo html_escape($pc['courier_name']); ?></div>
                            <div class="text-end">
                                <div class="fw-bold text-danger"><?php echo money($pc['total']); ?></div>
                                <div class="small text-muted-2"><?php echo count($pc['routes']); ?> ruta(s)</div>
                            </div>
                        </div>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Ruta</th><th>Fecha</th><th>Turno</th><th class="text-end">Efectivo</th></tr></thead>
                            <tbody>
                                <?php foreach ($pc['routes'] as $pr): ?>
                                    <tr>
                                        <td><a href="<?php echo base_url('routes/detail/' . $pr->id . '?back=' . urlencode(current_path_query())); ?>"><code><?php echo html_escape($pr->route_number); ?></code></a></td>
                                        <td><?php echo fmt_date($pr->route_date); ?></td>
                                        <td><?php echo html_escape($pr->shift_name); ?></td>
                                        <td class="text-end fw-semibold"><?php echo money($pr->cash_amount); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card sg-card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Mensajero</label>
                <select class="form-select form-select-sm" name="courier_user_id">
                    <option value="">Todos</option>
                    <?php foreach ($couriers as $c): ?>
                        <option value="<?php echo (int)$c->id; ?>" <?php echo (int)$filters['courier_user_id'] === (int)$c->id ? 'selected' : ''; ?>><?php echo html_escape(trim($c->first_name . ' ' . $c->last_name)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">Todos</option>
                    <?php foreach ($statuses as $key => $label): ?>
                        <option value="<?php echo html_escape($key); ?>" <?php echo $filters['status'] === $key ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="btn btn-brand btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card sg-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="depositsTable">
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
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="9" class="text-center text-muted-2 py-4">No hay depósitos registrados con los filtros aplicados.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $d): ?>
                        <tr>
                            <td><code><?php echo (int)$d->id; ?></code></td>
                            <td><?php echo fmt_datetime($d->created_at); ?></td>
                            <td><?php echo html_escape($d->courier_name); ?></td>
                            <td><?php echo html_escape($d->receiver_name); ?></td>
                            <td><?php echo $d->admin_receiver_user_id ? html_escape($d->admin_receiver_name) : '—'; ?></td>
                            <td class="text-end"><?php echo (int)$d->route_count; ?></td>
                            <td class="text-end fw-semibold"><?php echo money($d->amount); ?></td>
                            <td><span class="badge text-bg-<?php echo isset($statusColors[$d->status]) ? $statusColors[$d->status] : 'secondary'; ?>"><?php echo html_escape(isset($statusLabels[$d->status]) ? $statusLabels[$d->status] : $d->status); ?></span></td>
                            <td class="text-end">
                                <a href="<?php echo base_url('deposits/detail/' . $d->id . '?back=' . urlencode(current_path_query())); ?>" class="btn btn-sm btn-outline-brand" title="Ver detalle"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>