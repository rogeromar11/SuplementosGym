<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="section">
  <div class="container-x">
    <h1 class="section-title">Mi perfil</h1>
    <p class="section-subtitle">Administra tus datos de contacto y entrega.</p>

    <div class="account-nav mt-3">
      <a class="account-pill active" href="<?php echo base_url('cuenta'); ?>">Mi perfil</a>
      <a class="account-pill" href="<?php echo base_url('cuenta/pedidos'); ?>">Mis pedidos</a>
      <a class="account-pill" href="<?php echo base_url('salir'); ?>">Cerrar sesion</a>
    </div>

    <div class="checkout-layout">
      <div class="form-card">
        <h2 style="font-size:1.1rem; margin-top:0;">Datos personales</h2>
        <form method="post" action="<?php echo base_url('cuenta/actualizar'); ?>">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <div class="form-group">
            <label for="first_name">Nombre completo</label>
            <input class="form-control" type="text" id="first_name" name="first_name" value="<?php echo html_escape($user->first_name); ?>" required>
          </div>
          <div class="form-group">
            <label for="email">Correo electronico</label>
            <input class="form-control" type="email" id="email" name="email" value="<?php echo html_escape($user->email); ?>" required>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label for="phone">Numero celular</label>
              <input class="form-control" type="tel" id="phone" name="phone" value="<?php echo html_escape($user->phone); ?>" required>
            </div>
            <div class="form-group">
              <label for="phone2">Telefono secundario <span class="muted">(opcional)</span></label>
              <input class="form-control" type="tel" id="phone2" name="phone2" value="<?php echo html_escape($user->phone2); ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="delivery_zone">Zona de entrega</label>
            <input class="form-control" type="text" id="delivery_zone" name="delivery_zone" value="<?php echo html_escape($user->delivery_zone); ?>" required>
          </div>
          <div class="form-group">
            <label for="delivery_address">Direccion aproximada</label>
            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required><?php echo html_escape($user->delivery_address); ?></textarea>
          </div>
          <button type="submit" class="btn-brand">Guardar cambios</button>
        </form>
      </div>

      <aside class="form-card">
        <h2 style="font-size:1.1rem; margin-top:0;">Cambiar contrasena</h2>
        <form method="post" action="<?php echo base_url('cuenta/password'); ?>">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <div class="form-group">
            <label for="old">Contrasena actual</label>
            <input class="form-control" type="password" id="old" name="old" autocomplete="current-password" required>
          </div>
          <div class="form-group">
            <label for="new">Nueva contrasena</label>
            <input class="form-control" type="password" id="new" name="new" autocomplete="new-password" required>
          </div>
          <div class="form-group">
            <label for="new_confirm">Confirmar contrasena</label>
            <input class="form-control" type="password" id="new_confirm" name="new_confirm" autocomplete="new-password" required>
          </div>
          <button type="submit" class="btn-dark">Actualizar contrasena</button>
        </form>
      </aside>
    </div>
  </div>
</section>
