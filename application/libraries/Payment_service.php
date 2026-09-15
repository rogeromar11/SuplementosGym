<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Servicio de pagos: registro transaccional, actualizacion de saldos
 * e idempotencia para evitar dobles envios.
 */
class Payment_service
{
	/**
	 * Registra un pago y actualiza los saldos del pedido.
	 *
	 * @param int $orderId
	 * @param int $paymentMethodId
	 * @param float $amount
	 * @param int $userId
	 * @param array $opts Opciones: reference, notes, delivery_attempt_id, idempotency_key, received_at
	 * @return array ['success' => bool, 'error' => string|null, 'payment_id' => int|null]
	 */
	public function register_payment($orderId, $paymentMethodId, $amount, $userId, $opts = array())
	{
		$ci =& get_instance();
		$order = $ci->db->where('id', $orderId)->where('country_id', current_country_id())->get('store_orders')->row();

		if (!$order) {
			return array('success' => false, 'error' => 'El pedido no existe.');
		}

		$amount = round((float)$amount, 2);
		if ($amount <= 0) {
			return array('success' => false, 'error' => 'El monto debe ser mayor a cero.');
		}
		if ($amount > $order->balance_amount + 0.01) {
			return array('success' => false, 'error' => 'El monto supera el saldo pendiente.');
		}

		// Idempotencia: si ya existe un pago con la misma clave de referencia, se rechaza
		$idemKey = isset($opts['idempotency_key']) ? $opts['idempotency_key'] : null;
		if ($idemKey) {
			$existing = $ci->db->where('order_id', $orderId)->where('reference', $idemKey)->get('payments')->row();
			if ($existing) {
				return array('success' => false, 'error' => 'El pago ya fue registrado. Evite el doble envío.');
			}
		}

		$ci->db->trans_start();

		$ci->db->insert('payments', array(
			'order_id' => $orderId,
			'delivery_attempt_id' => isset($opts['delivery_attempt_id']) ? $opts['delivery_attempt_id'] : null,
			'payment_method_id' => $paymentMethodId,
			'amount' => $amount,
			'reference' => $idemKey ? $idemKey : (isset($opts['reference']) ? $opts['reference'] : null),
			'notes' => isset($opts['notes']) ? $opts['notes'] : null,
			'received_at' => isset($opts['received_at']) ? $opts['received_at'] : date('Y-m-d H:i:s'),
			'received_by' => $userId,
			'created_at' => date('Y-m-d H:i:s'),
		));
		$paymentId = $ci->db->insert_id();

		$newPaid = round($order->paid_amount + $amount, 2);
		$newBalance = round($order->total - $newPaid, 2);
		if ($newBalance <= 0.01) {
			$paymentStatus = 'pagado';
		} elseif ($newPaid > 0) {
			$paymentStatus = 'parcial';
		} else {
			$paymentStatus = 'pendiente';
		}

		$ci->db->where('id', $orderId)->update('store_orders', array(
			'paid_amount' => $newPaid,
			'balance_amount' => max(0, $newBalance),
			'payment_status' => $paymentStatus,
			'updated_by' => $userId,
			'updated_at' => date('Y-m-d H:i:s'),
		));

		$ci->db->insert('payment_history', array(
			'order_id' => $orderId,
			'from_payment_method_id' => $order->payment_method_id,
			'to_payment_method_id' => $paymentMethodId,
			'amount' => $amount,
			'previous_balance' => $order->balance_amount,
			'new_balance' => $newBalance,
			'user_id' => $userId,
			'reference' => $idemKey ? $idemKey : (isset($opts['reference']) ? $opts['reference'] : null),
			'notes' => isset($opts['notes']) ? $opts['notes'] : null,
			'created_at' => date('Y-m-d H:i:s'),
		));

		$ci->db->trans_complete();

		if (!$ci->db->trans_status()) {
			return array('success' => false, 'error' => 'No fue posible registrar el pago.');
		}

		$ci->audit_service->log('payment.register', 'pagos', 'payments', $paymentId, array(
			'order_id' => $orderId,
			'amount' => $amount,
			'method' => $paymentMethodId,
			'new_balance' => $newBalance,
		));

		return array('success' => true, 'error' => null, 'payment_id' => $paymentId, 'new_balance' => $newBalance);
	}
}


