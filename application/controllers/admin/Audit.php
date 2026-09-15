<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bitacora de auditoria.
 */
class Audit extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_permission('auditoria.ver');
	}

	public function index()
	{
		$f = array(
			'date_from' => $this->input->get('date_from'),
			'date_to' => $this->input->get('date_to'),
			'module' => $this->input->get('module'),
			'user_id' => $this->input->get('user_id'),
			'action' => $this->input->get('action'),
		);

		$this->db->select('a.*, CONCAT(u.first_name, " ", u.last_name) AS user_name')
			->from('audit_logs a')
			->join('users u', 'u.id = a.user_id', 'left')
			->where('a.country_id', current_country_id());
		if (!empty($f['date_from'])) {
			$this->db->where('a.created_at >=', $f['date_from'] . ' 00:00:00');
		}
		if (!empty($f['date_to'])) {
			$this->db->where('a.created_at <=', $f['date_to'] . ' 23:59:59');
		}
		if (!empty($f['module'])) {
			$this->db->where('a.module', $f['module']);
		}
		if (!empty($f['action'])) {
			$this->db->like('a.action', $f['action']);
		}
		if (!empty($f['user_id'])) {
			$this->db->where('a.user_id', $f['user_id']);
		}
		$this->db->order_by('a.created_at', 'DESC')->limit(500);
		$this->data['logs'] = $this->db->get()->result();

		$this->data['pageTitle'] = 'Auditoría';
		$this->data['breadcrumbs'] = array(array('label' => 'Auditoría'));
		$this->data['filters'] = $f;
		$this->data['modules'] = $this->db->distinct()->select('module')->from('audit_logs')->where('country_id', current_country_id())->order_by('module')->get()->result();

		$this->render('audit/index', $this->data);
	}
}
