<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Gestion de rutas diarias.
 */
class Routes extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Route_model');
		$this->load->model('Warehouse_model');
		$this->load->model('User_model');
		$this->load->library('Route_service');
		$this->load->helper('form');
	}

	public function index()
	{
		$this->require_permission('rutas.ver');

		$date = $this->input->get('date') ?: date('Y-m-d');
		$f = array(
			'route_date' => $date,
			'shift_id' => $this->input->get('shift_id'),
			'warehouse_id' => $this->input->get('warehouse_id'),
			'courier_user_id' => $this->input->get('courier_user_id'),
			'status' => $this->input->get('status'),
		);

		$this->data['pageTitle'] = 'Rutas';
		$this->data['breadcrumbs'] = array(array('label' => 'Rutas'));
		$this->data['routes'] = $this->Route_model->search($f);
		$this->data['date'] = $date;

		// Rutas de dias anteriores que quedaron sin finalizar (en progreso)
		// o que nunca se iniciaron (planificadas)
		$this->data['staleRoutes'] = $this->db->select('r.id, r.route_number, r.route_date, r.status, r.started_at,
				CONCAT(u.first_name, " ", u.last_name) AS courier_name,
				(SELECT COUNT(*) FROM route_orders ro WHERE ro.route_id = r.id AND ro.active_key = 1 AND ro.route_status != "entregado") AS pending_orders', false)
			->from('routes r')
			->join('users u', 'u.id = r.courier_user_id', 'left')
			->where('r.country_id', current_country_id())
			->where('r.route_date <', date('Y-m-d'))
			->where_in('r.status', array('en_progreso', 'planificada'))
			->order_by('r.route_date', 'ASC')
			->get()->result();

		$this->data['shifts'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('route_shifts')->result();
		$this->data['warehouses'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('name')->get('warehouses')->result();
		$this->data['couriers'] = $this->User_model->by_group('mensajero');
		$this->data['pageScripts'] = array('assets/js/pages/routes.js');

		$this->render('routes/index', $this->data);
	}

	public function create()
	{
		$this->require_permission('rutas.crear');

		$this->data['pageTitle'] = 'Nueva ruta';
		$this->data['breadcrumbs'] = array(
			array('label' => 'Rutas', 'href' => base_url('routes')),
			array('label' => 'Nueva ruta'),
		);
		$this->data['shifts'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('route_shifts')->result();
		$this->data['warehouses'] = $this->Warehouse_model->options();
		$this->data['couriers'] = $this->User_model->by_group('mensajero');

		$this->load->library('form_validation');

		if ($this->input->post()) {
			$this->form_validation->set_rules('route_date', 'Fecha', 'required');
			$this->form_validation->set_rules('shift_id', 'Turno', 'required|integer');
			$this->form_validation->set_rules('warehouse_id', 'Bodega', 'integer');
			$this->form_validation->set_rules('courier_user_id', 'Mensajero', 'required|integer');

			if ($this->form_validation->run() === TRUE) {
				$shift = $this->db->where('id', $this->input->post('shift_id'))->where('country_id', current_country_id())->get('route_shifts')->row();
				$data = array(
					'route_number' => $this->route_service->next_route_number($this->input->post('route_date'), $shift ? $shift->code : 'MA'),
					'route_date' => $this->input->post('route_date'),
					'shift_id' => $this->input->post('shift_id'),
					'warehouse_id' => $this->input->post('warehouse_id') ?: null,
					'courier_user_id' => $this->input->post('courier_user_id') ?: null,
					'notes' => $this->input->post('notes'),
					'status' => $this->input->post('status') ?: 'planificada',
				);

				$result = $this->route_service->create($data, $this->currentUser->id);
				if ($result['success']) {
					$this->session->set_flashdata('success', true);
					$this->session->set_flashdata('message', 'Ruta ' . $data['route_number'] . ' creada.');
					redirect('routes/detail/' . $result['id']);
				}
				$this->data['message'] = $result['error'];
			}
		}

		$this->render('routes/form', $this->data);
	}

	public function detail($id)
	{
		$this->require_permission('rutas.ver');

		$route = $this->db->select('r.*, s.name AS shift_name, s.code AS shift_code, w.name AS warehouse_name, w.address AS warehouse_address, w.latitude AS warehouse_lat, w.longitude AS warehouse_lng, u.first_name AS courier_first_name, u.last_name AS courier_last_name')
			->from('routes r')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('warehouses w', 'w.id = r.warehouse_id', 'left')
			->join('users u', 'u.id = r.courier_user_id', 'left')
			->where('r.id', $id)->where('r.country_id', current_country_id())->get()->row();
		if (!$route) {
			show_404();
		}

		$this->data['pageTitle'] = 'Ruta ' . $route->route_number;
		$this->data['breadcrumbs'] = array(
			array('label' => 'Rutas', 'href' => base_url('routes')),
			array('label' => $route->route_number),
		);
		$this->data['route'] = $route;
		$this->data['routeOrders'] = $this->Route_model->orders_for_route($id);
		$this->data['summary'] = $this->Route_model->summary($id);
		$this->data['availableOrders'] = $this->Route_model->available_orders($route->warehouse_id, $route->route_date);
		$this->data['releasedOrders'] = ($route->status === 'finalizada') ? $this->Route_model->released_orders($id) : array();
		$this->data['sameDayRoutes'] = $this->db->select('id, route_number, shift_id, status')
			->where('country_id', current_country_id())
			->where('route_date', $route->route_date)->where('id !=', $route->id)
			->where_in('status', array('borrador', 'planificada', 'en_progreso'))
			->get('routes')->result();
		$this->data['transports'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('transports')->result();
		$this->data['pageScripts'] = array('assets/js/pages/routes.js');

		$this->render('routes/detail', $this->data);
	}

	public function add_order()
	{
		$this->require_permission('rutas.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$result = $this->route_service->add_order((int)$this->input->post('route_id'), (int)$this->input->post('order_id'), $this->currentUser->id, $this->input->post('transport_id') ? (int)$this->input->post('transport_id') : null);
		$this->json_response($result['success'], $result['error'] ?: 'Pedido asignado a la ruta.');
	}

	public function remove_order()
	{
		$this->require_permission('rutas.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$result = $this->route_service->remove_order((int)$this->input->post('route_id'), (int)$this->input->post('order_id'), $this->currentUser->id);
		$this->json_response($result['success'], $result['error'] ?: 'Pedido retirado de la ruta.');
	}

	public function reorder()
	{
		$this->require_permission('rutas.reordenar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$orderIds = (array)$this->input->post('order_ids');
		$ok = $this->route_service->reorder((int)$this->input->post('route_id'), $orderIds, $this->currentUser->id);
		$this->json_response($ok, $ok ? 'Orden de la ruta actualizado.' : 'No fue posible guardar el orden.');
	}

	public function transfer()
	{
		$this->require_permission('rutas.transferir_pedido');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$result = $this->route_service->transfer_order(
			(int)$this->input->post('order_id'),
			(int)$this->input->post('from_route_id'),
			(int)$this->input->post('to_route_id'),
			$this->currentUser->id,
			$this->input->post('notes')
		);
		$this->json_response($result['success'], $result['error'] ?: 'Pedido transferido correctamente.');
	}

	public function start($id)
	{
		$this->require_permission('rutas.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$result = $this->route_service->start_route((int)$id, $this->currentUser->id);
		$this->json_response($result['success'], $result['error'] ?: 'Ruta iniciada.');
	}

	public function delete($id)
	{
		$this->require_permission('rutas.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$result = $this->route_service->delete_route((int)$id, $this->currentUser->id);
		$this->json_response($result['success'], $result['error'] ?: 'Ruta eliminada.');
	}
}


