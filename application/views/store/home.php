<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$cat_icons = array(
	'proteinas'        => 'bi-droplet-half',
	'creatinas'        => 'bi-lightning-charge',
	'preentrenos'      => 'bi-activity',
	'aminos'           => 'bi-droplet',
	'quemadores'       => 'bi-fire',
	'ganadores'        => 'bi-bar-chart-fill',
	'multivitaminicos' => 'bi-capsule-pill',
	'otros'            => 'bi-capsule',
);
$testimonios = array(
	'CR' => array(
		array('nombre' => 'Andres M.', 'ciudad' => 'San Jose', 'texto' => 'Pedí whey y creatina un martes y el miércoles ya estaba entrenando. Todo sellado y original.'),
		array('nombre' => 'Karla V.', 'ciudad' => 'Heredia', 'texto' => 'Llevo dos años comprando aquí. Nunca me han vendido una imitación y los precios se mantienen.'),
		array('nombre' => 'Diego R.', 'ciudad' => 'Cartago', 'texto' => 'La asesoría por WhatsApp es real: me armaron el combo según mi presupuesto, sin venderme de más.'),
	),
	'SV' => array(
		array('nombre' => 'Sofia A.', 'ciudad' => 'San Salvador', 'texto' => 'Pedí un viernes y el lunes ya tenía mi proteína en la puerta. Excelente servicio.'),
		array('nombre' => 'Mauricio L.', 'ciudad' => 'Santa Tecla', 'texto' => 'Precios justos y todo llega sellado. Es mi tienda fija para suplementos.'),
		array('nombre' => 'Andrea P.', 'ciudad' => 'Soyapango', 'texto' => 'Me ayudaron a elegir mi primer pre-entreno y me explicaron como tomarlo. 100% recomendados.'),
	),
);
$resenas = isset($testimonios[$current_country->code]) ? $testimonios[$current_country->code] : $testimonios['CR'];
$marquee_items = array('Envío 24-48 h', '100% originales', 'Asesoría por WhatsApp', 'Costa Rica y El Salvador', 'Proteína · Creatina · Preentrenos');
$wa_pedido = $whatsapp_url ?: base_url('contacto');
?>

<section class="hero">
  <div class="hero-media">
    <video class="hero-video" autoplay muted loop playsinline poster="<?php echo base_url('assets/img/hero-poster.jpg'); ?>" preload="metadata" aria-hidden="true">
      <source src="<?php echo base_url('assets/video/hero-web.mp4'); ?>" type="video/mp4">
    </video>
    <div class="hero-veil"></div>
  </div>

  <div class="container-x hero-content">
    <p class="eyebrow hero-kicker">Tienda oficial en <?php echo html_escape($current_country->name); ?> · Envíos a todo el país</p>

    <h1 class="hero-title">
      <span class="line"><span>DISCIPLINA HOY,</span></span>
      <span class="line"><span>RESULTADOS <em>SIEMPRE.</em></span></span>
    </h1>

    <p class="hero-sub">Proteína, creatina y pre-entrenos 100% originales. Asesoría de gente que entrena, entrega en 24-48 horas y pago contra entrega.</p>

    <div class="hero-ctas">
      <a class="btn btn-brand" href="#productos">Ver más vendidos
        <i class="bi bi-arrow-right" aria-hidden="true"></i>
      </a>
      <a class="btn btn-ghost" href="<?php echo html_escape($wa_pedido); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> Asesoría por WhatsApp</a>
    </div>

    <ul class="hero-stats">
      <li class="stat"><span class="stat-num">24-48 h</span><span class="stat-label">Entrega</span></li>
      <li class="stat"><span class="stat-num">100%</span><span class="stat-label">Originales</span></li>
      <li class="stat"><span class="stat-num"><?php echo (int) $brand_count; ?>+</span><span class="stat-label">Marcas</span></li>
      <li class="stat"><span class="stat-num">CR + SV</span><span class="stat-label">Dos países</span></li>
      <?php $socials = store_social_links(); ?>
      <?php if ( ! empty($socials)): ?>
        <li class="stat stat-social">
          <span class="stat-label">Siguenos</span>
          <span class="hero-social">
            <?php foreach ($socials as $s): ?>
              <a href="<?php echo html_escape($s['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo html_escape($s['label']); ?>" title="<?php echo html_escape($s['label']); ?>"><i class="bi <?php echo html_escape($s['icon']); ?>" aria-hidden="true"></i></a>
            <?php endforeach; ?>
          </span>
        </li>
      <?php endif; ?>
    </ul>
  </div>

  <button class="video-toggle" type="button" aria-label="Pausar video de fondo">
    <i class="bi bi-pause-fill i-pause" aria-hidden="true"></i>
    <i class="bi bi-play-fill i-play" aria-hidden="true"></i>
  </button>
</section>

<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($r = 0; $r < 2; $r++): ?>
      <div class="marquee-set">
        <?php foreach ($marquee_items as $item): ?>
          <span class="m-item"><?php echo html_escape($item); ?></span><span class="m-sep"></span>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>

<section class="section" id="categorías">
  <div class="container-x">
    <div class="section-head" data-reveal>
      <div>
        <p class="eyebrow">Categorías</p>
        <h2 class="section-title">TODO PARA TU <em>ENTRENO</em></h2>
        <p class="section-subtitle">Elegi por objetivo. Cada producto original, con garantia de cambio y respaldo de tienda.</p>
      </div>
    </div>
    <div class="cat-grid">
      <?php foreach (store_categories() as $key => $label): ?>
        <a class="card cat-card spot" href="<?php echo base_url('productos?categoria=' . $key); ?>" data-reveal>
          <span class="cat-icon"><i class="bi <?php echo html_escape($cat_icons[$key]); ?>" aria-hidden="true"></i></span>
          <h3 class="cat-name"><?php echo html_escape($label); ?></h3>
          <p class="cat-desc">Productos originales seleccionados.</p>
          <span class="cat-count"><?php echo (int) (isset($category_counts[$key]) ? $category_counts[$key] : 0); ?> productos</span>
          <span class="cat-arrow"><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="productos" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head" data-reveal>
      <div>
        <p class="eyebrow">Catálogo</p>
        <h2 class="section-title">MAS <em>VENDIDOS</em></h2>
        <p class="section-subtitle">Lo que más sale del mostrador. <span id="pcount"><?php echo count($featured); ?> productos</span> disponibles.</p>
      </div>
      <a class="btn btn-ghost btn-sm" href="<?php echo base_url('productos'); ?>">Ver todo <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    <div class="chips" role="group" aria-label="Filtrar por categoría" data-reveal>
      <a class="chip active" href="<?php echo base_url('productos'); ?>">Todos</a>
      <?php foreach (store_categories() as $key => $label): ?>
        <a class="chip" href="<?php echo base_url('productos?categoria=' . $key); ?>"><?php echo html_escape($label); ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($featured)): ?>
      <div class="empty-state">
        <i class="bi bi-box-seam" aria-hidden="true"></i>
        <h3>Actualmente no hay productos para este país.</h3>
        <p>Estamos ampliando el catálogo. Vuelve pronto.</p>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($featured as $product): ?>
          <?php $this->load->view('store/partials/product_card', array('group' => $product)); ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <p class="catalog-note" data-reveal>
      <span>Precios en <?php echo ($current_country->currency === 'USD') ? 'dólares (US$)' : 'colones costarricenses (₡)';?>. Envío en 24-48 h.</span>
      <a class="link" href="<?php echo base_url('productos'); ?>">¿No encontras lo que buscas? Escribinos</a>
    </p>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="promo card spot" data-reveal>
      <div class="promo-left">
        <p class="eyebrow">Primera compra</p>
        <h2 class="promo-title">10% OFF EN TU <em>PRIMER PEDIDO</em></h2>
        <p class="promo-sub">Válido en tu primer pedido con envío a cualquier parte del país.</p>
      </div>
      <div class="promo-right">
        <a class="btn btn-dark" href="<?php echo html_escape($wa_pedido); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> Reclamar descuento</a>
        <p class="promo-small">Sin código: solo decisnos que es tu primera compra.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" id="beneficios" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head" data-reveal>
      <div>
        <p class="eyebrow">Por que comprarnos</p>
        <h2 class="section-title">HECHO PARA GENTE <em>SERIA</em></h2>
        <p class="section-subtitle">Más que una tienda: un equipo que entrena y te responde cuando tienes dudas.</p>
      </div>
    </div>
    <div class="benefits-grid">
      <div class="card benefit spot" data-reveal>
        <span class="b-icon"><i class="bi bi-grid" aria-hidden="true"></i></span>
        <p class="b-num"><span class="count" data-target="<?php echo (int) $total_products; ?>" data-sufijo="+">0</span></p>
        <p class="b-label">Productos disponibles</p>
        <p class="b-desc">Catálogo activo en <?php echo html_escape($current_country->name); ?>.</p>
      </div>
      <div class="card benefit spot" data-reveal>
        <span class="b-icon"><i class="bi bi-award" aria-hidden="true"></i></span>
        <p class="b-num"><span class="count" data-target="<?php echo (int) $brand_count; ?>" data-sufijo="+">0</span></p>
        <p class="b-label">Marcas</p>
        <p class="b-desc">Laboratorios reconocidos a nivel mundial.</p>
      </div>
      <div class="card benefit spot" data-reveal>
        <span class="b-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
        <p class="b-num"><span class="count" data-target="2">0</span></p>
        <p class="b-label">Países</p>
        <p class="b-desc">Costa Rica y El Salvador con envíos nacionales.</p>
      </div>
      <div class="card benefit spot" data-reveal>
        <span class="b-icon"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
        <p class="b-num"><span class="count" data-target="100" data-sufijo="%">0</span></p>
        <p class="b-label">Originales</p>
        <p class="b-desc">Productos verificados o te devolvemos tu dinero.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" id="tiendas" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head" data-reveal>
      <div>
        <p class="eyebrow">Tiendas</p>
        <h2 class="section-title">DOS PAISES, UN MISMO <em>ESTÁNDAR</em></h2>
        <p class="section-subtitle">Compra donde estes: retiro en tienda o envío a todo el país.</p>
      </div>
    </div>
    <div class="stores-grid">
      <?php foreach ($countries as $index => $country): ?>
        <?php $activa = ((int) $country->id === (int) $current_country->id); ?>
        <article class="card store-card spot<?php echo $activa ? ' active' : ''; ?>" data-reveal>
          <div class="store-head">
            <span class="store-num">0<?php echo $index + 1; ?></span>
            <span class="store-tag">Tu tienda</span>
          </div>
          <h3 class="store-name"><?php echo html_escape($country->name); ?></h3>
          <p class="store-country">Atención en <?php echo html_escape($country->currency); ?></p>
          <ul class="store-rows">
            <li><i class="bi bi-clock" aria-hidden="true"></i><span><?php echo html_escape(store_setting('business_hours', 'Horario por confirmar', $country->id)); ?></span></li>
            <li><i class="bi bi-envelope" aria-hidden="true"></i><span><?php echo html_escape(store_setting('contact_email', 'Correo por confirmar', $country->id)); ?></span></li>
            <li><i class="bi bi-whatsapp" aria-hidden="true"></i><span><?php echo html_escape(store_setting('whatsapp_number', 'WhatsApp por confirmar', $country->id)); ?></span></li>
          </ul>
          <div class="store-actions">
            <?php $wa_country = store_whatsapp_url('Hola, quiero información sobre suplementos (' . $country->name . ').', $country->id); ?>
            <?php if ( ! empty($wa_country)): ?>
              <a class="btn btn-wa btn-sm" href="<?php echo html_escape($wa_country); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp</a>
            <?php endif; ?>
            <a class="btn btn-ghost btn-sm" href="<?php echo base_url('contacto'); ?>">Contacto</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="resenas" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head" data-reveal>
      <div>
        <p class="eyebrow">Resenas</p>
        <h2 class="section-title">LO QUE DICEN LOS QUE <em>ENTRENAN</em></h2>
        <p class="section-subtitle">Opiniones de clientes en Costa Rica y El Salvador.</p>
      </div>
    </div>
    <div class="t-grid">
      <?php foreach ($resenas as $t): ?>
        <?php $ini = mb_substr($t['nombre'], 0, 1, 'UTF-8'); ?>
        <article class="card tcard spot" data-reveal>
          <div class="t-top">
            <span class="t-avatar"><?php echo html_escape($ini); ?></span>
            <div>
              <p class="t-name"><?php echo html_escape($t['nombre']); ?></p>
              <p class="t-city"><?php echo html_escape($t['ciudad']); ?></p>
            </div>
            <span class="t-quote">&ldquo;</span>
          </div>
          <div class="t-stars"><span class="stars"><i class="bi bi-star-fill" aria-hidden="true"></i><i class="bi bi-star-fill" aria-hidden="true"></i><i class="bi bi-star-fill" aria-hidden="true"></i><i class="bi bi-star-fill" aria-hidden="true"></i><i class="bi bi-star-fill" aria-hidden="true"></i></span></div>
          <p class="t-text"><?php echo html_escape($t['texto']); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section final" id="contacto">
  <span class="final-ghost" aria-hidden="true">SG TIENDA</span>
  <div class="container-x final-inner" data-reveal>
    <h2 class="final-title">¿LISTO PARA <em>ENTRENAR EN SERIO?</em></h2>
    <p class="final-sub">Escribinos y te armamos el combo según tu objetivo y presupuesto. Te responde una persona del equipo, no un bot.</p>
    <div class="final-ctas">
      <a class="btn btn-brand btn-lg" href="<?php echo html_escape($wa_pedido); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> Escribir por WhatsApp</a>
      <a class="btn btn-ghost btn-lg" href="<?php echo base_url('productos'); ?>">Ver catálogo</a>
    </div>
    <p class="final-note"><?php echo html_escape(store_setting('business_hours', 'Respuesta en minutos', $current_country->id)); ?></p>
  </div>
</section>
