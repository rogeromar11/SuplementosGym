<?php defined('BASEPATH') OR exit('No direct script access allowed');
$pageTitle = isset($pageTitle) ? $pageTitle : $this->data['appName'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo html_escape($pageTitle); ?> · <?php echo html_escape($appName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/print.css'); ?>">
</head>
<body>
