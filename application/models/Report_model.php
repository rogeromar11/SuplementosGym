<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Consultas para reportes operativos y financieros.
 */
class Report_model extends CI_Model
{
	/**
	 * Aplica filtros comunes.
	 *
	 * @param array $f
	 */
	private function apply_filters($f)
	{
		$this->db->where('r.country_id', current_country_id());
		if (!empty($f['date_from'])) {
			$this->db->where('r.route_date >=', $f['date_from']);
		}
		if (!empty($f['date_to'])) {
			$this->db->where('r.route_date <=', $f['date_to']);
		}
		if (!empty($f['courier_user_id'])) {
			$this->db->where('r.courier_user_id', $f['courier_user_id']);
		}
		if (!empty($f['warehouse_id'])) {
			$this->db->where('r.warehouse_id', $f['warehouse_id']);
		}
		if (!empty($f['shift_id'])) {
			$this->db->where('r.shift_id', $f['shift_id']);
		}
		if (!empty($f['route_status'])) {
			$this->db->where('ro.route_status', $f['route_status']);
		}
	}

	/**
	 * Reporte general por ruta.
	 *
	 * @param array $f
	 * @return array
	 */
	public function general($f = array())
	{
		$this->db->select('r.id AS route_id, r.route_number, r.route_date, s.name AS shift_name,
				w.name AS warehouse_name,
				CONCAT(u.first_name, " ", u.last_name) AS courier_name,
				r.status AS route_status,
				COUNT(ro.id) AS total_orders,
				COALESCE(SUM(ro.route_status = "entregado"), 0) AS delivered,
				COALESCE(SUM(ro.route_status = "no_entregado"), 0) AS not_delivered,
				COALESCE(SUM(ro.route_status = "pendiente"), 0) AS pending,
				COALESCE(SUM(ro.route_status = "en_proceso"), 0) AS in_progress,
				COALESCE(SUM(o.total), 0) AS expected_amount,
				COALESCE(SUM(o.paid_amount), 0) AS collected_amount,
				COALESCE(SUM(o.balance_amount), 0) AS pending_amount')
			->from('routes r')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('warehouses w', 'w.id = r.warehouse_id', 'left')
			->join('users u', 'u.id = r.courier_user_id', 'left')
			->join('route_orders ro', 'ro.route_id = r.id AND ro.active_key = 1', 'left')
			->join('store_orders o', 'o.id = ro.order_id', 'left');

		$this->apply_filters($f);

		$this->db->group_by('r.id')
			->order_by('r.route_date', 'DESC')
			->order_by('s.sort_order', 'ASC');

		return $this->db->get()->result();
	}

	/**
	 * Detalle por pedido.
	 *
	 * @param array $f
	 * @return array
	 */
	public function detail($f = array())
	{
		$this->db->select('r.route_number, r.route_date, s.name AS shift_name,
				CONCAT(u.first_name, " ", u.last_name) AS courier_name,
				w.name AS warehouse_name,
				o.order_number, o.customer_name, o.customer_phone, o.delivery_address,
				o.total, o.paid_amount, o.balance_amount,
				ro.route_status, COALESCE(pm2.name, pm.name) AS payment_name,
				fr.name AS failure_reason_name, da.notes AS attempt_notes,
				pa.amount AS payment_amount')
			->from('route_orders ro')
			->join('routes r', 'r.id = ro.route_id', 'inner')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('warehouses w', 'w.id = r.warehouse_id', 'left')
			->join('users u', 'u.id = r.courier_user_id', 'left')
			->join('store_orders o', 'o.id = ro.order_id', 'inner')
			->join('delivery_attempts da', 'da.route_order_id = ro.id', 'left')
			->join('delivery_failure_reasons fr', 'fr.id = da.failure_reason_id', 'left')
			->join('payments pa', 'pa.delivery_attempt_id = da.id', 'left')
			->join('payment_methods pm2', 'pm2.id = pa.payment_method_id', 'left')
			->join('payment_methods pm', 'pm.id = o.payment_method_id', 'left');

		$this->apply_filters($f);

		$this->db->order_by('r.route_date', 'DESC')->order_by('r.route_number', 'ASC')->order_by('ro.delivery_order', 'ASC');

		return $this->db->get()->result();
	}

	/**
	 * Resumen por mensajero.
	 *
	 * @param array $f
	 * @return array
	 */
	public function courier_summary($f = array())
	{
		$this->db->select('u.id AS courier_user_id,
				CONCAT(u.first_name, " ", u.last_name) AS courier_name,
				COUNT(DISTINCT r.id) AS routes,
				COUNT(ro.id) AS total_orders,
				COALESCE(SUM(ro.route_status = "entregado"), 0) AS delivered,
				COALESCE(SUM(ro.route_status = "no_entregado"), 0) AS not_delivered,
				COALESCE(SUM(o.total), 0) AS expected_amount,
				COALESCE(SUM(o.paid_amount), 0) AS collected_amount,
				COALESCE(SUM(o.balance_amount), 0) AS pending_amount,
				COALESCE(SUM(ro.route_status = "entregado") / NULLIF(COUNT(ro.id), 0) * 100, 0) AS success_rate')
			->from('users u')
			->join('routes r', 'r.courier_user_id = u.id', 'inner')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('route_orders ro', 'ro.route_id = r.id AND ro.active_key = 1', 'left')
			->join('store_orders o', 'o.id = ro.order_id', 'left');

		$this->apply_filters($f);

		$this->db->where('u.active', 1);

		$this->db->group_by('u.id')
			->order_by('delivered', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Desglose de pagos por forma de pago.
	 *
	 * @param array $f
	 * @return array
	 */
	public function payment_breakdown($f = array())
	{
		$this->db->select('pm.name AS payment_name, pm.code, COUNT(p.id) AS total_payments, COALESCE(SUM(p.amount), 0) AS total')
			->from('payments p')
			->join('payment_methods pm', 'pm.id = p.payment_method_id', 'left')
			->join('delivery_attempts da', 'da.id = p.delivery_attempt_id', 'left')
			->join('routes r', 'r.id = da.route_id', 'left');

		$this->db->where('r.country_id', current_country_id());
		if (!empty($f['date_from'])) {
			$this->db->where('p.received_at >=', $f['date_from'] . ' 00:00:00');
		}
		if (!empty($f['date_to'])) {
			$this->db->where('p.received_at <=', $f['date_to'] . ' 23:59:59');
		}
		if (!empty($f['courier_user_id'])) {
			$this->db->where('da.courier_user_id', $f['courier_user_id']);
		}

		$this->db->group_by('pm.id')->order_by('total', 'DESC');
		return $this->db->get()->result();
	}

	/**
	 * Ventas por vendedor y dia: cantidad de ventas, montos y seguimiento.
	 * Los pedidos cancelados no cuentan como venta (su stock fue repuesto)
	 * pero se reportan aparte.
	 *
	 * @param array $f date_from, date_to, seller_user_id, warehouse_id
	 * @return array
	 */
	public function sellers_daily($f = array())
	{
		return $this->_sellers_query($f, true);
	}

	/**
	 * Resumen del periodo por vendedor (comparativa entre vendedores).
	 *
	 * @param array $f
	 * @return array
	 */
	public function sellers_summary($f = array())
	{
		return $this->_sellers_query($f, false);
	}

	/**
	 * Consulta base de ventas por vendedor.
	 *
	 * @param array $f
	 * @param bool $byDay Agrupar tambien por dia
	 * @return array
	 */
	private function _sellers_query($f, $byDay)
	{
		$groupBy = $byDay ? 'u.id, DATE(o.created_at)' : 'u.id';
		$selectDate = $byDay ? 'DATE(o.created_at) AS sale_date,' : 'MIN(o.created_at) AS primera_venta, MAX(o.created_at) AS ultima_venta,';
		$this->db->select($selectDate . '
				u.id AS seller_user_id,
				CONCAT(u.first_name, " ", u.last_name) AS seller_name,
				COUNT(o.id) AS total_pedidos,
				COALESCE(SUM(o.status = "cancelado"), 0) AS cancelados,
				COALESCE(SUM(o.status <> "cancelado"), 0) AS ventas,
				COALESCE(SUM(CASE WHEN o.status <> "cancelado" THEN o.total ELSE 0 END), 0) AS total_vendido,
				COALESCE(SUM(CASE WHEN o.status <> "cancelado" THEN o.paid_amount ELSE 0 END), 0) AS total_cobrado,
				COALESCE(SUM(CASE WHEN o.status <> "cancelado" THEN o.balance_amount ELSE 0 END), 0) AS pendiente_cobro,
				COALESCE(SUM(CASE WHEN o.status = "entregado" THEN 1 ELSE 0 END), 0) AS entregados', false)
			->from('store_orders o')
			->join('users u', 'u.id = o.seller_user_id', 'left')
			->where('o.country_id', current_country_id());

		if (!empty($f['date_from'])) {
			$this->db->where('DATE(o.created_at) >=', $f['date_from']);
		}
		if (!empty($f['date_to'])) {
			$this->db->where('DATE(o.created_at) <=', $f['date_to']);
		}
		if (!empty($f['seller_user_id'])) {
			$this->db->where('o.seller_user_id', $f['seller_user_id']);
		}
		if (!empty($f['warehouse_id'])) {
			$this->db->where('o.warehouse_id', $f['warehouse_id']);
		}

		$this->db->group_by($groupBy);
		if ($byDay) {
			$this->db->order_by('sale_date', 'DESC')->order_by('seller_name', 'ASC');
		} else {
			$this->db->order_by('total_vendido', 'DESC');
		}
		return $this->db->get()->result();
	}

	/**
	 * Depositos de efectivo por mensajero con su cadena de seguimiento.
	 *
	 * @param array $f date_from, date_to, courier_user_id, status
	 * @return array
	 */
	public function deposits($f = array())
	{
		$this->db->select('cd.id, cd.created_at, cd.amount, cd.route_count, cd.status,
				cd.courier_receipt_at, cd.aux_confirmed_at, cd.aux_receipt_at, cd.confirmed_at,
				cd.admin_receiver_user_id,
				CONCAT(cu.first_name, " ", cu.last_name) AS courier_name,
				CONCAT(ru.first_name, " ", ru.last_name) AS receiver_name,
				CONCAT(au.first_name, " ", au.last_name) AS admin_receiver_name,
				CONCAT(cb.first_name, " ", cb.last_name) AS confirmed_by_name', false)
			->from('cash_deposits cd')
			->join('users cu', 'cu.id = cd.courier_user_id', 'left')
			->join('users ru', 'ru.id = cd.receiver_user_id', 'left')
			->join('users au', 'au.id = cd.admin_receiver_user_id', 'left')
			->join('users cb', 'cb.id = cd.confirmed_by', 'left')
			->where('cd.country_id', current_country_id());

		if (!empty($f['date_from'])) {
			$this->db->where('DATE(cd.created_at) >=', $f['date_from']);
		}
		if (!empty($f['date_to'])) {
			$this->db->where('DATE(cd.created_at) <=', $f['date_to']);
		}
		if (!empty($f['courier_user_id'])) {
			$this->db->where('cd.courier_user_id', $f['courier_user_id']);
		}
		if (!empty($f['status'])) {
			$this->db->where('cd.status', $f['status']);
		}

		return $this->db->order_by('cd.created_at', 'DESC')->order_by('cd.id', 'DESC')->get()->result();
	}

	/**
	 * Productos vendidos por fecha de registro del pedido.
	 * Los pedidos anulados se excluyen porque su inventario fue repuesto.
	 *
	 * @param array $f Filtros: date_from, date_to, warehouse_id
	 * @return array
	 */
	public function product_sales($f = array())
	{
		$this->db->select('DATE(o.created_at) AS sale_date,
				oi.product_id, COALESCE(NULLIF(oi.item_sku, ""), p.sku, "—") AS sku,
				oi.item_name, COALESCE(p.stock_qty, 0) AS stock_qty,
				SUM(oi.quantity) AS quantity_sold, SUM(oi.line_total) AS total_sold')
			->from('store_orders o')
			->join('store_order_items oi', 'oi.order_id = o.id', 'inner')
			->join('products p', 'p.id = oi.product_id', 'left')
			->where('o.country_id', current_country_id())
			->where('o.status !=', 'cancelado');

		if (!empty($f['date_from'])) {
			$this->db->where('DATE(o.created_at) >=', $f['date_from']);
		}
		if (!empty($f['date_to'])) {
			$this->db->where('DATE(o.created_at) <=', $f['date_to']);
		}
		if (!empty($f['warehouse_id'])) {
			$this->db->where('o.warehouse_id', $f['warehouse_id']);
		}

		$this->db->group_by('DATE(o.created_at), oi.product_id, oi.item_sku, oi.item_name, p.sku, p.stock_qty')
			->order_by('sale_date', 'DESC')
			->order_by('quantity_sold', 'DESC')
			->order_by('oi.item_name', 'ASC');

		return $this->db->get()->result();
	}
}
