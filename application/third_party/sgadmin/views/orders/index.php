<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Pedidos</h1>
        <p>Registro y seguimiento de pedidos</p>
    </div>
    <div>
        <?php if (has_permission('pedidos.crear')): ?>
            <a href="<?php echo base_url('orders/create'); ?>" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Nuevo pedido</a>
        <?php endif; ?>
    </div>
</div>

<div class="card sg-card mb-3">
    <div class="sg-card-body">
        <form method="get" action="<?php echo base_url('orders'); ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="search">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Número, cliente, teléfono" value="<?php echo html_escape($filters['search']); ?>">
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
                <label class="form-label" for="date_from">Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="date_to">Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
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
            <?php if (!$this->ion_auth->in_group('vendedor') || $this->ion_auth->is_admin()): ?>
            <div class="col-md-2">
                <label class="form-label" for="seller_user_id">Vendedor</label>
                <select class="form-select" id="seller_user_id" name="seller_user_id">
                    <option value="">Todos</option>
                    <?php foreach ($sellers as $s): ?>
                        <option value="<?php echo $s->id; ?>" <?php echo ($filters['seller_user_id'] == $s->id) ? 'selected' : ''; ?>><?php echo html_escape(trim($s->first_name . ' ' . $s->last_name)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-2">
                <label class="form-label" for="origin">Origen</label>
                <select class="form-select" id="origin" name="origin">
                    <option value="">Todos</option>
                    <option value="web" <?php echo ($filters['origin'] === 'web') ? 'selected' : ''; ?>>Web</option>
                    <option value="admin" <?php echo ($filters['origin'] === 'admin') ? 'selected' : ''; ?>>Manual</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-brand flex-fill"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?php echo base_url('orders'); ?>" class="btn btn-light" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card sg-card">
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>" data-order="[[3, 'desc']]">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Saldo</th>
                        <th>Estado</th>
                        <th>Origen</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><a href="<?php echo base_url('orders/detail/' . $o->id . '?back=' . urlencode(current_path_query())); ?>" class="fw-semibold"><code><?php echo html_escape($o->order_number); ?></code></a></td>
                            <td><?php echo html_escape($o->customer_name); ?></td>
                            <td><?php echo html_escape($o->customer_phone); ?></td>
                            <td data-order="<?php echo $o->created_at; ?>"><?php echo fmt_datetime($o->created_at); ?></td>
                            <td><?php echo html_escape(trim($o->seller_first_name . ' ' . $o->seller_last_name)); ?></td>
                            <td class="text-end fw-semibold"><?php echo money($o->total); ?></td>
                            <td class="text-end <?php echo (float)$o->balance_amount > 0 ? 'text-danger fw-semibold' : 'text-muted-2'; ?>"><?php echo money($o->balance_amount); ?></td>
                            <td><?php echo order_status_badge($o->status); ?></td>
                            <td>
                                <?php if ($o->origin === 'web'): ?>
                                    <span class="badge text-bg-info"><i class="bi bi-globe me-1"></i>Web</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary"><i class="bi bi-person-workspace me-1"></i>Manual</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo base_url('orders/detail/' . $o->id . '?back=' . urlencode(current_path_query())); ?>" class="btn btn-sm btn-outline-brand" title="Ver detalle"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
