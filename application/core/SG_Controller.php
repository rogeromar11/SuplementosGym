<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador base del panel de administración (backoffice).
 *
 * Proporciona: sesión de país, permisos granulares, auditoría,
 * respuesta JSON normalizada y render de los layouts administrativos.
 *
 * Las vistas del backoffice viven en application/third_party/sgadmin/views
 * y se resuelven mediante un package path agregado en el constructor.
 */
class SG_Controller extends CI_Controller
{
	/**
	 * @var array Datos compartidos con las vistas.
	 */
	protected $data = array();

	/**
	 * @var object|null Usuario autenticado (ion_auth).
	 */
	protected $currentUser = null;

	/**
	 * @var array Grupos del usuario autenticado.
	 */
	protected $currentGroups = array();

	/**
	 * @var array Configuración del sistema del país actual (clave => valor).
	 */
	public $settings = array();

	/**
	 * @var object|null País activo de la sesión.
	 */
	public $currentCountry = null;

	public function __construct()
	{
		parent::__construct();

		$this->load->add_package_path(APPPATH . 'third_party/sgadmin');

		$this->load->library(array('ion_auth', 'Permission_service', 'Audit_service'));
		$this->load->helper(array('app', 'permission'));
		$this->load->model('Settings_model');

		// Zona horaria de la sesión de MySQL (Costa Rica / El Salvador, UTC-6).
		$this->db->query("SET time_zone = '-06:00'");

		// País activo: sesión del login o, si falta, el país del usuario.
		$countryData = $this->session->userdata('sgms_country');
		if (!$countryData && $this->ion_auth->logged_in()) {
			$user = $this->ion_auth->user()->row();
			if ($user && $user->country_id) {
				$row = $this->db->where('id', $user->country_id)->get('countries')->row();
				if ($row) {
					$countryData = (array) $row;
					$this->session->set_userdata('sgms_country', $countryData);
				}
			}
		}
		if ($countryData) {
			$this->currentCountry = is_object($countryData) ? $countryData : (object) $countryData;
			date_default_timezone_set($this->currentCountry->timezone ?: 'America/Costa_Rica');
		}
		$this->data['country'] = $this->currentCountry;

		$this->settings = $this->Settings_model->all_key_value();
		$this->data['settings'] = $this->settings;

		$this->data['appName'] = isset($this->settings['company_name']) ? $this->settings['company_name'] : 'SG Tienda';
		$this->data['appLogo'] = base_url('assets/img/logo.png');
		$this->data['csrf_name'] = $this->config->item('csrf_token_name');
		$this->data['csrf_hash'] = $this->security->get_csrf_hash();
		$this->data['isAjax'] = $this->input->is_ajax_request();

		if ($this->ion_auth->logged_in()) {
			$this->currentUser = $this->ion_auth->user()->row();
			$this->currentGroups = $this->ion_auth->get_users_groups()->result();
			$this->data['currentUser'] = $this->currentUser;
			$this->data['currentGroups'] = $this->currentGroups;
			$this->data['isAdmin'] = $this->ion_auth->is_admin();
		}
	}

	/**
	 * Verifica si el usuario autenticado posee un permiso granular.
	 *
	 * @param string $permission Ej.: 'pedidos.crear'
	 * @return bool
	 */
	protected function has_permission($permission)
	{
		return $this->permission_service->has($permission);
	}

	/**
	 * Requiere un permiso o detiene la petición con 403.
	 *
	 * @param string $permission
	 */
	protected function require_permission($permission)
	{
		if (!$this->permission_service->has($permission)) {
			$this->deny_access();
		}
	}

	/**
	 * Requiere sesión iniciada.
	 */
	protected function require_login()
	{
		if (!$this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				$this->json_response(false, 'Sesión expirada. Inicie sesión nuevamente.', null, null, 401);
				exit;
			}
			redirect('auth/login');
		}
	}

	/**
	 * Respuesta 403 para peticiones sin permiso.
	 */
	protected function deny_access()
	{
		if ($this->input->is_ajax_request()) {
			$this->json_response(false, 'No tiene permiso para realizar esta acción.', null, null, 403);
		} else {
			show_error('No tiene permiso para acceder a esta sección.', 403, 'Acceso denegado');
		}
		exit;
	}

	/**
	 * Redirige según el rol del usuario autenticado.
	 */
	protected function redirect_by_role()
	{
		$groups = $this->ion_auth->get_users_groups()->result_array();
		$names = array_column($groups, 'name');
		if (in_array('mensajero', $names, true) && count($names) === 1) {
			redirect('courier');
		}
		redirect('dashboard');
	}

	/**
	 * Respuesta JSON normalizada.
	 *
	 * @param bool $success
	 * @param string $message
	 * @param mixed $data
	 * @param mixed $errors
	 * @param int $code
	 */
	protected function json_response($success, $message = '', $data = null, $errors = null, $code = 200)
	{
		$csrf = array(
			'name' => $this->config->item('csrf_token_name'),
			'token' => $this->security->get_csrf_hash(),
		);
		$this->output
			->set_status_header($code)
			->set_content_type('application/json')
			->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
			->set_header('Pragma: no-cache')
			->set_output(json_encode(array(
				'success' => $success,
				'message' => $message,
				'data' => $data,
				'errors' => $errors,
				'csrf' => $csrf,
			), JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Renderiza una vista dentro del layout administrativo.
	 *
	 * @param string $view
	 * @param array $data
	 */
	protected function render($view, $data = array())
	{
		$this->data = array_merge($this->data, $data);
		$this->load->view('layouts/admin/header', $this->data);
		$this->load->view($view, $this->data);
		$this->load->view('layouts/admin/footer', $this->data);
	}

	/**
	 * Renderiza una vista con el layout móvil del mensajero.
	 *
	 * @param string $view
	 * @param array $data
	 */
	protected function render_courier($view, $data = array())
	{
		$this->data = array_merge($this->data, $data);
		$this->load->view('layouts/courier/header', $this->data);
		$this->load->view($view, $this->data);
		$this->load->view('layouts/courier/footer', $this->data);
	}

	/**
	 * Renderiza una vista con layout imprimible (sin sidebar).
	 *
	 * @param string $view
	 * @param array $data
	 */
	protected function render_print($view, $data = array())
	{
		$this->data = array_merge($this->data, $data);
		$this->load->view('layouts/print/header', $this->data);
		$this->load->view($view, $this->data);
		$this->load->view('layouts/print/footer', $this->data);
	}
}

// Las clases base del backoffice se cargan aquí porque CI3 solo autocarga
// la clase con prefijo MY_ (MY_Controller). Deben existir antes de instanciar
// los controladores que las extienden.
require_once __DIR__ . '/Authenticated_Controller.php';
require_once __DIR__ . '/Admin_Controller.php';
require_once __DIR__ . '/Courier_Controller.php';
