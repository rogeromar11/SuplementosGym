<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller
{
	public function index()
	{
		$country = current_store_country();
		$contents = $this->store_cart->contents($country->id);
		$subtotal = 0;
		foreach ($contents as $line)
		{
			$subtotal += $line['line_total'];
		}
		$shipping = $this->store_cart->shipping($subtotal, $country->id);

		$this->render_store('store/cart', array(
			'contents' => $contents,
			'subtotal' => $subtotal,
			'shipping' => $shipping,
			'total'    => $subtotal + $shipping,
		), array(
			'title'       => 'Carrito · SG Tienda',
			'description' => 'Revisa los productos de tu carrito.',
			'robots'      => 'noindex,follow',
		));
	}

	public function add()
	{
		if ($this->input->method() !== 'post')
		{
			return $this->json_response(array('success' => FALSE, 'message' => 'Metodo no permitido.'), 405);
		}

		$country = current_store_country();
		$product_id = (int) $this->input->post('product_id');
		$quantity = (int) $this->input->post('quantity');
		$quantity = $quantity > 0 ? $quantity : 1;

		$product = $this->Product_model->find_active($product_id, $country->id);
		if ( ! $product)
		{
			return $this->json_response(array('success' => FALSE, 'message' => 'Producto no disponible.'), 404);
		}

		if ( ! store_product_available($product))
		{
			return $this->json_response(array('success' => FALSE, 'message' => 'Agotado: no hay existencias disponibles.'), 409);
		}

		$added = $this->store_cart->add($product, $quantity, $country->id);
		if ( ! $added)
		{
			return $this->json_response(array('success' => FALSE, 'message' => 'La cantidad solicitada supera la disponibilidad actual.'), 409);
		}

		return $this->json_response(array(
			'success'  => TRUE,
			'message'  => 'Producto agregado al carrito.',
			'count'    => $this->store_cart->count(),
			'subtotal' => store_price($this->store_cart->subtotal($country->id)),
		));
	}

	public function update()
	{
		if ($this->input->method() !== 'post')
		{
			return $this->json_response(array('success' => FALSE, 'message' => 'Metodo no permitido.'), 405);
		}

		$country = current_store_country();
		$product_id = (int) $this->input->post('product_id');
		$quantity = (int) $this->input->post('quantity');

		$product = $this->Product_model->find($product_id, $country->id);
		if ( ! $product)
		{
			return $this->json_response(array('success' => FALSE, 'message' => 'Producto no disponible.'), 404);
		}

		$max = ((int) $product->stock_enabled === 1) ? (int) $product->stock_qty : 99;
		if ($quantity > $max)
		{
			return $this->json_response(array(
				'success'  => FALSE,
				'message'  => 'La cantidad solicitada supera la disponibilidad actual.',
				'quantity' => max(1, $max),
			), 409);
		}

		$this->store_cart->update($product_id, $quantity, $country->id);

		return $this->json_response($this->summary_payload($country->id));
	}

	public function remove()
	{
		if ($this->input->method() !== 'post')
		{
			return $this->json_response(array('success' => FALSE, 'message' => 'Metodo no permitido.'), 405);
		}

		$country = current_store_country();
		$this->store_cart->remove((int) $this->input->post('product_id'));

		return $this->json_response($this->summary_payload($country->id));
	}

	public function clear()
	{
		if ($this->input->method() !== 'post')
		{
			return $this->json_response(array('success' => FALSE, 'message' => 'Metodo no permitido.'), 405);
		}

		$this->store_cart->clear();

		return $this->json_response(array(
			'success'  => TRUE,
			'count'    => 0,
			'subtotal' => store_price(0),
			'shipping' => store_price(0),
			'total'    => store_price(0),
			'lines'    => array(),
		));
	}

	public function mini()
	{
		$country = current_store_country();
		return $this->json_response($this->summary_payload($country->id));
	}

	protected function summary_payload($country_id)
	{
		$contents = $this->store_cart->contents($country_id);
		$subtotal = 0;
		$lines = array();
		foreach ($contents as $line)
		{
			$subtotal += $line['line_total'];
			$lines[(string) $line['product_id']] = array(
				'line_total' => store_price($line['line_total']),
				'quantity'   => (int) $line['quantity'],
			);
		}
		$shipping = $this->store_cart->shipping($subtotal, $country_id);

		return array(
			'success'  => TRUE,
			'count'    => $this->store_cart->count(),
			'subtotal' => store_price($subtotal),
			'shipping' => store_price($shipping),
			'total'    => store_price($subtotal + $shipping),
			'lines'    => $lines,
		);
	}
}
