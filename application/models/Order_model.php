<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model
{
	/**
	 * Lista de pedidos con filtros.
	 *
	 * @param array $f Filtros: status, date_from, date_to, search, seller_user_id, warehouse_id
	 * @param int|null $limit
	 * @param int $offset
	 * @return array
	 */
	public function search($f = array(), $limit = null, $offset = 0)
	{
		$this->db->select('o.*, u.first_name AS seller_first_name, u.last_name AS seller_last_name, w.name AS warehouse_name, pm.name AS payment_name, tr.name AS transport_name')
			->from('store_orders o')
			->join('users u', 'u.id = o.seller_user_id', 'left')
			->join('warehouses w', 'w.id = o.warehouse_id', 'left')
			->join('payment_methods pm', 'pm.id = o.payment_method_id', 'left')
			->join('transports tr', 'tr.id = o.transport_id', 'left');

		$this->apply_filters($f);

		$this->db->order_by('o.created_at', 'DESC');
		if ($limit !== null) {
			$this->db->limit($limit, $offset);
		}
		return $this->db->get()->result();
	}

	/**
	 * Cuenta de pedidos con los mismos filtros.
	 *
	 * @param array $f
	 * @return int
	 */
	public function count_search($f = array())
	{
		$this->db->from('store_orders o');
		$this->apply_filters($f);
		return $this->db->count_all_results();
	}

	/**
	 * Aplica filtros a la consulta.
	 *
	 * @param array $f
	 */
	private function apply_filters($f)
	{
		$this->db->where('o.country_id', current_country_id());
		if (!empty($f['status'])) {
			$this->db->where('o.status', $f['status']);
		}
		if (!empty($f['date_from'])) {
			$this->db->where('DATE(o.created_at) >=', $f['date_from']);
		}
		if (!empty($f['date_to'])) {
			$this->db->where('DATE(o.created_at) <=', $f['date_to']);
		}
		if (!empty($f['requested_date'])) {
			$this->db->where('o.requested_delivery_date', $f['requested_date']);
		}
		if (!empty($f['seller_user_id'])) {
			$this->db->where('o.seller_user_id', $f['seller_user_id']);
		}
		if (!empty($f['warehouse_id'])) {
			$this->db->where('o.warehouse_id', $f['warehouse_id']);
		}
		if (!empty($f['origin'])) {
			$this->db->where('o.origin', $f['origin']);
		}
		if (!empty($f['search'])) {
			$term = $f['search'];
			$this->db->group_start()
				->like('o.order_number', $term)
				->or_like('o.customer_name', $term)
				->or_like('o.customer_phone', $term)
				->group_end();
		}
	}

	/**
	 * Detalle completo de un pedido con lineas.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function find_with_items($id)
	{
		$order = $this->db->select('o.*, u.first_name AS seller_first_name, u.last_name AS seller_last_name,
				w.name AS warehouse_name, pm.name AS payment_name, pm.code AS payment_code, tr.name AS transport_name')
			->from('store_orders o')
			->join('users u', 'u.id = o.seller_user_id', 'left')
			->join('warehouses w', 'w.id = o.warehouse_id', 'left')
			->join('payment_methods pm', 'pm.id = o.payment_method_id', 'left')
			->join('transports tr', 'tr.id = o.transport_id', 'left')
			->where('o.id', $id)
			->where('o.country_id', current_country_id())
			->get()->row();
		if (!$order) {
			return null;
		}
		$order->items = $this->db->where('order_id', $id)->order_by('id', 'ASC')->get('store_order_items')->result();
		$order->payments = $this->db->select('p.*, pm.name AS method_name')->from('payments p')
			->join('payment_methods pm', 'pm.id = p.payment_method_id', 'left')
			->where('p.order_id', $id)->order_by('p.created_at', 'ASC')->get()->result();
		$order->history = $this->db->where('order_id', $id)->order_by('created_at', 'ASC')->get('order_status_history')->result();
		$order->attachments = $this->db->where('order_id', $id)->order_by('created_at', 'DESC')->get('attachments')->result();
		return $order;
	}

	/**
	 * Conteo simple por estado.
	 *
	 * @param string|null $status Si es null cuenta todos los estados
	 * @param string|null $date
	 * @return int
	 */
	public function count_by_status($status, $date = null)
	{
		$this->db->where('country_id', current_country_id());
		if ($status) {
			$this->db->where('status', $status);
		}
		if ($date) {
			$this->db->where('DATE(created_at)', $date);
		}
		return $this->db->count_all_results('store_orders');
	}

	/**
	 * Pedidos unicos preparados en una fecha, segun el historial de estados.
	 * Un pedido puede pasar a "preparado" varias veces el mismo dia (p. ej.
	 * al retirarse de ruta), por eso se cuentan pedidos distintos.
	 *
	 * @param string $date
	 * @return int
	 */
	public function count_prepared_today($date)
	{
		$this->db->select('COUNT(DISTINCT h.order_id) AS c')
			->from('order_status_history h')
			->join('store_orders o', 'o.id = h.order_id')
			->where('h.to_status', 'preparado')
			->where('o.country_id', current_country_id())
			->where('DATE(h.created_at)', $date);
		return (int)$this->db->get()->row()->c;
	}

	/**
	 * Resumen de montos por estado para el dashboard.
	 *
	 * @param string $date
	 * @return object|null
	 */
	public function today_totals($date)
	{
		return $this->db->select('
				COALESCE(SUM(CASE WHEN status = "entregado" THEN paid_amount END), 0) AS collected,
				COALESCE(SUM(CASE WHEN status IN ("entregado","no_entregado") THEN 0 ELSE balance_amount END), 0) AS pending
			')
			->where('country_id', current_country_id())
			->where('DATE(created_at)', $date)
			->get('store_orders')->row();
	}
}
