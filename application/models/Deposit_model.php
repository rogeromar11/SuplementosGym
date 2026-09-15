<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Depositos de efectivo por ruta.
 *
 * Flujo: el mensajero deposita el efectivo de una o varias rutas finalizadas
 * a un receptor (Auxiliar Admin o Administrador) con comprobante. El receptor
 * confirma la recepcion; si fue auxiliar, este entrega luego al administrador
 * con su propio comprobante y el administrador da el visto bueno final.
 *
 * Estados: pendiente -> recibido -> entregado -> aprobado
 * (receptor directo = administrador: pendiente -> aprobado)
 */
class Deposit_model extends CI_Model
{
	const STATUS_PENDIENTE = 'pendiente';
	const STATUS_RECIBIDO = 'recibido';
	const STATUS_ENTREGADO = 'entregado';
	const STATUS_APROBADO = 'aprobado';

	/**
	 * Efectivo cobrado en una ruta (pagos en efectivo ligados a intentos de la ruta).
	 *
	 * @param int $routeId
	 * @return float
	 */
	public function route_cash_total($routeId)
	{
		$row = $this->db->select('COALESCE(SUM(p.amount), 0) AS total', false)
			->from('payments p')
			->join('payment_methods pm', 'pm.id = p.payment_method_id', 'inner')
			->join('delivery_attempts da', 'da.id = p.delivery_attempt_id', 'inner')
			->where('da.route_id', $routeId)
			->where('pm.code', 'efectivo')
			->get()->row();
		return round((float)($row ? $row->total : 0), 2);
	}

	/**
	 * Rutas finalizadas del mensajero con efectivo pendiente de deposito.
	 *
	 * @param int $courierId
	 * @return array
	 */
	public function pending_routes($courierId)
	{
		return $this->db->select('r.id, r.route_number, r.route_date, r.finished_at,
				s.name AS shift_name,
				COALESCE(SUM(CASE WHEN pm.code = "efectivo" THEN p.amount ELSE 0 END), 0) AS cash_amount', false)
			->from('routes r')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('delivery_attempts da', 'da.route_id = r.id', 'left')
			->join('payments p', 'p.delivery_attempt_id = da.id', 'left')
			->join('payment_methods pm', 'pm.id = p.payment_method_id', 'left')
			->where('r.country_id', current_country_id())
			->where('r.courier_user_id', $courierId)
			->where('r.status', 'finalizada')
			->where('NOT EXISTS (SELECT 1 FROM cash_deposit_routes cdr WHERE cdr.route_id = r.id)', null, false)
			->group_by('r.id')
			->having('cash_amount >', 0)
			->order_by('r.route_date', 'ASC')
			->get()->result();
	}

	/**
	 * Rutas finalizadas con efectivo pendiente de deposito, de todos los mensajeros.
	 *
	 * @return array
	 */
	public function all_pending_routes()
	{
		return $this->db->select('r.id, r.route_number, r.route_date, r.finished_at, r.courier_user_id,
				s.name AS shift_name,
				CONCAT(u.first_name, " ", u.last_name) AS courier_name,
				COALESCE(SUM(CASE WHEN pm.code = "efectivo" THEN p.amount ELSE 0 END), 0) AS cash_amount', false)
			->from('routes r')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->join('users u', 'u.id = r.courier_user_id', 'left')
			->join('delivery_attempts da', 'da.route_id = r.id', 'left')
			->join('payments p', 'p.delivery_attempt_id = da.id', 'left')
			->join('payment_methods pm', 'pm.id = p.payment_method_id', 'left')
			->where('r.country_id', current_country_id())
			->where('r.status', 'finalizada')
			->where('NOT EXISTS (SELECT 1 FROM cash_deposit_routes cdr WHERE cdr.route_id = r.id)', null, false)
			->group_by('r.id')
			->having('cash_amount >', 0)
			->order_by('u.first_name', 'ASC')
			->order_by('r.route_date', 'ASC')
			->get()->result();
	}

	/**
	 * Crea un deposito con sus rutas (transaccional).
	 *
	 * @param array $deposit Datos: country_id, courier_user_id, receiver_user_id, notes, receipt (array)
	 * @param array $routeIds Rutas incluidas
	 * @return array ['success' => bool, 'id' => int|null, 'error' => string|null]
	 */
	public function create($deposit, $routeIds)
	{
		$ci =& get_instance();
		$routeIds = array_values(array_unique(array_filter(array_map('intval', $routeIds))));
		if (empty($routeIds)) {
			return array('success' => false, 'id' => null, 'error' => 'Seleccione al menos una ruta para depositar.');
		}

		$ci->db->trans_begin();

		$total = 0.0;
		$details = array();
		foreach ($routeIds as $routeId) {
			$route = $ci->db->select('id, route_number, courier_user_id, status, country_id')
				->where('id', $routeId)
				->where('country_id', current_country_id())
				->get('routes')->row();
			if (!$route) {
				$ci->db->trans_rollback();
				return array('success' => false, 'id' => null, 'error' => 'La ruta ' . $routeId . ' no existe.');
			}
			if ((int)$route->courier_user_id !== (int)$deposit['courier_user_id']) {
				$ci->db->trans_rollback();
				return array('success' => false, 'id' => null, 'error' => 'La ruta ' . $route->route_number . ' no le pertenece.');
			}
			if ($route->status !== 'finalizada') {
				$ci->db->trans_rollback();
				return array('success' => false, 'id' => null, 'error' => 'La ruta ' . $route->route_number . ' no esta finalizada.');
			}
			$already = $ci->db->where('route_id', $routeId)->count_all_results('cash_deposit_routes');
			if ($already > 0) {
				$ci->db->trans_rollback();
				return array('success' => false, 'id' => null, 'error' => 'La ruta ' . $route->route_number . ' ya fue incluida en un deposito.');
			}

			$cash = $this->route_cash_total($routeId);
			if ($cash <= 0) {
				$ci->db->trans_rollback();
				return array('success' => false, 'id' => null, 'error' => 'La ruta ' . $route->route_number . ' no tiene efectivo por depositar.');
			}
			$total += $cash;
			$details[] = array('route_id' => $routeId, 'amount' => $cash);
		}

		$ci->db->insert('cash_deposits', array(
			'country_id' => $deposit['country_id'],
			'courier_user_id' => $deposit['courier_user_id'],
			'receiver_user_id' => $deposit['receiver_user_id'],
			'amount' => round($total, 2),
			'route_count' => count($details),
			'status' => self::STATUS_PENDIENTE,
			'notes' => isset($deposit['notes']) && $deposit['notes'] !== '' ? $deposit['notes'] : null,
			'courier_receipt_file' => $deposit['receipt']['file'],
			'courier_receipt_original' => $deposit['receipt']['original'],
			'courier_receipt_mime' => $deposit['receipt']['mime'],
			'courier_receipt_size' => $deposit['receipt']['size'],
			'courier_receipt_at' => date('Y-m-d H:i:s'),
		));
		$depositId = $ci->db->insert_id();
		if (!$depositId) {
			$ci->db->trans_rollback();
			return array('success' => false, 'id' => null, 'error' => 'No fue posible registrar el deposito.');
		}

		foreach ($details as $d) {
			$ci->db->insert('cash_deposit_routes', array(
				'deposit_id' => $depositId,
				'route_id' => $d['route_id'],
				'amount' => $d['amount'],
			));
		}

		if ($ci->db->trans_status() === FALSE) {
			$ci->db->trans_rollback();
			return array('success' => false, 'id' => null, 'error' => 'No fue posible registrar el deposito.');
		}
		$ci->db->trans_commit();
		return array('success' => true, 'id' => $depositId, 'error' => null);
	}

	/**
	 * Listado de depositos con filtros.
	 *
	 * @param array $f status, date_from, date_to, courier_user_id
	 * @return array
	 */
	public function search($f = array())
	{
		$receiver = 'ru';
		$adminRecv = 'au';
		$this->db->select('cd.*, CONCAT(cu.first_name, " ", cu.last_name) AS courier_name,
				CONCAT(' . $receiver . '.first_name, " ", ' . $receiver . '.last_name) AS receiver_name,
				CONCAT(' . $adminRecv . '.first_name, " ", ' . $adminRecv . '.last_name) AS admin_receiver_name', false)
			->from('cash_deposits cd')
			->join('users cu', 'cu.id = cd.courier_user_id', 'left')
			->join('users ' . $receiver, $receiver . '.id = cd.receiver_user_id', 'left')
			->join('users ' . $adminRecv, $adminRecv . '.id = cd.admin_receiver_user_id', 'left')
			->where('cd.country_id', current_country_id());

		if (!empty($f['status'])) {
			$this->db->where('cd.status', $f['status']);
		}
		if (!empty($f['hide_approved'])) {
			// El auxiliar no debe ver los depositos ya aprobados (informacion contable).
			$this->db->where('cd.status !=', self::STATUS_APROBADO);
		}
		if (!empty($f['date_from'])) {
			$this->db->where('DATE(cd.created_at) >=', $f['date_from']);
		}
		if (!empty($f['date_to'])) {
			$this->db->where('DATE(cd.created_at) <=', $f['date_to']);
		}
		if (!empty($f['courier_user_id'])) {
			$this->db->where('cd.courier_user_id', $f['courier_user_id']);
		}

		return $this->db->order_by('cd.created_at', 'DESC')->order_by('cd.id', 'DESC')->get()->result();
	}

	/**
	 * Detalle de un deposito con sus rutas.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function find($id)
	{
		$deposit = $this->db->select('cd.*, CONCAT(cu.first_name, " ", cu.last_name) AS courier_name,
				CONCAT(ru.first_name, " ", ru.last_name) AS receiver_name,
				CONCAT(au.first_name, " ", au.last_name) AS admin_receiver_name,
				CONCAT(cb.first_name, " ", cb.last_name) AS confirmed_by_name', false)
			->from('cash_deposits cd')
			->join('users cu', 'cu.id = cd.courier_user_id', 'left')
			->join('users ru', 'ru.id = cd.receiver_user_id', 'left')
			->join('users au', 'au.id = cd.admin_receiver_user_id', 'left')
			->join('users cb', 'cb.id = cd.confirmed_by', 'left')
			->where('cd.id', $id)
			->where('cd.country_id', current_country_id())
			->get()->row();
		if (!$deposit) {
			return null;
		}
		$deposit->routes = $this->db->select('cdr.route_id, cdr.amount, r.route_number, r.route_date, s.name AS shift_name')
			->from('cash_deposit_routes cdr')
			->join('routes r', 'r.id = cdr.route_id', 'inner')
			->join('route_shifts s', 's.id = r.shift_id', 'left')
			->where('cdr.deposit_id', $id)
			->order_by('r.route_date', 'ASC')
			->get()->result();
		return $deposit;
	}

	/**
	 * Usuarios activos que pueden recibir depositos (Auxiliar Admin y Administrador).
	 *
	 * @return array
	 */
	public function receivers()
	{
		return $this->db->select('DISTINCT u.id, u.first_name, u.last_name, g.name AS group_name', false)
			->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id', 'inner')
			->join('groups g', 'g.id = ug.group_id', 'inner')
			->where('u.country_id', current_country_id())
			->where('u.active', 1)
			->where_in('g.name', array('auxiliar_admin', 'admin'))
			->order_by('g.name', 'ASC')
			->order_by('u.first_name', 'ASC')
			->get()->result();
	}

	/**
	 * Confirma la recepcion del deposito por parte del receptor.
	 * Si el receptor es auxiliar -> recibido; si es administrador -> aprobado.
	 *
	 * @param object $deposit
	 * @param int $userId
	 * @param bool $isAdmin
	 * @return array
	 */
	public function confirm_receipt($deposit, $userId, $isAdmin)
	{
		if ($deposit->status !== self::STATUS_PENDIENTE) {
			return array('success' => false, 'error' => 'El deposito ya fue confirmado.');
		}
		if (!$isAdmin && (int)$deposit->receiver_user_id !== (int)$userId) {
			return array('success' => false, 'error' => 'Solo el receptor asignado puede confirmar este deposito.');
		}

		$receiverGroup = $this->db->select('g.name')
			->from('users_groups ug')
			->join('groups g', 'g.id = ug.group_id')
			->where('ug.user_id', $deposit->receiver_user_id)
			->where_in('g.name', array('auxiliar_admin', 'admin'))
			->order_by('g.name', 'ASC')
			->get()->row();
		$isAuxReceiver = $receiverGroup && $receiverGroup->name === 'auxiliar_admin';

		if ($isAuxReceiver && !$isAdmin) {
			$newStatus = self::STATUS_RECIBIDO;
		} else {
			$newStatus = self::STATUS_APROBADO;
		}

		$data = array('status' => $newStatus);
		if ($newStatus === self::STATUS_RECIBIDO) {
			$data['aux_confirmed_by'] = $userId;
			$data['aux_confirmed_at'] = date('Y-m-d H:i:s');
		} else {
			$data['confirmed_by'] = $userId;
			$data['confirmed_at'] = date('Y-m-d H:i:s');
		}
		$this->db->where('id', $deposit->id)->update('cash_deposits', $data);
		return array('success' => true, 'status' => $newStatus, 'error' => null);
	}

	/**
	 * El auxiliar entrega el efectivo al administrador con su comprobante.
	 *
	 * @param object $deposit
	 * @param int $adminUserId Administrador que recibe
	 * @param int $userId Auxiliar que entrega
	 * @param array $receipt Comprobante
	 * @return array
	 */
	public function handover($deposit, $adminUserId, $userId, $receipt)
	{
		if ($deposit->status !== self::STATUS_RECIBIDO) {
			return array('success' => false, 'error' => 'El deposito debe estar confirmado como recibido antes de entregarlo.');
		}
		if ((int)$deposit->receiver_user_id !== (int)$userId) {
			return array('success' => false, 'error' => 'Solo el receptor asignado puede entregar este deposito.');
		}

		$this->db->where('id', $deposit->id)->update('cash_deposits', array(
			'status' => self::STATUS_ENTREGADO,
			'admin_receiver_user_id' => $adminUserId,
			'aux_receipt_file' => $receipt['file'],
			'aux_receipt_original' => $receipt['original'],
			'aux_receipt_mime' => $receipt['mime'],
			'aux_receipt_size' => $receipt['size'],
			'aux_receipt_at' => date('Y-m-d H:i:s'),
		));
		return array('success' => true, 'error' => null);
	}

	/**
	 * Visto bueno final del administrador.
	 *
	 * @param object $deposit
	 * @param int $userId
	 * @return array
	 */
	public function approve($deposit, $userId)
	{
		if (!in_array($deposit->status, array(self::STATUS_PENDIENTE, self::STATUS_ENTREGADO), true)) {
			return array('success' => false, 'error' => 'El deposito no puede aprobarse en su estado actual.');
		}
		$this->db->where('id', $deposit->id)->update('cash_deposits', array(
			'status' => self::STATUS_APROBADO,
			'confirmed_by' => $userId,
			'confirmed_at' => date('Y-m-d H:i:s'),
		));
		return array('success' => true, 'error' => null);
	}

	/**
	 * Resumen por estado para tarjetas.
	 *
	 * @param bool $hideApproved Excluir los aprobados (vista del auxiliar)
	 * @return object
	 */
	public function status_counts($hideApproved = false)
	{
		$this->db->select('status, COUNT(*) AS c, COALESCE(SUM(amount), 0) AS total', false)
			->where('country_id', current_country_id());
		if ($hideApproved) {
			$this->db->where('status !=', self::STATUS_APROBADO);
		}
		$rows = $this->db->group_by('status')
			->get('cash_deposits')->result();
		$out = new stdClass();
		foreach (array(self::STATUS_PENDIENTE, self::STATUS_RECIBIDO, self::STATUS_ENTREGADO, self::STATUS_APROBADO) as $s) {
			$out->{$s} = array('count' => 0, 'total' => 0.0);
		}
		foreach ($rows as $row) {
			if (isset($out->{$row->status})) {
				$out->{$row->status} = array('count' => (int)$row->c, 'total' => (float)$row->total);
			}
		}
		return $out;
	}
}
