<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vista movil del mensajero.
 */
class Courier extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Route_model');
		$this->load->library(array('Delivery_service', 'Map_link_parser'));
		$this->load->helper('form');

		$isCourier = $this->ion_auth->in_group('mensajero');
		$isAdmin = $this->ion_auth->is_admin();
		if (!$isCourier && !$isAdmin) {
			$this->deny_access();
		}
		$this->data['isCourierPreview'] = !$isCourier;
	}

	private function courier_user_id()
	{
		if ($this->ion_auth->in_group('mensajero')) {
			return (int)$this->ion_auth->get_user_id();
		}
		return null; // admin puede ver rutas de todos
	}

	public function index()
	{
		$this->data['pageTitle'] = 'Mis rutas';
		$today = date('Y-m-d');
		$this->data['today'] = $today;

		$courierId = $this->courier_user_id();
		if ($courierId) {
			$this->data['routes'] = $this->Route_model->for_courier($courierId, $today);

			// Rutas de dias anteriores sin finalizar (olvidadas)
			$this->data['staleRoutes'] = $this->db->select('r.id, r.route_number, r.route_date, r.status, s.name AS shift_name, w.name AS warehouse_name', false)
				->from('routes r')
				->join('route_shifts s', 's.id = r.shift_id', 'left')
				->join('warehouses w', 'w.id = r.warehouse_id', 'left')
				->where('r.courier_user_id', $courierId)
				->where('r.country_id', current_country_id())
				->where('r.route_date <', $today)
				->where_in('r.status', array('en_progreso', 'planificada'))
				->order_by('r.route_date', 'ASC')
				->get()->result();
		} else {
			$this->data['routes'] = $this->db->select('r.*, s.name AS shift_name, s.code AS shift_code, w.name AS warehouse_name, w.address AS warehouse_address, u.first_name AS courier_first_name, u.last_name AS courier_last_name')
				->from('routes r')
				->join('route_shifts s', 's.id = r.shift_id', 'left')
				->join('warehouses w', 'w.id = r.warehouse_id', 'left')
				->join('users u', 'u.id = r.courier_user_id', 'left')
				->where('r.route_date', $today)
				->where('r.country_id', current_country_id())
				->where_in('r.status', array('planificada', 'en_progreso'))
				->order_by('s.sort_order', 'ASC')
				->get()->result();
			$this->data['staleRoutes'] = array();
		}

		$this->render_courier('courier/index', $this->data);
	}

	public function route($routeId, $action = null)
	{
		if ($action === 'start' || $action === 'finish') {
			if (!$this->input->is_ajax_request()) {
				show_404();
			}
			$result = $action === 'start'
				? $this->route_service()->start_route((int)$routeId, (int)$this->courier_user_id())
				: $this->route_service()->finish_route((int)$routeId, (int)$this->courier_user_id(), $this->ion_auth->is_admin());
			$message = $action === 'start' ? 'Ruta iniciada.' : 'Ruta finalizada.';
			if ($action === 'finish' && $result['success'] && !empty($result['released_orders'])) {
				$message = 'Ruta finalizada. ' . (int)$result['released_orders'] . ' pedido(s) sin entregar quedaron disponibles para reprogramar.';
			}
			$this->json_response($result['success'], $result['error'] ?: $message);
			return;
		}

		$courierId = $this->courier_user_id();

		$this->db->select('r.*, s.name AS shift_name, s.code AS shift_code, w.name AS warehouse_name, w.address AS warehouse_address, w.latitude AS warehouse_lat, w.longitude AS warehouse_lng, u.first_name AS courier_first_name, u.last_name AS courier_last_name')
			->from('routes r')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('warehouses w', 'w.id = r.warehouse_id', 'left')
			->join('users u', 'u.id = r.courier_user_id', 'left')
			->where('r.id', $routeId);
		if ($courierId) {
			$this->db->where('r.courier_user_id', $courierId);
		}
		$this->db->where('r.country_id', current_country_id());
		$route = $this->db->get()->row();
		if (!$route) {
			show_404();
		}

		$this->data['pageTitle'] = 'Ruta ' . $route->route_number;
		$this->data['route'] = $route;
		$this->data['routeOrders'] = $this->Route_model->orders_for_route($routeId);
		$this->data['summary'] = $this->Route_model->summary($routeId);
		$this->data['paymentMethods'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('payment_methods')->result();
		$this->data['failureReasons'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('delivery_failure_reasons')->result();
		$this->data['pageScripts'] = array('assets/js/pages/courier.js');

		$this->render_courier('courier/route', $this->data);
	}

	private function route_service()
	{
		$ci =& get_instance();
		if (!isset($ci->route_service)) {
			$ci->load->library('Route_service');
		}
		return $ci->route_service;
	}

	public function wa($orderId)
	{
		$order = $this->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order) {
			show_404();
		}
		$msg = 'Hola, le contactamos por la entrega del pedido ' . $order->order_number . '.';
		redirect($this->map_link_parser->whatsapp_link($order->customer_phone_whatsapp ?: $order->customer_phone, $msg));
	}

	public function tel($orderId)
	{
		$order = $this->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order) {
			show_404();
		}
		// Los enlaces tel: no pasan por redirect() de CI (los convierte en ruta relativa)
		$url = $this->map_link_parser->tel_link($order->customer_phone_whatsapp ?: $order->customer_phone);
		header('Location: ' . $url);
		exit;
	}

	public function wa2($orderId)
	{
		$order = $this->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order || !$order->customer_phone2_whatsapp) {
			show_404();
		}
		$msg = 'Hola, le contactamos por la entrega del pedido ' . $order->order_number . '.';
		redirect($this->map_link_parser->whatsapp_link($order->customer_phone2_whatsapp ?: $order->customer_phone2, $msg));
	}

	public function tel2($orderId)
	{
		$order = $this->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order || !$order->customer_phone2_whatsapp) {
			show_404();
		}
		$url = $this->map_link_parser->tel_link($order->customer_phone2_whatsapp ?: $order->customer_phone2);
		header('Location: ' . $url);
		exit;
	}

	public function nav($routeOrderId)
	{
		$ro = $this->db->select('ro.*, o.map_url, o.delivery_address, o.latitude, o.longitude')
			->from('route_orders ro')
			->join('store_orders o', 'o.id = ro.order_id', 'inner')
			->where('ro.id', $routeOrderId)->get()->row();
		if (!$ro) {
			show_404();
		}
		$url = $this->map_link_parser->waze_link($ro->latitude, $ro->longitude, $ro->delivery_address);
		redirect($url);
	}

	public function start_delivery()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$courierId = $this->courier_user_id();
		if (!$courierId) {
			$this->json_response(false, 'Accion solo para mensajeros.', null, null, 403);
			return;
		}
		$result = $this->delivery_service->start_delivery((int)$this->input->post('route_order_id'), $courierId);
		$this->json_response($result['success'], $result['error'] ?: 'Entrega en proceso.');
	}

	public function deliver($routeOrderId)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$courierId = $this->courier_user_id();
		if (!$courierId) {
			$this->json_response(false, 'Accion solo para mensajeros.', null, null, 403);
			return;
		}

		$payment = array(
			'payment_method_id' => $this->input->post('payment_method_id'),
			'amount' => $this->input->post('amount'),
			'notes' => $this->input->post('notes'),
			'received_by_name' => $this->input->post('received_by_name'),
			'latitude' => $this->input->post('latitude'),
			'longitude' => $this->input->post('longitude'),
			'idempotency_key' => $this->input->post('idempotency_key'),
		);

		$evidence = isset($_FILES['evidence']) ? $_FILES['evidence'] : null;
		$result = $this->delivery_service->deliver((int)$routeOrderId, $courierId, $payment, $evidence);
		$this->json_response($result['success'], $result['error'] ?: 'Entrega registrada exitosamente.');
	}

	public function not_delivered($routeOrderId)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$courierId = $this->courier_user_id();
		if (!$courierId) {
			$this->json_response(false, 'Accion solo para mensajeros.', null, null, 403);
			return;
		}

		$data = array(
			'failure_reason_id' => $this->input->post('failure_reason_id'),
			'failure_other' => $this->input->post('failure_other'),
			'notes' => $this->input->post('notes'),
			'reschedule_requested' => $this->input->post('reschedule_requested'),
			'rescheduled_date' => $this->input->post('rescheduled_date'),
			'latitude' => $this->input->post('latitude'),
			'longitude' => $this->input->post('longitude'),
		);

		$evidence = isset($_FILES['evidence']) ? $_FILES['evidence'] : null;
		$result = $this->delivery_service->not_delivered((int)$routeOrderId, $courierId, $data, $evidence);
		$this->json_response($result['success'], $result['error'] ?: 'Entrega registrada como no realizada.');
	}
}



