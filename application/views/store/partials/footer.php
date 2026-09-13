<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$wa = isset($whatsapp_url) ? $whatsapp_url : '';
$email = store_setting('contact_email', '');
$hours = store_setting('business_hours', '');
?>
</main>

<footer class="site-footer">
  <div class="footer-cta">
    <div class="container-x footer-cta-inner">
      <div>
        <h3>¿Listo para rendir mas?</h3>
        <p>Haz tu pedido por WhatsApp o completa tu compra en linea.</p>
      </div>
      <div style="display:flex; gap:.6rem; flex-wrap:wrap;">
        <a class="btn-brand" href="<?php echo base_url('productos'); ?>">Comprar ahora</a>
        <?php if ( ! empty($wa)): ?>
          <a class="btn-wa" href="<?php echo html_escape($wa); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="container-x">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="SG Tienda">
          <span>SG <span style="color:var(--brand)">Tienda</span></span>
        </div>
        <p>Suplementos deportivos originales para fuerza, rendimiento y recuperacion. Entrega en <?php echo html_escape($current_country->name); ?>.</p>
      </div>

      <div>
        <h4>Navegacion</h4>
        <ul class="footer-list">
          <li><a href="<?php echo base_url(); ?>">Inicio</a></li>
          <li><a href="<?php echo base_url('productos'); ?>">Productos</a></li>
          <li><a href="<?php echo base_url('guia'); ?>">Guia de suplementos</a></li>
          <li><a href="<?php echo base_url('nosotros'); ?>">Nosotros</a></li>
          <li><a href="<?php echo base_url('formas-de-pago'); ?>">Formas de pago</a></li>
          <li><a href="<?php echo base_url('contacto'); ?>">Contacto</a></li>
        </ul>
      </div>

      <div>
        <h4>Categorias</h4>
        <ul class="footer-list">
          <?php foreach (store_categories() as $key => $label): ?>
            <li><a href="<?php echo base_url('productos?categoria=' . $key); ?>"><?php echo html_escape($label); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div>
        <h4>Contacto</h4>
        <ul class="footer-list">
          <?php if ( ! empty($wa)): ?>
            <li><a href="<?php echo html_escape($wa); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i>WhatsApp</a></li>
          <?php endif; ?>
          <?php if ( ! empty($email)): ?>
            <li><a href="mailto:<?php echo html_escape($email); ?>"><i class="bi bi-envelope"></i><?php echo html_escape($email); ?></a></li>
          <?php endif; ?>
          <?php if ( ! empty($hours)): ?>
            <li><i class="bi bi-clock"></i><?php echo html_escape($hours); ?></li>
          <?php endif; ?>
          <?php if (empty($wa) && empty($email) && empty($hours)): ?>
            <li>Datos de contacto por definir.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?php echo date('Y'); ?> SG Tienda. Todos los derechos reservados.</span>
      <span>
        <a href="<?php echo base_url('nosotros'); ?>">Politicas</a> ·
        <a href="<?php echo base_url('nosotros'); ?>">Terminos</a>
      </span>
    </div>
  </div>
</footer>

<div class="store-modal" data-country-modal role="dialog" aria-modal="true" aria-labelledby="countryModalTitle">
  <div class="store-modal-card">
    <h3 id="countryModalTitle">Cambiar de pais</h3>
    <p>Al cambiar de pais, los productos actuales del carrito podrian dejar de estar disponibles. ¿Deseas continuar?</p>
    <div class="mt-3" style="display:flex; gap:.6rem; justify-content:flex-end;">
      <button type="button" class="btn-ghost" data-country-cancel>Cancelar</button>
      <button type="button" class="btn-brand" data-country-confirm>Continuar</button>
    </div>
  </div>
</div>

<script src="<?php echo base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js'); ?>"></script>
<script>
window.STORE_CONFIG = {
  base: <?php echo json_encode(base_url()); ?>,
  csrfName: <?php echo json_encode($this->security->get_csrf_token_name()); ?>,
  csrfHash: <?php echo json_encode($this->security->get_csrf_hash()); ?>
};
</script>
<script src="<?php echo base_url('assets/js/store.js'); ?>"></script>
</body>
</html>
