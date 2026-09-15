<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Administracion de usuarios.
 */
class Users extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
		$this->load->library('form_validation');
		$this->load->helper('form');
	}

	public function index()
	{
		$this->require_permission('usuarios.ver');

		$this->data['pageTitle'] = 'Usuarios';
		$this->data['breadcrumbs'] = array(array('label' => 'Usuarios'));
		$this->data['users'] = $this->User_model->all();
		$this->data['pageScripts'] = array('assets/js/pages/users.js');

		$this->render('users/index', $this->data);
	}

	public function create()
	{
		$this->require_permission('usuarios.crear');

		$this->data['pageTitle'] = 'Nuevo usuario';
		$this->data['breadcrumbs'] = array(
			array('label' => 'Usuarios', 'href' => base_url('users')),
			array('label' => 'Nuevo usuario'),
		);
		$this->data['groups'] = $this->User_model->all_groups();

		if ($this->input->post()) {
			$this->form_validation->set_rules('first_name', 'Nombre', 'trim|required');
			$this->form_validation->set_rules('last_name', 'Apellido', 'trim|required');
			$this->form_validation->set_rules('username', 'Usuario', 'trim|required|regex_match[/^[a-z0-9_.-]+$/]|min_length[3]|max_length[60]|is_unique[users.username]');
			$this->form_validation->set_rules('email', 'Correo', 'trim|required|valid_email|is_unique[users.email]');
			$this->form_validation->set_rules('phone', 'Teléfono', 'trim');
			$this->form_validation->set_rules('groups[]', 'Grupos', 'required');
			$this->form_validation->set_rules('password', 'Contraseña', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']');

			if ($this->form_validation->run() === TRUE) {
				$email = strtolower($this->input->post('email'));
				$username = strtolower($this->input->post('username'));
				$identity = $email;
				$password = $this->input->post('password');
				$additional = array(
					'first_name' => $this->input->post('first_name'),
					'last_name' => $this->input->post('last_name'),
					'username' => $username,
					'phone' => $this->input->post('phone'),
					'company' => 'SGMensajeria',
					'country_id' => current_country_id(),
				);
				$groupIds = array_map('intval', (array)$this->input->post('groups'));
				$userId = $this->ion_auth->register($identity, $password, $email, $additional, $groupIds);
				if ($userId) {
					$this->audit_service->log('user.create', 'usuarios', 'users', $userId, array('email' => $email));
					$this->session->set_flashdata('success', true);
					$this->session->set_flashdata('message', 'Usuario creado correctamente.');
					redirect('users');
				}
				$this->data['message'] = $this->ion_auth->errors();
			} else {
				$this->data['message'] = validation_errors();
			}
		}

		$this->render('users/form', $this->data);
	}

	public function edit($id)
	{
		$this->require_permission('usuarios.editar');

		$user = $this->User_model->find_with_groups($id);
		if (!$user) {
			show_404();
		}

		$this->data['pageTitle'] = 'Editar usuario';
		$this->data['breadcrumbs'] = array(
			array('label' => 'Usuarios', 'href' => base_url('users')),
			array('label' => 'Editar usuario'),
		);
		$this->data['groups'] = $this->User_model->all_groups();
		$this->data['user'] = $user;

		if ($this->input->post()) {
			$this->form_validation->set_rules('first_name', 'Nombre', 'trim|required');
			$this->form_validation->set_rules('last_name', 'Apellido', 'trim|required');
			$this->form_validation->set_rules('username', 'Usuario', 'trim|required|regex_match[/^[a-z0-9_.-]+$/]|min_length[3]|max_length[60]|callback_username_unique_edit');
			$this->form_validation->set_rules('email', 'Correo', 'trim|required|valid_email');
			$this->form_validation->set_rules('phone', 'Teléfono', 'trim');
			$this->form_validation->set_rules('groups[]', 'Grupos', 'required');

			if ($this->form_validation->run() === TRUE) {
				$data = array(
					'first_name' => $this->input->post('first_name'),
					'last_name' => $this->input->post('last_name'),
					'username' => strtolower($this->input->post('username')),
					'phone' => $this->input->post('phone'),
					'email' => strtolower($this->input->post('email')),
				);
				$password = $this->input->post('password');
				if (!empty($password)) {
					$this->form_validation->set_rules('password', 'Contraseña', 'min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']');
					if ($this->form_validation->run() === TRUE) {
						$data['password'] = $password;
					} else {
						$this->data['message'] = validation_errors();
						$this->render('users/form', $this->data);
						return;
					}
				}

				if ($this->ion_auth->update($id, $data)) {
					$this->ion_auth->remove_from_group('', $id);
					foreach ($this->input->post('groups') as $gid) {
						$this->ion_auth->add_to_group($gid, $id);
					}
					$this->audit_service->log('user.update', 'usuarios', 'users', $id);
					$this->session->set_flashdata('success', true);
					$this->session->set_flashdata('message', 'Usuario actualizado correctamente.');
					redirect('users');
				}
				$this->data['message'] = $this->ion_auth->errors();
			} else {
				$this->data['message'] = validation_errors();
			}
		}

		$this->render('users/form', $this->data);
	}

	/**
	 * Callback: el username no debe existir en otro usuario al editar.
	 *
	 * @param string $str
	 * @return bool
	 */
	public function username_unique_edit($str)
	{
		$id = (int)$this->uri->segment(3);
		$exists = $this->db->where('username', $str)
			->where('id !=', $id)
			->count_all_results('users');
		if ($exists > 0) {
			$this->form_validation->set_message('username_unique_edit', 'El campo Usuario ya está en uso.');
			return FALSE;
		}
		return TRUE;
	}

	public function toggle_active($id)
	{
		$this->require_permission('usuarios.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$user = $this->db->where('id', $id)->where('country_id', current_country_id())->get('users')->row();
		if (!$user) {
			$this->json_response(false, 'El usuario no existe.', null, null, 404);
			return;
		}
		if ((int)$user->id === (int)$this->ion_auth->get_user_id()) {
			$this->json_response(false, 'No puede desactivar su propia cuenta.');
			return;
		}

		if ((int)$user->active === 1) {
			$this->ion_auth->deactivate($id);
			$newState = 0;
		} else {
			$this->ion_auth->activate($id);
			$newState = 1;
		}
		$this->audit_service->log('user.toggle_active', 'usuarios', 'users', $id, array('active' => $newState));
		$this->json_response(true, 'Estado del usuario actualizado.');
	}
}

