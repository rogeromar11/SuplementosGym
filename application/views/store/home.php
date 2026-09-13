<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cat_icons = array(
	'proteinas'   => 'bi-droplet-half',
	'creatinas'   => 'bi-lightning-charge',
	'quemadores'  => 'bi-fire',
	'preentrenos' => 'bi-activity',
	'ganadores'   => 'bi-bar-chart-fill',
	'otros'       => 'bi-capsule',
);
?>

<section class="hero">
  <div class="container-x hero-inner">
    <div class="hero-copy">
      <span class="hero-eyebrow">Suplementacion profesional</span>
      <h1 class="hero-title">Entrena duro.<br>Recupera <span>mejor</span>.</h1>
      <p class="hero-text">Suplementos deportivos originales para fuerza, rendimiento y recuperacion. Seleccionamos cada producto para que alcances tus objetivos, con entrega en <?php echo html_escape($current_country->name); ?>.</p>
      <div class="hero-actions">
        <a class="btn-brand btn-lg" href="<?php echo base_url('productos'); ?>"><i class="bi bi-grid" aria-hidden="true"></i> Ver productos</a>
        <a class="btn-outline btn-lg" href="<?php echo base_url('guia'); ?>"><i class="bi bi-journal-text" aria-hidden="true"></i> Guia de suplementos</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><strong><?php echo (int) $total_products; ?>+</strong><span>Productos disponibles</span></div>
        <div class="hero-stat"><strong>100%</strong><span>Originales</span></div>
        <div class="hero-stat"><strong><?php echo html_escape($current_country->code); ?></strong><span>Cobertura nacional</span></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-visual-card">
        <img src="<?php echo base_url('assets/img/product-placeholder.svg'); ?>" alt="Suplementos deportivos SG Tienda">
        <div class="hero-float f1"><i class="bi bi-patch-check-fill" aria-hidden="true"></i><span><b>Originales</b><br>garantizados</span></div>
        <div class="hero-float f2"><i class="bi bi-truck" aria-hidden="true"></i><span><b>Entrega</b><br><?php echo html_escape($current_country->name); ?></span></div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container-x">
    <div class="benefit-strip reveal">
      <div class="benefit-strip-inner">
        <div class="benefit-item">
          <div class="bicon"><i class="bi bi-patch-check-fill" aria-hidden="true"></i></div>
          <div><h3>Productos originales</h3><p>Trabajamos solo con marcas reconocidas y verificamos su procedencia.</p></div>
        </div>
        <div class="benefit-item">
          <div class="bicon"><i class="bi bi-person-check-fill" aria-hidden="true"></i></div>
          <div><h3>Asesoria experta</h3><p>Te orientamos para elegir el suplemento ideal segun tu objetivo.</p></div>
        </div>
        <div class="benefit-item">
          <div class="bicon"><i class="bi bi-truck" aria-hidden="true"></i></div>
          <div><h3>Entrega rapida</h3><p>Coordinamos tu entrega de forma agil y segura.</p></div>
        </div>
        <div class="benefit-item">
          <div class="bicon"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i></div>
          <div><h3>Cobertura nacional</h3><p>Envios a las principales zonas de <?php echo html_escape($current_country->name); ?>.</p></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <span class="section-eyebrow">Categorias</span>
        <h2 class="section-title">Compra por categoria</h2>
        <p class="section-subtitle">Encuentra lo que tu entrenamiento necesita.</p>
      </div>
      <a class="btn-ghost btn-sm" href="<?php echo base_url('productos'); ?>">Ver todo <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
    <div class="category-grid">
      <?php foreach (store_categories() as $key => $label): ?>
        <a class="category-tile reveal" href="<?php echo base_url('productos?categoria=' . $key); ?>">
          <span class="ticon"><i class="bi <?php echo html_escape(isset($cat_icons[$key]) ? $cat_icons[$key] : 'bi-box-seam'); ?>" aria-hidden="true"></i></span>
          <b><?php echo html_escape($label); ?></b>
          <small><?php echo (int) (isset($category_counts[$key]) ? $category_counts[$key] : 0); ?> productos</small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <span class="section-eyebrow">Seleccion</span>
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
    <div class="section-head">
      <div>
        <span class="section-eyebrow">Aprende</span>
        <h2 class="section-title">Conoce los suplementos</h2>
        <p class="section-subtitle">Que hace cada suplemento y para que sirve.</p>
      </div>
      <a class="btn-ghost btn-sm" href="<?php echo base_url('guia'); ?>">Guia completa <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
    <div class="guide-grid">
      <?php foreach (store_supplement_guides() as $key => $guide): ?>
        <article class="guide-card reveal">
          <div class="guide-icon"><i class="bi <?php echo html_escape($guide['icon']); ?>" aria-hidden="true"></i></div>
          <span class="guide-tagline"><?php echo html_escape($guide['tagline']); ?></span>
          <h3><?php echo html_escape($guide['name']); ?></h3>
          <p><?php echo html_escape($guide['description']); ?></p>
          <div class="guide-meta">
            <div><strong>Beneficio</strong><br><?php echo html_escape($guide['benefit']); ?></div>
            <div><strong>Uso</strong><br><?php echo html_escape($guide['usage']); ?></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <span class="section-eyebrow">Tu objetivo</span>
        <h2 class="section-title">¿Cual es tu meta?</h2>
        <p class="section-subtitle">Te ayudamos a elegir segun lo que quieres lograr.</p>
      </div>
    </div>
    <div class="goal-grid">
      <?php foreach (store_goals() as $goal): ?>
        <div class="goal-card reveal">
          <div class="gicon"><i class="bi <?php echo html_escape($goal['icon']); ?>" aria-hidden="true"></i></div>
          <h3><?php echo html_escape($goal['title']); ?></h3>
          <p><?php echo html_escape($goal['description']); ?></p>
          <div class="goal-rec"><strong>Recomendado:</strong> <?php echo html_escape($goal['recommended']); ?></div>
          <a class="btn-ghost btn-sm mt-2" href="<?php echo base_url('productos?categoria=' . $goal['link']); ?>">Explorar</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <span class="section-eyebrow">Dudas</span>
        <h2 class="section-title">Preguntas frecuentes</h2>
        <p class="section-subtitle">Resolvemos las dudas mas comunes.</p>
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

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="reveal" style="background: var(--brand); color:#fff; border-radius: var(--radius); padding: 2.6rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap;">
      <div>
        <h2 class="section-title" style="color:#fff; font-size:1.6rem;">¿Listo para tu proximo pedido?</h2>
        <p class="mb-0" style="color:rgba(255,255,255,.9);">Haz tu pedido por WhatsApp o completa tu compra en linea.</p>
      </div>
      <div style="display:flex; gap:.6rem; flex-wrap:wrap;">
        <a class="btn-dark" href="<?php echo base_url('productos'); ?>">Comprar ahora</a>
        <?php if ( ! empty($whatsapp_url)): ?>
          <a class="btn-wa" href="<?php echo html_escape($whatsapp_url); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
