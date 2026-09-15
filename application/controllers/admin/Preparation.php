<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Preparación de pedidos en bodega.
 */
class Preparation extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_permission('pedidos.preparar');
		$this->load->library('Order_service');
		$this->load->model('Order_model');
		$this->load->helper('form');
	}

	public function index()
	{
		$warehouseId = $this->input->get('warehouse_id');
		$date = $this->input->get('date') ?: date('Y-m-d');
		$status = $this->input->get('status');
		$search = $this->input->get('search');

		$this->data['pageTitle'] = 'Preparación';
		$this->data['breadcrumbs'] = array(array('label' => 'Preparación'));

		$pendingStatuses = array('confirmado', 'registrado', 'pendiente_preparacion', 'en_preparacion');

		$this->db->select('o.*, w.name AS warehouse_name')
			->from('store_orders o')
			->join('warehouses w', 'w.id = o.warehouse_id', 'left');
		$this->db->where('o.country_id', current_country_id());
		$this->db->group_start();
		$this->db->where_in('o.status', array('confirmado', 'registrado', 'pendiente_preparacion', 'en_preparacion'));
		$this->db->or_group_start()->where('o.status', 'preparado')->where('DATE(o.created_at)', $date)->group_end();
		$this->db->group_end();
		if ($warehouseId) {
			$this->db->where('o.warehouse_id', $warehouseId);
		}
		if ($search) {
			$this->db->group_start()->like('o.order_number', $search)->or_like('o.customer_name', $search)->group_end();
		}
		$this->db->order_by('FIELD(o.status, "en_preparacion", "pendiente_preparacion", "registrado", "preparado")', '', false)
			->order_by('o.created_at', 'ASC');
		$this->data['queue'] = $this->db->get()->result();

		$this->data['counters'] = array(
			'pending' => $this->Order_model->count_by_status('pendiente_preparacion') + $this->Order_model->count_by_status('registrado') + $this->Order_model->count_by_status('confirmado'),
			'preparing' => $this->Order_model->count_by_status('en_preparacion'),
			'prepared_today' => $this->Order_model->count_prepared_today(date('Y-m-d')),
			'overdue' => $this->_count_overdue(),
		);

		// Pedidos pendientes de preparar de dias anteriores (posibles olvidados)
		$this->data['overdueOrders'] = $this->db->select('o.id, o.order_number, o.customer_name, o.status, o.created_at, o.total, w.name AS warehouse_name')
			->from('store_orders o')
			->join('warehouses w', 'w.id = o.warehouse_id', 'left')
			->where('o.country_id', current_country_id())
			->where_in('o.status', array('confirmado', 'registrado', 'pendiente_preparacion', 'en_preparacion'))
			->where('DATE(o.created_at) <', date('Y-m-d'))
			->order_by('o.created_at', 'ASC')
			->limit(50)
			->get()->result();
		$this->data['warehouses'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('name')->get('warehouses')->result();
		$this->data['date'] = $date;
		$this->data['pageScripts'] = array('assets/js/pages/preparation.js');

		$this->render('preparation/index', $this->data);
	}

	/**
	 * Cuenta pedidos pendientes de preparar con fecha de creacion anterior a hoy.
	 *
	 * @return int
	 */
	private function _count_overdue()
	{
		return (int)$this->db->from('store_orders')
			->where('country_id', current_country_id())
			->where_in('status', array('confirmado', 'registrado', 'pendiente_preparacion', 'en_preparacion'))
			->where('DATE(created_at) <', date('Y-m-d'))
			->count_all_results();
	}

	public function start_prep($orderId)
	{
		if ($this->input->method() !== 'post') {
			show_404();
		}
		$order = $this->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order || !in_array($order->status, array('confirmado', 'registrado', 'pendiente_preparacion'), true)) {
			$this->json_response(false, 'El pedido no puede iniciarse en su estado actual.');
			return;
		}
		$this->_change_prep_state($order, 'en_preparacion', null);
	}

	public function mark_prepared($orderId)
	{
		if ($this->input->method() !== 'post') {
			show_404();
		}
		$order = $this->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order || $order->status !== 'en_preparacion') {
			$this->json_response(false, 'El pedido debe estar en preparación.');
			return;
		}
		$notes = $this->input->post('notes');
		$this->_change_prep_state($order, 'preparado', $notes);
	}

	public function return_to_pending($orderId)
	{
		if ($this->input->method() !== 'post') {
			show_404();
		}
		$order = $this->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order || $order->status !== 'en_preparacion') {
			$this->json_response(false, 'El pedido debe estar en preparación.');
			return;
		}
		$justification = $this->input->post('justification');
		if (!$justification) {
			$this->json_response(false, 'Debe indicar una justificación.');
			return;
		}
		$this->_change_prep_state($order, 'pendiente_preparacion', $justification);
	}

	private function _change_prep_state($order, $toStatus, $notes)
	{
		$userId = $this->currentUser->id;

		$this->db->trans_start();
		$this->db->where('id', $order->id)->update('store_orders', array(
			'status' => $toStatus,
			'updated_by' => $userId,
			'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->db->insert('warehouse_preparation_history', array(
			'order_id' => $order->id,
			'from_status' => $order->status,
			'to_status' => $toStatus,
			'user_id' => $userId,
			'notes' => $notes,
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->order_service->record_status_change($order->id, $order->status, $toStatus, $userId, $notes);
		$this->db->trans_complete();

		if (!$this->db->trans_status()) {
			$this->json_response(false, 'No fue posible actualizar la preparación.');
			return;
		}

		$this->audit_service->log('preparation.state_change', 'preparacion', 'orders', $order->id, array(
			'from' => $order->status,
			'to' => $toStatus,
			'notes' => $notes,
		));

		$messages = array(
			'en_preparacion' => 'Preparación iniciada.',
			'preparado' => 'Pedido marcado como preparado.',
			'pendiente_preparacion' => 'Pedido devuelto a pendiente.',
		);
		$this->json_response(true, isset($messages[$toStatus]) ? $messages[$toStatus] : 'Estado actualizado.');
	}

	public function add_observation($orderId)
	{
		if ($this->input->method() !== 'post') {
			show_404();
		}
		$notes = $this->input->post('notes');
		if (!$notes) {
			$this->json_response(false, 'La observación no puede estar vacía.');
			return;
		}
		$this->db->insert('warehouse_preparation_history', array(
			'order_id' => $orderId,
			'from_status' => null,
			'to_status' => 'observacion',
			'user_id' => $this->currentUser->id,
			'notes' => $notes,
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->audit_service->log('preparation.observation', 'preparacion', 'orders', $orderId, array('notes' => $notes));
		$this->json_response(true, 'Observacion registrada.');
	}
}


