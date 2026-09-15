<?php defined('BASEPATH') OR exit('No direct script access allowed');
$table = (object)array(
    'name' => 'Turnos de ruta',
    'description' => 'Turnos disponibles para las rutas diarias',
    'addLabel' => 'Nuevo turno',
);
$columns = array(
    array('key' => 'code', 'label' => 'Código', 'type' => 'code'),
    array('key' => 'name', 'label' => 'Nombre'),
    array('key' => 'start_time', 'label' => 'Inicio'),
    array('key' => 'end_time', 'label' => 'Fin'),
    array('key' => 'sort_order', 'label' => 'Orden'),
    array('key' => 'is_active', 'label' => 'Estado', 'type' => 'boolean', 'yes' => 'Activo', 'no' => 'Inactivo'),
);
$formFields = array(
    array('name' => 'code', 'label' => 'Código', 'type' => 'text', 'required' => true, 'col' => 6, 'placeholder' => 'manana'),
    array('name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'col' => 6, 'placeholder' => 'Manana'),
    array('name' => 'start_time', 'label' => 'Hora inicio', 'type' => 'time', 'col' => 6),
    array('name' => 'end_time', 'label' => 'Hora fin', 'type' => 'time', 'col' => 6),
    array('name' => 'sort_order', 'label' => 'Orden', 'type' => 'number', 'col' => 6, 'default' => 0),
    array('name' => 'is_active', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 6, 'default' => 1),
);
$saveEndpoint = base_url('catalogs/shift_save');
$deleteEndpoint = base_url('catalogs/shift_delete/{id}');
$this->load->view('catalogs/_catalog', get_defined_vars());
