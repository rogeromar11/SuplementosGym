<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_auth extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('auth');
	}

	public function login()
	{
		if ($this->ion_auth->logged_in())
		{
			redirect('cuenta');
		}

		$return = $this->input->get('return');
		$this->data['return'] = $this->safe_return($return);

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('identity', 'Correo', 'trim|required|valid_email');
			$this->form_validation->set_rules('password', 'Contrasena', 'required');

			if ($this->form_validation->run() === TRUE)
			{
				$remember = (bool) $this->input->post('remember');
				if ($this->ion_auth->login($this->input->post('identity', TRUE), $this->input->post('password'), $remember))
				{
					$target = $this->safe_return($this->input->post('return'));
					redirect($target ?: 'cuenta');
				}
				$this->data['auth_error'] = trim(strip_tags($this->ion_auth->errors()));
			}
			else
			{
				$this->data['auth_error'] = trim(strip_tags(validation_errors()));
			}
		}

		$this->render_store('store/auth/login', array(
			'auth_error' => isset($this->data['auth_error']) ? $this->data['auth_error'] : '',
		), array('title' => 'Iniciar sesion · SG Tienda', 'robots' => 'noindex,follow'));
	}

	public function register()
	{
		if ($this->ion_auth->logged_in())
		{
			redirect('cuenta');
		}

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('first_name', 'Nombre', 'trim|required|max_length[60]');
			$this->form_validation->set_rules('email', 'Correo', 'trim|required|valid_email|is_unique[users.email]');
			$this->form_validation->set_rules('phone', 'Numero celular', 'trim|required|max_length[30]');
			$this->form_validation->set_rules('phone2', 'Telefono secundario', 'trim|max_length[30]');
			$this->form_validation->set_rules('delivery_zone', 'Zona de entrega', 'trim|required|max_length[100]');
			$this->form_validation->set_rules('delivery_address', 'Direccion', 'trim|required');
			$this->form_validation->set_rules('password', 'Contrasena', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Confirmar contrasena', 'required');

			if ($this->form_validation->run() === TRUE)
			{
				$email = strtolower(trim((string) $this->input->post('email')));
				$additional = array(
					'first_name'       => $this->input->post('first_name', TRUE),
					'phone'            => $this->input->post('phone', TRUE),
					'phone2'           => $this->input->post('phone2', TRUE),
					'delivery_zone'    => $this->input->post('delivery_zone', TRUE),
					'delivery_address' => $this->input->post('delivery_address', TRUE),
					'country_id'       => current_store_country_id(),
				);

				$registered = $this->ion_auth->register($email, $this->input->post('password'), $email, $additional);

				if ($registered)
				{
					$this->ion_auth->activate($registered);
					$this->session->set_flashdata('store_success', 'Cuenta creada. Ya puedes iniciar sesion.');
					redirect('ingresar');
				}

				$this->data['auth_error'] = trim(strip_tags($this->ion_auth->errors()));
			}
			else
			{
				$this->data['auth_error'] = trim(strip_tags(validation_errors()));
			}
		}

		$this->render_store('store/auth/register', array(
			'auth_error' => isset($this->data['auth_error']) ? $this->data['auth_error'] : '',
		), array('title' => 'Crear cuenta · SG Tienda', 'robots' => 'noindex,follow'));
	}

	public function logout()
	{
		$this->ion_auth->logout();
		$this->session->set_flashdata('store_success', 'Sesion cerrada.');
		redirect('/');
	}

	public function forgot_password()
	{
		if ($this->ion_auth->logged_in())
		{
			redirect('cuenta');
		}

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('identity', 'Correo', 'trim|required|valid_email');

			if ($this->form_validation->run() === TRUE)
			{
				$identity = $this->input->post('identity', TRUE);
				$user = $this->ion_auth->where('email', $identity)->users()->row();

				if ($user)
				{
					$this->ion_auth->forgotten_password($identity);
				}

				$this->session->set_flashdata('store_success', 'Si el correo esta registrado, recibiras las instrucciones para restablecer tu acceso.');
				redirect('ingresar');
			}
			else
			{
				$this->data['auth_error'] = trim(strip_tags(validation_errors()));
			}
		}

		$this->render_store('store/auth/forgot_password', array(
			'auth_error' => isset($this->data['auth_error']) ? $this->data['auth_error'] : '',
		), array('title' => 'Recuperar acceso · SG Tienda', 'robots' => 'noindex,follow'));
	}

	public function reset_password($code = NULL)
	{
		if ( ! $code)
		{
			show_404();
		}

		$user = $this->ion_auth->forgotten_password_check($code);

		if ( ! $user)
		{
			$this->session->set_flashdata('store_error', 'El enlace de restablecimiento no es valido o expiro.');
			redirect('ingresar');
		}

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('new', 'Nueva contrasena', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
			$this->form_validation->set_rules('new_confirm', 'Confirmar contrasena', 'required');

			if ($this->form_validation->run() === TRUE)
			{
				$identity = $user->{$this->config->item('identity', 'ion_auth')};
				if ($this->ion_auth->reset_password($identity, $this->input->post('new')))
				{
					$this->session->set_flashdata('store_success', 'Contrasena actualizada. Ya puedes iniciar sesion.');
					redirect('ingresar');
				}
				$this->data['auth_error'] = trim(strip_tags($this->ion_auth->errors()));
			}
			else
			{
				$this->data['auth_error'] = trim(strip_tags(validation_errors()));
			}
		}

		$this->render_store('store/auth/reset_password', array(
			'code'       => $code,
			'auth_error' => isset($this->data['auth_error']) ? $this->data['auth_error'] : '',
		), array('title' => 'Restablecer acceso · SG Tienda', 'robots' => 'noindex,follow'));
	}

	protected function safe_return($url)
	{
		$url = trim((string) $url);
		if ($url === '' || strpos($url, "\n") !== FALSE || strpos($url, "\r") !== FALSE)
		{
			return '';
		}
		if (strpos($url, '://') !== FALSE && strpos($url, base_url()) !== 0)
		{
			return '';
		}
		return $url;
	}
}
