<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$meta = isset($meta) ? $meta : array();
$active = uri_string();
$is_home = ($active === '' || $active === 'store' || $active === 'store/index');
$flags = array('CR' => 'cr', 'SV' => 'sv');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo html_escape(isset($meta['title']) ? $meta['title'] : 'SG Tienda'); ?></title>
<meta name="description" content="<?php echo html_escape(isset($meta['description']) ? $meta['description'] : ''); ?>">
<meta name="robots" content="<?php echo html_escape(isset($meta['robots']) ? $meta['robots'] : 'index,follow'); ?>">
<meta name="theme-color" content="#0A0A0B">
<link rel="canonical" href="<?php echo html_escape(isset($meta['canonical']) ? $meta['canonical'] : current_url()); ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="SG Tienda">
<meta property="og:title" content="<?php echo html_escape(isset($meta['title']) ? $meta['title'] : 'SG Tienda'); ?>">
<meta property="og:description" content="<?php echo html_escape(isset($meta['description']) ? $meta['description'] : ''); ?>">
<meta property="og:image" content="<?php echo html_escape(isset($meta['og_image']) ? $meta['og_image'] : base_url('assets/img/logo.png')); ?>">
<meta property="og:url" content="<?php echo html_escape(isset($meta['canonical']) ? $meta['canonical'] : current_url()); ?>">
<meta name="csrf-token-name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
<meta name="csrf-token-hash" content="<?php echo $this->security->get_csrf_hash(); ?>">
<link rel="icon" href="<?php echo base_url('assets/img/logo.png'); ?>">
<link rel="preload" href="<?php echo base_url('assets/fonts/anton-400-latin.woff2'); ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?php echo base_url('assets/fonts/manrope-400-latin.woff2'); ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?php echo base_url('assets/css/fonts.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/css/design.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/store.css') . '?v=' . @filemtime(FCPATH . 'assets/css/store.css'); ?>">
</head>
<body>
<div class="progress-bar" id="progressBar" aria-hidden="true"></div>

<div class="topbar">
  <div class="container-x topbar-inner">
    <span class="tb-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="14" height="12" rx="1"/><path d="M15 8h4l3 4v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2"/><circle cx="17.5" cy="18.5" r="2"/></svg>
    </span>
    <p class="tb-text">Suplementos originales · Entrega en <?php echo html_escape($current_country->name); ?></p>
  </div>
</div>

<header class="site-header">
  <div class="container-x header-inner">
    <a class="brand" href="<?php echo base_url(); ?>" aria-label="SG Tienda — inicio">
      <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="SG Tienda" width="120" height="46">
    </a>

    <nav class="nav" aria-label="Principal">
      <ul class="nav-list">
        <li><a href="<?php echo base_url(); ?>">Inicio</a></li>
        <li class="nav-item-dropdown" data-dropdown>
          <button type="button" class="nav-drop-btn" data-dropdown-toggle aria-expanded="false" aria-haspopup="true">
            Productos <i class="bi bi-chevron-down" aria-hidden="true"></i>
          </button>
          <div class="nav-drop-menu" data-dropdown-menu>
            <a href="<?php echo base_url('productos'); ?>"><i class="bi bi-grid" aria-hidden="true"></i> Todos los productos</a>
            <?php foreach (store_categories() as $key => $label): ?>
              <a href="<?php echo base_url('productos?categoria=' . $key); ?>"><?php echo html_escape($label); ?></a>
            <?php endforeach; ?>
          </div>
        </li>
        <li><a href="<?php echo base_url('guia'); ?>">Guía</a></li>
        <li><a href="<?php echo base_url('calculadora'); ?>">Calculadora</a></li>
        <li><a href="<?php echo base_url('nosotros'); ?>">Nosotros</a></li>
        <li><a href="<?php echo base_url('formas-de-pago'); ?>">Formas de pago</a></li>
        <li><a href="<?php echo base_url('contacto'); ?>">Contacto</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <div class="country-switch" role="group" aria-label="Elegir país de compra">
        <?php foreach ($countries as $country): ?>
          <?php $iso = strtolower($country->code); ?>
          <button class="country-btn<?php echo ((int) $country->id === (int) $current_country->id) ? ' active' : ''; ?>" type="button" data-country-choice data-country-id="<?php echo (int) $country->id; ?>" aria-pressed="<?php echo ((int) $country->id === (int) $current_country->id) ? 'true' : 'false'; ?>" title="Comprar en <?php echo html_escape($country->name); ?>">
            <img src="<?php echo base_url('assets/img/flags/' . $iso . '.svg'); ?>" alt="" aria-hidden="true">
            <span class="cb-label"><?php echo html_escape($country->name); ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <a class="icon-link" href="<?php echo base_url('carrito'); ?>" aria-label="Carrito de compras">
        <i class="bi bi-cart3" aria-hidden="true"></i>
        <span class="cart-badge<?php echo $cart_count < 1 ? ' d-none' : ''; ?>" data-cart-count><?php echo (int) $cart_count; ?></span>
      </a>

      <?php if ($store_logged_in): ?>
        <div class="nav-item-dropdown account-dropdown" data-dropdown>
          <button type="button" class="icon-link" data-dropdown-toggle aria-expanded="false" aria-haspopup="true" aria-label="Mi cuenta">
            <i class="bi bi-person-circle" aria-hidden="true"></i>
          </button>
          <div class="nav-drop-menu nav-drop-menu--right" data-dropdown-menu>
            <a href="<?php echo base_url('cuenta'); ?>"><i class="bi bi-person" aria-hidden="true"></i> Mi perfil</a>
            <a href="<?php echo base_url('cuenta/pedidos'); ?>"><i class="bi bi-box-seam" aria-hidden="true"></i> Mis pedidos</a>
            <hr>
            <a href="<?php echo base_url('salir'); ?>"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Cerrar sesión</a>
          </div>
        </div>
      <?php else: ?>
        <a class="icon-link" href="<?php echo base_url('ingresar'); ?>" aria-label="Iniciar sesión"><i class="bi bi-person" aria-hidden="true"></i></a>
      <?php endif; ?>

      <button class="menu-btn" type="button" aria-label="Abrir menu" aria-expanded="false">
        <span></span><span></span>
      </button>
    </div>
  </div>

  <nav class="menu-panel" aria-label="Menu movil">
    <a class="menu-link" href="<?php echo base_url(); ?>">Inicio</a>
    <a class="menu-link" href="<?php echo base_url('productos'); ?>">Productos</a>
    <a class="menu-link" href="<?php echo base_url('guia'); ?>">Guía</a>
    <a class="menu-link" href="<?php echo base_url('calculadora'); ?>">Calculadora</a>
    <a class="menu-link" href="<?php echo base_url('nosotros'); ?>">Nosotros</a>
    <a class="menu-link" href="<?php echo base_url('formas-de-pago'); ?>">Formas de pago</a>
    <a class="menu-link" href="<?php echo base_url('contacto'); ?>">Contacto</a>
    <?php if ($store_logged_in): ?>
      <a class="menu-link" href="<?php echo base_url('cuenta'); ?>">Mi perfil</a>
      <a class="menu-link" href="<?php echo base_url('cuenta/pedidos'); ?>">Mis pedidos</a>
      <a class="menu-link" href="<?php echo base_url('salir'); ?>">Cerrar sesión</a>
    <?php else: ?>
      <a class="menu-link" href="<?php echo base_url('ingresar'); ?>">Iniciar sesión</a>
      <a class="menu-link" href="<?php echo base_url('registro'); ?>">Crear cuenta</a>
    <?php endif; ?>
  </nav>
</header>

<main id="main-content">
<?php if ( ! empty($flash_error)): ?>
  <div class="container-x"><div class="flash flash-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><?php echo html_escape($flash_error); ?></span></div></div>
<?php endif; ?>
<?php if ( ! empty($flash_success)): ?>
  <div class="container-x"><div class="flash flash-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i><span><?php echo html_escape($flash_success); ?></span></div></div>
<?php endif; ?>
