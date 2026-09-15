<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('has_permission')) {
	/**
	 * Verifica un permiso granular del usuario autenticado.
	 *
	 * @param string $permission
	 * @return bool
	 */
	function has_permission($permission)
	{
		$ci =& get_instance();
		return $ci->permission_service->has($permission);
	}
}

if (!function_exists('require_permission')) {
	/**
	 * Requiere un permiso o detiene la peticion.
	 *
	 * @param string $permission
	 */
	function require_permission($permission)
	{
		$ci =& get_instance();
		if (!$ci->permission_service->has($permission)) {
			if ($ci->input->is_ajax_request()) {
				$ci->output
					->set_status_header(403)
					->set_content_type('application/json')
					->set_output(json_encode(array(
						'success' => false,
						'message' => 'No tiene permiso para realizar esta accion.',
						'data' => null,
						'errors' => null,
					), JSON_UNESCAPED_UNICODE));
			} else {
				show_error('No tiene permiso para acceder a esta seccion.', 403, 'Acceso denegado');
			}
			exit;
		}
	}
}

if (!function_exists('default_landing_url')) {
	/**
	 * Destino por defecto tras el login / enlace "Inicio" del layout.
	 * Dashboard solo si el usuario tiene el permiso; si no, primer modulo accesible.
	 *
	 * @return string URL absoluta.
	 */
	function default_landing_url()
	{
		$ci =& get_instance();

		$groups = $ci->ion_auth->get_users_groups()->result_array();
		$names = array_column($groups, 'name');
		if (in_array('mensajero', $names, true) && count($names) === 1) {
			return base_url('courier');
		}

		if (!isset($ci->permission_service)) {
			$ci->load->library('Permission_service');
		}
		if ($ci->permission_service->has('dashboard.ver')) {
			return base_url('dashboard');
		}

		$modules = array(
			'clientes.ver' => 'clients',
			'pedidos.ver' => 'orders',
			'pedidos.preparar' => 'preparation',
			'rutas.ver' => 'routes',
			'bodegas.ver' => 'warehouses',
			'productos.ver' => 'products',
			'reportes.ver' => 'reports',
			'usuarios.ver' => 'users',
			'auditoria.ver' => 'audit',
			'configuracion.editar' => 'settings',
		);
		foreach ($modules as $permission => $uri) {
			if ($ci->permission_service->has($permission)) {
				return base_url($uri);
			}
		}

		return base_url('auth/logout');
	}
}

if (!function_exists('current_user')) {
	/**
	 * Usuario autenticado o null.
	 *
	 * @return object|null
	 */
	function current_user()
	{
		$ci =& get_instance();
		if (!$ci->ion_auth->logged_in()) {
			return null;
		}
		return $ci->ion_auth->user()->row();
	}
}

