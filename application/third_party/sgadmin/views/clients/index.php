<?php defined('BASEPATH') OR exit('No direct script access allowed');
$table = (object)array(
    'name' => 'Clientes',
    'description' => 'Directorio de clientes, direcciones y tipos de entrega',
    'addLabel' => 'Nuevo cliente',
);
$columns = array(
    array('key' => 'code', 'label' => 'Número', 'type' => 'code'),
    array('key' => 'name', 'label' => 'Cliente'),
    array('key' => 'phone', 'label' => 'Celular'),
    array('key' => 'phone2', 'label' => 'Celular secundario'),
    array('key' => 'zone', 'label' => 'Zona'),
    array('key' => 'address', 'label' => 'Dirección'),
    array('key' => 'delivery_type', 'label' => 'Tipo de entrega'),
    array('key' => 'notes', 'label' => 'Notas'),
    array('key' => 'client_type', 'label' => 'Tipo de cliente', 'type' => 'badge',
        'map' => array('excelente' => 'Excelente', 'bueno' => 'Bueno', 'normal' => 'Normal', 'malo' => 'Malo'),
        'colors' => array('excelente' => 'success', 'bueno' => 'primary', 'normal' => 'secondary', 'malo' => 'danger')),
    array('key' => 'is_active', 'label' => 'Estado', 'type' => 'boolean', 'yes' => 'Activo', 'no' => 'Inactivo'),
);
$formFields = array(
    array('name' => 'name', 'label' => 'Cliente', 'type' => 'text', 'required' => true, 'col' => 12, 'placeholder' => 'Nombre del cliente'),
    array('name' => 'phone', 'label' => 'Número celular', 'type' => 'text', 'col' => 6, 'placeholder' => '8888 8888', 'foreignNumber' => true),
    array('name' => 'phone2', 'label' => 'Celular secundario (opcional)', 'type' => 'text', 'col' => 6, 'placeholder' => '8888 8888', 'foreignNumber' => true),
    array('name' => 'zone', 'label' => 'Zona', 'type' => 'text', 'col' => 6, 'placeholder' => 'Ciudad o departamento'),
    array('name' => 'delivery_type', 'label' => 'Tipo de entrega', 'type' => 'select', 'col' => 6,
        'options' => array(
            array('value' => 'San Salvador', 'label' => 'San Salvador'),
            array('value' => 'Departamental', 'label' => 'Departamental'),
            array('value' => 'domicilio', 'label' => 'Domicilio'),
            array('value' => 'recoger_tienda', 'label' => 'Recoger en tienda'),
            array('value' => 'programada', 'label' => 'Programada'),
            array('value' => 'encomienda', 'label' => 'Encomienda'),
        )),
    array('name' => 'client_type', 'label' => 'Tipo de cliente', 'type' => 'select', 'col' => 6, 'default' => 'normal',
        'options' => array(
            array('value' => 'excelente', 'label' => 'Excelente'),
            array('value' => 'bueno', 'label' => 'Bueno'),
            array('value' => 'normal', 'label' => 'Normal'),
            array('value' => 'malo', 'label' => 'Malo'),
        )),
    array('name' => 'address', 'label' => 'Dirección', 'type' => 'textarea', 'col' => 12),
    array('name' => 'notes', 'label' => 'Notas', 'type' => 'textarea', 'col' => 12),
    array('name' => 'is_active', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 6, 'default' => 1),
);
$saveEndpoint = base_url('clients/save');
$deleteEndpoint = base_url('clients/delete/{id}');
$afterHeaderView = has_permission('clientes.crear') ? 'clients/_import' : null;
$canAdd = has_permission('clientes.crear');
$canEdit = has_permission('clientes.editar');
$canDelete = has_permission('clientes.eliminar');
$deleteOnlyInactive = true;
$this->load->view('catalogs/_catalog', get_defined_vars());
