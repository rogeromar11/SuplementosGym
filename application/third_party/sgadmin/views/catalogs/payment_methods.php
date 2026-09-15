<?php defined('BASEPATH') OR exit('No direct script access allowed');
$table = (object)array(
    'name' => 'Formas de pago',
    'description' => 'Metodos disponibles para registrar pagos',
    'addLabel' => 'Nueva forma de pago',
);
$columns = array(
    array('key' => 'code', 'label' => 'Código', 'type' => 'code'),
    array('key' => 'name', 'label' => 'Nombre'),
    array('key' => 'sort_order', 'label' => 'Orden'),
    array('key' => 'is_active', 'label' => 'Estado', 'type' => 'boolean', 'yes' => 'Activa', 'no' => 'Inactiva'),
);
$formFields = array(
    array('name' => 'code', 'label' => 'Código', 'type' => 'text', 'required' => true, 'col' => 6, 'placeholder' => 'efectivo'),
    array('name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'col' => 6, 'placeholder' => 'Efectivo'),
    array('name' => 'sort_order', 'label' => 'Orden', 'type' => 'number', 'col' => 6, 'default' => 0),
    array('name' => 'is_active', 'label' => 'Activa', 'type' => 'checkbox', 'col' => 6, 'default' => 1),
);
$saveEndpoint = base_url('catalogs/payment_method_save');
$deleteEndpoint = base_url('catalogs/payment_method_delete/{id}');
$this->load->view('catalogs/_catalog', get_defined_vars());
