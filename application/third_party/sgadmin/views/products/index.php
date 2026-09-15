<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Productos</h1>
        <p>Catálogo de productos y precios</p>
    </div>
    <div>
        <?php if (has_permission('productos.crear')): ?>
            <a href="<?php echo base_url('products/create'); ?>" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Nuevo producto</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($lowStock)): ?>
<div class="alert alert-warning">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Pocos productos (existencias &le; <?php echo (int)$lowStockThreshold; ?>)</strong>
        <span class="badge text-bg-danger"><?php echo count($lowStock); ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0 bg-white">
            <thead><tr><th>SKU</th><th>Producto</th><th class="text-end">Existencias</th></tr></thead>
            <tbody>
                <?php foreach ($lowStock as $lp): ?>
                    <tr>
                        <td><code><?php echo html_escape($lp->sku); ?></code></td>
                        <td><?php echo html_escape($lp->name); ?></td>
                        <td class="text-end fw-bold text-danger"><?php echo (int)$lp->stock_qty; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card sg-card">
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>">
                <thead>
                    <tr>
                        <th>SKU</th>
						<th>Producto</th>
						<th>Detalle</th>
						<th class="text-end">Venta</th>
						<th class="text-end">Costo</th>
                        <th class="text-end">Existencias</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><code><?php echo html_escape($p->sku); ?></code></td>
							<td>
								<div class="d-flex align-items-center gap-2">
									<?php if (!empty($p->image)): ?>
										<img src="<?php echo base_url('assets/img/products/' . rawurlencode($p->image)); ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
									<?php else: ?>
										<div style="width:40px;height:40px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;"><i class="bi bi-image"></i></div>
									<?php endif; ?>
									<div>
										<div class="fw-semibold"><?php echo html_escape($p->display_name); ?></div>
										<small class="text-muted-2"><?php echo html_escape($p->product_type); ?></small>
									</div>
								</div>
							</td>
							<td class="text-muted-2" style="max-width:300px;">
								<?php echo html_escape(implode(' · ', array_filter(array($p->servings, $p->description), function ($value) { return trim((string)$value) !== ''; }))); ?>
							</td>
							<td class="text-end fw-semibold"><?php echo money($p->unit_price); ?></td>
							<td class="text-end"><?php echo money($p->cost_price); ?></td>
                            <td class="text-end">
                                <?php echo (int)$p->stock_qty; ?>
                                <?php if ((int)$p->is_active === 1 && (int)$p->stock_enabled === 1 && (int)$p->stock_qty <= (int)$lowStockThreshold): ?>
                                    <i class="bi bi-exclamation-triangle-fill text-danger" title="Existencias bajas"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$p->is_active === 1): ?>
                                    <span class="badge text-bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (has_permission('productos.editar')): ?>
                                    <a href="<?php echo base_url('products/edit/' . $p->id); ?>" class="btn btn-sm btn-outline-brand" title="Editar"><i class="bi bi-pencil"></i></a>
                                <?php endif; ?>
                                <?php if (has_permission('productos.eliminar')): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-product" data-url="<?php echo base_url('products/delete/' . $p->id); ?>" title="Eliminar"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
