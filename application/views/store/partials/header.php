<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$meta = isset($meta) ? $meta : array();
$active = uri_string();
$is_home = ($active === '' || $active === 'store' || $active === 'store/index');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo html_escape(isset($meta['title']) ? $meta['title'] : 'SG Tienda'); ?></title>
<meta name="description" content="<?php echo html_escape(isset($meta['description']) ? $meta['description'] : ''); ?>">
<meta name="robots" content="<?php echo html_escape(isset($meta['robots']) ? $meta['robots'] : 'index,follow'); ?>">
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap/bootstrap.min.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/css/store.css'); ?>">
</head>
<body>
<a class="skip-link" href="#main-content">Saltar al contenido</a>

<header class="site-header">
  <div class="site-navbar">
    <div class="container-x">
      <a class="brand" href="<?php echo base_url(); ?>">
        <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="SG Tienda">
        <span>SG <b>Tienda</b></span>
      </a>

      <nav aria-label="Principal">
        <ul class="nav-links" data-nav-links>
          <li><a href="<?php echo base_url(); ?>"<?php echo $is_home ? ' class="active" aria-current="page"' : ''; ?>>Inicio</a></li>
          <li><a href="<?php echo base_url('productos'); ?>"<?php echo strpos($active, 'productos') === 0 ? ' class="active" aria-current="page"' : ''; ?>>Productos</a></li>
          <li><a href="<?php echo base_url('guia'); ?>"<?php echo strpos($active, 'guia') === 0 ? ' class="active" aria-current="page"' : ''; ?>>Guia</a></li>
          <li><a href="<?php echo base_url('nosotros'); ?>">Nosotros</a></li>
          <li><a href="<?php echo base_url('formas-de-pago'); ?>">Formas de pago</a></li>
          <li><a href="<?php echo base_url('contacto'); ?>">Contacto</a></li>
        </ul>
      </nav>

      <div class="nav-actions">
        <div class="dropdown country-dropdown" data-country-select>
          <button class="country-select" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-country-current="<?php echo (int) $current_country->id; ?>" aria-label="Seleccionar pais">
            <img src="<?php echo base_url('assets/img/flags/' . strtolower($current_country->code) . '.svg'); ?>" alt="" class="flag-icon" data-country-flag aria-hidden="true">
            <span data-country-code><?php echo html_escape($current_country->code); ?></span>
            <i class="bi bi-chevron-down" aria-hidden="true"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end country-menu">
            <?php foreach ($countries as $country): ?>
              <li>
                <button type="button" class="dropdown-item" data-country-option data-country-id="<?php echo (int) $country->id; ?>">
                  <img src="<?php echo base_url('assets/img/flags/' . strtolower($country->code) . '.svg'); ?>" alt="" class="flag-icon" aria-hidden="true">
                  <span><?php echo html_escape($country->name); ?></span>
                </button>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <a class="icon-link" href="<?php echo base_url('carrito'); ?>" aria-label="Carrito de compras">
          <i class="bi bi-cart3" aria-hidden="true"></i>
          <span class="cart-badge<?php echo $cart_count < 1 ? ' d-none' : ''; ?>" data-cart-count><?php echo (int) $cart_count; ?></span>
        </a>

        <?php if ($store_logged_in): ?>
          <div class="dropdown">
            <button class="icon-link" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mi cuenta">
              <i class="bi bi-person-circle" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?php echo base_url('cuenta'); ?>">Mi perfil</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('cuenta/pedidos'); ?>">Mis pedidos</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?php echo base_url('salir'); ?>">Cerrar sesion</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a class="btn-outline btn-sm d-none d-lg-inline-flex" href="<?php echo base_url('ingresar'); ?>">Iniciar sesion</a>
        <?php endif; ?>

        <?php if ( ! empty($whatsapp_url)): ?>
          <a class="btn-brand btn-sm d-none d-xl-inline-flex" href="<?php echo html_escape($whatsapp_url); ?>" target="_blank" rel="noopener">
            <i class="bi bi-whatsapp" aria-hidden="true"></i> Haz tu pedido
          </a>
        <?php endif; ?>

        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Abrir menu">
          <i class="bi bi-list" aria-hidden="true"></i>
        </button>
      </div>
    </div>
  </div>
</header>

<main id="main-content">
<?php if ( ! empty($flash_error)): ?>
  <div class="container-x"><div class="flash flash-error" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><?php echo html_escape($flash_error); ?></span></div></div>
<?php endif; ?>
<?php if ( ! empty($flash_success)): ?>
  <div class="container-x"><div class="flash flash-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i><span><?php echo html_escape($flash_success); ?></span></div></div>
<?php endif; ?>
