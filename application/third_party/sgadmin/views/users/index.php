<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="sg-page-header">
    <div>
        <h1>Usuarios</h1>
        <p>Administra cuentas, grupos y estado de acceso</p>
    </div>
    <div>
        <a href="<?php echo base_url('roles'); ?>" class="btn btn-outline-brand me-2"><i class="bi bi-shield-check me-1"></i>Permisos</a>
        <?php if (has_permission('usuarios.crear')): ?>
            <a href="<?php echo base_url('users/create'); ?>" class="btn btn-brand"><i class="bi bi-person-plus me-1"></i>Nuevo usuario</a>
        <?php endif; ?>
    </div>
</div>

<div class="card sg-card">
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Grupos</th>
                        <th>Último acceso</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php
                            $groupBadges = '';
                            foreach (explode(',', $user->group_names) as $g) {
                                if ($g === '') continue;
                                $badge = ($g === 'admin') ? 'dark' : 'danger';
                                $groupBadges .= '<span class="badge text-bg-' . $badge . ' me-1">' . html_escape($g) . '</span>';
                            }
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="sg-avatar"><?php echo html_escape(mb_substr(trim($user->first_name . ' ' . $user->last_name), 0, 1)); ?></span>
                                    <div>
                                        <div class="fw-semibold"><?php echo html_escape(trim($user->first_name . ' ' . $user->last_name)); ?></div>
                                        <div class="small text-muted-2">@<?php echo html_escape($user->username); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo html_escape($user->email); ?></td>
                            <td><?php echo html_escape($user->phone); ?></td>
                            <td><?php echo $groupBadges; ?></td>
                            <td><?php echo $user->last_login ? date('d/m/Y H:i', $user->last_login) : 'Nunca'; ?></td>
                            <td>
                                <?php if ((int)$user->active === 1): ?>
                                    <span class="badge text-bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo base_url('users/edit/' . $user->id); ?>" class="btn btn-sm btn-outline-brand" title="Editar"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-user" data-url="<?php echo base_url('users/toggle_active/' . $user->id); ?>" data-active="<?php echo (int)$user->active; ?>" title="<?php echo ((int)$user->active === 1) ? 'Desactivar' : 'Activar'; ?>">
                                    <i class="bi <?php echo ((int)$user->active === 1) ? 'bi-pause-circle' : 'bi-play-circle'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
