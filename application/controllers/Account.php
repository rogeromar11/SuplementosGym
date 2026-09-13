<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends MY_Controller
{
	public function index()
	{
		$this->require_login();
		$user = $this->ion_auth->user()->row();

		$this->render_store('store/account/profile', array(
			'user' => $user,
		), array(
			'title'  => 'Mi perfil · SG Tienda',
			'robots' => 'noindex,follow',
		));
	}

	public function update()
	{
		$this->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('cuenta');
		}

		$user = $this->ion_auth->user()->row();

		$this->form_validation->set_rules('first_name', 'Nombre', 'trim|required|max_length[60]');
		$this->form_validation->set_rules('email', 'Correo', 'trim|required|valid_email|max_length[254]');
		$this->form_validation->set_rules('phone', 'Numero celular', 'trim|required|max_length[30]');
		$this->form_validation->set_rules('phone2', 'Telefono secundario', 'trim|max_length[30]');
		$this->form_validation->set_rules('delivery_zone', 'Zona de entrega', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('delivery_address', 'Direccion', 'trim|required');

		$email = strtolower(trim((string) $this->input->post('email')));
		if ($email !== strtolower((string) $user->email))
		{
			$exists = $this->db->where('email', $email)->where('id !=', $user->id)->count_all_results('users') > 0;
			if ($exists)
			{
				$this->session->set_flashdata('store_error', 'Ese correo ya esta registrado.');
				redirect('cuenta');
			}
		}

		if ($this->form_validation->run() === FALSE)
		{
			$this->session->set_flashdata('store_error', trim(strip_tags(validation_errors())));
			redirect('cuenta');
		}

		$this->ion_auth->update($user->id, array(
			'first_name'       => $this->input->post('first_name', TRUE),
			'email'            => $email,
			'phone'            => $this->input->post('phone', TRUE),
			'phone2'           => $this->input->post('phone2', TRUE),
			'delivery_zone'    => $this->input->post('delivery_zone', TRUE),
			'delivery_address' => $this->input->post('delivery_address', TRUE),
		));

		$this->session->set_flashdata('store_success', 'Perfil actualizado.');
		redirect('cuenta');
	}

	public function password()
	{
		$this->require_login();

		if ($this->input->method() !== 'post')
		{
			redirect('cuenta');
		}

		$this->form_validation->set_rules('old', 'Contrasena actual', 'required');
		$this->form_validation->set_rules('new', 'Nueva contrasena', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', 'Confirmar contrasena', 'required');

		if ($this->form_validation->run() === FALSE)
		{
			$this->session->set_flashdata('store_error', trim(strip_tags(validation_errors())));
			redirect('cuenta');
		}

		$identity = $this->session->userdata('identity');
		$changed = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));

		if ( ! $changed)
		{
			$this->session->set_flashdata('store_error', trim(strip_tags($this->ion_auth->errors())));
			redirect('cuenta');
		}

		$this->ion_auth->logout();
		$this->session->set_flashdata('store_success', 'Contrasena actualizada. Inicia sesion nuevamente.');
		redirect('ingresar');
	}

	public function orders()
	{
		$this->require_login();
		$user = $this->ion_auth->user()->row();

		$this->render_store('store/account/orders', array(
			'orders' => $this->Store_order_model->for_user($user->id),
		), array(
			'title'  => 'Mis pedidos · SG Tienda',
			'robots' => 'noindex,follow',
		));
	}

	public function order($id = 0)
	{
		$this->require_login();
		$user = $this->ion_auth->user()->row();
		$order = $this->Store_order_model->find_for_user((int) $id, $user->id);

		if ( ! $order)
		{
			show_404();
		}

		$this->render_store('store/account/order', array(
			'order' => $order,
		), array(
			'title'  => 'Pedido ' . $order->order_number . ' · SG Tienda',
			'robots' => 'noindex,follow',
		));
	}
}
