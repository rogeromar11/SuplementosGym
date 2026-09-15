<?php defined('BASEPATH') OR exit('No direct script access allowed');
$table = (object)array(
    'name' => 'Motivos de no entrega',
    'description' => 'Razones para registrar una entrega fallida',
    'addLabel' => 'Nuevo motivo',
);
$columns = array(
    array('key' => 'code', 'label' => 'Código', 'type' => 'code'),
    array('key' => 'name', 'label' => 'Motivo'),
    array('key' => 'requires_description', 'label' => 'Requiere detalle', 'type' => 'boolean', 'yes' => 'Si', 'no' => 'No'),
    array('key' => 'sort_order', 'label' => 'Orden'),
    array('key' => 'is_active', 'label' => 'Estado', 'type' => 'boolean', 'yes' => 'Activo', 'no' => 'Inactivo'),
);
$formFields = array(
    array('name' => 'code', 'label' => 'Código', 'type' => 'text', 'required' => true, 'col' => 6, 'placeholder' => 'cliente_ausente'),
    array('name' => 'name', 'label' => 'Motivo', 'type' => 'text', 'required' => true, 'col' => 6, 'placeholder' => 'Cliente ausente'),
    array('name' => 'requires_description', 'label' => 'Requiere descripción obligatoria', 'type' => 'checkbox', 'col' => 6),
    array('name' => 'sort_order', 'label' => 'Orden', 'type' => 'number', 'col' => 6, 'default' => 0),
    array('name' => 'is_active', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 6, 'default' => 1),
);
$saveEndpoint = base_url('catalogs/failure_reason_save');
$deleteEndpoint = base_url('catalogs/failure_reason_delete/{id}');
$this->load->view('catalogs/_catalog', get_defined_vars());
