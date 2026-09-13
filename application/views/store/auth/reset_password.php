<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-head">
      <h1>Restablecer acceso</h1>
      <p>Define una contrasena nueva para tu cuenta.</p>
    </div>

    <?php if ( ! empty($auth_error)): ?>
      <div class="flash flash-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><?php echo html_escape($auth_error); ?></span></div>
    <?php endif; ?>

    <form method="post" action="<?php echo base_url('restablecer/' . html_escape($code)); ?>">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <div class="form-group">
        <label for="new">Nueva contrasena</label>
        <input class="form-control" type="password" id="new" name="new" autocomplete="new-password" required>
      </div>
      <div class="form-group">
        <label for="new_confirm">Confirmar contrasena</label>
        <input class="form-control" type="password" id="new_confirm" name="new_confirm" autocomplete="new-password" required>
      </div>
      <button type="submit" class="btn-brand btn-block">Actualizar contrasena</button>
    </form>

    <div class="auth-links">
      <a href="<?php echo base_url('ingresar'); ?>">Volver a iniciar sesion</a>
    </div>
  </div>
</div>
