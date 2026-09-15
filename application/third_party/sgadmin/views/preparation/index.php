<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Preparación de pedidos</h1>
        <p>Cola de preparación en bodega</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card sg-card sg-stat">
            <div class="card-body">
                <div class="sg-stat-label">Pendientes de preparar</div>
                <div class="sg-stat-value"><?php echo (int)$counters['pending']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card sg-card sg-stat" style="border-left-color:#F59E0B;">
            <div class="card-body">
                <div class="sg-stat-label">En preparación</div>
                <div class="sg-stat-value"><?php echo (int)$counters['preparing']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card sg-card sg-stat" style="border-left-color:#16A34A;">
            <div class="card-body">
                <div class="sg-stat-label">Preparados hoy</div>
                <div class="sg-stat-value"><?php echo (int)$counters['prepared_today']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card sg-card sg-stat" style="border-left-color:#DC2626;">
            <div class="card-body">
                <div class="sg-stat-label">Atrasados (días anteriores)</div>
                <div class="sg-stat-value <?php echo (int)$counters['overdue'] > 0 ? 'text-danger' : ''; ?>"><?php echo (int)$counters['overdue']; ?></div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($overdueOrders)): ?>
<div class="alert alert-warning">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Pedidos pendientes de días anteriores</strong>
        <span class="badge text-bg-danger"><?php echo (int)$counters['overdue']; ?></span>
    </div>
    <div class="table-responsive" style="max-height:260px;">
        <table class="table table-sm table-hover align-middle mb-0 bg-white">
            <thead><tr><th>Pedido</th><th>Cliente</th><th>Bodega</th><th>Registrado</th><th class="text-end">Atraso</th></tr></thead>
            <tbody>
                <?php foreach ($overdueOrders as $o): ?>
                    <?php $days = (int)floor((time() - strtotime($o->created_at)) / 86400); ?>
                    <tr>
                        <td><a href="<?php echo base_url('orders/detail/' . $o->id . '?back=' . urlencode(current_path_query())); ?>" class="fw-semibold"><code><?php echo html_escape($o->order_number); ?></code></a></td>
                        <td><?php echo html_escape($o->customer_name); ?></td>
                        <td><?php echo html_escape($o->warehouse_name ?: '—'); ?></td>
                        <td><?php echo fmt_datetime($o->created_at); ?></td>
                        <td class="text-end"><span class="badge text-bg-danger"><?php echo $days > 0 ? $days . ' día(s)' : 'Hoy'; ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ((int)$counters['overdue'] > count($overdueOrders)): ?>
        <div class="small text-muted-2 mt-1">Mostrando los <?php echo count($overdueOrders); ?> más antiguos de <?php echo (int)$counters['overdue']; ?>.</div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="card sg-card mb-3">
    <div class="sg-card-body">
        <form method="get" action="<?php echo base_url('preparation'); ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="search">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Número o cliente" value="<?php echo html_escape($this->input->get('search')); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="warehouse_id">Bodega</label>
                <select class="form-select" id="warehouse_id" name="warehouse_id">
                    <option value="">Todas</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?php echo $w->id; ?>" <?php echo ($this->input->get('warehouse_id') == $w->id) ? 'selected' : ''; ?>><?php echo html_escape($w->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="date">Fecha</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo html_escape($date); ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-brand flex-fill"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?php echo base_url('preparation'); ?>" class="btn btn-light"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($queue)): ?>
        <div class="col-12">
            <div class="card sg-card">
                <div class="sg-card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted-2 d-block mb-2"></i>
                    <p class="text-muted-2 mb-0">No hay pedidos en la cola de preparación.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($queue as $o): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card sg-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <a href="<?php echo base_url('orders/detail/' . $o->id . '?back=' . urlencode(current_path_query())); ?>" class="fw-bold"><code><?php echo html_escape($o->order_number); ?></code></a>
                            <div class="fw-semibold"><?php echo html_escape($o->customer_name); ?></div>
                            <div class="small text-muted-2"><?php echo fmt_datetime($o->created_at); ?></div>
                        </div>
                        <?php echo order_status_badge($o->status); ?>
                        <?php if (substr($o->created_at, 0, 10) < date('Y-m-d')): ?>
                            <span class="badge text-bg-danger">Atrasado</span>
                        <?php endif; ?>
                    </div>
                    <div class="small mb-1"><i class="bi bi-geo-alt me-1"></i><?php echo html_escape(mb_substr($o->delivery_address, 0, 60)); ?></div>
                    <div class="small mb-1"><i class="bi bi-buildings me-1"></i><?php echo html_escape($o->warehouse_name ?: '—'); ?></div>
                    <div class="d-flex justify-content-between align-items-center my-2">
                        <span class="fw-bold"><?php echo money($o->total); ?></span>
                        <span class="small text-muted-2"><?php echo html_escape($o->customer_phone); ?></span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo base_url('orders/print_view/' . $o->id); ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Comprobante"><i class="bi bi-printer"></i></a>
                        <?php if ($o->status === 'registrado' || $o->status === 'pendiente_preparacion'): ?>
                            <button type="button" class="btn btn-sm btn-brand btn-prep-action" data-url="<?php echo base_url('preparation/start_prep/' . $o->id); ?>" data-action="start">Iniciar preparación</button>
                        <?php elseif ($o->status === 'en_preparacion'): ?>
                            <button type="button" class="btn btn-sm btn-success btn-prep-action" data-url="<?php echo base_url('preparation/mark_prepared/' . $o->id); ?>" data-action="prepared">Marcar preparado</button>
                            <button type="button" class="btn btn-sm btn-outline-warning btn-return-pending" data-url="<?php echo base_url('preparation/return_to_pending/' . $o->id); ?>">Devolver</button>
                        <?php elseif ($o->status === 'preparado'): ?>
                            <span class="badge text-bg-success align-self-center">Listo para ruta</span>
                        <?php endif; ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-add-observation" data-url="<?php echo base_url('preparation/add_observation/' . $o->id); ?>"><i class="bi bi-chat-left-text"></i></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
