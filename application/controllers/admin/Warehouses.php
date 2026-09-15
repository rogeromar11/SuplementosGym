<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Catalogo de bodegas (ACME).
 */
class Warehouses extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Warehouse_model');
		$this->load->helper('form');
	}

	public function index()
	{
		$this->require_permission('bodegas.ver');

		$this->data['pageTitle'] = 'Bodegas';
		$this->data['breadcrumbs'] = array(array('label' => 'Bodegas'));
		$this->data['warehouses'] = $this->Warehouse_model->all(array(), 'name', 'ASC');
		$this->data['pageScripts'] = array('assets/js/pages/warehouses.js');

		$this->render('warehouses/index', $this->data);
	}

	public function create()
	{
		$this->require_permission('bodegas.crear');

		$this->data['pageTitle'] = 'Nueva bodega';
		$this->data['breadcrumbs'] = array(
			array('label' => 'Bodegas', 'href' => base_url('warehouses')),
			array('label' => 'Nueva bodega'),
		);
		$this->_save_form(null);
	}

	public function edit($id)
	{
		$this->require_permission('bodegas.editar');

		$warehouse = $this->Warehouse_model->find($id);
		if (!$warehouse) {
			show_404();
		}
		$this->data['pageTitle'] = 'Editar bodega';
		$this->data['breadcrumbs'] = array(
			array('label' => 'Bodegas', 'href' => base_url('warehouses')),
			array('label' => 'Editar bodega'),
		);
		$this->_save_form($warehouse);
	}

	private function _save_form($warehouse)
	{
		$this->load->library('form_validation');

		$this->data['pageScripts'] = array('assets/js/pages/warehouse_form.js');

		if ($this->input->post()) {
			$this->form_validation->set_rules('code', 'Código', 'trim|required|max_length[20]');
			$this->form_validation->set_rules('name', 'Nombre', 'trim|required|max_length[120]');
			$this->form_validation->set_rules('phone', 'Teléfono', 'trim|max_length[30]');

			if ($this->form_validation->run() === TRUE) {
				$data = array(
					'code' => strtoupper($this->input->post('code')),
					'name' => $this->input->post('name'),
					'address' => $this->input->post('address'),
					'phone' => $this->input->post('phone'),
					'latitude' => $this->input->post('latitude') !== '' ? $this->input->post('latitude') : null,
					'longitude' => $this->input->post('longitude') !== '' ? $this->input->post('longitude') : null,
					'directions' => $this->input->post('directions'),
					'is_active' => $this->input->post('is_active') ? 1 : 0,
					'is_default' => 0,
				);

				if ($warehouse) {
					$data['updated_by'] = $this->currentUser->id;
					if (!$this->Warehouse_model->update($warehouse->id, $data)) {
						$this->data['message'] = 'El código de bodega ya existe.';
						return $this->render('warehouses/form', $this->data);
					}
					$id = $warehouse->id;
					$this->audit_service->log('warehouse.update', 'bodegas', 'warehouses', $id);
					$flash = 'Bodega actualizada correctamente.';
				} else {
					$data['created_by'] = $this->currentUser->id;
					$id = $this->Warehouse_model->insert($data);
					if (!$id) {
						$this->data['message'] = 'El código de bodega ya existe.';
						return $this->render('warehouses/form', $this->data);
					}
					$this->audit_service->log('warehouse.create', 'bodegas', 'warehouses', $id);
					$flash = 'Bodega creada correctamente.';
				}

				if ($this->input->post('set_default')) {
					$this->Warehouse_model->set_default($id);
				}

				$this->session->set_flashdata('success', true);
				$this->session->set_flashdata('message', $flash);
				redirect('warehouses');
			}
		}

		$this->data['warehouse'] = $warehouse;
		$this->render('warehouses/form', $this->data);
	}

	public function set_default($id)
	{
		$this->require_permission('bodegas.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		if ($this->Warehouse_model->set_default($id)) {
			$this->audit_service->log('warehouse.set_default', 'bodegas', 'warehouses', $id);
			$this->json_response(true, 'Bodega predeterminada actualizada.');
		} else {
			$this->json_response(false, 'No fue posible actualizar la bodega predeterminada.');
		}
	}

	public function delete($id)
	{
		$this->require_permission('bodegas.eliminar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$used = $this->db->where('warehouse_id', $id)->count_all_results('store_orders');
		if ($used > 0) {
			$this->json_response(false, 'La bodega tiene pedidos asociados; solo se desactiva (baja lógica).');
			return;
		}

		if ($this->Warehouse_model->delete($id)) {
			$this->audit_service->log('warehouse.delete', 'bodegas', 'warehouses', $id);
			$this->json_response(true, 'Bodega desactivada.');
		} else {
			$this->json_response(false, 'No fue posible eliminar la bodega.');
		}
	}
}
