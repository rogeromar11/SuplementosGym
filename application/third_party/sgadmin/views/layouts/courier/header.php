<?php defined('BASEPATH') OR exit('No direct script access allowed');
$pageTitle = isset($pageTitle) ? $pageTitle : 'Mensajero';
$isPreview = isset($isCourierPreview) ? $isCourierPreview : false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo html_escape($pageTitle); ?> · <?php echo html_escape($appName); ?></title>
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/img/logo.png'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert2/sweetalert2.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/app.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/courier.css'); ?>">
</head>
<body class="courier-app">
    <header class="courier-topbar">
        <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="Logo SGMensajeria">
        <div class="titles">
            <strong>SG<em>Mensajeria</em></strong>
            <small><?php echo html_escape($pageTitle); ?></small>
        </div>
        <?php if ($isPreview): ?>
            <span class="badge text-bg-warning">Vista previa</span>
        <?php endif; ?>
        <?php if (isset($country) && $country): ?>
            <span class="badge text-bg-light text-dark" style="font-size:0.72rem;">
                <?php if ($country->code === 'CR'): ?><img src="<?php echo base_url('assets/img/flags/cr.svg'); ?>" alt="" style="width:18px;height:11px;border-radius:2px;object-fit:cover;vertical-align:-1px;" aria-hidden="true"><?php endif; ?>
                <?php if ($country->code === 'SV'): ?><img src="<?php echo base_url('assets/img/flags/sv.svg'); ?>" alt="" style="width:18px;height:11px;border-radius:2px;object-fit:cover;vertical-align:-1px;" aria-hidden="true"><?php endif; ?>
                <?php echo html_escape($country->name); ?>
            </span>
        <?php endif; ?>
        <?php if ($isPreview): ?>
            <a href="<?php echo default_landing_url(); ?>" class="btn btn-sm btn-outline-light" aria-label="Volver al panel" title="Volver al panel"><i class="bi bi-arrow-left me-1"></i><span class="d-none d-sm-inline">Volver al panel</span></a>
        <?php endif; ?>
        <a href="<?php echo base_url('auth/logout'); ?>" class="btn btn-sm btn-outline-light" aria-label="Cerrar sesión"><i class="bi bi-box-arrow-right"></i></a>
    </header>
    <div class="courier-wrap">
        <main class="courier-content">
