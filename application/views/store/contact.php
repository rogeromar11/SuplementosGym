<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$email = store_setting('contact_email', '');
$hours = store_setting('business_hours', '');
?>
<section class="section">
  <div class="container-x">
    <h1 class="section-title">Contacto</h1>
    <p class="section-subtitle">Escribenos y te ayudamos con tu pedido.</p>

    <div class="info-grid mt-4" style="grid-template-columns: repeat(3, 1fr);">
      <div class="info-card">
        <i class="bi bi-whatsapp" aria-hidden="true"></i>
        <h3>WhatsApp</h3>
        <?php if ( ! empty($whatsapp_url)): ?>
          <a class="btn-wa mt-2" href="<?php echo html_escape($whatsapp_url); ?>" target="_blank" rel="noopener">Abrir WhatsApp</a>
        <?php else: ?>
          <p>Numero por definir.</p>
        <?php endif; ?>
      </div>
      <div class="info-card">
        <i class="bi bi-envelope" aria-hidden="true"></i>
        <h3>Correo</h3>
        <?php if ( ! empty($email)): ?>
          <a class="btn-ghost mt-2" href="mailto:<?php echo html_escape($email); ?>"><?php echo html_escape($email); ?></a>
        <?php else: ?>
          <p>Correo por definir.</p>
        <?php endif; ?>
      </div>
      <div class="info-card">
        <i class="bi bi-clock" aria-hidden="true"></i>
        <h3>Horario</h3>
        <?php if ( ! empty($hours)): ?>
          <p><?php echo html_escape($hours); ?></p>
        <?php else: ?>
          <p>Horario por definir.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
