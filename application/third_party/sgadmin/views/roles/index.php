<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Grupos y permisos</h1>
        <p>Administra los roles y su matriz de permisos</p>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($groups as $group): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card sg-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="sg-avatar"><?php echo html_escape(mb_strtoupper(mb_substr($group->name, 0, 1))); ?></span>
                        <div>
                            <h5 class="mb-0"><?php echo html_escape($group->name); ?></h5>
                            <div class="small text-muted-2"><?php echo html_escape($group->description); ?></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="badge text-bg-dark"><?php echo (int)$group->permission_count; ?> permisos</span>
                    </div>
                    <?php if ($group->name !== 'admin'): ?>
                        <a href="<?php echo base_url('roles/edit/' . $group->id); ?>" class="btn btn-sm btn-outline-brand"><i class="bi bi-shield-check me-1"></i>Editar permisos</a>
                    <?php else: ?>
                        <span class="badge text-bg-danger"><i class="bi bi-infinity me-1"></i>Acceso total</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
