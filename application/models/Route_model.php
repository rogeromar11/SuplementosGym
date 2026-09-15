<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Route_model extends CI_Model
{
	/**
	 * Lista de rutas con filtros.
	 *
	 * @param array $f Filtros: date_from, date_to, shift_id, warehouse_id, courier_user_id, status
	 * @return array
	 */
	public function search($f = array())
	{
		$this->db->select('r.*, s.name AS shift_name, w.name AS warehouse_name,
				u.first_name AS courier_first_name, u.last_name AS courier_last_name')
			->from('routes r')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('warehouses w', 'w.id = r.warehouse_id', 'left')
			->join('users u', 'u.id = r.courier_user_id', 'left');

		$this->db->where('r.country_id', current_country_id());
		if (!empty($f['date_from'])) {
			$this->db->where('r.route_date >=', $f['date_from']);
		}
		if (!empty($f['date_to'])) {
			$this->db->where('r.route_date <=', $f['date_to']);
		}
		if (!empty($f['route_date'])) {
			$this->db->where('r.route_date', $f['route_date']);
		}
		if (!empty($f['shift_id'])) {
			$this->db->where('r.shift_id', $f['shift_id']);
		}
		if (!empty($f['warehouse_id'])) {
			$this->db->where('r.warehouse_id', $f['warehouse_id']);
		}
		if (!empty($f['courier_user_id'])) {
			$this->db->where('r.courier_user_id', $f['courier_user_id']);
		}
		if (!empty($f['status'])) {
			$this->db->where('r.status', $f['status']);
		}

		$this->db->order_by('r.route_date', 'DESC')->order_by('s.sort_order', 'ASC');
		return $this->db->get()->result();
	}

	/**
	 * Rutas de un mensajero para una fecha (o todas las activas).
	 *
	 * @param int $courierUserId
	 * @param string|null $date
	 * @return array
	 */
	public function for_courier($courierUserId, $date = null)
	{
		$this->db->select('r.*, s.name AS shift_name, s.code AS shift_code, w.name AS warehouse_name, w.address AS warehouse_address')
			->from('routes r')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('warehouses w', 'w.id = r.warehouse_id', 'left')
			->where('r.courier_user_id', $courierUserId)
			->where('r.country_id', current_country_id())
			->where_in('r.status', array('planificada', 'en_progreso'));
		if ($date) {
			$this->db->where('r.route_date', $date);
		}
		$this->db->order_by('r.route_date', 'ASC')->order_by('s.sort_order', 'ASC');
		return $this->db->get()->result();
	}

	/**
	 * Pedidos activos de una ruta (con datos del pedido y cliente).
	 *
	 * @param int $routeId
	 * @return array
	 */
	public function orders_for_route($routeId)
	{
		return $this->db			->select('ro.*, o.order_number, o.customer_name, o.customer_phone,
				o.customer_phone_whatsapp, o.customer_phone2, o.customer_phone2_whatsapp,
				o.delivery_address, o.delivery_reference, o.delivery_zone,
				o.latitude, o.longitude, o.map_url, o.total, o.paid_amount, o.balance_amount,
				o.notes, o.is_parcel, o.transport_id, o.payment_method_id, pm.name AS initial_payment_name,
				EXISTS(SELECT 1 FROM order_status_history h WHERE h.order_id = o.id AND h.to_status = "reprogramado") AS was_reprogrammed,
				(SELECT COALESCE(fr.name, "") FROM delivery_attempts da
					LEFT JOIN delivery_failure_reasons fr ON fr.id = da.failure_reason_id
					WHERE da.route_order_id = ro.id ORDER BY da.id DESC LIMIT 1) AS failure_reason,
				(SELECT COALESCE(da.notes, "") FROM delivery_attempts da
					WHERE da.route_order_id = ro.id ORDER BY da.id DESC LIMIT 1) AS failure_notes,
				COALESCE((SELECT pm2.name FROM payments p
					JOIN delivery_attempts da2 ON da2.id = p.delivery_attempt_id
					JOIN payment_methods pm2 ON pm2.id = p.payment_method_id
					WHERE da2.route_order_id = ro.id
					ORDER BY p.id DESC LIMIT 1), pm.name) AS payment_name,
				tr.name AS transport_name')
			->from('route_orders ro')
			->join('routes r', 'r.id = ro.route_id', 'inner')
			->join('store_orders o', 'o.id = ro.order_id', 'inner')
			->join('payment_methods pm', 'pm.id = o.payment_method_id', 'left')
			->join('transports tr', 'tr.id = o.transport_id', 'left')
			->where('ro.route_id', $routeId)
			->where('r.country_id', current_country_id())
			->where('ro.active_key', 1)
			->order_by('ro.delivery_order', 'ASC')
			->get()->result();
	}

	/**
	 * Totales resumidos de una ruta.
	 *
	 * @param int $routeId
	 * @return object
	 */
	public function summary($routeId)
	{
		$status = $this->db->select('status')->where('id', $routeId)->get('routes')->row();
		$status = $status ? $status->status : '';

		$this->db->select('
				COUNT(ro.id) AS total_orders,
				COALESCE(SUM(CASE WHEN ro.route_status = "entregado" THEN 1 ELSE 0 END), 0) AS delivered,
				COALESCE(SUM(CASE WHEN ro.route_status = "no_entregado" THEN 1 ELSE 0 END), 0) AS not_delivered,
				COALESCE(SUM(CASE WHEN ro.route_status = "pendiente" THEN 1 ELSE 0 END), 0) AS pending,
				COALESCE(SUM(o.total), 0) AS expected_amount,
				COALESCE(SUM(o.paid_amount), 0) AS collected_amount
			')
			->from('route_orders ro')
			->join('routes r', 'r.id = ro.route_id', 'inner')
			->join('store_orders o', 'o.id = ro.order_id', 'inner')
			->where('ro.route_id', $routeId)
			->where('r.country_id', current_country_id());
		if ($status === 'finalizada') {
			// En rutas finalizadas incluye los pedidos entregados y los no
			// entregados (aunque quedaron fuera de la ruta activa), mas los
			// pendientes que siguen asignados.
			$this->db->group_start()
				->where_in('ro.route_status', array('entregado', 'no_entregado'))
				->or_where('ro.active_key', 1)
				->group_end();
		} else {
			$this->db->where('ro.active_key', 1);
		}
		$row = $this->db->get()->row();

		$row->pending_amount = round((float)$row->expected_amount - (float)$row->collected_amount, 2);
		return $row;
	}

	/**
	 * Pedidos disponibles (sin ruta activa) para asignar a una ruta.
	 * Incluye pedidos "preparado" y pedidos "reprogramado" cuya fecha
	 * reprogramada coincide con la fecha de la ruta.
	 *
	 * @param int|null $warehouseId
	 * @param string|null $date Fecha de la ruta
	 * @return array
	 */
	public function available_orders($warehouseId = null, $date = null)
	{
		$this->db->select('o.id, o.order_number, o.customer_name, o.customer_phone,
				o.delivery_address, o.delivery_zone, o.total, o.balance_amount, o.latitude, o.longitude,
				o.is_parcel, o.transport_id, o.status, pm.name AS payment_name')
			->from('store_orders o')
			->join('payment_methods pm', 'pm.id = o.payment_method_id', 'left')
			->where('o.country_id', current_country_id())
			->where('NOT EXISTS (SELECT 1 FROM route_orders ro WHERE ro.order_id = o.id AND ro.active_key = 1)', null, false)
			->group_start()
			->where('o.status', 'preparado')
			->or_group_start()
				->where('o.status', 'reprogramado')
				->where('o.rescheduled_delivery_date', $date)
			->group_end()
			->group_end();
		if ($warehouseId) {
			$this->db->where('o.warehouse_id', $warehouseId);
		}
		$this->db->order_by('o.order_number', 'ASC');
		return $this->db->get()->result();
	}

	public function released_orders($routeId)
	{
		return $this->db->select('o.order_number, o.customer_name, o.customer_phone, o.is_parcel, o.status,
				(SELECT COALESCE(fr.name, "") FROM delivery_attempts da
					LEFT JOIN delivery_failure_reasons fr ON fr.id = da.failure_reason_id
					WHERE da.route_order_id = ro.id ORDER BY da.id DESC LIMIT 1) AS failure_reason,
				(SELECT COALESCE(da.notes, "") FROM delivery_attempts da
					WHERE da.route_order_id = ro.id ORDER BY da.id DESC LIMIT 1) AS failure_notes')
			->from('route_orders ro')
			->join('store_orders o', 'o.id = ro.order_id', 'inner')
			->where('ro.route_id', $routeId)
			->where('ro.active_key', null)
			->where('ro.route_status', 'no_entregado')
			->order_by('o.order_number', 'ASC')
			->get()->result();
	}
}
