<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Administracion de clientes (CRUD e importacion por pais).
 */
class Clients extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_permission('clientes.ver');
		$this->load->model('Client_model');
		$this->load->helper('form');
	}

	private function next_code()
	{
		$prefix = 'CLI-';
		$row = $this->db->select('code')
			->from('clients')
			->where('country_id', current_country_id())
			->like('code', $prefix, 'after')
			->order_by('code', 'DESC')
			->limit(1)
			->get()->row();

		$seq = $row ? (int)substr($row->code, -4) + 1 : 1;
		return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
	}

	/**
	 * Genera un codigo de cliente unico (CLI-XXXX) evitando los codigos ya
	 * ocupados en el lote de importacion y los existentes en la base de datos.
	 *
	 * @param array $usedCodes Codigos ocupados (por referencia; se actualiza)
	 * @return string
	 */
	private function _generate_client_code(&$usedCodes)
	{
		$prefix = 'CLI-';
		$row = $this->db->select('code')
			->from('clients')
			->where('country_id', current_country_id())
			->like('code', $prefix, 'after')
			->order_by('code', 'DESC')
			->limit(1)
			->get()->row();
		$seq = $row ? (int)substr($row->code, -4) + 1 : 1;

		do {
			$code = $prefix . str_pad($seq++, 4, '0', STR_PAD_LEFT);
		} while (isset($usedCodes[$code]) || $this->Client_model->code_exists($code));

		$usedCodes[$code] = true;
		return $code;
	}

	public function index()
	{
		$this->_set_index_data();
		$this->render('clients/index', $this->data);
	}

	/**
	 * Descarga una plantilla de Excel con el formato esperado para importar clientes.
	 */
	public function import_template()
	{
		$this->require_permission('clientes.crear');
		$this->load->library('Excel_service');

		$columns = array(
			array('header' => 'Cliente', 'required' => true, 'example' => 'Comercial Ejemplo S.A.', 'hint' => 'Nombre o razón social'),
			array('header' => 'Celular', 'required' => true, 'example' => '8888 8888', 'hint' => 'Número de teléfono'),
			array('header' => 'Celular secundario', 'required' => false, 'example' => '7777 7777', 'hint' => 'Número secundario (opcional)'),
			array('header' => 'Zona', 'required' => true, 'example' => 'San José', 'hint' => 'Ciudad o departamento'),
			array('header' => 'Dirección', 'required' => true, 'example' => 'Av. Central, frente al parque', 'hint' => 'Dirección de entrega'),
			array('header' => 'Tipo de entrega', 'required' => true, 'example' => 'Domicilio', 'hint' => 'Ej.: Domicilio, San Salvador, Departamental, Recoger en tienda, Programada, Encomienda'),
			array('header' => 'Notas', 'required' => false, 'example' => 'Entregar por la tarde', 'hint' => 'Observaciones'),
		);

		$this->excel_service->import_template('plantilla-clientes.xlsx', 'Lista de Clientes', $columns);
	}

	/**
	 * Lee y valida la hoja Lista de Clientes antes de guardar.
	 */
	public function import_preview()
	{
		$this->require_permission('clientes.crear');
		if ($this->input->method(TRUE) !== 'POST') {
			redirect('clients');
		}

		$this->_set_index_data();
		$this->session->unset_userdata('client_import_preview');
		$file = isset($_FILES['client_file']) ? $_FILES['client_file'] : null;
		$error = $this->_validate_import_file($file);
		if ($error !== null) {
			$this->data['importMessage'] = $error;
			return $this->render('clients/index', $this->data);
		}

		$this->load->library('Client_import_service');
		$preview = $this->client_import_service->parse($file['tmp_name']);

		// Todos los clientes se crean nuevos; el codigo CLI-XXXX se genera
		// automaticamente en secuencia al confirmar.
		$preview['new_count'] = count($preview['rows']);
		$preview['update_count'] = 0;
		$preview['row_error_count'] = 0;
		foreach ($preview['rows'] as &$row) {
			$row['status'] = 'Nuevo';
			if (!empty($row['errors'])) {
				$preview['row_error_count']++;
			}
		}
		unset($row);

		$preview['valid'] = empty($preview['errors'])
			&& $preview['row_error_count'] === 0
			&& !empty($preview['rows']);
		$preview['filename'] = basename($file['name']);
		$this->data['importPreview'] = $preview;

		if ($preview['valid']) {
			$token = bin2hex(random_bytes(24));
			$this->session->set_userdata('client_import_preview', array(
				'token' => $token,
				'user_id' => (int)$this->currentUser->id,
				'country_id' => (int)current_country_id(),
				'expires_at' => time() + 1800,
				'filename' => $preview['filename'],
				'rows' => $preview['rows'],
			));
			$this->data['importToken'] = $token;
		}

		$this->render('clients/index', $this->data);
	}

	/**
	 * Confirma una vista previa valida y aplica el lote atomicamente.
	 */
	public function import_commit()
	{
		$this->require_permission('clientes.crear');
		if ($this->input->method(TRUE) !== 'POST') {
			redirect('clients');
		}

		$stored = $this->session->userdata('client_import_preview');
		$token = (string)$this->input->post('import_token');
		if (!$this->_valid_import_session($stored, $token)) {
			$this->session->unset_userdata('client_import_preview');
			$this->session->set_flashdata('message', 'La vista previa expiro o no pertenece a esta sesión. Vuelva a cargar el archivo.');
			redirect('clients');
		}

		$usedCodes = array();
		$created = 0;
		$this->db->trans_begin();

		foreach ($stored['rows'] as $row) {
			$data = array(
				'code' => $this->_generate_client_code($usedCodes),
				'name' => $row['name'],
				'phone' => $this->_nullable($row['phone']),
				'phone2' => $this->_nullable($row['phone2']),
				'zone' => $this->_nullable($row['zone']),
				'address' => $this->_nullable($row['address']),
				'delivery_type' => $this->_nullable($row['delivery_type']),
				'is_active' => 1,
				'country_id' => current_country_id(),
				'client_type' => 'normal',
				'notes' => $this->_nullable($row['notes']),
				'created_by' => $this->currentUser->id,
			);

			if (!$this->Client_model->insert($data)) {
				$this->db->trans_rollback();
				return $this->_import_failed();
			}
			$created++;
		}

		$this->audit_service->log('client.import', 'clientes', 'clients', null, array(
			'filename' => $stored['filename'],
			'created' => $created,
			'user_id' => (int)$this->currentUser->id,
		));

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return $this->_import_failed();
		}
		$this->db->trans_commit();
		$this->session->unset_userdata('client_import_preview');
		$this->session->set_flashdata('success', true);
		$this->session->set_flashdata('message', 'Clientes importados: ' . $created . ' nuevos.');
		redirect('clients');
	}

	public function save()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$id = (int)$this->input->post('id');
		$this->require_permission($id ? 'clientes.editar' : 'clientes.crear');
		$client = $id ? $this->Client_model->find_for_country($id) : null;
		if ($id && !$client) {
			show_404();
		}

		$name = trim((string)$this->input->post('name'));
		if ($name === '') {
			return $this->json_response(false, 'El nombre del cliente es obligatorio.');
		}

		$code = mb_strtoupper(trim((string)$this->input->post('code')), 'UTF-8');
		if ($code === '') {
			$code = $client ? $client->code : $this->next_code();
		}
		if (mb_strlen($code, 'UTF-8') > 20) {
			return $this->json_response(false, 'El número de cliente no puede exceder 20 caracteres.');
		}
		if ($this->Client_model->code_exists($code, $id ?: null)) {
			return $this->json_response(false, 'El número de cliente ya existe en este país.');
		}

		$clientType = $this->input->post('client_type');
		if (!in_array($clientType, array('excelente', 'bueno', 'normal', 'malo'), true)) {
			$clientType = 'normal';
		}

		$data = array(
			'code' => $code,
			'name' => $name,
			'phone' => $this->_nullable($this->input->post('phone')),
			'phone2' => $this->_nullable($this->input->post('phone2')),
			'zone' => $this->_nullable($this->input->post('zone')),
			'address' => $this->_nullable($this->input->post('address')),
			'delivery_type' => $this->_nullable($this->input->post('delivery_type')),
			'client_type' => $clientType,
			'notes' => $this->_nullable($this->input->post('notes')),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
		);

		if ($client) {
			$data['updated_by'] = $this->currentUser->id;
			if (!$this->Client_model->update_for_country($id, $data)) {
				return $this->json_response(false, 'No fue posible actualizar el cliente.');
			}
			$this->audit_service->log('client.update', 'clientes', 'clients', $id, array('code' => $code, 'name' => $name));
			return $this->json_response(true, 'Cliente actualizado.', array('id' => $id));
		}

		$data['country_id'] = current_country_id();
		$data['created_by'] = $this->currentUser->id;
		$id = $this->Client_model->insert($data);
		if (!$id) {
			return $this->json_response(false, 'No fue posible crear el cliente.');
		}
		$this->audit_service->log('client.create', 'clientes', 'clients', $id, array('code' => $code, 'name' => $name));
		$this->json_response(true, 'Cliente ' . $code . ' creado.', array('id' => $id));
	}

	public function delete($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$this->require_permission('clientes.eliminar');
		$client = $this->Client_model->find_for_country($id);
		if (!$client) {
			show_404();
		}
		if ((int)$client->is_active === 1) {
			return $this->json_response(false, 'No se puede eliminar un cliente activo. Desactívelo primero en la edición.');
		}
		if (!$this->Client_model->delete_for_country($id)) {
			return $this->json_response(false, 'No fue posible eliminar el cliente.');
		}
		$this->audit_service->log('client.delete', 'clientes', 'clients', $id, array('code' => $client->code, 'name' => $client->name));
		$this->json_response(true, 'Cliente eliminado.');
	}

	private function _set_index_data()
	{
		$this->data['pageTitle'] = 'Clientes';
		$this->data['breadcrumbs'] = array(array('label' => 'Clientes'));
		$this->data['items'] = $this->db->where('country_id', current_country_id())
			->order_by('name', 'ASC')->get('clients')->result();
		$this->data['pageScripts'] = array('assets/js/pages/catalogs.js');
		$this->data['importPreview'] = null;
	}

	private function _validate_import_file($file)
	{
		if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
			return 'Seleccione un archivo .xlsx.';
		}
		if ($file['error'] !== UPLOAD_ERR_OK) {
			return 'No fue posible recibir el archivo.';
		}
		if ((int)$file['size'] <= 0 || (int)$file['size'] > 5 * 1024 * 1024) {
			return 'El archivo debe pesar como máximo 5 MB.';
		}
		if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'xlsx') {
			return 'Solo se permiten archivos con extension .xlsx.';
		}
		return null;
	}

	private function _valid_import_session($stored, $token)
	{
		return is_array($stored)
			&& isset($stored['token'], $stored['user_id'], $stored['country_id'], $stored['expires_at'], $stored['rows'])
			&& $token !== ''
			&& hash_equals($stored['token'], $token)
			&& (int)$stored['user_id'] === (int)$this->currentUser->id
			&& (int)$stored['country_id'] === (int)current_country_id()
			&& (int)$stored['expires_at'] >= time()
			&& !empty($stored['rows']);
	}

	private function _import_failed()
	{
		$this->session->set_flashdata('message', 'No fue posible importar los clientes. No se guardo ningun cambio.');
		redirect('clients');
	}

	private function _nullable($value)
	{
		$value = trim((string)$value);
		return $value === '' ? null : $value;
	}
}
