<?php defined('BASEPATH') OR exit('No direct script access allowed');
$isEdit = isset($user);
$formAction = $isEdit ? base_url('users/edit/' . $user->id) : base_url('users/create');
$selectedGroups = array();
if ($isEdit) {
    foreach ($user->groups as $g) {
        $selectedGroups[] = $g->id;
    }
}
?>
<div class="sg-page-header">
    <div>
        <h1><?php echo $isEdit ? 'Editar usuario' : 'Nuevo usuario'; ?></h1>
        <p><?php echo $isEdit ? 'Actualiza los datos y grupos de acceso' : 'Crea una cuenta para un integrante del equipo'; ?></p>
    </div>
</div>

<div class="card sg-card mx-auto" style="max-width:720px;">
    <div class="sg-card-body">
        <?php if (isset($message) && $message): ?>
            <div class="alert alert-danger" role="alert"><?php echo html_escape(strip_tags($message)); ?></div>
        <?php endif; ?>

        <?php echo form_open($formAction, array('id' => 'userForm')); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="first_name">Nombre *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo set_value('first_name', $isEdit ? $user->first_name : ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="last_name">Apellido *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo set_value('last_name', $isEdit ? $user->last_name : ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Correo electrónico *</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo set_value('email', $isEdit ? $user->email : ''); ?>" required <?php echo $isEdit ? '' : 'autocomplete="off"'; ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="username">Usuario *</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo set_value('username', $isEdit ? $user->username : ''); ?>" required pattern="[a-z0-9_.-]+" maxlength="60" autocomplete="username">
                    <div class="form-text">Solo minúsculas, números, puntos, guiones y guiones bajos.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Grupos (roles) *</label>
                    <div class="row g-2">
                        <?php foreach ($groups as $g): ?>
                            <div class="col-sm-6">
                                <div class="form-check border rounded p-2 ps-4">
                                    <input class="form-check-input" type="checkbox" name="groups[]" value="<?php echo $g->id; ?>" id="group_<?php echo $g->id; ?>" <?php echo in_array($g->id, $selectedGroups) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="group_<?php echo $g->id; ?>">
                                        <strong><?php echo html_escape($g->name); ?></strong>
                                        <div class="small text-muted-2"><?php echo html_escape($g->description); ?></div>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password"><?php echo $isEdit ? 'Nueva contraseña (opcional)' : 'Contraseña *'; ?></label>
                    <input type="password" class="form-control" id="password" name="password" <?php echo $isEdit ? '' : 'required'; ?> autocomplete="new-password" minlength="8">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="phone">Teléfono</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo set_value('phone', $isEdit ? $user->phone : ''); ?>">
                </div>
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                    <a href="<?php echo base_url('users'); ?>" class="btn btn-light">Cancelar</a>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>
