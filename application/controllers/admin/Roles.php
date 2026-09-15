<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Administracion de grupos y matriz de permisos.
 */
class Roles extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
	}

	public function index()
	{
		$this->require_permission('usuarios.ver');

		$this->data['pageTitle'] = 'Grupos y permisos';
		$this->data['breadcrumbs'] = array(array('label' => 'Grupos y permisos'));
		$this->data['groups'] = $this->db->order_by('id', 'ASC')->get('groups')->result();

		foreach ($this->data['groups'] as $g) {
			$g->permission_count = $this->db->where('group_id', $g->id)->count_all_results('group_permissions');
		}

		$this->render('roles/index', $this->data);
	}

	public function edit($groupId)
	{
		$this->require_permission('configuracion.editar');

		$group = $this->db->where('id', $groupId)->get('groups')->row();
		if (!$group) {
			show_404();
		}

		$this->data['pageTitle'] = 'Permisos: ' . $group->description;
		$this->data['breadcrumbs'] = array(
			array('label' => 'Grupos y permisos', 'href' => base_url('roles')),
			array('label' => $group->name),
		);
		$this->data['group'] = $group;
		$this->data['permissions'] = $this->permission_service->all_grouped();
		$this->data['current'] = $this->permission_service->permissions_for_group($groupId);
		$this->data['pageScripts'] = array('assets/js/pages/roles.js');

		$this->render('roles/form', $this->data);
	}

	public function save($groupId)
	{
		$this->require_permission('configuracion.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$selected = (array)$this->input->post('permissions');

		$this->db->trans_start();
		$this->db->where('group_id', $groupId)->delete('group_permissions');
		foreach ($selected as $permName) {
			$perm = $this->db->where('name', $permName)->get('permissions')->row();
			if ($perm) {
				$this->db->insert('group_permissions', array('group_id' => $groupId, 'permission_id' => $perm->id));
			}
		}
		$this->db->trans_complete();

		if (!$this->db->trans_status()) {
			$this->json_response(false, 'No fue posible guardar los permisos.');
			return;
		}

		$this->audit_service->log('role.permissions.update', 'usuarios', 'group_permissions', $groupId, array('permissions' => $selected));
		$this->json_response(true, 'Permisos actualizados correctamente.');
	}
}
