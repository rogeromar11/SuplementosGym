<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Servicio de auditoria general.
 */
class Audit_service
{
	/**
	 * Registra una accion en la bitacora de auditoria.
	 *
	 * @param string $action
	 * @param string|null $module
	 * @param string|null $table
	 * @param int|null $recordId
	 * @param mixed $data
	 * @return int
	 */
	public function log($action, $module = null, $table = null, $recordId = null, $data = null)
	{
		$ci =& get_instance();
		$userId = $ci->ion_auth->logged_in() ? $ci->ion_auth->get_user_id() : null;

		$country = current_country();
		$ci->db->insert('audit_logs', array(
			'country_id' => $country ? (int)$country->id : null,
			'user_id' => $userId,
			'action' => $action,
			'module' => $module,
			'table_name' => $table,
			'record_id' => $recordId,
			'ip_address' => $ci->input->ip_address(),
			'user_agent' => substr((string)$ci->input->user_agent(), 0, 255),
			'data' => ($data !== null) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
			'created_at' => date('Y-m-d H:i:s'),
		));

		return $ci->db->insert_id();
	}
}
