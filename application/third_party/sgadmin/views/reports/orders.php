<?php defined('BASEPATH') OR exit('No direct script access allowed');
$totals = array('orders' => 0, 'delivered' => 0, 'not_delivered' => 0, 'sold' => 0, 'collected' => 0, 'balance' => 0);
foreach ($rows as $row) {
    $totals['orders']++;
    if ($row->status === 'entregado') { $totals['delivered']++; }
    if ($row->status === 'no_entregado') { $totals['not_delivered']++; }
    if ($row->status !== 'cancelado') {
        $totals['sold'] += (float)$row->total;
        $totals['collected'] += (float)$row->paid_amount;
        $totals['balance'] += (float)$row->balance_amount;
    }
}
?>
<div class="sg-page-header">
    <div>
        <h1>Reporte de pedidos</h1>
        <p>Pedidos web y manuales del periodo</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (has_permission('reportes.exportar')): ?>
            <a href="<?php echo base_url('reports/export_orders_pdf?' . http_build_query($filters)); ?>" class="btn btn-outline-dark"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="<?php echo base_url('reports/export_orders_excel?' . http_build_query($filters)); ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <?php endif; ?>
    </div>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'general') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports'); ?>">General</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'orders') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/orders'); ?>">Pedidos</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'products') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/products'); ?>">Productos</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'deposits') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/deposits'); ?>">Depósitos</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'sellers') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/sellers'); ?>">Vendedores</a></li>
    <li class="nav-item"><a class="nav-link <?php echo ($activeTab === 'couriers') ? 'active bg-black text-white' : ''; ?>" href="<?php echo base_url('reports/couriers'); ?>">Mensajeros</a></li>
</ul>

<div class="card sg-card mb-3">
    <div class="sg-card-body">
        <form method="get" action="<?php echo base_url('reports/orders'); ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="search">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Número, cliente o teléfono" value="<?php echo html_escape($filters['search']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="status">Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <?php foreach ($statuses as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($filters['status'] === $key) ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="origin">Origen</label>
                <select class="form-select" id="origin" name="origin">
                    <option value="">Todos</option>
                    <option value="web" <?php echo ($filters['origin'] === 'web') ? 'selected' : ''; ?>>Web</option>
                    <option value="admin" <?php echo ($filters['origin'] === 'admin') ? 'selected' : ''; ?>>Manual</option>
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
                <label class="form-label" for="seller_user_id">Vendedor</label>
                <select class="form-select" id="seller_user_id" name="seller_user_id">
                    <option value="">Todos</option>
                    <?php foreach ($sellers as $s): ?>
                        <option value="<?php echo $s->id; ?>" <?php echo ($filters['seller_user_id'] == $s->id) ? 'selected' : ''; ?>><?php echo html_escape(trim($s->first_name . ' ' . $s->last_name)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="payment_method_id">Forma de pago</label>
                <select class="form-select" id="payment_method_id" name="payment_method_id">
                    <option value="">Todas</option>
                    <?php foreach ($paymentMethods as $pm): ?>
                        <option value="<?php echo $pm->id; ?>" <?php echo ($filters['payment_method_id'] == $pm->id) ? 'selected' : ''; ?>><?php echo html_escape($pm->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="date_from">Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="date_to">Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-brand flex-fill"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?php echo base_url('reports/orders'); ?>" class="btn btn-light" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-2"><div class="card sg-card"><div class="card-body"><div class="sg-stat-label">Pedidos</div><div class="sg-stat-value fs-4"><?php echo (int)$totals['orders']; ?></div></div></div></div>
    <div class="col-6 col-md-2"><div class="card sg-card" style="border-left-color:#16A34A;"><div class="card-body"><div class="sg-stat-label">Entregados</div><div class="sg-stat-value fs-4 text-success"><?php echo (int)$totals['delivered']; ?></div></div></div></div>
    <div class="col-6 col-md-2"><div class="card sg-card" style="border-left-color:#DC2626;"><div class="card-body"><div class="sg-stat-label">No entregados</div><div class="sg-stat-value fs-4 text-danger"><?php echo (int)$totals['not_delivered']; ?></div></div></div></div>
    <div class="col-6 col-md-2"><div class="card sg-card" style="border-left-color:#2563EB;"><div class="card-body"><div class="sg-stat-label">Total vendido</div><div class="sg-stat-value fs-4"><?php echo money($totals['sold']); ?></div></div></div></div>
    <div class="col-6 col-md-2"><div class="card sg-card" style="border-left-color:#16A34A;"><div class="card-body"><div class="sg-stat-label">Cobrado</div><div class="sg-stat-value fs-4 text-success"><?php echo money($totals['collected']); ?></div></div></div></div>
    <div class="col-6 col-md-2"><div class="card sg-card" style="border-left-color:#F59E0B;"><div class="card-body"><div class="sg-stat-label">Saldo pendiente</div><div class="sg-stat-value fs-4 text-danger"><?php echo money($totals['balance']); ?></div></div></div></div>
</div>

<div class="card sg-card">
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle data-table" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>" data-order="[[1, 'desc']]">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Fecha</th>
                        <th>Origen</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Zona</th>
                        <th>Bodega</th>
                        <th>Vendedor</th>
                        <th>Forma de pago</th>
                        <th>Estado</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Pagado</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><a href="<?php echo base_url('orders/detail/' . $row->id); ?>" class="fw-semibold"><code><?php echo html_escape($row->order_number); ?></code></a></td>
                            <td data-order="<?php echo $row->created_at; ?>"><?php echo fmt_datetime($row->created_at); ?></td>
                            <td>
                                <?php if ($row->origin === 'web'): ?><span class="badge text-bg-info">Web</span>
                                <?php else: ?><span class="badge text-bg-secondary">Manual</span><?php endif; ?>
                            </td>
                            <td><?php echo html_escape($row->customer_name); ?></td>
                            <td><?php echo html_escape($row->customer_phone); ?></td>
                            <td><?php echo html_escape($row->delivery_zone ?: '—'); ?></td>
                            <td><?php echo html_escape($row->warehouse_name ?: '—'); ?></td>
                            <td><?php echo html_escape(trim((string)$row->seller_name) ?: '—'); ?></td>
                            <td><?php echo html_escape($row->payment_name ?: '—'); ?></td>
                            <td><?php echo order_status_badge($row->status); ?></td>
                            <td class="text-end fw-semibold"><?php echo money($row->total); ?></td>
                            <td class="text-end text-success"><?php echo money($row->paid_amount); ?></td>
                            <td class="text-end <?php echo (float)$row->balance_amount > 0 ? 'text-danger fw-semibold' : 'text-muted-2'; ?>"><?php echo money($row->balance_amount); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($rows)): ?><p class="text-muted-2 mb-0">Sin resultados para los filtros.</p><?php endif; ?>
    </div>
</div>
