<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="hero">
  <div class="container-x hero-inner">
    <div>
      <span class="hero-badge"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Rendimiento sin excusas</span>
      <h1 class="hero-title">Entrena mas fuerte.<br>Recupera <span>mejor</span>.</h1>
      <p class="hero-text">Suplementos originales para fuerza, resistencia y recuperacion. Seleccionados para llevarte al siguiente nivel, con entrega en <?php echo html_escape($current_country->name); ?>.</p>
      <div class="hero-actions">
        <a class="btn-brand" href="<?php echo base_url('productos'); ?>"><i class="bi bi-grid" aria-hidden="true"></i> Ver productos</a>
        <?php if ( ! empty($whatsapp_url)): ?>
          <a class="btn-wa" href="<?php echo html_escape($whatsapp_url); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> Escribenos</a>
        <?php else: ?>
          <a class="btn-ghost" href="<?php echo base_url('contacto'); ?>"><i class="bi bi-chat-dots" aria-hidden="true"></i> Escribenos</a>
        <?php endif; ?>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><strong><?php echo (int) $total_products; ?>+</strong><span>Productos disponibles</span></div>
        <div class="hero-stat"><strong>100%</strong><span>Originales</span></div>
        <div class="hero-stat"><strong><?php echo html_escape($current_country->code); ?></strong><span>Cobertura nacional</span></div>
      </div>
    </div>
    <div class="hero-media" aria-hidden="true">
      <img src="<?php echo base_url('assets/img/product-placeholder.svg'); ?>" alt="">
    </div>
  </div>
</section>

<section class="section">
  <div class="container-x">
    <div class="info-grid">
      <div class="info-card reveal">
        <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
        <h3>Productos originales</h3>
        <p>Trabajamos con marcas reconocidas y productos verificados.</p>
      </div>
      <div class="info-card reveal">
        <i class="bi bi-headset" aria-hidden="true"></i>
        <h3>Atencion personalizada</h3>
        <p>Te ayudamos a elegir el suplemento ideal para tu objetivo.</p>
      </div>
      <div class="info-card reveal">
        <i class="bi bi-truck" aria-hidden="true"></i>
        <h3>Entrega rapida</h3>
        <p>Coordinamos tu entrega de forma agil y segura.</p>
      </div>
      <div class="info-card reveal">
        <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
        <h3>Cobertura nacional</h3>
        <p>Envios a las principales zonas de <?php echo html_escape($current_country->name); ?>.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <h2 class="section-title">Compra por categoria</h2>
        <p class="section-subtitle">Encuentra lo que tu entrenamiento necesita.</p>
      </div>
      <a class="btn-ghost btn-sm" href="<?php echo base_url('productos'); ?>">Ver todo <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
    <div class="category-pills">
      <?php foreach (store_categories() as $key => $label): ?>
        <a class="pill" href="<?php echo base_url('productos?categoria=' . $key); ?>">
          <?php echo html_escape($label); ?>
          <small>(<?php echo (int) (isset($category_counts[$key]) ? $category_counts[$key] : 0); ?>)</small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <h2 class="section-title">Destacados</h2>
        <p class="section-subtitle">Los favoritos de nuestros clientes.</p>
      </div>
    </div>

    <?php if (empty($featured)): ?>
      <div class="empty-state">
        <i class="bi bi-box-seam" aria-hidden="true"></i>
        <h3>Actualmente no hay productos disponibles para este pais.</h3>
        <p>Estamos trabajando para ampliar nuestro catalogo. Vuelve pronto.</p>
        <a class="btn-ghost mt-3" href="<?php echo base_url('contacto'); ?>">Contactar</a>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($featured as $product): ?>
          <?php $this->load->view('store/partials/product_card', array('product' => $product)); ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="form-card reveal" style="display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap;">
      <div>
        <h2 class="section-title" style="font-size:1.5rem;">¿Listo para tu proximo pedido?</h2>
        <p class="muted mb-0">Haz tu pedido por WhatsApp o completa tu compra en linea.</p>
      </div>
      <div style="display:flex; gap:.6rem; flex-wrap:wrap;">
        <a class="btn-brand" href="<?php echo base_url('productos'); ?>">Comprar ahora</a>
        <?php if ( ! empty($whatsapp_url)): ?>
          <a class="btn-wa" href="<?php echo html_escape($whatsapp_url); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
