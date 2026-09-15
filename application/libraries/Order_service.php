<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Servicio de pedidos: creacion, edicion, estados y numeracion.
 * Los totales se calculan siempre en servidor.
 */
class Order_service
{
	/**
	 * Genera el siguiente numero de pedido.
	 * Formato: PED-YYYYMMDD-XXXX
	 *
	 * @param string $date Fecha interna (yyyy-mm-dd)
	 * @return string
	 */
	public function next_order_number($date = null)
	{
		$ci =& get_instance();
		$date = $date ?: date('Y-m-d');
		$prefix = 'PED-' . date('Ymd', strtotime($date)) . '-';

		$ci->db->select('order_number')
			->from('store_orders')
			->where('country_id', current_country_id())
			->like('order_number', $prefix, 'after')
			->order_by('order_number', 'DESC')
			->limit(1);
		$row = $ci->db->get()->row();

		if ($row) {
			$seq = (int)substr($row->order_number, -4) + 1;
		} else {
			$seq = 1;
		}

		return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
	}

	/**
	 * Crea un pedido con sus lineas de detalle.
	 *
	 * @param array $order Datos del pedido (sin montos ni estado)
	 * @param array $items Lineas: [['product_id','item_sku','item_name','quantity','unit_price','is_manual','description'], ...]
	 * @param int $userId
	 * @return array ['success' => bool, 'id' => int, 'error' => string|null]
	 */
	public function create($order, $items, $userId)
	{
		$ci =& get_instance();

		$stockError = $this->check_stock($items);
		if ($stockError !== null) {
			return array('success' => false, 'id' => null, 'error' => $stockError);
		}

		$ci->db->trans_start();

		$totals = $this->calculate_totals($items, isset($order['discount']) ? $order['discount'] : 0);

		$orderData = array_merge($order, array(
			'country_id' => current_country_id(),
			'subtotal' => $totals['subtotal'],
			'discount' => $totals['discount'],
			'total' => $totals['total'],
			'paid_amount' => $totals['paid'],
			'balance_amount' => $totals['total'] - $totals['paid'],
			'status' => isset($order['status']) && $order['status'] ? $order['status'] : 'registrado',
			'created_by' => $userId,
			'updated_by' => $userId,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));
		unset($orderData['discount_raw']);

		$ci->db->insert('store_orders', $orderData);
		$orderId = $ci->db->insert_id();

		$this->insert_items($orderId, $items);
		$this->adjust_stock($items, 'deduct');
		$this->record_status_change($orderId, null, $orderData['status'], $userId);

		$ci->db->trans_complete();

		if ($ci->db->trans_status() === false) {
			return array('success' => false, 'id' => null, 'error' => 'No fue posible guardar el pedido.');
		}

		$ci->audit_service->log('order.create', 'pedidos', 'orders', $orderId, array(
			'order_number' => $orderData['order_number'],
			'total' => $totals['total'],
		));

		return array('success' => true, 'id' => $orderId, 'error' => null);
	}

	/**
	 * Actualiza un pedido y su detalle.
	 *
	 * @param int $orderId
	 * @param array $order
	 * @param array $items
	 * @param int $userId
	 * @param array $skip Si ['status'] no se permite tocar el estado
	 * @return array
	 */
	public function update($orderId, $order, $items, $userId, $skip = array())
	{
		$ci =& get_instance();
		$current = $ci->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$current) {
			return array('success' => false, 'error' => 'El pedido no existe.');
		}

		$ci->db->trans_start();

		// Repone el stock que consumió el pedido anterior y valida el nuevo detalle
		$previous = $ci->db->where('order_id', $orderId)->get('store_order_items')->result_array();
		$this->adjust_stock($previous, 'restore');

		$stockError = $this->check_stock($items);
		if ($stockError !== null) {
			$ci->db->trans_rollback();
			return array('success' => false, 'error' => $stockError);
		}

		$totals = $this->calculate_totals($items, isset($order['discount']) ? $order['discount'] : $current->discount);

		$orderData = array_merge($order, array(
			'subtotal' => $totals['subtotal'],
			'discount' => $totals['discount'],
			'total' => $totals['total'],
			'paid_amount' => $current->paid_amount,
			'balance_amount' => max(0, round($totals['total'] - (float)$current->paid_amount, 2)),
			'updated_by' => $userId,
			'updated_at' => date('Y-m-d H:i:s'),
		));
		if (isset($orderData['status'])) {
			unset($orderData['status']);
		}

		$ci->db->where('id', $orderId)->update('store_orders', $orderData);
		$ci->db->where('order_id', $orderId)->delete('store_order_items');
		$this->insert_items($orderId, $items);
		$this->adjust_stock($items, 'deduct');

		if (!in_array('status', $skip, true) && isset($order['status']) && $order['status'] && $order['status'] !== $current->status) {
			$this->record_status_change($orderId, $current->status, $order['status'], $userId, isset($order['status_note']) ? $order['status_note'] : null);
			$ci->db->where('id', $orderId)->update('store_orders', array('status' => $order['status']));
		}

		$ci->db->trans_complete();

		if ($ci->db->trans_status() === false) {
			return array('success' => false, 'error' => 'No fue posible actualizar el pedido.');
		}

		$ci->audit_service->log('order.update', 'pedidos', 'orders', $orderId, array(
			'order_number' => $current->order_number,
			'total' => $totals['total'],
		));

		return array('success' => true, 'id' => $orderId, 'error' => null);
	}

	/**
	 * Cambia el estado de un pedido con auditoria e historial.
	 *
	 * @param int $orderId
	 * @param string $toStatus
	 * @param int $userId
	 * @param string|null $notes
	 * @param int|null $courierUserId
	 * @param int|null $routeId
	 * @return bool
	 */
	public function change_status($orderId, $toStatus, $userId, $notes = null, $courierUserId = null, $routeId = null)
	{
		$ci =& get_instance();
		$current = $ci->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$current) {
			return false;
		}

		$ci->db->trans_start();

		// Al anular un pedido se repone el stock de sus líneas
		if ($toStatus === 'cancelado' && $current->status !== 'cancelado') {
			$items = $ci->db->where('order_id', $orderId)->get('store_order_items')->result_array();
			$this->adjust_stock($items, 'restore');
		}

		$ci->db->where('id', $orderId)->update('store_orders', array(
			'status' => $toStatus,
			'updated_by' => $userId,
			'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->record_status_change($orderId, $current->status, $toStatus, $userId, $notes, $courierUserId, $routeId);
		$ci->db->trans_complete();

		$ok = $ci->db->trans_status();

		if ($ok) {
			$ci->audit_service->log('order.status_change', 'pedidos', 'orders', $orderId, array(
				'from' => $current->status,
				'to' => $toStatus,
				'notes' => $notes,
			));
		}

		return $ok;
	}

	/**
	 * Calcula totales del pedido en servidor.
	 *
	 * @param array $items
	 * @param float $discount
	 * @return array ['subtotal','discount','total','paid']
	 */
	public function calculate_totals($items, $discount = 0)
	{
		$subtotal = 0.0;
		foreach ($items as $item) {
			$qty = $this->_int_qty(isset($item['quantity']) ? $item['quantity'] : 1);
			$price = (float)$item['unit_price'];
			$subtotal += $qty * $price;
		}
		$discount = max(0.0, (float)$discount);
		$discount = min($discount, $subtotal);
		$total = $subtotal - $discount;
		$paid = 0.0;

		return array(
			'subtotal' => round($subtotal, 2),
			'discount' => round($discount, 2),
			'total' => round($total, 2),
			'paid' => round($paid, 2),
		);
	}

	/**
	 * Valida que exista stock suficiente para las líneas de productos.
	 * Aplica a todos los productos referenciados en el detalle.
	 *
	 * @param array $items
	 * @return string|null Mensaje de error o null si hay stock.
	 */
	public function check_stock($items)
	{
		$ci =& get_instance();
		$needed = array();
		foreach ($items as $item) {
			$pid = isset($item['product_id']) ? (int)$item['product_id'] : 0;
			if ($pid <= 0) {
				continue;
			}
			$qty = $this->_int_qty(isset($item['quantity']) ? $item['quantity'] : 1);
			$needed[$pid] = isset($needed[$pid]) ? $needed[$pid] + $qty : $qty;
		}
		if (empty($needed)) {
			return null;
		}

		$products = $ci->db->where_in('id', array_keys($needed))
			->where('stock_enabled', 1)
			->where('country_id', current_country_id())
			->get('products')->result();
		$missing = array();
		foreach ($products as $p) {
			$available = (int)$p->stock_qty;
			$requested = $needed[$p->id];
			if ($requested > $available) {
				$missing[] = $p->name . ' (disponible ' . $available . ', solicitado ' . $requested . ')';
			}
		}
		if (empty($missing)) {
			return null;
		}
		return 'Stock insuficiente para: ' . implode(', ', $missing) . '.';
	}

	/**
	 * Descuenta o repone el stock de las líneas de productos.
	 *
	 * @param array $items
	 * @param string $op 'deduct' para restar, 'restore' para sumar.
	 */
	public function adjust_stock($items, $op)
	{
		$ci =& get_instance();
		$byProduct = array();
		foreach ($items as $item) {
			$pid = isset($item['product_id']) ? (int)$item['product_id'] : 0;
			if ($pid <= 0) {
				continue;
			}
			$qty = $this->_int_qty(isset($item['quantity']) ? $item['quantity'] : 1);
			if (!isset($byProduct[$pid])) {
				$byProduct[$pid] = 0;
			}
			$byProduct[$pid] += $qty;
		}
		if (empty($byProduct)) {
			return;
		}

		foreach ($byProduct as $pid => $qty) {
			$ci->db->set('stock_qty', 'stock_qty ' . ($op === 'restore' ? '+' : '-') . ' ' . $this->_sql_number($qty), false)
				->where('id', $pid)
				->where('stock_enabled', 1)
				->where('country_id', current_country_id())
				->update('products');
		}
	}

	/**
	 * Formatea un número para usarlo como literal SQL.
	 *
	 * @param int $value
	 * @return string
	 */
	private function _sql_number($value)
	{
		return (string)(int)$value;
	}

	/**
	 * Convierte una cantidad a entero (redondea y asegura minimo 1).
	 *
	 * @param mixed $value
	 * @return int
	 */
	private function _int_qty($value)
	{
		return max(1, (int)round((float)$value));
	}

	/**
	 * Inserta lineas de detalle.
	 *
	 * @param int $orderId
	 * @param array $items
	 */
	private function insert_items($orderId, $items)
	{
		$ci =& get_instance();
		foreach ($items as $item) {
			$qty = $this->_int_qty(isset($item['quantity']) ? $item['quantity'] : 1);
			$price = (float)$item['unit_price'];
			$ci->db->insert('store_order_items', array(
				'order_id' => $orderId,
				'product_id' => isset($item['product_id']) && $item['product_id'] ? $item['product_id'] : null,
				'item_sku' => isset($item['item_sku']) ? $item['item_sku'] : null,
				'item_name' => $item['item_name'],
				'description' => isset($item['description']) ? $item['description'] : null,
				'quantity' => $qty,
				'unit_price' => $price,
				'line_total' => round($qty * $price, 2),
				'is_manual' => !empty($item['is_manual']) ? 1 : 0,
				'created_at' => date('Y-m-d H:i:s'),
			));
		}
	}

	/**
	 * Elimina fisicamente un pedido y sus dependencias (FK con CASCADE).
	 * Solo aplica para pedidos que no esten asignados a una ruta activa
	 * ni en estados de entrega (entregado, en_ruta, asignado_ruta).
	 *
	 * @param int $orderId
	 * @param int $userId
	 * @return array
	 */
	public function delete($orderId, $userId)
	{
		$ci =& get_instance();
		$order = $ci->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order) {
			return array('success' => false, 'error' => 'El pedido no existe.');
		}
		if (in_array($order->status, array('asignado_ruta', 'en_ruta', 'entregado'), true)) {
			return array('success' => false, 'error' => 'El pedido no puede eliminarse en su estado actual.');
		}
		$active = $ci->db->where('order_id', $orderId)->where('active_key', 1)->get('route_orders')->row();
		if ($active) {
			return array('success' => false, 'error' => 'El pedido está asignado a una ruta; no puede eliminarse.');
		}

		$ci->db->trans_start();

		// Repone el stock de las líneas antes de borrar el pedido.
		// Si ya estaba anulado, el stock fue repuesto al anular: no se reintegra dos veces.
		if ($order->status !== 'cancelado') {
			$items = $ci->db->where('order_id', $orderId)->get('store_order_items')->result_array();
			$this->adjust_stock($items, 'restore');
		}

		$ci->db->where('id', $orderId)->delete('store_orders');
		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible eliminar el pedido.');
		}

		$ci->load->library('Audit_service');
		$ci->audit_service->log('order.delete', 'pedidos', 'orders', $orderId, array(
			'order_number' => $order->order_number,
			'status' => $order->status,
		));

		return array('success' => true, 'error' => null);
	}

	/**
	 * Registra un cambio de estado en el historial.
	 *
	 * @param int $orderId
	 * @param string|null $fromStatus
	 * @param string $toStatus
	 * @param int|null $userId
	 * @param string|null $notes
	 * @param int|null $courierUserId
	 * @param int|null $routeId
	 */
	public function record_status_change($orderId, $fromStatus, $toStatus, $userId = null, $notes = null, $courierUserId = null, $routeId = null)
	{
		$ci =& get_instance();
		$ci->db->insert('order_status_history', array(
			'order_id' => $orderId,
			'from_status' => $fromStatus,
			'to_status' => $toStatus,
			'user_id' => $userId,
			'courier_user_id' => $courierUserId,
			'ip_address' => $ci->input->ip_address(),
			'user_agent' => substr((string)$ci->input->user_agent(), 0, 255),
			'notes' => $notes,
			'route_id' => $routeId,
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	/**
	 * Guarda un comprobante de pago opcional asociado al pedido.
	 * El archivo se almacena en uploads/payment_receipts/ y se registra
	 * en attachments con tipo 'comprobante_pago'.
	 *
	 * @param int $orderId
	 * @param int $userId
	 * @param array $file Item de $_FILES
	 * @return int|false id del attachment
	 */
	public function store_payment_receipt($orderId, $userId, $file)
	{
		$ci =& get_instance();

		$allowed = array(
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/webp' => 'webp',
			'application/pdf' => 'pdf',
		);
		$maxBytes = (float)app_setting('evidence_max_size_mb', 8) * 1024 * 1024;

		if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
			return false;
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($file['tmp_name']);
		if (!isset($allowed[$mime])) {
			return false;
		}
		if ($file['size'] > $maxBytes || $file['size'] <= 0) {
			return false;
		}

		$dir = FCPATH . 'uploads/payment_receipts/';
		if (!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		$name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
		$dest = $dir . $name;

		if (!move_uploaded_file($file['tmp_name'], $dest)) {
			return false;
		}

		// Miniatura solo para imágenes
		$thumb = null;
		if ($mime !== 'application/pdf') {
			$thumb = 'thumb_' . $name;
			$thumbPath = $dir . $thumb;
			$this->make_thumbnail($dest, $thumbPath, $mime);
		}

		$ci->db->insert('attachments', array(
			'order_id' => $orderId,
			'delivery_attempt_id' => null,
			'type' => 'comprobante_pago',
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
	 * Genera una miniatura JPEG para el comprobante de pago.
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
		$th = (int)max(1, round($h * $tw / $w));
		$thumb = imagecreatetruecolor($tw, $th);
		if ($mime === 'image/png' || $mime === 'image/webp') {
			imagealphablending($thumb, false);
			imagesavealpha($thumb, true);
		}
		imagecopyresampled($thumb, $img, 0, 0, 0, 0, $tw, $th, $w, $h);
		if ($mime === 'image/png') {
			imagepng($thumb, $dest);
		} elseif ($mime === 'image/webp') {
			imagewebp($thumb, $dest);
		} else {
			imagejpeg($thumb, $dest, 80);
		}
		imagedestroy($img);
		imagedestroy($thumb);
	}
}

