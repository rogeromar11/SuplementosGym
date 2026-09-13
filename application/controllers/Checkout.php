<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Checkout extends MY_Controller
{
	public function index()
	{
		$this->require_login();

		$country = current_store_country();
		$contents = $this->store_cart->contents($country->id);

		if (empty($contents))
		{
			$this->session->set_flashdata('store_error', 'Tu carrito esta vacio.');
			redirect('productos');
		}

		$subtotal = 0;
		foreach ($contents as $line)
		{
			$subtotal += $line['line_total'];
		}
		$shipping = $this->store_cart->shipping($subtotal, $country->id);

		$this->render_store('store/checkout', array(
			'contents' => $contents,
			'subtotal' => $subtotal,
			'shipping' => $shipping,
			'total'    => $subtotal + $shipping,
			'methods'  => $this->payment_methods($country->id),
		), array(
			'title'       => 'Finalizar compra · SG Tienda',
			'description' => 'Completa tu pedido.',
			'robots'      => 'noindex,follow',
		));
	}

	public function place()
	{
		$this->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('checkout');
		}

		$country = current_store_country();
		$user = $this->ion_auth->user()->row();
		$contents = $this->store_cart->contents($country->id);

		if (empty($contents))
		{
			$this->session->set_flashdata('store_error', 'Tu carrito esta vacio.');
			redirect('productos');
		}

		$this->form_validation->set_rules('customer_name', 'Nombre', 'trim|required|max_length[150]');
		$this->form_validation->set_rules('customer_phone', 'Numero celular', 'trim|required|max_length[30]');
		$this->form_validation->set_rules('delivery_zone', 'Zona de entrega', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('delivery_address', 'Direccion', 'trim|required');
		$this->form_validation->set_rules('payment_method_id', 'Metodo de pago', 'trim|required|integer');

		if ($this->form_validation->run() === FALSE)
		{
			$this->session->set_flashdata('store_error', trim(strip_tags(validation_errors())));
			redirect('checkout');
		}

		$payment_method = $this->db->where('id', (int) $this->input->post('payment_method_id'))
			->where('country_id', $country->id)
			->where('is_active', 1)
			->get('payment_methods')->row();

		if ( ! $payment_method)
		{
			$this->session->set_flashdata('store_error', 'El metodo de pago seleccionado no es valido para tu pais.');
			redirect('checkout');
		}

		$subtotal = 0;
		$lines = array();
		foreach ($contents as $line)
		{
			$subtotal += $line['line_total'];
			$lines[] = array(
				'product_id' => $line['product_id'],
				'sku'        => $line['sku'],
				'name'       => $line['name'],
				'unit_price' => $line['unit_price'],
				'quantity'   => (int) $line['quantity'],
				'line_total' => $line['line_total'],
			);
		}
		$shipping = $this->store_cart->shipping($subtotal, $country->id);
		$total = $subtotal + $shipping;

		$this->db->trans_begin();

		$order_id = $this->Store_order_model->create(array(
			'country_id'        => $country->id,
			'user_id'           => (int) $user->id,
			'order_number'      => $this->Store_order_model->next_number($country->id),
			'customer_name'     => $this->input->post('customer_name', TRUE),
			'customer_email'    => $user->email,
			'customer_phone'    => $this->input->post('customer_phone', TRUE),
			'customer_phone2'   => $this->input->post('customer_phone2', TRUE),
			'delivery_zone'     => $this->input->post('delivery_zone', TRUE),
			'delivery_address'  => $this->input->post('delivery_address', TRUE),
			'subtotal'          => $subtotal,
			'shipping'          => $shipping,
			'total'             => $total,
			'payment_method_id' => (int) $payment_method->id,
			'payment_status'    => 'pendiente',
			'status'            => 'confirmado',
			'inventory_applied' => 0,
			'notes'             => $this->input->post('notes', TRUE),
		));

		if ( ! $order_id)
		{
			$this->db->trans_rollback();
			$this->session->set_flashdata('store_error', 'No fue posible crear el pedido. Intenta nuevamente.');
			redirect('checkout');
		}

		$this->Store_order_model->add_items($order_id, $lines);

		$applied = $this->Inventory_model->apply_for_order($order_id, $lines, (int) $user->id);

		if ( ! $applied || $this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$this->session->set_flashdata('store_error', 'La cantidad solicitada supera la disponibilidad actual.');
			redirect('carrito');
		}

		$this->db->trans_commit();

		$this->sync_user_profile($user->id);
		$this->store_cart->clear();

		$this->session->set_flashdata('store_success', 'Tu pedido fue registrado correctamente.');
		redirect('cuenta/pedido/' . $order_id);
	}

	protected function payment_methods($country_id)
	{
		return $this->db->where('country_id', (int) $country_id)
			->where('is_active', 1)
			->order_by('sort_order', 'ASC')
			->get('payment_methods')->result();
	}

	protected function sync_user_profile($user_id)
	{
		$this->ion_auth->update($user_id, array(
			'phone'            => $this->input->post('customer_phone', TRUE),
			'phone2'           => $this->input->post('customer_phone2', TRUE),
			'delivery_zone'    => $this->input->post('delivery_zone', TRUE),
			'delivery_address' => $this->input->post('delivery_address', TRUE),
		));
	}
}
