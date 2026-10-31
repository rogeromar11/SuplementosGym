<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_model extends CI_Model
{
	protected $table = 'inventory_movements';

	/**
	 * Aplica el descuento de inventario de una orden.
	 * DEBE ejecutarse dentro de una transaccion abierta por el llamador.
	 * Devuelve TRUE si todo se aplico; FALSE si no hay stock suficiente.
	 */
	public function apply_for_order($order_id, $lines, $user_id = NULL)
	{
		$order = $this->db->select('inventory_applied')
			->where('id', (int) $order_id)
			->get('store_orders')->row();

		if ( ! $order)
		{
			return FALSE;
		}

		if ((int) $order->inventory_applied === 1)
		{
			return TRUE;
		}

		foreach ($lines as $line)
		{
			$product = $this->db->query(
				'SELECT id, stock_enabled, stock_qty FROM products WHERE id = ? FOR UPDATE',
				array((int) $line['product_id'])
			)->row();

			if ( ! $product)
			{
				return FALSE;
			}

			if ((int) $product->stock_enabled === 0)
			{
				continue;
			}

			$quantity = (int) $line['quantity'];
			$previous = (int) $product->stock_qty;

			if ($quantity < 1 || $previous < $quantity)
			{
				return FALSE;
			}

			$new = $previous - $quantity;

			$this->db->where('id', $product->id)->update('products', array('stock_qty' => $new));

			$this->db->insert($this->table, array(
				'product_id'   => (int) $product->id,
				'order_id'     => (int) $order_id,
				'type'         => 'salida',
				'quantity'     => $quantity,
				'previous_qty' => $previous,
				'new_qty'      => $new,
				'reason'       => 'Venta tienda',
				'created_by'   => $user_id,
			));
		}

		$this->db->where('id', (int) $order_id)->update('store_orders', array('inventory_applied' => 1));

		return TRUE;
	}

	/**
	 * Repone inventario por una devolución post venta.
	 * DEBE ejecutarse dentro de una transacción abierta por el llamador.
	 *
	 * @param int $order_id
	 * @param array $lines [['product_id' => int, 'quantity' => int], ...]
	 * @param int|null $user_id
	 * @return bool
	 */
	public function restore_for_return($order_id, $lines, $user_id = NULL)
	{
		foreach ((array)$lines as $line)
		{
			$product_id = isset($line['product_id']) ? (int)$line['product_id'] : 0;
			$quantity = isset($line['quantity']) ? (int)$line['quantity'] : 0;
			if ($product_id <= 0 || $quantity <= 0)
			{
				continue;
			}

			$product = $this->db->query(
				'SELECT id, stock_enabled, stock_qty FROM products WHERE id = ? FOR UPDATE',
				array($product_id)
			)->row();

			if ( ! $product || (int)$product->stock_enabled === 0)
			{
				continue;
			}

			$previous = (int)$product->stock_qty;
			$new = $previous + $quantity;

			$this->db->where('id', $product->id)->update('products', array('stock_qty' => $new));

			$this->db->insert($this->table, array(
				'product_id' => (int)$product->id,
				'order_id' => (int)$order_id,
				'type' => 'entrada',
				'quantity' => $quantity,
				'previous_qty' => $previous,
				'new_qty' => $new,
				'reason' => 'Devolución post venta',
				'created_by' => $user_id,
			));
		}

		return TRUE;
	}
}
