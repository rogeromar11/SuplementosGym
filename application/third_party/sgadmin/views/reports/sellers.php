<?php defined('BASEPATH') OR exit('No direct script access allowed');
$totalVentas = 0;
$totalVendido = 0.0;
$totalCobrado = 0.0;
$totalPendiente = 0.0;
$totalCancelados = 0;
foreach ($summary as $row) {
    $totalVentas += (int)$row->ventas;
    $totalVendido += (float)$row->total_vendido;
    $totalCobrado += (float)$row->total_cobrado;
    $totalPendiente += (float)$row->pendiente_cobro;
    $totalCancelados += (int)$row->cancelados;
}
?>
<div class="sg-page-header">
    <div>
        <h1>Reporte de vendedores</h1>
        <p>Ventas por dia y comparativa entre vendedores</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (has_permission('reportes.exportar')): ?>
            <a href="<?php echo base_url('reports/export_sellers_pdf?' . http_build_query($filters)); ?>" class="btn btn-outline-dark"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="<?php echo base_url('reports/export_sellers_excel?' . http_build_query($filters)); ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
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
        <form method="get" action="<?php echo base_url('reports/sellers'); ?>" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label" for="date_from">Desde</label>
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="date_to">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="seller_user_id">Vendedor</label>
                <select class="form-select form-select-sm" id="seller_user_id" name="seller_user_id">
                    <option value="">Todos</option>
                    <?php foreach ($sellers as $s): ?>
                        <option value="<?php echo (int)$s->id; ?>" <?php echo (int)$filters['seller_user_id'] === (int)$s->id ? 'selected' : ''; ?>><?php echo html_escape(trim($s->first_name . ' ' . $s->last_name)); ?></option>
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
    <div class="col-6 col-md">
        <div class="card sg-card"><div class="card-body">
            <div class="sg-stat-label">Ventas del periodo</div>
            <div class="sg-stat-value fs-5"><?php echo $totalVentas; ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card" style="border-left-color:#16A34A;"><div class="card-body">
            <div class="sg-stat-label">Total vendido</div>
            <div class="sg-stat-value fs-5"><?php echo money($totalVendido); ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card" style="border-left-color:#F59E0B;"><div class="card-body">
            <div class="sg-stat-label">Cobrado</div>
            <div class="sg-stat-value fs-5"><?php echo money($totalCobrado); ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card" style="border-left-color:#DC2626;"><div class="card-body">
            <div class="sg-stat-label">Pendiente de cobro</div>
            <div class="sg-stat-value fs-5"><?php echo money($totalPendiente); ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card sg-card"><div class="card-body">
            <div class="sg-stat-label">Cancelados</div>
            <div class="sg-stat-value fs-5"><?php echo $totalCancelados; ?></div>
        </div></div>
    </div>
</div>

<div class="card sg-card mb-3">
    <div class="sg-card-header"><h5><i class="bi bi-trophy me-2 text-danger"></i>Resumen por vendedor (periodo)</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th class="text-end">Ventas</th>
                    <th class="text-end">% de Ventas</th>
                    <th class="text-end">Total vendido</th>
                    <th class="text-end">% del Monto</th>
                    <th class="text-end">Ticket promedio</th>
                    <th class="text-end">Cobrado</th>
                    <th class="text-end">Pendiente</th>
                    <th class="text-end">Entregados</th>
                    <th class="text-end">Cancelados</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($summary)): ?>
                    <tr><td colspan="8" class="text-center text-muted-2 py-4">Sin ventas en el periodo seleccionado.</td></tr>
                <?php else: ?>
                    <?php foreach ($summary as $row): ?>
                        <?php $avg = (int)$row->ventas > 0 ? round((float)$row->total_vendido / (int)$row->ventas, 2) : 0; ?>
                        <tr>
                            <td class="fw-semibold"><?php echo html_escape($row->seller_name ?: '—'); ?></td>
                            <td class="text-end"><?php echo (int)$row->ventas; ?></td>
                            <td class="text-end" style="min-width:110px;">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;max-width:80px;">
                                        <div class="progress-bar" style="width:<?php echo (float)$row->share_ventas_pct; ?>%;background:#0B0B0F;"></div>
                                    </div>
                                    <span class="fw-semibold"><?php echo $row->share_ventas_pct; ?>%</span>
                                </div>
                            </td>
                            <td class="text-end fw-semibold"><?php echo money($row->total_vendido); ?></td>
                            <td class="text-end" style="min-width:110px;">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;max-width:80px;">
                                        <div class="progress-bar" style="width:<?php echo (float)$row->share_pct; ?>%;background:#DC2626;"></div>
                                    </div>
                                    <span class="fw-semibold"><?php echo $row->share_pct; ?>%</span>
                                </div>
                            </td>
                            <td class="text-end"><?php echo money($avg); ?></td>
                            <td class="text-end"><?php echo money($row->total_cobrado); ?></td>
                            <td class="text-end <?php echo (float)$row->pendiente_cobro > 0 ? 'text-danger fw-semibold' : ''; ?>"><?php echo money($row->pendiente_cobro); ?></td>
                            <td class="text-end"><?php echo (int)$row->entregados; ?></td>
                            <td class="text-end"><?php echo (int)$row->cancelados; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card">
    <div class="sg-card-header"><h5><i class="bi bi-calendar-day me-2 text-danger"></i>Detalle por día y vendedor</h5></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Vendedor</th>
                    <th class="text-end">Ventas</th>
                    <th class="text-end">Total vendido</th>
                    <th class="text-end">Cobrado</th>
                    <th class="text-end">Pendiente</th>
                    <th class="text-end">Entregados</th>
                    <th class="text-end">Cancelados</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daily)): ?>
                    <tr><td colspan="8" class="text-center text-muted-2 py-4">Sin información para los filtros aplicados.</td></tr>
                <?php else: ?>
                    <?php foreach ($daily as $d): ?>
                        <tr>
                            <td><?php echo fmt_date($d->sale_date); ?></td>
                            <td><?php echo html_escape($d->seller_name ?: '—'); ?></td>
                            <td class="text-end"><?php echo (int)$d->ventas; ?></td>
                            <td class="text-end fw-semibold"><?php echo money($d->total_vendido); ?></td>
                            <td class="text-end"><?php echo money($d->total_cobrado); ?></td>
                            <td class="text-end"><?php echo money($d->pendiente_cobro); ?></td>
                            <td class="text-end"><?php echo (int)$d->entregados; ?></td>
                            <td class="text-end"><?php echo (int)$d->cancelados; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>