<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Rutas</h1>
        <p>Planificacion diaria de rutas de entrega</p>
    </div>
    <div>
        <a href="<?php echo base_url('routes?date=' . date('Y-m-d')); ?>" class="btn btn-light me-1"><i class="bi bi-calendar-day"></i></a>
        <?php if (has_permission('rutas.crear')): ?>
            <a href="<?php echo base_url('routes/create'); ?>" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Nueva ruta</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($staleRoutes)): ?>
<div class="alert alert-warning">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Rutas de días anteriores sin finalizar</strong>
        <span class="badge text-bg-danger"><?php echo count($staleRoutes); ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0 bg-white">
            <thead><tr><th>Ruta</th><th>Fecha</th><th>Estado</th><th>Mensajero</th><th class="text-end">Pedidos sin resolver</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($staleRoutes as $sr): ?>
                    <tr>
                        <td><a href="<?php echo base_url('routes/detail/' . $sr->id . '?back=' . urlencode(current_path_query())); ?>" class="fw-semibold"><code><?php echo html_escape($sr->route_number); ?></code></a></td>
                        <td><?php echo fmt_date($sr->route_date); ?></td>
                        <td><?php echo route_status_badge($sr->status); ?></td>
                        <td><?php echo html_escape($sr->courier_name ?: '—'); ?></td>
                        <td class="text-end"><?php echo (int)$sr->pending_orders; ?></td>
                        <td class="text-end"><a href="<?php echo base_url('routes/detail/' . $sr->id . '?back=' . urlencode(current_path_query())); ?>" class="btn btn-sm btn-outline-brand">Abrir</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card sg-card mb-3">
    <div class="sg-card-body">
        <form method="get" action="<?php echo base_url('routes'); ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="date">Fecha</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo html_escape($date); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="shift_id">Turno</label>
                <select class="form-select" id="shift_id" name="shift_id">
                    <option value="">Todos</option>
                    <?php foreach ($shifts as $s): ?>
                        <option value="<?php echo $s->id; ?>" <?php echo ($this->input->get('shift_id') == $s->id) ? 'selected' : ''; ?>><?php echo html_escape($s->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="warehouse_id">Bodega</label>
                <select class="form-select" id="warehouse_id" name="warehouse_id">
                    <option value="">Todas</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?php echo $w->id; ?>" <?php echo ($this->input->get('warehouse_id') == $w->id) ? 'selected' : ''; ?>><?php echo html_escape($w->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="courier_user_id">Mensajero</label>
                <select class="form-select" id="courier_user_id" name="courier_user_id">
                    <option value="">Todos</option>
                    <?php foreach ($couriers as $cu): ?>
                        <option value="<?php echo $cu->id; ?>" <?php echo ($this->input->get('courier_user_id') == $cu->id) ? 'selected' : ''; ?>><?php echo html_escape(trim($cu->first_name . ' ' . $cu->last_name)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="status">Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="borrador" <?php echo ($this->input->get('status') === 'borrador') ? 'selected' : ''; ?>>Borrador</option>
                    <option value="planificada" <?php echo ($this->input->get('status') === 'planificada') ? 'selected' : ''; ?>>Planificada</option>
                    <option value="en_progreso" <?php echo ($this->input->get('status') === 'en_progreso') ? 'selected' : ''; ?>>En progreso</option>
                    <option value="finalizada" <?php echo ($this->input->get('status') === 'finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                    <option value="cancelada" <?php echo ($this->input->get('status') === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-brand"><i class="bi bi-funnel"></i></button>
                <a href="<?php echo base_url('routes'); ?>" class="btn btn-light"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($routes)): ?>
        <div class="col-12">
            <div class="card sg-card">
                <div class="sg-card-body text-center py-5">
                    <i class="bi bi-signpost fs-1 text-muted-2 d-block mb-2"></i>
                    <p class="text-muted-2 mb-0">No hay rutas para esta fecha.</p>
                    <?php if (has_permission('rutas.crear')): ?>
                        <a href="<?php echo base_url('routes/create'); ?>" class="btn btn-brand mt-3">Crear la primera ruta</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($routes as $r): ?>
        <?php $summary = $this->Route_model->summary($r->id); ?>
        <div class="col-md-6 col-xl-4">
            <div class="card sg-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <a href="<?php echo base_url('routes/detail/' . $r->id); ?>" class="fw-bold"><code><?php echo html_escape($r->route_number); ?></code></a>
                            <div class="small text-muted-2"><?php echo html_escape($r->shift_name); ?> · <?php echo fmt_date($r->route_date); ?></div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php echo route_status_badge($r->status); ?>
                            <?php if ((int)$summary->total_orders === 0 && in_array($r->status, array('borrador', 'planificada'), true) && has_permission('rutas.editar')): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-route" data-url="<?php echo base_url('routes/delete/' . $r->id); ?>" title="Eliminar ruta"><i class="bi bi-trash"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="small mb-1"><i class="bi bi-buildings me-1"></i><?php echo html_escape($r->warehouse_name ?: '—'); ?></div>
                    <div class="small mb-2"><i class="bi bi-person-badge me-1"></i><?php echo html_escape(trim($r->courier_first_name . ' ' . $r->courier_last_name)); ?></div>
                    <?php if ($r->notes): ?>
                        <div class="small mb-2 text-muted-2"><i class="bi bi-chat-left-text me-1"></i><?php echo html_escape(mb_substr($r->notes, 0, 90)); ?></div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between text-center border-top pt-2">
                        <div><div class="fw-bold"><?php echo (int)$summary->total_orders; ?></div><div class="small text-muted-2">Pedidos</div></div>
                        <div><div class="fw-bold text-success"><?php echo (int)$summary->delivered; ?></div><div class="small text-muted-2">Entregados</div></div>
                        <div><div class="fw-bold text-danger"><?php echo (int)$summary->not_delivered; ?></div><div class="small text-muted-2">No entregados</div></div>
                        <div><div class="fw-bold"><?php echo money($summary->collected_amount); ?></div><div class="small text-muted-2">Cobrado</div></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
