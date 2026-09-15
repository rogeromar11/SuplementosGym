<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$email = store_setting('contact_email', '');
$hours = store_setting('business_hours', '');
?>
<section class="page-band">
  <div class="container-x">
    <h1>Contacto</h1>
    <p>Estamos para ayudarte. Escríbenos y te respondemos a la brevedad.</p>
  </div>
</section>

<section class="section">
  <div class="container-x">
    <div class="info-grid" style="grid-template-columns: repeat(3, 1fr);">
      <div class="info-card reveal">
        <div class="bicon"><i class="bi bi-whatsapp" aria-hidden="true"></i></div>
        <h3>WhatsApp</h3>
        <?php if ( ! empty($whatsapp_url)): ?>
          <p>La via más rápida para consultas y pedidos.</p>
          <a class="btn-wa mt-2" href="<?php echo html_escape($whatsapp_url); ?>" target="_blank" rel="noopener">Abrir WhatsApp</a>
        <?php else: ?>
          <p>Número por definir.</p>
        <?php endif; ?>
      </div>
      <div class="info-card reveal">
        <div class="bicon"><i class="bi bi-envelope" aria-hidden="true"></i></div>
        <h3>Correo</h3>
        <?php if ( ! empty($email)): ?>
          <p>Para consultas por escrito.</p>
          <a class="btn-ghost mt-2" href="mailto:<?php echo html_escape($email); ?>"><?php echo html_escape($email); ?></a>
        <?php else: ?>
          <p>Correo por definir.</p>
        <?php endif; ?>
      </div>
      <div class="info-card reveal">
        <div class="bicon"><i class="bi bi-clock" aria-hidden="true"></i></div>
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

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <span class="section-eyebrow">Dudas</span>
        <h2 class="section-title">Preguntas frecuentes</h2>
        <p class="section-subtitle">Antes de escribirnos, revisa estas respuestas.</p>
      </div>
    </div>
    <div class="store-accordion reveal" style="max-width:820px;">
      <?php foreach (store_faqs() as $index => $faq): ?>
        <div class="store-accordion-item" data-accordion>
          <button type="button" class="store-accordion-btn" data-accordion-btn aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>">
            <span><?php echo html_escape($faq['q']); ?></span>
            <i class="bi bi-chevron-down" aria-hidden="true"></i>
          </button>
          <div class="store-accordion-panel" data-accordion-panel<?php echo $index === 0 ? ' style="max-height: 200px;"' : ''; ?>>
            <div class="store-accordion-panel-inner"><?php echo html_escape($faq['a']); ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
