<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Administracion de catalogos operativos:
 * formas de pago, motivos de no entrega y turnos de ruta.
 */
class Catalogs extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_permission('configuracion.editar');
	}

	// ------------------------------------------------------------------
	// Formas de pago
	// ------------------------------------------------------------------
	public function payment_methods()
	{
		$this->data['pageTitle'] = 'Formas de pago';
		$this->data['breadcrumbs'] = array(array('label' => 'Formas de pago'));
		$this->data['items'] = $this->db->where('country_id', current_country_id())->order_by('sort_order', 'ASC')->get('payment_methods')->result();
		$this->data['pageScripts'] = array('assets/js/pages/catalogs.js');
		$this->render('catalogs/payment_methods', $this->data);
	}

	public function payment_method_save()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$id = (int)$this->input->post('id');
		$data = array(
			'code' => strtolower(trim($this->input->post('code'))),
			'name' => trim($this->input->post('name')),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'sort_order' => (int)$this->input->post('sort_order'),
		);
		if ($data['code'] === '' || $data['name'] === '') {
			$this->json_response(false, 'Código y nombre son obligatorios.');
			return;
		}
		if ($id) {
			$this->db->where('id', $id)->where('country_id', current_country_id())->update('payment_methods', $data);
			$this->audit_service->log('catalog.payment_method.update', 'configuracion', 'payment_methods', $id);
		} else {
			if ($this->db->where('country_id', current_country_id())->where('code', $data['code'])->count_all_results('payment_methods') > 0) {
				$this->json_response(false, 'El código ya existe.');
				return;
			}
			$data['country_id'] = current_country_id();
	
			$this->db->insert('payment_methods', $data);
			$id = $this->db->insert_id();
			$this->audit_service->log('catalog.payment_method.create', 'configuracion', 'payment_methods', $id);
		}
		$this->json_response(true, 'Forma de pago guardada.', array('id' => $id));
	}

	public function payment_method_delete($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$used = $this->db->where('payment_method_id', $id)->count_all_results('payments') + $this->db->where('payment_method_id', $id)->count_all_results('store_orders');
		if ($used > 0) {
			$this->json_response(false, 'La forma de pago está en uso; solo se desactiva.');
			return;
		}
		$this->db->where('id', $id)->where('country_id', current_country_id())->delete('payment_methods');
		$this->audit_service->log('catalog.payment_method.delete', 'configuracion', 'payment_methods', $id);
		$this->json_response(true, 'Forma de pago eliminada.');
	}

	// ------------------------------------------------------------------
	// Motivos de no entrega
	// ------------------------------------------------------------------
	public function failure_reasons()
	{
		$this->data['pageTitle'] = 'Motivos de no entrega';
		$this->data['breadcrumbs'] = array(array('label' => 'Motivos de no entrega'));
		$this->data['items'] = $this->db->where('country_id', current_country_id())->order_by('sort_order', 'ASC')->get('delivery_failure_reasons')->result();
		$this->data['pageScripts'] = array('assets/js/pages/catalogs.js');
		$this->render('catalogs/failure_reasons', $this->data);
	}

	public function failure_reason_save()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$id = (int)$this->input->post('id');
		$data = array(
			'code' => strtolower(trim($this->input->post('code'))),
			'name' => trim($this->input->post('name')),
			'requires_description' => $this->input->post('requires_description') ? 1 : 0,
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'sort_order' => (int)$this->input->post('sort_order'),
		);
		if ($data['code'] === '' || $data['name'] === '') {
			$this->json_response(false, 'Código y nombre son obligatorios.');
			return;
		}
		if ($id) {
			$this->db->where('id', $id)->where('country_id', current_country_id())->update('delivery_failure_reasons', $data);
			$this->audit_service->log('catalog.failure_reason.update', 'configuracion', 'delivery_failure_reasons', $id);
		} else {
			if ($this->db->where('country_id', current_country_id())->where('code', $data['code'])->count_all_results('delivery_failure_reasons') > 0) {
				$this->json_response(false, 'El código ya existe.');
				return;
			}
			$data['country_id'] = current_country_id();
	
			$this->db->insert('delivery_failure_reasons', $data);
			$id = $this->db->insert_id();
			$this->audit_service->log('catalog.failure_reason.create', 'configuracion', 'delivery_failure_reasons', $id);
		}
		$this->json_response(true, 'Motivo guardado.', array('id' => $id));
	}

	public function failure_reason_delete($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$used = $this->db->where('failure_reason_id', $id)->count_all_results('delivery_attempts');
		if ($used > 0) {
			$this->json_response(false, 'El motivo está en uso; solo se desactiva.');
			return;
		}
		$this->db->where('id', $id)->where('country_id', current_country_id())->delete('delivery_failure_reasons');
		$this->audit_service->log('catalog.failure_reason.delete', 'configuracion', 'delivery_failure_reasons', $id);
		$this->json_response(true, 'Motivo eliminado.');
	}

	// ------------------------------------------------------------------
	// Transportes de encomienda
	// ------------------------------------------------------------------
	public function transports()
	{
		$this->data['pageTitle'] = 'Transportes de encomienda';
		$this->data['breadcrumbs'] = array(array('label' => 'Transportes de encomienda'));
		$this->data['items'] = $this->db->where('country_id', current_country_id())->order_by('sort_order', 'ASC')->get('transports')->result();
		$this->data['pageScripts'] = array('assets/js/pages/catalogs.js');
		$this->render('catalogs/transports', $this->data);
	}

	public function transport_save()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$id = (int)$this->input->post('id');
		$data = array(
			'code' => strtolower(trim($this->input->post('code'))),
			'name' => trim($this->input->post('name')),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'sort_order' => (int)$this->input->post('sort_order'),
		);
		if ($data['code'] === '' || $data['name'] === '') {
			$this->json_response(false, 'Código y nombre son obligatorios.');
			return;
		}
		if ($id) {
			$this->db->where('id', $id)->where('country_id', current_country_id())->update('transports', $data);
			$this->audit_service->log('catalog.transport.update', 'configuracion', 'transports', $id);
		} else {
			if ($this->db->where('country_id', current_country_id())->where('code', $data['code'])->count_all_results('transports') > 0) {
				$this->json_response(false, 'El código ya existe.');
				return;
			}
			$data['country_id'] = current_country_id();

			$this->db->insert('transports', $data);
			$id = $this->db->insert_id();
			$this->audit_service->log('catalog.transport.create', 'configuracion', 'transports', $id);
		}
		$this->json_response(true, 'Transporte guardado.', array('id' => $id));
	}

	public function transport_delete($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$used = $this->db->where('transport_id', $id)->count_all_results('store_orders');
		if ($used > 0) {
			$this->json_response(false, 'El transporte está en uso; solo se desactiva.');
			return;
		}
		$this->db->where('id', $id)->where('country_id', current_country_id())->delete('transports');
		$this->audit_service->log('catalog.transport.delete', 'configuracion', 'transports', $id);
		$this->json_response(true, 'Transporte eliminado.');
	}

	// ------------------------------------------------------------------
	// Turnos
	// ------------------------------------------------------------------
	public function shifts()
	{
		$this->data['pageTitle'] = 'Turnos de ruta';
		$this->data['breadcrumbs'] = array(array('label' => 'Turnos de ruta'));
		$this->data['items'] = $this->db->where('country_id', current_country_id())->order_by('sort_order', 'ASC')->get('route_shifts')->result();
		$this->data['pageScripts'] = array('assets/js/pages/catalogs.js');
		$this->render('catalogs/shifts', $this->data);
	}

	public function shift_save()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$id = (int)$this->input->post('id');
		$data = array(
			'code' => strtolower(trim($this->input->post('code'))),
			'name' => trim($this->input->post('name')),
			'start_time' => $this->input->post('start_time') ?: null,
			'end_time' => $this->input->post('end_time') ?: null,
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'sort_order' => (int)$this->input->post('sort_order'),
		);
		if ($data['code'] === '' || $data['name'] === '') {
			$this->json_response(false, 'Código y nombre son obligatorios.');
			return;
		}
		if ($id) {
			$this->db->where('id', $id)->where('country_id', current_country_id())->update('route_shifts', $data);
			$this->audit_service->log('catalog.shift.update', 'configuracion', 'route_shifts', $id);
		} else {
			if ($this->db->where('country_id', current_country_id())->where('code', $data['code'])->count_all_results('route_shifts') > 0) {
				$this->json_response(false, 'El código ya existe.');
				return;
			}
			$data['country_id'] = current_country_id();
	
			$this->db->insert('route_shifts', $data);
			$id = $this->db->insert_id();
			$this->audit_service->log('catalog.shift.create', 'configuracion', 'route_shifts', $id);
		}
		$this->json_response(true, 'Turno guardado.', array('id' => $id));
	}

	public function shift_delete($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$used = $this->db->where('shift_id', $id)->count_all_results('routes');
		if ($used > 0) {
			$this->json_response(false, 'El turno está en uso; solo se desactiva.');
			return;
		}
		$this->db->where('id', $id)->where('country_id', current_country_id())->delete('route_shifts');
		$this->audit_service->log('catalog.shift.delete', 'configuracion', 'route_shifts', $id);
		$this->json_response(true, 'Turno eliminado.');
	}
}




