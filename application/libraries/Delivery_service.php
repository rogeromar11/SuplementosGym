<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Servicio de entregas del mensajero: inicio, entrega exitosa,
 * entrega no realizada, evidencias y reprogramacion.
 */
class Delivery_service
{
	/**
	 * Verifica que el mensajero tenga acceso al pedido de una ruta.
	 *
	 * @param int $routeOrderId
	 * @param int $courierUserId
	 * @return object|null route_order con joins o null
	 */
	private function authorize_route_order($routeOrderId, $courierUserId)
	{
		$ci =& get_instance();
		return $ci->db->select('ro.*, r.courier_user_id AS route_courier, r.route_number, r.id AS route_id,
				o.order_number, o.id AS order_id, o.total, o.paid_amount, o.balance_amount,
				o.status AS order_status, o.payment_method_id, o.rescheduled_delivery_date,
				o.is_parcel, o.transport_id, tr.name AS transport_name')
			->from('route_orders ro')
			->join('routes r', 'r.id = ro.route_id', 'inner')
			->join('store_orders o', 'o.id = ro.order_id', 'inner')
			->join('transports tr', 'tr.id = o.transport_id', 'left')
			->where('ro.id', $routeOrderId)
			->where('ro.active_key', 1)
			->where('r.courier_user_id', $courierUserId)
			->where('r.country_id', current_country_id())
			->where_in('r.status', array('planificada', 'en_progreso'))
			->get()->row();
	}

	/**
	 * Inicia la entrega (Pendiente -> En proceso).
	 *
	 * @param int $routeOrderId
	 * @param int $courierUserId
	 * @return array
	 */
	public function start_delivery($routeOrderId, $courierUserId)
	{
		$ci =& get_instance();
		$ro = $this->authorize_route_order($routeOrderId, $courierUserId);
		if (!$ro) {
			return array('success' => false, 'error' => 'Entrega no encontrada o sin acceso.');
		}
		if ($ro->route_status !== 'pendiente') {
			return array('success' => false, 'error' => 'La entrega ya está en proceso.');
		}

		$ci->db->trans_start();
		$ci->db->where('id', $routeOrderId)->update('route_orders', array(
			'route_status' => 'en_proceso',
			'started_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->order_service()->change_status($ro->order_id, 'en_ruta', $courierUserId, 'Entrega en proceso', $courierUserId, $ro->route_id);
		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible iniciar la entrega.');
		}
		return array('success' => true, 'error' => null);
	}

	/**
	 * Registra una entrega exitosa con pago.
	 *
	 * @param int $routeOrderId
	 * @param int $courierUserId
	 * @param array $payment Datos: payment_method_id, amount, notes, received_by_name, idempotency_key
	 * @param array|null $evidence Archivo subido (opcional)
	 * @return array
	 */
	public function deliver($routeOrderId, $courierUserId, $payment, $evidence = null)
	{
		$ci =& get_instance();
		$ro = $this->authorize_route_order($routeOrderId, $courierUserId);
		if (!$ro) {
			return array('success' => false, 'error' => 'Entrega no encontrada o sin acceso.');
		}
		if (!in_array($ro->route_status, array('pendiente', 'en_proceso'), true)) {
			return array('success' => false, 'error' => 'La entrega ya fue procesada.');
		}

		// Encomiendas y pedidos con saldo en cero ya fueron pagados; no se cobra de nuevo.
		$isParcel = (int)$ro->is_parcel === 1;
		$isPrepaid = (float)$ro->balance_amount <= 0.01;
		$skipCollection = $isParcel || $isPrepaid;

		if (!$skipCollection) {
			$amount = (float)$payment['amount'];
			if ($amount <= 0) {
				return array('success' => false, 'error' => 'El monto recibido debe ser mayor a cero.');
			}

			// El cobro en efectivo exige evidencia fotografica del dinero recibido.
			$methodRow = $ci->db->select('code')->where('id', (int)$payment['payment_method_id'])->get('payment_methods')->row();
			$isCash = $methodRow && $methodRow->code === 'efectivo';
			if ($isCash && (!$evidence || empty($evidence['tmp_name']))) {
				return array('success' => false, 'error' => 'Adjunte la foto del efectivo recibido para continuar.');
			}
		}

		// Rechazo temprano si PHP mismo descarto la subida por limites del
		// servidor (upload_max_filesize / post_max_size en cPanel)
		$uploadProblem = $this->upload_problem($evidence);
		if ($uploadProblem !== null) {
			return array('success' => false, 'error' => $uploadProblem);
		}

		$ci->db->trans_start();

		// Evidencia (si aplica)
		$attachmentId = null;
		if ($evidence && !empty($evidence['tmp_name'])) {
			$attachmentId = $this->store_evidence($ro->order_id, null, 'evidencia_entrega', $courierUserId, $evidence);
			if (!$attachmentId) {
				$ci->db->trans_rollback();
				return array('success' => false, 'error' => 'La evidencia no es válida (solo JPG/PNG/WEBP, max ' . app_setting('evidence_max_size_mb', 8) . ' MB).');
			}
		}
		// Intento de entrega
		$ci->db->insert('delivery_attempts', array(
			'order_id' => $ro->order_id,
			'route_id' => $ro->route_id,
			'route_order_id' => $routeOrderId,
			'courier_user_id' => $courierUserId,
			'status' => 'entregado',
			'notes' => isset($payment['notes']) ? $payment['notes'] : null,
			'attempted_at' => date('Y-m-d H:i:s'),
			'received_by_name' => isset($payment['received_by_name']) ? $payment['received_by_name'] : null,
			'latitude' => isset($payment['latitude']) ? $payment['latitude'] : null,
			'longitude' => isset($payment['longitude']) ? $payment['longitude'] : null,
			'created_at' => date('Y-m-d H:i:s'),
		));
		$attemptId = $ci->db->insert_id();

		if ($attachmentId) {
			$ci->db->where('id', $attachmentId)->update('attachments', array('delivery_attempt_id' => $attemptId));
		}

		// Pago transaccional (se omite si el pedido ya está pagado).
		if (!$skipCollection) {
			$payResult = $this->payment_service()->register_payment(
				$ro->order_id,
				(int)$payment['payment_method_id'],
				$amount,
				$courierUserId,
				array(
					'delivery_attempt_id' => $attemptId,
					'notes' => isset($payment['notes']) ? $payment['notes'] : null,
					'idempotency_key' => isset($payment['idempotency_key']) ? $payment['idempotency_key'] : null,
				)
			);
			if (!$payResult['success']) {
				$ci->db->trans_rollback();
				return array('success' => false, 'error' => $payResult['error']);
			}
		}

		// Estado de la entrega y del pedido
		$ci->db->where('id', $routeOrderId)->update('route_orders', array(
			'route_status' => 'entregado',
			'completed_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->order_service()->change_status($ro->order_id, 'entregado', $courierUserId, 'Entrega exitosa', $courierUserId, $ro->route_id);

		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible registrar la entrega.');
		}

		$this->audit_service()->log('delivery.delivered', 'entregas', 'delivery_attempts', $attemptId, array(
			'order_id' => $ro->order_id,
			'amount' => $skipCollection ? null : $amount,
			'is_parcel' => $isParcel,
			'is_prepaid' => $isPrepaid,
			'route_id' => $ro->route_id,
		));

		return array('success' => true, 'error' => null, 'attempt_id' => $attemptId);
	}

	/**
	 * Registra una entrega no realizada (con motivo, evidencia y reprogramacion opcional).
	 *
	 * @param int $routeOrderId
	 * @param int $courierUserId
	 * @param array $data failure_reason_id, failure_other, notes, reschedule_requested, rescheduled_date, latitude, longitude
	 * @param array|null $evidence
	 * @return array
	 */
	public function not_delivered($routeOrderId, $courierUserId, $data, $evidence = null)
	{
		$ci =& get_instance();
		$ro = $this->authorize_route_order($routeOrderId, $courierUserId);
		if (!$ro) {
			return array('success' => false, 'error' => 'Entrega no encontrada o sin acceso.');
		}
		if (!in_array($ro->route_status, array('pendiente', 'en_proceso'), true)) {
			return array('success' => false, 'error' => 'La entrega ya fue procesada.');
		}

		$reason = (int)$data['failure_reason_id'];
		$reasonRow = $ci->db->where('id', $reason)->get('delivery_failure_reasons')->row();
		if (!$reasonRow) {
			return array('success' => false, 'error' => 'Debe seleccionar un motivo de no entrega.');
		}
		if ((int)$reasonRow->requires_description === 1 && empty($data['failure_other'])) {
			return array('success' => false, 'error' => 'Debe describir el motivo (opción "Otro").');
		}

		$reschedule = !empty($data['reschedule_requested']);
		$rescheduledDate = $reschedule && !empty($data['rescheduled_date']) ? $data['rescheduled_date'] : null;
		if ($reschedule && !$rescheduledDate) {
			return array('success' => false, 'error' => 'Debe indicar la nueva fecha de entrega.');
		}

		// Rechazo temprano si PHP mismo descarto la subida por limites del servidor
		$uploadProblem = $this->upload_problem($evidence);
		if ($uploadProblem !== null) {
			return array('success' => false, 'error' => $uploadProblem);
		}

		$ci->db->trans_start();

		$attachmentId = null;
		if ($evidence && !empty($evidence['tmp_name'])) {
			$attachmentId = $this->store_evidence($ro->order_id, null, 'evidencia_intento', $courierUserId, $evidence);
			if (!$attachmentId) {
				$ci->db->trans_rollback();
				return array('success' => false, 'error' => 'La evidencia no es válida (solo JPG/PNG/WEBP, max ' . app_setting('evidence_max_size_mb', 8) . ' MB).');
			}
		}

		$ci->db->insert('delivery_attempts', array(
			'order_id' => $ro->order_id,
			'route_id' => $ro->route_id,
			'route_order_id' => $routeOrderId,
			'courier_user_id' => $courierUserId,
			'status' => 'no_entregado',
			'failure_reason_id' => $reason,
			'failure_other' => !empty($data['failure_other']) ? $data['failure_other'] : null,
			'notes' => !empty($data['notes']) ? $data['notes'] : null,
			'attempted_at' => date('Y-m-d H:i:s'),
			'latitude' => isset($data['latitude']) ? $data['latitude'] : null,
			'longitude' => isset($data['longitude']) ? $data['longitude'] : null,
			'reschedule_requested' => $reschedule ? 1 : 0,
			'rescheduled_date' => $rescheduledDate,
			'created_at' => date('Y-m-d H:i:s'),
		));
		$attemptId = $ci->db->insert_id();

		if ($attachmentId) {
			$ci->db->where('id', $attachmentId)->update('attachments', array('delivery_attempt_id' => $attemptId));
		}

		$ci->db->where('id', $routeOrderId)->update('route_orders', array(
			'route_status' => 'no_entregado',
			'completed_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));

		if ($reschedule) {
			// Reprogramado: retirar de ruta activa, dejar disponible y guardar nueva fecha
			$ci->db->where('id', $routeOrderId)->update('route_orders', array('active_key' => null));
			$ci->db->where('id', $ro->order_id)->update('store_orders', array(
				'status' => 'reprogramado',
				'rescheduled_delivery_date' => $rescheduledDate,
				'updated_at' => date('Y-m-d H:i:s'),
			));
			$this->order_service()->record_status_change($ro->order_id, $ro->order_status, 'reprogramado', $courierUserId, 'Reprogramado para ' . $rescheduledDate, $courierUserId, $ro->route_id);
		} else {
			$ci->db->where('id', $ro->order_id)->update('store_orders', array(
				'status' => 'no_entregado',
				'updated_at' => date('Y-m-d H:i:s'),
			));
			$this->order_service()->record_status_change($ro->order_id, $ro->order_status, 'no_entregado', $courierUserId, isset($data['notes']) ? $data['notes'] : null, $courierUserId, $ro->route_id);
		}

		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible registrar la entrega.');
		}

		$this->audit_service()->log('delivery.not_delivered', 'entregas', 'delivery_attempts', $attemptId, array(
			'order_id' => $ro->order_id,
			'reason' => $reasonRow->name,
			'reschedule' => $reschedule,
		));

		return array('success' => true, 'error' => null, 'attempt_id' => $attemptId);
	}

	/**
	 * Traduce los errores de subida de PHP a mensajes claros para el mensajero.
	 * Critical: cuando el POST excede los limites del servidor, PHP vacia
	 * $_POST y $_FILES y la peticion se rechazaria por CSRF sin explicacion.
	 *
	 * @param array|null $evidence
	 * @return string|null null si no hay problema de subida
	 */
	private function upload_problem($evidence)
	{
		if (!$evidence || !isset($evidence['error']) || $evidence['error'] === UPLOAD_ERR_NO_FILE) {
			return null;
		}
		switch ($evidence['error']) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return 'La foto supera el tamaño máximo permitido por el servidor (' . app_setting('evidence_max_size_mb', 8) . ' MB). Tome una foto de menor tamaño.';
			case UPLOAD_ERR_PARTIAL:
				return 'La foto no se subió completa. Verifique su conexión e intente de nuevo.';
			case UPLOAD_ERR_NO_TMP_DIR:
			case UPLOAD_ERR_CANT_WRITE:
				return 'El servidor no pudo recibir la foto. Contacte al administrador.';
		}
		return null;
	}

	/**
	 * Valida y guarda una evidencia.
	 *
	 * @param int $orderId
	 * @param int|null $attemptId
	 * @param string $type
	 * @param int $userId
	 * @param array $file $_FILES item
	 * @return int|false id del attachment
	 */
	public function store_evidence($orderId, $attemptId, $type, $userId, $file)
	{
		$ci =& get_instance();

		$allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
		$maxBytes = (float)app_setting('evidence_max_size_mb', 8) * 1024 * 1024;

		// Validacion MIME real
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($file['tmp_name']);
		if (!isset($allowed[$mime])) {
			return false;
		}
		if ($file['size'] > $maxBytes || $file['size'] <= 0) {
			return false;
		}

		$dir = FCPATH . 'uploads/delivery_evidence/';
		if (!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		$name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
		$dest = $dir . $name;

		if (!move_uploaded_file($file['tmp_name'], $dest)) {
			return false;
		}

		// Miniatura
		$thumb = 'thumb_' . $name;
		$thumbPath = $dir . $thumb;
		$this->make_thumbnail($dest, $thumbPath, $mime);

		$ci->db->insert('attachments', array(
			'order_id' => $orderId,
			'delivery_attempt_id' => $attemptId,
			'type' => $type,
			'filename' => $name,
			'original_name' => isset($file['name']) ? $file['name'] : null,
			'mime_type' => $mime,
			'size_bytes' => $file['size'],
			'thumbnail' => $thumb,
			'uploaded_by' => $userId,
			'created_at' => date('Y-m-d H:i:s'),
		));
		return $ci->db->insert_id();
	}

	/**
	 * Genera una miniatura JPEG para la evidencia.
	 *
	 * @param string $src
	 * @param string $dest
	 * @param string $mime
	 */
	private function make_thumbnail($src, $dest, $mime)
	{
		switch ($mime) {
			case 'image/jpeg': $img = @imagecreatefromjpeg($src); break;
			case 'image/png': $img = @imagecreatefrompng($src); break;
			case 'image/webp': $img = @imagecreatefromwebp($src); break;
			default: return;
		}
		if (!$img) {
			return;
		}
		$w = imagesx($img);
		$h = imagesy($img);
		$tw = 240;
		$th = (int)round($h * ($tw / $w));
		$thumb = imagecreatetruecolor($tw, $th);
		imagecopyresampled($thumb, $img, 0, 0, 0, 0, $tw, $th, $w, $h);
		imagejpeg($thumb, $dest, 80);
		imagedestroy($img);
		imagedestroy($thumb);
	}

	private function order_service()
	{
		$ci =& get_instance();
		if (!isset($ci->order_service)) {
			$ci->load->library('Order_service');
		}
		return $ci->order_service;
	}

	private function payment_service()
	{
		$ci =& get_instance();
		if (!isset($ci->payment_service)) {
			$ci->load->library('Payment_service');
		}
		return $ci->payment_service;
	}

	private function audit_service()
	{
		$ci =& get_instance();
		if (!isset($ci->audit_service)) {
			$ci->load->library('Audit_service');
		}
		return $ci->audit_service;
	}
}
