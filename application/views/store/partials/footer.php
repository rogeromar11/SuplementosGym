<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$wa = isset($whatsapp_url) ? $whatsapp_url : '';
$email = store_setting('contact_email', '');
$hours = store_setting('business_hours', '');
?>
</main>

<footer class="site-footer">
  <div class="container-x footer-grid">
    <div class="f-brand">
      <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="SG Tienda" width="110" height="44">
      <p>Suplementos originales para gente que entrena en serio. <?php echo html_escape($current_country->name); ?>.</p>
      <?php $socials = store_social_links(); ?>
      <div class="socials">
        <?php if ( ! empty($socials)): ?>
          <?php foreach ($socials as $s): ?>
            <a href="<?php echo html_escape($s['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo html_escape($s['label']); ?>"><i class="bi <?php echo html_escape($s['icon']); ?>" aria-hidden="true"></i></a>
          <?php endforeach; ?>
        <?php else: ?>
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
          <a href="#" aria-label="TikTok"><i class="bi bi-tiktok" aria-hidden="true"></i></a>
        <?php endif; ?>
      </div>
    </div>

    <nav class="f-col" aria-label="Tienda">
      <h3>Tienda</h3>
      <ul>
        <li><a href="<?php echo base_url('productos'); ?>">Productos</a></li>
        <li><a href="<?php echo base_url('guia'); ?>">Guía de suplementos</a></li>
        <li><a href="<?php echo base_url('calculadora'); ?>">Calculadora de macros</a></li>
        <li><a href="<?php echo base_url('formas-de-pago'); ?>">Formas de pago</a></li>
      </ul>
    </nav>

    <nav class="f-col" aria-label="Ayuda">
      <h3>Ayuda</h3>
      <ul>
        <li><a href="<?php echo base_url('contacto'); ?>">Envíos y entregas</a></li>
        <li><a href="<?php echo base_url('contacto'); ?>">Cambios y devoluciones</a></li>
        <li><a href="<?php echo base_url('contacto'); ?>">Preguntas frecuentes</a></li>
      </ul>
    </nav>

    <div class="f-col f-contact">
      <h3>Contacto</h3>
      <ul>
        <?php if ( ! empty($hours)): ?>
          <li><i class="bi bi-clock" aria-hidden="true"></i><span><?php echo html_escape($hours); ?></span></li>
        <?php endif; ?>
        <?php if ( ! empty($email)): ?>
          <li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:<?php echo html_escape($email); ?>"><?php echo html_escape($email); ?></a></li>
        <?php endif; ?>
        <?php if ( ! empty($wa)): ?>
          <li><i class="bi bi-whatsapp" aria-hidden="true"></i><a href="<?php echo html_escape($wa); ?>" target="_blank" rel="noopener">Escríbenos por WhatsApp</a></li>
        <?php endif; ?>
        <?php if (empty($hours) && empty($email) && empty($wa)): ?>
          <li><i class="bi bi-info-circle" aria-hidden="true"></i><span>Datos de contacto por definir.</span></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <div class="container-x footer-bottom">
    <p>Precios y disponibilidad según el país seleccionado (<?php echo html_escape($current_country->name); ?>).</p>
    <p>&copy; <?php echo date('Y'); ?> SG Tienda &middot; Costa Rica &amp; El Salvador</p>
  </div>
</footer>

<?php $fab_href = ! empty($wa) ? $wa : base_url('contacto'); ?>
<a class="fab-wa" href="<?php echo html_escape($fab_href); ?>"<?php echo ! empty($wa) ? ' target="_blank" rel="noopener"' : ''; ?> aria-label="Escribir por WhatsApp">
  <i class="bi bi-whatsapp" aria-hidden="true"></i>
</a>

<div class="store-modal" data-country-modal role="dialog" aria-modal="true" aria-labelledby="countryModalTitle">
  <div class="store-modal-card">
    <h3 id="countryModalTitle">Cambiar de país</h3>
    <p>Al cambiar de país, los productos actuales del carrito podrian dejar de estar disponibles. ¿Deseas continuar?</p>
    <div class="mt-3" style="display:flex; gap:.6rem; justify-content:flex-end;">
      <button type="button" class="btn-ghost" data-country-cancel>Cancelar</button>
      <button type="button" class="btn-brand" data-country-confirm>Continuar</button>
    </div>
  </div>
</div>

<div class="store-modal" data-flavor-modal role="dialog" aria-modal="true" aria-labelledby="flavorModalTitle">
  <div class="store-modal-card">
    <h3 id="flavorModalTitle">Elegir sabor</h3>
    <p class="muted" data-flavor-product style="margin-top:-.2rem;"></p>
    <div class="flavor-options" data-flavor-options></div>
    <div class="mt-3" style="display:flex; gap:.6rem; justify-content:flex-end;">
      <button type="button" class="btn-ghost" data-flavor-cancel>Cancelar</button>
      <button type="button" class="btn-brand" data-flavor-confirm>Agregar al carrito</button>
    </div>
  </div>
</div>

<script>
window.STORE_CONFIG = {
  base: <?php echo json_encode(base_url()); ?>,
  csrfName: <?php echo json_encode($this->security->get_csrf_token_name()); ?>,
  csrfHash: <?php echo json_encode($this->security->get_csrf_hash()); ?>
};
</script>
<script src="<?php echo base_url('assets/vendor/gsap/gsap.min.js'); ?>" defer></script>
<script src="<?php echo base_url('assets/vendor/gsap/ScrollTrigger.min.js'); ?>" defer></script>
<script src="<?php echo base_url('assets/js/store.js') . '?v=' . @filemtime(FCPATH . 'assets/js/store.js'); ?>" defer></script>
<script src="<?php echo base_url('assets/js/landing.js'); ?>" defer></script>
</body>
</html>
