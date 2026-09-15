<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Permisos: <?php echo html_escape($group->name); ?></h1>
        <p><?php echo html_escape($group->description); ?></p>
    </div>
    <div>
        <a href="<?php echo base_url('roles'); ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Volver</a>
        <button type="button" class="btn btn-brand" id="savePermissions" data-url="<?php echo base_url('roles/save/' . $group->id); ?>"><i class="bi bi-check-lg me-1"></i>Guardar permisos</button>
    </div>
</div>

<div class="card sg-card">
    <div class="sg-card-body">
        <?php if ($group->name === 'admin'): ?>
            <div class="alert alert-warning">El grupo <strong>admin</strong> tiene acceso total al sistema por definición y no requiere asignación de permisos.</div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($permissions as $module => $perms): ?>
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-uppercase text-danger fw-bold mb-3" style="font-size:0.75rem;letter-spacing:0.08em;">
                            <i class="bi bi-folder2-open me-1"></i><?php echo html_escape($module); ?>
                        </h6>
                        <div class="d-grid gap-1">
                            <?php foreach ($perms as $p): ?>
                                <?php $checked = in_array($p->name, $current, true) ? 'checked' : ''; ?>
                                <label class="form-check d-flex align-items-center gap-2 mb-1 cursor-pointer">
                                    <input class="form-check-input perm-check" type="checkbox" name="permissions[]" value="<?php echo html_escape($p->name); ?>" <?php echo $checked; ?>>
                                    <span class="form-check-label small">
                                        <code><?php echo html_escape($p->name); ?></code>
                                        <div class="text-muted-2" style="font-size:0.78rem;"><?php echo html_escape($p->description); ?></div>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

