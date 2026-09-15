<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Servicio de rutas: creacion, asignacion de pedidos, orden, transferencias
 * e inicio/finalizacion. Las operaciones criticas usan transacciones.
 */
class Route_service
{
	/**
	 * Genera el numero de ruta.
	 * Formato: RUT-YYYYMMDD-{MA|TA}-XX
	 *
	 * @param string $date
	 * @param string $shiftCode
	 * @return string
	 */
	public function next_route_number($date = null, $shiftCode = 'MA')
	{
		$ci =& get_instance();
		$date = $date ?: date('Y-m-d');
		$shift = ($shiftCode === 'tarde' || $shiftCode === 'TA') ? 'TA' : 'MA';
		$prefix = 'RUT-' . date('Ymd', strtotime($date)) . '-' . $shift . '-';

		$ci->db->select('route_number')
			->from('routes')
			->where('country_id', current_country_id())
			->like('route_number', $prefix, 'after')
			->order_by('route_number', 'DESC')
			->limit(1);
		$row = $ci->db->get()->row();

		$seq = $row ? (int)substr($row->route_number, -2) + 1 : 1;
		return $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Valida que un mensajero no tenga otra ruta en el mismo turno y fecha.
	 * Un mensajero no puede tener mas de una ruta por turno el mismo dia.
	 *
	 * @param string $date
	 * @param int $shiftId
	 * @param int|null $courierId
	 * @param int|null $excludeRouteId
	 * @return bool
	 */
	public function courier_has_route($date, $shiftId, $courierId = null, $excludeRouteId = null)
	{
		if (!$courierId) {
			return false;
		}
		$ci =& get_instance();
		$ci->db->where('country_id', current_country_id())
			->where('route_date', $date)
			->where('shift_id', $shiftId)
			->where('courier_user_id', $courierId);
		if ($excludeRouteId) {
			$ci->db->where('id !=', $excludeRouteId);
		}
		return $ci->db->count_all_results('routes') > 0;
	}

	/**
	 * Crea una ruta.
	 *
	 * @param array $data
	 * @param int $userId
	 * @return array
	 */
	public function create($data, $userId)
	{
		$ci =& get_instance();

		if (!empty($data['courier_user_id']) && $this->courier_has_route($data['route_date'], $data['shift_id'], $data['courier_user_id'])) {
			return array('success' => false, 'error' => 'El mensajero ya tiene una ruta asignada en el mismo turno para esta fecha.');
		}

		$ci->db->insert('routes', array_merge($data, array(
			'country_id' => current_country_id(),
			'status' => isset($data['status']) ? $data['status'] : 'borrador',
			'created_by' => $userId,
			'updated_by' => $userId,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		)));
		$routeId = $ci->db->insert_id();

		$this->audit_service()->log('route.create', 'rutas', 'routes', $routeId, array('route_number' => $data['route_number']));

		return array('success' => true, 'id' => $routeId, 'error' => null);
	}

	/**
	 * Agrega un pedido preparado a una ruta.
	 *
	 * @param int $routeId
	 * @param int $orderId
	 * @param int $userId
	 * @param int|null $transportId Transporte de encomienda (obligatorio si is_parcel)
	 * @return array
	 */
	public function add_order($routeId, $orderId, $userId, $transportId = null)
	{
		$ci =& get_instance();
		$order = $ci->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		$route = $ci->db->where('id', $routeId)->where('country_id', current_country_id())->get('routes')->row();

		if (!$order || !$route) {
			return array('success' => false, 'error' => 'Pedido o ruta no existen.');
		}
		$statusOk = ($order->status === 'preparado')
			|| ($order->status === 'reprogramado' && $order->rescheduled_delivery_date === $route->route_date);
		if (!$statusOk) {
			return array('success' => false, 'error' => 'Solo se pueden asignar pedidos preparados o reprogramados para la fecha de la ruta.');
		}
		if (in_array($order->status, array('cancelado', 'entregado', 'no_entregado'), true)) {
			return array('success' => false, 'error' => 'El pedido no puede asignarse a una ruta.');
		}
		if (!in_array($route->status, array('borrador', 'planificada'), true)) {
			return array('success' => false, 'error' => 'La ruta no admite cambios en su estado actual.');
		}

		// El transporte de encomienda es obligatorio al planear la ruta.
		if ((int)$order->is_parcel === 1) {
			$transportId = (int)$transportId;
			if (!$transportId) {
				return array('success' => false, 'error' => 'Debe indicar el transporte de la encomienda.');
			}
			$transport = $ci->db->where('id', $transportId)->where('country_id', current_country_id())->where('is_active', 1)->get('transports')->row();
			if (!$transport) {
				return array('success' => false, 'error' => 'El transporte seleccionado no es válido.');
			}
		} else {
			$transportId = null;
		}

		// Evitar que un pedido quede activo en dos rutas a la vez
		$inActiveRoute = $ci->db->where('order_id', $orderId)->where('active_key', 1)->count_all_results('route_orders');
		if ($inActiveRoute > 0) {
			return array('success' => false, 'error' => 'El pedido ya está asignado a una ruta activa.');
		}

		$ci->db->trans_start();

		$ci->db->select('MAX(delivery_order) AS max_order')->where('route_id', $routeId);
		$maxRow = $ci->db->get('route_orders')->row();
		$deliveryOrder = (int)$maxRow->max_order + 1;

		$existing = $ci->db->where('route_id', $routeId)->where('order_id', $orderId)->get('route_orders')->row();
		if ($existing) {
			$ci->db->where('id', $existing->id)->update('route_orders', array(
				'delivery_order' => $deliveryOrder,
				'route_status' => 'pendiente',
				'assigned_at' => date('Y-m-d H:i:s'),
				'started_at' => null,
				'completed_at' => null,
				'active_key' => 1,
			));
		} else {
			$ci->db->insert('route_orders', array(
				'route_id' => $routeId,
				'order_id' => $orderId,
				'delivery_order' => $deliveryOrder,
				'route_status' => 'pendiente',
				'assigned_at' => date('Y-m-d H:i:s'),
				'active_key' => 1,
			));
		}

		$orderUpdate = array('status' => 'asignado_ruta', 'updated_at' => date('Y-m-d H:i:s'));
		if ($transportId) {
			$orderUpdate['transport_id'] = $transportId;
		}
		if ($order->status === 'reprogramado') {
			$orderUpdate['rescheduled_delivery_date'] = null;
		}
		$ci->db->where('id', $orderId)->update('store_orders', $orderUpdate);

		$this->order_service()->record_status_change($orderId, $order->status, 'asignado_ruta', $userId, null, null, $routeId);

		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible asignar el pedido a la ruta.');
		}

		$this->audit_service()->log('route.add_order', 'rutas', 'route_orders', $orderId, array(
			'route_id' => $routeId,
			'order_id' => $orderId,
			'delivery_order' => $deliveryOrder,
		));

		return array('success' => true, 'error' => null);
	}

	/**
	 * Quita un pedido de una ruta (mientras no haya iniciado).
	 *
	 * @param int $routeId
	 * @param int $orderId
	 * @param int $userId
	 * @return array
	 */
	public function remove_order($routeId, $orderId, $userId)
	{
		$ci =& get_instance();
		$route = $ci->db->where('id', $routeId)->where('country_id', current_country_id())->get('routes')->row();
		if (!$route) {
			return array('success' => false, 'error' => 'La ruta no existe.');
		}
		if (!in_array($route->status, array('borrador', 'planificada'), true)) {
			return array('success' => false, 'error' => 'La ruta ya inició; no se pueden quitar pedidos.');
		}

		$ci->db->trans_start();

		// Baja logica del registro activo (active_key -> NULL permite reasignar luego)
		$ci->db->where('route_id', $routeId)->where('order_id', $orderId)->where('active_key', 1)
			->update('route_orders', array('active_key' => null));

		$order = $ci->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if ($order && in_array($order->status, array('asignado_ruta', 'en_ruta'), true)) {
			$ci->db->where('id', $orderId)->update('store_orders', array('status' => 'preparado', 'updated_at' => date('Y-m-d H:i:s')));
			$this->order_service()->record_status_change($orderId, $order->status, 'preparado', $userId, 'Retirado de ruta');
		}

		$this->renumber_route($routeId);
		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible retirar el pedido.');
		}

		$this->audit_service()->log('route.remove_order', 'rutas', 'route_orders', $orderId, array('route_id' => $routeId));

		return array('success' => true, 'error' => null);
	}

	/**
	 * Guarda el orden de los pedidos de una ruta.
	 *
	 * @param int $routeId
	 * @param array $orderIds Orden secuencial de ids de pedidos
	 * @param int $userId
	 * @return bool
	 */
	public function reorder($routeId, $orderIds, $userId)
	{
		$ci =& get_instance();
		$ci->db->trans_start();

		$position = 1;
		foreach ($orderIds as $orderId) {
			$ci->db->where('route_id', $routeId)
				->where('order_id', $orderId)
				->where('active_key', 1)
				->update('route_orders', array('delivery_order' => $position++));
		}

		$ci->db->trans_complete();
		$ok = $ci->db->trans_status();

		if ($ok) {
			$this->audit_service()->log('route.reorder', 'rutas', 'routes', $routeId, array('order_ids' => $orderIds));
		}

		return $ok;
	}

	/**
	 * Transfiere un pedido entre rutas del mismo dia (transaccional).
	 *
	 * @param int $orderId
	 * @param int $fromRouteId
	 * @param int $toRouteId
	 * @param int $userId
	 * @param string|null $notes
	 * @return array
	 */
	public function transfer_order($orderId, $fromRouteId, $toRouteId, $userId, $notes = null)
	{
		$ci =& get_instance();

		$fromOrder = $ci->db->where('route_id', $fromRouteId)->where('order_id', $orderId)->where('active_key', 1)->get('route_orders')->row();
		$order = $ci->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$fromOrder || !$order) {
			return array('success' => false, 'error' => 'El pedido no está en la ruta de origen.');
		}
		if ($fromRouteId === $toRouteId) {
			return array('success' => false, 'error' => 'La ruta de destino es igual a la de origen.');
		}
		if ($order->status === 'entregado') {
			return array('success' => false, 'error' => 'No se puede transferir un pedido entregado.');
		}

		$ci->db->trans_start();

		// Quitar de origen (baja logica)
		$ci->db->where('id', $fromOrder->id)->update('route_orders', array('active_key' => null));
		$this->renumber_route($fromRouteId);

		// Agregar a destino
		$ci->db->select('MAX(delivery_order) AS max_order')->where('route_id', $toRouteId);
		$maxRow = $ci->db->get('route_orders')->row();
		$deliveryOrder = (int)$maxRow->max_order + 1;

		$ci->db->insert('route_orders', array(
			'route_id' => $toRouteId,
			'order_id' => $orderId,
			'delivery_order' => $deliveryOrder,
			'route_status' => 'pendiente',
			'assigned_at' => date('Y-m-d H:i:s'),
			'active_key' => 1,
		));

		$ci->db->insert('route_transfer_history', array(
			'order_id' => $orderId,
			'from_route_id' => $fromRouteId,
			'to_route_id' => $toRouteId,
			'from_delivery_order' => $fromOrder->delivery_order,
			'to_delivery_order' => $deliveryOrder,
			'transferred_by' => $userId,
			'notes' => $notes,
			'created_at' => date('Y-m-d H:i:s'),
		));

		$this->order_service()->record_status_change($orderId, $order->status, 'asignado_ruta', $userId, 'Transferido entre rutas: ' . $fromRouteId . ' -> ' . $toRouteId, null, $toRouteId);

		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible transferir el pedido.');
		}

		$this->audit_service()->log('route.transfer', 'rutas', 'route_transfer_history', $orderId, array(
			'from_route' => $fromRouteId,
			'to_route' => $toRouteId,
		));

		return array('success' => true, 'error' => null);
	}

	/**
	 * Inicia una ruta (estado en_progreso).
	 *
	 * @param int $routeId
	 * @param int $userId
	 * @return array
	 */
	public function start_route($routeId, $userId)
	{
		$ci =& get_instance();
		$route = $ci->db->where('id', $routeId)->where('country_id', current_country_id())->get('routes')->row();
		if (!$route) {
			return array('success' => false, 'error' => 'La ruta no existe.');
		}
		if ($route->status !== 'planificada' && $route->status !== 'borrador') {
			return array('success' => false, 'error' => 'La ruta no puede iniciarse desde su estado actual.');
		}
		if (!$route->courier_user_id) {
			return array('success' => false, 'error' => 'La ruta no tiene mensajero asignado.');
		}

		$ci->db->where('id', $routeId)->update('routes', array(
			'status' => 'en_progreso',
			'started_at' => date('Y-m-d H:i:s'),
			'updated_by' => $userId,
			'updated_at' => date('Y-m-d H:i:s'),
		));

		// Pedidos de la ruta pasan a en_ruta
		$ci->db->where('route_id', $routeId)->where('active_key', 1)->update('route_orders', array(
			'route_status' => 'pendiente',
			'started_at' => date('Y-m-d H:i:s'),
		));

		$ci->db->select('order_id')->from('route_orders')->where('route_id', $routeId)->where('active_key', 1);
		$orderIds = array_column($ci->db->get()->result(), 'order_id');
		foreach ($orderIds as $oid) {
			$this->order_service()->change_status($oid, 'en_ruta', $userId, 'Ruta iniciada', null, $routeId);
		}

		$this->audit_service()->log('route.start', 'rutas', 'routes', $routeId);

		return array('success' => true, 'error' => null);
	}

	/**
	 * Finaliza una ruta y libera los pedidos no entregados para reprogramar.
	 * No permite finalizar mientras existan entregas sin procesar (pendiente o
	 * en proceso), salvo que se fuerce (administrador).
	 *
	 * @param int $routeId
	 * @param int $userId
	 * @param bool $force Omitir la validacion de entregas pendientes (admin)
	 * @return array
	 */
	public function finish_route($routeId, $userId, $force = false)
	{
		$ci =& get_instance();
		$route = $ci->db->where('id', $routeId)->where('country_id', current_country_id())->get('routes')->row();
		if (!$route) {
			return array('success' => false, 'error' => 'La ruta no existe.');
		}
		if ($route->status !== 'en_progreso') {
			return array('success' => false, 'error' => 'La ruta no está en progreso.');
		}

		// Evita finalizar por error con pedidos sin procesar (pendiente/en_proceso).
		// Deben quedar todos como entregado o no_entregado.
		if (!$force) {
			$ci->db->where('route_id', $routeId)
				->where('active_key', 1)
				->where_in('route_status', array('pendiente', 'en_proceso'));
			$pendingCount = (int)$ci->db->count_all_results('route_orders');
			if ($pendingCount > 0) {
				return array(
					'success' => false,
					'error' => 'Aún tiene ' . $pendingCount . ' entrega(s) sin procesar. Debe marcar cada pedido como entregado o no entregado antes de finalizar la ruta.',
					'released_orders' => 0,
				);
			}
		}

		$ci->db->trans_start();

		$ci->db->where('id', $routeId)->update('routes', array(
			'status' => 'finalizada',
			'finished_at' => date('Y-m-d H:i:s'),
			'updated_by' => $userId,
			'updated_at' => date('Y-m-d H:i:s'),
		));

		// Pedidos que quedaron sin entregar en la ruta: se liberan (active_key -> null)
		// y vuelven a "preparado" para reaparecer en la planificacion y reprogramar.
		$pending = $ci->db->select('ro.id, ro.order_id, o.status AS order_status')
			->from('route_orders ro')
			->join('store_orders o', 'o.id = ro.order_id', 'inner')
			->where('ro.route_id', $routeId)
			->where('ro.active_key', 1)
			->where('ro.route_status !=', 'entregado')
			->get()->result();

		foreach ($pending as $ro) {
			$ci->db->where('id', $ro->id)->update('route_orders', array('active_key' => null, 'updated_at' => date('Y-m-d H:i:s')));
			$ci->db->where('id', $ro->order_id)->update('store_orders', array('status' => 'preparado', 'updated_at' => date('Y-m-d H:i:s')));
			$this->order_service()->record_status_change($ro->order_id, $ro->order_status, 'preparado', $userId, 'Ruta finalizada sin entrega; disponible para reprogramar', null, $routeId);
		}

		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible finalizar la ruta.');
		}

		$this->audit_service()->log('route.finish', 'rutas', 'routes', $routeId, array('released_orders' => count($pending)));

		return array('success' => true, 'error' => null, 'released_orders' => count($pending));
	}

	/**
	 * Elimina una ruta, solo si no tiene pedidos asignados y esta editable.
	 *
	 * @param int $routeId
	 * @param int $userId
	 * @return array
	 */
	public function delete_route($routeId, $userId)
	{
		$ci =& get_instance();
		$route = $ci->db->where('id', $routeId)->where('country_id', current_country_id())->get('routes')->row();
		if (!$route) {
			return array('success' => false, 'error' => 'La ruta no existe.');
		}
		if (!in_array($route->status, array('borrador', 'planificada'), true)) {
			return array('success' => false, 'error' => 'Solo se pueden eliminar rutas en borrador o planificadas.');
		}
		// Solo los pedidos ACTIVOS en la ruta impiden eliminarla; los retirados
		// (active_key = NULL, baja logica) se eliminan en cascada con la ruta.
		$orderCount = $ci->db->where('route_id', $routeId)
			->where('active_key', 1)
			->count_all_results('route_orders');
		if ($orderCount > 0) {
			return array('success' => false, 'error' => 'La ruta tiene pedidos asignados; no se puede eliminar.');
		}

		$ci->db->trans_start();
		$ci->db->where('id', $routeId)->where('country_id', current_country_id())->delete('routes');
		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible eliminar la ruta.');
		}

		$this->audit_service()->log('route.delete', 'rutas', 'routes', $routeId, array('route_number' => $route->route_number));

		return array('success' => true, 'error' => null);
	}

	/**
	 * Obtiene el servicio de pedidos (lo carga si es necesario).
	 *
	 * @return object
	 */
	private function order_service()
	{
		$ci =& get_instance();
		if (!isset($ci->order_service)) {
			$ci->load->library('Order_service');
		}
		return $ci->order_service;
	}

	/**
	 * Obtiene el servicio de auditoria (lo carga si es necesario).
	 *
	 * @return object
	 */
	private function audit_service()
	{
		$ci =& get_instance();
		if (!isset($ci->audit_service)) {
			$ci->load->library('Audit_service');
		}
		return $ci->audit_service;
	}

	/**
	 * Re-numera los pedidos de una ruta segun su orden actual.
	 *
	 * @param int $routeId
	 */
	private function renumber_route($routeId)
	{
		$ci =& get_instance();
		$rows = $ci->db->where('route_id', $routeId)->where('active_key', 1)->order_by('delivery_order', 'ASC')->get('route_orders')->result();
		$pos = 1;
		foreach ($rows as $row) {
			$ci->db->where('id', $row->id)->update('route_orders', array('delivery_order' => $pos++));
		}
	}
}




