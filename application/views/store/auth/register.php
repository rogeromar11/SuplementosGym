<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="auth-wrap">
  <div class="auth-card auth-card-wide">
    <div class="auth-head">
      <div class="bicon"><i class="bi bi-person-plus" aria-hidden="true"></i></div>
      <h1>Crear cuenta</h1>
      <p>Completa tus datos para comprar en SG Tienda.</p>
    </div>

    <?php if ( ! empty($auth_error)): ?>
      <div class="flash flash-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><?php echo html_escape($auth_error); ?></span></div>
    <?php endif; ?>

    <form method="post" action="<?php echo base_url('registro'); ?>">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <div class="form-grid-2">
        <div class="form-group">
          <label for="first_name">Nombre completo</label>
          <input class="form-control" type="text" id="first_name" name="first_name" value="<?php echo html_escape(set_value('first_name')); ?>" required>
        </div>
        <div class="form-group">
          <label for="email">Correo electronico</label>
          <input class="form-control" type="email" id="email" name="email" value="<?php echo html_escape(set_value('email')); ?>" autocomplete="email" required>
        </div>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label for="phone">Numero celular</label>
          <input class="form-control" type="tel" id="phone" name="phone" value="<?php echo html_escape(set_value('phone')); ?>" required>
        </div>
        <div class="form-group">
          <label for="phone2">Telefono secundario <span class="muted">(opcional)</span></label>
          <input class="form-control" type="tel" id="phone2" name="phone2" value="<?php echo html_escape(set_value('phone2')); ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="delivery_zone">Zona de entrega</label>
        <input class="form-control" type="text" id="delivery_zone" name="delivery_zone" value="<?php echo html_escape(set_value('delivery_zone')); ?>" required>
      </div>
      <div class="form-group">
        <label for="delivery_address">Direccion aproximada</label>
        <textarea class="form-control" id="delivery_address" name="delivery_address" rows="2" required><?php echo html_escape(set_value('delivery_address')); ?></textarea>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label for="password">Contrasena</label>
          <input class="form-control" type="password" id="password" name="password" autocomplete="new-password" required>
          <div class="form-text">Minimo <?php echo (int) $this->config->item('min_password_length', 'ion_auth'); ?> caracteres.</div>
        </div>
        <div class="form-group">
          <label for="password_confirm">Confirmar contrasena</label>
          <input class="form-control" type="password" id="password_confirm" name="password_confirm" autocomplete="new-password" required>
        </div>
      </div>
      <button type="submit" class="btn-brand btn-block">Crear cuenta</button>
    </form>

    <div class="auth-links">
      <span>¿Ya tienes cuenta? <a href="<?php echo base_url('ingresar'); ?>">Iniciar sesion</a></span>
    </div>
  </div>
</div>
