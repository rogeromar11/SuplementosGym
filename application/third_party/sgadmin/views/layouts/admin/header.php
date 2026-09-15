<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Cabecera del layout administrativo.
 * Variables disponibles: $pageTitle, $breadcrumbs (array), $currentUser.
 */
$pageTitle = isset($pageTitle) ? $pageTitle : $this->data['appName'];
$breadcrumbs = isset($breadcrumbs) ? $breadcrumbs : array();
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$userName = isset($currentUser) ? trim($currentUser->first_name . ' ' . $currentUser->last_name) : '';
$userInitial = $userName !== '' ? mb_substr($userName, 0, 1) : '?';

$menuSections = array(
    'Operación' => array(),
    'Datos' => array(),
    'Catálogos' => array(),
    'Reportes' => array(),
    'Configuración' => array(),
);

if (has_permission('pedidos.ver')) {
    $menuSections['Operación'][] = array('href' => 'orders', 'icon' => 'bi-bag-check', 'label' => 'Pedidos');
}
if (has_permission('pedidos.preparar')) {
    $menuSections['Operación'][] = array('href' => 'preparation', 'icon' => 'bi-box-seam', 'label' => 'Preparación');
}
if (has_permission('rutas.ver')) {
    $menuSections['Operación'][] = array('href' => 'routes', 'icon' => 'bi-signpost-split', 'label' => 'Rutas');
}

if (has_permission('clientes.ver')) {
    $menuSections['Datos'][] = array('href' => 'clients', 'icon' => 'bi-people', 'label' => 'Clientes');
}
if (has_permission('productos.ver')) {
    $menuSections['Datos'][] = array('href' => 'products', 'icon' => 'bi-boxes', 'label' => 'Productos');
}
if (has_permission('bodegas.ver')) {
    $menuSections['Datos'][] = array('href' => 'warehouses', 'icon' => 'bi-buildings', 'label' => 'Bodegas');
}
if (has_permission('depositos.ver')) {
    $menuSections['Datos'][] = array('href' => 'deposits', 'icon' => 'bi-cash-stack', 'label' => 'Depósitos');
}

if (has_permission('configuracion.editar')) {
    $menuSections['Catálogos'][] = array('href' => 'catalogs/payment_methods', 'icon' => 'bi-cash-coin', 'label' => 'Formas de pago');
    $menuSections['Catálogos'][] = array('href' => 'catalogs/failure_reasons', 'icon' => 'bi-x-octagon', 'label' => 'Motivos no entrega');
    $menuSections['Catálogos'][] = array('href' => 'catalogs/shifts', 'icon' => 'bi-clock', 'label' => 'Turnos de ruta');
    $menuSections['Catálogos'][] = array('href' => 'catalogs/transports', 'icon' => 'bi-truck', 'label' => 'Transportes encomienda');
}

if (has_permission('reportes.ver')) {
    $menuSections['Reportes'][] = array('href' => 'reports', 'icon' => 'bi-graph-up', 'label' => 'Reportes');
}

if (has_permission('usuarios.ver')) {
    $menuSections['Configuración'][] = array('href' => 'users', 'icon' => 'bi-people', 'label' => 'Usuarios');
}
if (has_permission('auditoria.ver')) {
    $menuSections['Configuración'][] = array('href' => 'audit', 'icon' => 'bi-journal-check', 'label' => 'Auditoría');
}
if (has_permission('configuracion.editar')) {
    $menuSections['Configuración'][] = array('href' => 'settings', 'icon' => 'bi-gear', 'label' => 'Configuracion');
}

$catalogMenu = array();
if (has_permission('configuracion.editar')) {
    $catalogMenu[] = array('href' => 'catalogs/payment_methods', 'icon' => 'bi-cash-coin', 'label' => 'Formas de pago');
    $catalogMenu[] = array('href' => 'catalogs/failure_reasons', 'icon' => 'bi-x-octagon', 'label' => 'Motivos no entrega');
    $catalogMenu[] = array('href' => 'catalogs/shifts', 'icon' => 'bi-clock', 'label' => 'Turnos de ruta');
    $catalogMenu[] = array('href' => 'catalogs/transports', 'icon' => 'bi-truck', 'label' => 'Transportes encomienda');
}

$currentUri = '/' . $this->uri->segment(1);
$landingUrl = default_landing_url();
$canViewDashboard = has_permission('dashboard.ver');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($pageTitle); ?> · <?php echo html_escape($appName); ?></title>
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/img/logo.png'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/datatables/dataTables.bootstrap5.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/select2/select2.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert2/sweetalert2.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/leaflet/leaflet.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/app.css'); ?>">
</head>
<body>
    <div class="sg-layout">
        <aside class="sg-sidebar" id="sgSidebar">
            <a class="sg-sidebar-brand" href="<?php echo $landingUrl; ?>">
                <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="Logo SGMensajeria">
                <div>
                    <strong>SG<em>Mensajeria</em></strong>
                    <small>Sistema de entregas</small>
                </div>
            </a>

            <nav class="sg-sidebar-nav" aria-label="Menu principal">
                <?php if ($canViewDashboard): ?>
                <div class="sg-sidebar-section">General</div>
                <a class="sg-nav-item <?php echo ($currentUri === '/dashboard') ? 'active' : ''; ?>" href="<?php echo base_url('dashboard'); ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <?php endif; ?>
                <?php foreach ($menuSections as $sectionLabel => $items): ?>
                    <?php if (empty($items)): continue; endif; ?>
                    <div class="sg-sidebar-section"><?php echo html_escape($sectionLabel); ?></div>
                    <?php foreach ($items as $item): ?>
                        <a class="sg-nav-item <?php echo ($currentUri === '/' . $item['href']) ? 'active' : ''; ?>" href="<?php echo base_url($item['href']); ?>">
                            <i class="bi <?php echo $item['icon']; ?>"></i> <?php echo html_escape($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <?php if ($this->ion_auth->in_group('mensajero') || $isAdmin): ?>
                    <div class="sg-sidebar-section">Mensajero</div>
                    <a class="sg-nav-item" href="<?php echo base_url('courier'); ?>" target="_blank">
                        <i class="bi bi-phone"></i> Vista mensajero
                    </a>
                    <?php endif; ?>
                </nav>

            <div class="sg-sidebar-foot">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-geo-alt"></i> <?php echo html_escape(isset($settings['company_name']) ? $settings['company_name'] : 'SGMensajeria'); ?>
                </div>
                <div>© <?php echo date('Y'); ?> · Costa Rica</div>
            </div>
        </aside>

        <div class="sg-main">
            <header class="sg-topbar">
                <div class="sg-topbar-inner">
                    <button type="button" class="btn btn-light d-lg-none" data-sidebar-toggle aria-label="Abrir menu">
                        <i class="bi bi-list"></i>
                    </button>

                    <nav class="sg-breadcrumb" aria-label="breadcrumb">
                        <a href="<?php echo $landingUrl; ?>">Inicio</a>
                        <?php if (!empty($breadcrumbs)): ?>
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <i class="bi bi-chevron-right"></i>
                                <?php if (isset($crumb['href']) && $crumb['href']): ?>
                                    <a href="<?php echo html_escape($crumb['href']); ?>"><?php echo html_escape($crumb['label']); ?></a>
                                <?php else: ?>
                                    <strong><?php echo html_escape($crumb['label']); ?></strong>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <i class="bi bi-chevron-right"></i>
                            <strong><?php echo html_escape($pageTitle); ?></strong>
                        <?php endif; ?>
                    </nav>

                    <div class="dropdown">
                        <button type="button" class="sg-user-menu btn btn-link dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="sg-avatar"><?php echo html_escape($userInitial); ?></span>
                            <span class="d-none d-sm-inline"><?php echo html_escape($userName !== '' ? $userName : 'Usuario'); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header"><?php echo html_escape(isset($currentUser) ? $currentUser->email : ''); ?></h6></li>
                            <?php if (isset($country) && $country): ?>
                            <li><h6 class="dropdown-header">
                                    <span class="badge text-bg-dark">
                                        <?php if ($country->code === 'CR'): ?><img src="<?php echo base_url('assets/img/flags/cr.svg'); ?>" alt="" style="width:16px;height:10px;border-radius:2px;object-fit:cover;vertical-align:-1px;" aria-hidden="true"><?php endif; ?>
                                        <?php if ($country->code === 'SV'): ?><img src="<?php echo base_url('assets/img/flags/sv.svg'); ?>" alt="" style="width:16px;height:10px;border-radius:2px;object-fit:cover;vertical-align:-1px;" aria-hidden="true"><?php endif; ?>
                                        <?php echo html_escape($country->name); ?>
                                    </span>
                            </h6></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('auth/change_password'); ?>"><i class="bi bi-shield-lock me-2"></i>Cambiar contraseña</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('auth/logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="sg-content">
