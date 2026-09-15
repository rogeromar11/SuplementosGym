<?php defined('BASEPATH') OR exit('No direct script access allowed');
$table = (object)array(
    'name' => 'Transportes de encomienda',
    'description' => 'Medios de transporte usados para encomiendas (se indica al planear la ruta)',
    'addLabel' => 'Nuevo transporte',
);
$columns = array(
    array('key' => 'code', 'label' => 'Código', 'type' => 'code'),
    array('key' => 'name', 'label' => 'Nombre'),
    array('key' => 'sort_order', 'label' => 'Orden'),
    array('key' => 'is_active', 'label' => 'Estado', 'type' => 'boolean', 'yes' => 'Activo', 'no' => 'Inactivo'),
);
$formFields = array(
    array('name' => 'code', 'label' => 'Código', 'type' => 'text', 'required' => true, 'col' => 6, 'placeholder' => 'taxi'),
    array('name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'col' => 6, 'placeholder' => 'Taxi'),
    array('name' => 'sort_order', 'label' => 'Orden', 'type' => 'number', 'col' => 6, 'default' => 0),
    array('name' => 'is_active', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 6, 'default' => 1),
);
$saveEndpoint = base_url('catalogs/transport_save');
$deleteEndpoint = base_url('catalogs/transport_delete/{id}');
$this->load->view('catalogs/_catalog', get_defined_vars());
