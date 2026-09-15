<?php defined('BASEPATH') OR exit('No direct script access allowed');
$totals = array('quantity' => 0, 'amount' => 0);
foreach ($rows as $row) {
    $totals['quantity'] += (int)$row->quantity_sold;
    $totals['amount'] += (float)$row->total_sold;
}
?>
<div class="sg-page-header">
    <div>
        <h1>Reporte de productos</h1>
        <p>Productos vendidos por fecha y existencias actuales</p>
    </div>
    <?php if (has_permission('reportes.exportar')): ?>
        <a href="<?php echo base_url('reports/export_products_excel?' . http_build_query($filters)); ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
    <?php endif; ?>
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
        <form method="get" action="<?php echo base_url('reports/products'); ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="date_from">Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="date_to">Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="warehouse_id">Bodega</label>
                <select class="form-select" id="warehouse_id" name="warehouse_id">
                    <option value="">Todas</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?php echo $w->id; ?>" <?php echo ($filters['warehouse_id'] == $w->id) ? 'selected' : ''; ?>><?php echo html_escape($w->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><button type="submit" class="btn btn-brand"><i class="bi bi-funnel me-1"></i>Filtrar</button></div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card sg-card"><div class="card-body"><div class="sg-stat-label">Unidades vendidas</div><div class="sg-stat-value fs-4"><?php echo $totals['quantity']; ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card sg-card" style="border-left-color:#16A34A;"><div class="card-body"><div class="sg-stat-label">Venta total</div><div class="sg-stat-value fs-4 text-success"><?php echo money($totals['amount']); ?></div></div></div></div>
</div>

<div class="card sg-card">
    <div class="sg-card-header"><h5>Ventas por producto y fecha</h5></div>
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table data-table-no-search" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>">
                <thead><tr><th>Fecha</th><th>SKU</th><th>Producto</th><th class="text-end">Vendidos</th><th class="text-end">Venta</th><th class="text-end">Existencias actuales</th></tr></thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?php echo fmt_date($row->sale_date); ?></td>
                            <td><code><?php echo html_escape($row->sku); ?></code></td>
                            <td class="fw-semibold"><?php echo html_escape($row->item_name); ?></td>
                            <td class="text-end"><?php echo (int)$row->quantity_sold; ?></td>
                            <td class="text-end fw-semibold"><?php echo money($row->total_sold); ?></td>
                            <td class="text-end <?php echo (int)$row->stock_qty <= 0 ? 'text-danger fw-semibold' : ''; ?>"><?php echo (int)$row->stock_qty; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($rows)): ?><p class="text-muted-2 mb-0">No hay productos vendidos para los filtros seleccionados.</p><?php endif; ?>
    </div>
</div>
