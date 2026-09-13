<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-head">
      <h1>Iniciar sesion</h1>
      <p>Ingresa para completar tu compra y ver tus pedidos.</p>
    </div>

    <?php if ( ! empty($auth_error)): ?>
      <div class="flash flash-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><?php echo html_escape($auth_error); ?></span></div>
    <?php endif; ?>

    <form method="post" action="<?php echo base_url('ingresar'); ?>">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="return" value="<?php echo html_escape(isset($return) ? $return : ''); ?>">
      <div class="form-group">
        <label for="identity">Correo electronico</label>
        <input class="form-control" type="email" id="identity" name="identity" value="<?php echo html_escape(set_value('identity')); ?>" autocomplete="email" required>
      </div>
      <div class="form-group">
        <label for="password">Contrasena</label>
        <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" id="remember" name="remember" value="1">
        <label for="remember">Recordarme</label>
      </div>
      <button type="submit" class="btn-brand btn-block">Iniciar sesion</button>
    </form>

    <div class="auth-links">
      <a href="<?php echo base_url('recuperar'); ?>">¿Olvidaste tu contrasena?</a><br>
      <span>¿No tienes cuenta? <a href="<?php echo base_url('registro'); ?>">Crear cuenta</a></span>
    </div>
  </div>
</div>
