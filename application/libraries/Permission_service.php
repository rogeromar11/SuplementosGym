<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Servicio de permisos granulares.
 * El administrador (grupo admin) siempre tiene todos los permisos.
 * Los permisos se cargan desde la base de datos una vez por request.
 */
class Permission_service
{
	/**
	 * @var array|null Lista de permisos del usuario (nombre => true).
	 */
	private $permissions = null;

	/**
	 * @var bool|null Si el usuario pertenece al grupo admin.
	 */
	private $isAdmin = null;

	public function __construct()
	{
		$ci =& get_instance();
		if (!$ci->ion_auth->logged_in()) {
			return;
		}
		$this->isAdmin = $ci->ion_auth->is_admin();
	}

	/**
	 * Verifica si el usuario autenticado tiene un permiso.
	 *
	 * @param string $permission Ej.: 'pedidos.crear'
	 * @return bool
	 */
	public function has($permission)
	{
		$ci =& get_instance();
		if (!$ci->ion_auth->logged_in()) {
			return false;
		}
		if ($this->isAdmin) {
			return true;
		}
		if ($this->permissions === null) {
			$this->permissions = $this->load_user_permissions();
		}
		return isset($this->permissions[$permission]);
	}

	/**
	 * Permisos del grupo para gestion en interfaz.
	 *
	 * @param int $groupId
	 * @return array Nombres de permisos
	 */
	public function permissions_for_group($groupId)
	{
		$ci =& get_instance();
		$ci->db->select('p.name')
			->from('permissions p')
			->join('group_permissions gp', 'gp.permission_id = p.id')
			->where('gp.group_id', $groupId)
			->where('p.is_active', 1);
		return array_column($ci->db->get()->result(), 'name');
	}

	/**
	 * Lista todos los permisos agrupados por modulo.
	 *
	 * @return array
	 */
	public function all_grouped()
	{
		$ci =& get_instance();
		$ci->db->where('is_active', 1)->order_by('module', 'ASC')->order_by('action', 'ASC');
		$rows = $ci->db->get('permissions')->result();
		$grouped = array();
		foreach ($rows as $row) {
			$grouped[$row->module][] = $row;
		}
		return $grouped;
	}

	/**
	 * Carga los permisos del usuario autenticado desde la base de datos.
	 *
	 * @return array
	 */
	private function load_user_permissions()
	{
		$ci =& get_instance();
		$ci->db->distinct()
			->select('p.name')
			->from('permissions p')
			->join('group_permissions gp', 'gp.permission_id = p.id', 'inner')
			->join('users_groups ug', 'ug.group_id = gp.group_id', 'inner')
			->where('ug.user_id', $ci->ion_auth->get_user_id())
			->where('p.is_active', 1);
		$rows = $ci->db->get()->result();
		$out = array();
		foreach ($rows as $row) {
			$out[$row->name] = true;
		}
		return $out;
	}
}
