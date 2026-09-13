<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Carrito de SG Tienda basado en sesion.
 * Solo almacena product_id => cantidad. Los precios y el stock SIEMPRE
 * se leen de la base de datos (nunca se confia en el cliente).
 */
class Store_cart
{
	protected $ci;
	protected $key = 'store_cart';
	protected $country_key = 'store_cart_country_id';

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	protected function raw()
	{
		$cart = $this->ci->session->userdata($this->key);
		return is_array($cart) ? $cart : array();
	}

	protected function save($cart)
	{
		$this->ci->session->set_userdata($this->key, $cart);
	}

	public function items()
	{
		return $this->raw();
	}

	public function is_empty()
	{
		return count($this->raw()) === 0;
	}

	public function count()
	{
		$total = 0;
		foreach ($this->raw() as $qty)
		{
			$total += (int) $qty;
		}
		return $total;
	}

	public function cart_country_id()
	{
		return (int) $this->ci->session->userdata($this->country_key);
	}

	public function has_foreign_items($country_id)
	{
		return ( ! $this->is_empty() && $this->cart_country_id() !== (int) $country_id);
	}

	public function add($product, $quantity = 1, $country_id = NULL)
	{
		$country_id = ($country_id === NULL) ? current_store_country_id() : (int) $country_id;
		if ($this->has_foreign_items($country_id))
		{
			$this->clear();
		}

		$quantity = max(1, (int) $quantity);
		$cart = $this->raw();
		$id = (int) $product->id;
		$current = isset($cart[$id]) ? (int) $cart[$id] : 0;
		$desired = $current + $quantity;

		if ((int) $product->stock_enabled === 1)
		{
			if ((int) $product->stock_qty < 1 || $desired > (int) $product->stock_qty)
			{
				return FALSE;
			}
		}

		$cart[$id] = $desired;
		$this->save($cart);
		$this->ci->session->set_userdata($this->country_key, $country_id);
		return TRUE;
	}

	public function update($product_id, $quantity, $country_id = NULL)
	{
		$quantity = (int) $quantity;
		$cart = $this->raw();
		$id = (int) $product_id;

		if ( ! isset($cart[$id]))
		{
			return;
		}

		if ($quantity < 1)
		{
			unset($cart[$id]);
			$this->save($cart);
			return;
		}

		$product = $this->ci->db->where('id', $id)->get('products')->row();
		if ($product && (int) $product->stock_enabled === 1)
		{
			$quantity = min($quantity, max(1, (int) $product->stock_qty));
		}
		$cart[$id] = $quantity;
		$this->save($cart);
	}

	public function remove($product_id)
	{
		$cart = $this->raw();
		unset($cart[(int) $product_id]);
		$this->save($cart);
	}

	public function clear()
	{
		$this->ci->session->unset_userdata($this->key);
		$this->ci->session->unset_userdata($this->country_key);
	}

	public function set_country($country_id)
	{
		if ($this->cart_country_id() !== (int) $country_id)
		{
			$this->clear();
			$this->ci->session->set_userdata($this->country_key, (int) $country_id);
		}
	}

	/**
	 * Devuelve las lineas reales del carrito para el pais dado.
	 * Descarta productos inexistentes, inactivos o de otro pais.
	 */
	public function contents($country_id = NULL)
	{
		$country_id = ($country_id === NULL) ? current_store_country_id() : (int) $country_id;
		$raw = $this->raw();
		if (empty($raw))
		{
			return array();
		}

		$ids = array_map('intval', array_keys($raw));
		$products = $this->ci->db->where_in('id', $ids)
			->where('country_id', $country_id)
			->where('is_active', 1)
			->get('products')->result();

		$by_id = array();
		foreach ($products as $product)
		{
			$by_id[(int) $product->id] = $product;
		}

		$lines = array();
		foreach ($raw as $id => $qty)
		{
			$id = (int) $id;
			if ( ! isset($by_id[$id]))
			{
				continue;
			}
			$product = $by_id[$id];
			$quantity = max(1, (int) $qty);
			if ((int) $product->stock_enabled === 1)
			{
				$quantity = min($quantity, max(0, (int) $product->stock_qty));
			}
			if ($quantity < 1)
			{
				continue;
			}
			$unit = (float) $product->unit_price;
			$lines[] = array(
				'product_id' => $id,
				'name'       => $product->name,
				'sku'        => $product->sku,
				'laboratory' => $product->laboratory,
				'unit_price' => $unit,
				'quantity'   => $quantity,
				'line_total' => $unit * $quantity,
				'available'  => store_product_available($product),
				'product'    => $product,
			);
		}

		return $lines;
	}

	public function subtotal($country_id = NULL)
	{
		$subtotal = 0;
		foreach ($this->contents($country_id) as $line)
		{
			$subtotal += $line['line_total'];
		}
		return $subtotal;
	}

	public function shipping($subtotal, $country_id = NULL)
	{
		$country_id = ($country_id === NULL) ? current_store_country_id() : (int) $country_id;
		$cost = (float) store_setting('shipping_cost', 0, $country_id);
		$free_from = (float) store_setting('free_shipping_from', 0, $country_id);
		if ($free_from > 0 && $subtotal >= $free_from)
		{
			return 0.0;
		}
		return $cost;
	}
}
