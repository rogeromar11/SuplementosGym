<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Dashboard principal.
 * Variables: $stats, $totals, $today, $todayRoutes, $recentOrders.
 */
?>
<div class="sg-page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Resumen operativo de hoy</p>
    </div>
    <div>
        <span class="badge text-bg-dark"><i class="bi bi-calendar3 me-1"></i><?php echo fmt_date($today); ?></span>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100">
            <div class="card-body">
                <div class="sg-stat-label">Pedidos registrados hoy</div>
                <div class="sg-stat-value"><?php echo (int)$stats['orders_today']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100" style="border-left-color:#F59E0B;">
            <div class="card-body">
                <div class="sg-stat-label">Pendientes de preparar</div>
                <div class="sg-stat-value"><?php echo (int)$stats['pending_prep']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100" style="border-left-color:#16A34A;">
            <div class="card-body">
                <div class="sg-stat-label">Preparados hoy</div>
                <div class="sg-stat-value"><?php echo (int)$stats['prepared']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100" style="border-left-color:#2563EB;">
            <div class="card-body">
                <div class="sg-stat-label">Entregados hoy</div>
                <div class="sg-stat-value"><?php echo (int)$stats['delivered_today']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100" style="border-left-color:#16A34A;">
            <div class="card-body">
                <div class="sg-stat-label">Monto cobrado hoy</div>
                <div class="sg-stat-value"><?php echo money($totals ? $totals->collected : 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100" style="border-left-color:#EF4444;">
            <div class="card-body">
                <div class="sg-stat-label">Monto pendiente</div>
                <div class="sg-stat-value"><?php echo money($totals ? $totals->pending : 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100" style="border-left-color:#DC2626;">
            <div class="card-body">
                <div class="sg-stat-label">En ruta</div>
                <div class="sg-stat-value"><?php echo (int)$stats['in_route']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100" style="border-left-color:#6B7280;">
            <div class="card-body">
                <div class="sg-stat-label">No entregados hoy</div>
                <div class="sg-stat-value"><?php echo (int)$stats['not_delivered_today']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sg-card sg-stat h-100" style="border-left-color:#7C3AED;">
            <div class="card-body">
                <div class="sg-stat-label">Visitas web hoy</div>
                <div class="sg-stat-value"><?php echo (int)$visits->visitors; ?></div>
                <div class="small text-muted-2"><?php echo (int)$visits->pageviews; ?> vistas de página</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <?php if ($this->ion_auth->is_admin()): ?>
    <div class="col-lg-7">
        <div class="card sg-card h-100">
            <div class="sg-card-header">
                <h5><i class="bi bi-signpost-split me-2 text-danger"></i>Rutas de hoy</h5>
                <?php if (has_permission('rutas.crear')): ?>
                    <a href="<?php echo base_url('routes/create'); ?>" class="btn btn-sm btn-brand">Crear ruta</a>
                <?php endif; ?>
            </div>
            <div class="sg-card-body">
                <?php if (empty($todayRoutes)): ?>
                    <p class="text-muted-2 mb-0">No hay rutas planificadas para hoy.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Turno</th>
                                    <th>Bodega</th>
                                    <th>Mensajero</th>
                                    <th>Estado</th>
                                    <th>Pedidos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayRoutes as $route): ?>
                                    <tr class="cursor-pointer" onclick="window.location='<?php echo base_url('routes/detail/' . $route->id . '?back=' . urlencode('dashboard')); ?>'">
                                        <td><?php echo html_escape($route->shift_name); ?></td>
                                        <td><?php echo html_escape($route->warehouse_name); ?></td>
                                        <td><?php echo html_escape(trim($route->courier_first_name . ' ' . $route->courier_last_name)); ?></td>
                                        <td><?php echo route_status_badge($route->status); ?></td>
                                        <td><a href="<?php echo base_url('routes/detail/' . $route->id . '?back=' . urlencode('dashboard')); ?>" class="fw-semibold">Ver detalle</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-lg-5">
        <div class="card sg-card h-100">
            <div class="sg-card-header">
                <h5><i class="bi bi-clock-history me-2 text-danger"></i>Últimos pedidos</h5>
                <?php if (has_permission('pedidos.crear')): ?>
                    <a href="<?php echo base_url('orders/create'); ?>" class="btn btn-sm btn-brand">Nuevo pedido</a>
                <?php endif; ?>
            </div>
            <div class="sg-card-body p-0">
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted-2 mb-0 p-3">Sin pedidos recientes.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentOrders as $o): ?>
                            <li class="list-group-item d-flex align-items-center gap-3">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate"><?php echo html_escape($o->order_number); ?> · <?php echo html_escape($o->customer_name); ?></div>
                                    <div class="small text-muted-2"><?php echo fmt_datetime($o->created_at); ?></div>
                                </div>
                                <div class="text-end">
                                    <?php echo order_status_badge($o->status); ?>
                                    <div class="small fw-semibold mt-1"><?php echo money($o->total); ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($this->ion_auth->is_admin()): ?>
<div class="row g-3 mt-1">
    <div class="col-lg-7">
        <div class="card sg-card h-100">
            <div class="sg-card-header">
                <h5><i class="bi bi-graph-up me-2 text-danger"></i>Visitas web (últimos 7 días)</h5>
            </div>
            <div class="sg-card-body">
                <canvas id="visitsChart" height="170"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card sg-card h-100">
            <div class="sg-card-header">
                <h5><i class="bi bi-pie-chart me-2 text-danger"></i>Entregas de hoy</h5>
            </div>
            <div class="sg-card-body">
                <canvas id="deliveryChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Datos para el script assets/js/pages/dashboard.js (se ejecuta en el footer,
// después de cargar jQuery y Chart.js).
window.sgmsDashboard = {
    labels: <?php echo json_encode(array_column($visitsWeek, 'label')); ?>,
    visitors: <?php echo json_encode(array_column($visitsWeek, 'visitors')); ?>,
    pageviews: <?php echo json_encode(array_column($visitsWeek, 'pageviews')); ?>,
    delivered: <?php echo (int)$stats['delivered_today']; ?>,
    notDelivered: <?php echo (int)$stats['not_delivered_today']; ?>,
    inRoute: <?php echo max(0, (int)$stats['in_route']); ?>
};
</script>
<?php endif; ?>
