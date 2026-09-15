<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Auditoría</h1>
        <p>Bitacora de cambios y eventos del sistema</p>
    </div>
</div>

<div class="card sg-card mb-3">
    <div class="sg-card-body">
        <form method="get" action="<?php echo base_url('audit'); ?>" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label" for="date_from">Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo html_escape($filters['date_from']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="date_to">Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo html_escape($filters['date_to']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="module">Modulo</label>
                <select class="form-select" id="module" name="module">
                    <option value="">Todos</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?php echo html_escape($m->module); ?>" <?php echo ($filters['module'] === $m->module) ? 'selected' : ''; ?>><?php echo html_escape($m->module); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="action">Acción</label>
                <input type="text" class="form-control" id="action" name="action" placeholder="Ej: order.create" value="<?php echo html_escape($filters['action']); ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-brand"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?php echo base_url('audit'); ?>" class="btn btn-light"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card sg-card">
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Modulo</th>
                        <th>Acción</th>
                        <th>Tabla / Registro</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo fmt_datetime($log->created_at); ?></td>
                            <td><?php echo html_escape($log->user_name ?: 'Sistema'); ?></td>
                            <td><span class="badge text-bg-dark"><?php echo html_escape($log->module ?: '—'); ?></span></td>
                            <td><code><?php echo html_escape($log->action); ?></code></td>
                            <td><?php echo html_escape($log->table_name ?: '—'); ?> / <?php echo html_escape($log->record_id ?: '—'); ?></td>
                            <td class="small text-muted-2" style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo html_escape($log->data ?: ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($logs)): ?><p class="text-muted-2 mb-0">Sin registros.</p><?php endif; ?>
    </div>
</div>
