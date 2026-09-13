<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-head">
      <h1>Recuperar acceso</h1>
      <p>Escribe tu correo y te enviaremos las instrucciones.</p>
    </div>

    <?php if ( ! empty($auth_error)): ?>
      <div class="flash flash-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><?php echo html_escape($auth_error); ?></span></div>
    <?php endif; ?>

    <form method="post" action="<?php echo base_url('recuperar'); ?>">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <div class="form-group">
        <label for="identity">Correo electronico</label>
        <input class="form-control" type="email" id="identity" name="identity" value="<?php echo html_escape(set_value('identity')); ?>" autocomplete="email" required>
      </div>
      <button type="submit" class="btn-brand btn-block">Enviar instrucciones</button>
    </form>

    <div class="auth-links">
      <a href="<?php echo base_url('ingresar'); ?>">Volver a iniciar sesion</a>
    </div>
  </div>
</div>
