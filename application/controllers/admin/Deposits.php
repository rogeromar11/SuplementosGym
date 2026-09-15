<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Depositos de efectivo por ruta.
 *
 * Mensajero (movil): mis depositos, crear deposito de rutas con comprobante.
 * Auxiliar/Admin (escritorio): listado, detalle con comprobantes,
 * confirmacion de recepcion, entrega al administrador y visto bueno.
 */
class Deposits extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Deposit_model');
		$this->load->helper('form');
	}

	/**
	 * Escritorio: listado de depositos (Auxiliar Admin y Administrador).
	 */
	public function index()
	{
		$this->require_permission('depositos.ver');

		// El auxiliar no ve los depositos aprobados (informacion contable del admin).
		$showApproved = $this->ion_auth->is_admin();

		$f = array(
			'status' => $this->input->get('status'),
			'date_from' => $this->input->get('date_from'),
			'date_to' => $this->input->get('date_to'),
			'courier_user_id' => $this->input->get('courier_user_id'),
			'hide_approved' => !$showApproved,
		);

		$this->data['pageTitle'] = 'Depósitos de efectivo';
		$this->data['breadcrumbs'] = array(array('label' => 'Depósitos'));
		$this->data['filters'] = $f;
		$this->data['rows'] = $this->Deposit_model->search($f);
		$this->data['counts'] = $this->Deposit_model->status_counts(!$showApproved);
		$this->data['showApproved'] = $showApproved;

		// Rutas con efectivo pendiente de deposito, agrupadas por mensajero
		$pendingByCourier = array();
		foreach ($this->Deposit_model->all_pending_routes() as $pr) {
			$key = (int)$pr->courier_user_id;
			if (!isset($pendingByCourier[$key])) {
				$pendingByCourier[$key] = array(
					'courier_name' => $pr->courier_name ?: 'Sin mensajero',
					'routes' => array(),
					'total' => 0.0,
				);
			}
			$pendingByCourier[$key]['routes'][] = $pr;
			$pendingByCourier[$key]['total'] += (float)$pr->cash_amount;
		}
		$this->data['pendingByCourier'] = $pendingByCourier;

		$this->data['statuses'] = array(
			Deposit_model::STATUS_PENDIENTE => 'Pendiente de confirmar',
			Deposit_model::STATUS_RECIBIDO => 'Recibido (pendiente de entregar)',
			Deposit_model::STATUS_ENTREGADO => 'Entregado al administrador',
		);
		if ($showApproved) {
			$this->data['statuses'][Deposit_model::STATUS_APROBADO] = 'Aprobado';
		}
		$this->data['couriers'] = $this->db->select('u.id, u.first_name, u.last_name')
			->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')
			->join('groups g', 'g.id = ug.group_id')
			->where('u.country_id', current_country_id())->where('g.name', 'mensajero')
			->order_by('u.first_name')->get()->result();
		$this->data['canConfirm'] = $this->has_permission('depositos.confirmar');
		$this->data['canApprove'] = $this->has_permission('depositos.aprobar');
		$this->data['pageScripts'] = array('assets/js/pages/deposits.js');

		$this->render('deposits/index', $this->data);
	}

	/**
	 * Detalle de un deposito (escritorio).
	 */
	public function detail($id)
	{
		$this->require_permission('depositos.ver');
		$deposit = $this->Deposit_model->find((int)$id);
		if (!$deposit) {
			show_404();
		}
		// Los depositos aprobados solo los consulta el administrador.
		if ($deposit->status === Deposit_model::STATUS_APROBADO && !$this->ion_auth->is_admin()) {
			$this->deny_access();
		}

		$this->data['pageTitle'] = 'Depósito #' . $deposit->id;
		$this->data['breadcrumbs'] = array(
			array('label' => 'Depósitos', 'href' => base_url('deposits')),
			array('label' => 'Depósito #' . $deposit->id),
		);
		$this->data['deposit'] = $deposit;
		$this->data['isAdmin'] = $this->ion_auth->is_admin();
		$this->data['canConfirm'] = $this->has_permission('depositos.confirmar');
		$this->data['canApprove'] = $this->has_permission('depositos.aprobar');
		$this->data['canHandover'] = $this->has_permission('depositos.entregar')
			&& (int)$deposit->receiver_user_id === (int)$this->currentUser->id;
		$this->data['admins'] = $this->db->select('DISTINCT u.id, u.first_name, u.last_name', false)
			->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')
			->join('groups g', 'g.id = ug.group_id')
			->where('u.country_id', current_country_id())->where('u.active', 1)
			->where('g.name', 'admin')
			->order_by('u.first_name')->get()->result();
		$this->data['pageScripts'] = array('assets/js/pages/deposits.js');

		$this->render('deposits/detail', $this->data);
	}

	/**
	 * Movil del mensajero: rutas pendientes de deposito e historial.
	 */
	public function mine()
	{
		if (!$this->_is_courier()) {
			$this->deny_access();
		}
		$courierId = (int)$this->ion_auth->get_user_id();

		$this->data['pageTitle'] = 'Depósitos por ruta';
		$this->data['courierTab'] = 'depositos';
		$this->data['pendingRoutes'] = $this->Deposit_model->pending_routes($courierId);
		$this->data['pendingTotal'] = array_sum(array_map(function ($r) {
			return (float)$r->cash_amount;
		}, $this->data['pendingRoutes']));
		// Los depositos aprobados desaparecen del historial del mensajero.
		$this->data['deposits'] = $this->Deposit_model->search(array(
			'courier_user_id' => $courierId,
			'hide_approved' => true,
		));
		$this->data['pageScripts'] = array('assets/js/pages/deposits.js');

		$this->render_courier('deposits/courier_index', $this->data);
	}

	/**
	 * Movil del mensajero: formulario de nuevo deposito.
	 */
	public function create()
	{
		if (!$this->_is_courier()) {
			$this->deny_access();
		}
		$courierId = (int)$this->ion_auth->get_user_id();

		$pending = $this->Deposit_model->pending_routes($courierId);
		if (empty($pending)) {
			$this->session->set_flashdata('message', 'No tiene rutas con efectivo pendiente de depositar.');
			redirect('deposits/mine');
		}

		$this->data['pageTitle'] = 'Nuevo depósito';
		$this->data['courierTab'] = 'depositos';
		$this->data['pendingRoutes'] = $pending;
		$this->data['pendingTotal'] = array_sum(array_map(function ($r) {
			return (float)$r->cash_amount;
		}, $pending));
		$this->data['receivers'] = $this->Deposit_model->receivers();
		$this->data['maxMb'] = (float)app_setting('evidence_max_size_mb', 8);

		$this->render_courier('deposits/courier_create', $this->data);
	}

	/**
	 * Movil del mensajero: guarda el deposito con comprobante.
	 */
	public function store()
	{
		if (!$this->_is_courier()) {
			$this->deny_access();
		}
		$isAjax = $this->input->is_ajax_request();
		$courierId = (int)$this->ion_auth->get_user_id();

		$fail = function ($message) use ($isAjax) {
			if ($isAjax) {
				$this->json_response(false, $message);
			} else {
				$this->session->set_flashdata('message', $message);
				redirect('deposits/create');
			}
		};

		$routeIds = (array)$this->input->post('route_ids');
		$receiverId = (int)$this->input->post('receiver_user_id');
		$notes = trim((string)$this->input->post('notes'));

		if (!$receiverId) {
			$fail('Indique a quién entrega el depósito.');
			return;
		}
		$receiver = $this->db->select('u.id')
			->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')
			->join('groups g', 'g.id = ug.group_id')
			->where('u.id', $receiverId)->where('u.active', 1)
			->where('u.country_id', current_country_id())
			->where_in('g.name', array('auxiliar_admin', 'admin'))
			->get()->row();
		if (!$receiver) {
			$fail('El receptor seleccionado no es válido.');
			return;
		}

		$receipt = $this->_store_receipt('deposit_receipt');
		if ($receipt === null) {
			$fail('Adjunte la foto del comprobante del depósito (JPG, PNG, WEBP o PDF).');
			return;
		}

		$result = $this->Deposit_model->create(array(
			'country_id' => current_country_id(),
			'courier_user_id' => $courierId,
			'receiver_user_id' => $receiverId,
			'notes' => $notes,
			'receipt' => $receipt,
		), $routeIds);

		if (!$result['success']) {
			$fail($result['error']);
			return;
		}

		$this->audit_service->log('deposit.create', 'depositos', 'cash_deposits', $result['id'], array(
			'routes' => count($routeIds),
			'receiver_user_id' => $receiverId,
		));

		if ($isAjax) {
			$this->json_response(true, 'Depósito registrado. Queda pendiente de confirmación.', array('id' => $result['id']));
			return;
		}
		$this->session->set_flashdata('success', true);
		$this->session->set_flashdata('message', 'Depósito registrado. Queda pendiente de confirmación.');
		redirect('deposits/mine');
	}

	/**
	 * Movil del mensajero: detalle de su deposito.
	 */
	public function my_detail($id)
	{
		if (!$this->_is_courier()) {
			$this->deny_access();
		}
		$deposit = $this->Deposit_model->find((int)$id);
		if (!$deposit || (int)$deposit->courier_user_id !== (int)$this->ion_auth->get_user_id()) {
			show_404();
		}
		// Una vez aprobado, el deposito deja de mostrarse al mensajero.
		if ($deposit->status === Deposit_model::STATUS_APROBADO) {
			show_404();
		}

		$this->data['pageTitle'] = 'Depósito #' . $deposit->id;
		$this->data['courierTab'] = 'depositos';
		$this->data['deposit'] = $deposit;
		$this->render_courier('deposits/courier_detail', $this->data);
	}

	/**
	 * Confirma la recepcion del deposito (receptor auxiliar/admin).
	 */
	public function confirm($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$this->require_permission('depositos.confirmar');
		$deposit = $this->Deposit_model->find((int)$id);
		if (!$deposit) {
			$this->json_response(false, 'El depósito no existe.', null, null, 404);
			return;
		}
		$result = $this->Deposit_model->confirm_receipt($deposit, (int)$this->currentUser->id, $this->ion_auth->is_admin());
		if (!$result['success']) {
			$this->json_response(false, $result['error']);
			return;
		}
		$this->audit_service->log('deposit.confirm', 'depositos', 'cash_deposits', $deposit->id, array('status' => $result['status']));
		$this->json_response(true, 'Recepción confirmada.', array('status' => $result['status']));
	}

	/**
	 * El auxiliar entrega el efectivo al administrador con su comprobante.
	 */
	public function handover($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$this->require_permission('depositos.entregar');
		$deposit = $this->Deposit_model->find((int)$id);
		if (!$deposit) {
			$this->json_response(false, 'El depósito no existe.', null, null, 404);
			return;
		}

		$adminUserId = (int)$this->input->post('admin_receiver_user_id');
		$admin = $this->db->select('u.id')
			->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')
			->join('groups g', 'g.id = ug.group_id')
			->where('u.id', $adminUserId)->where('u.active', 1)
			->where('u.country_id', current_country_id())
			->where('g.name', 'admin')
			->get()->row();
		if (!$admin) {
			$this->json_response(false, 'Indique el administrador que recibe el efectivo.');
			return;
		}

		$receipt = $this->_store_receipt('aux_receipt');
		if ($receipt === null) {
			$this->json_response(false, 'Adjunte la foto del comprobante de entrega (JPG, PNG, WEBP o PDF).');
			return;
		}

		$result = $this->Deposit_model->handover($deposit, $adminUserId, (int)$this->currentUser->id, $receipt);
		if (!$result['success']) {
			$this->json_response(false, $result['error']);
			return;
		}
		$this->audit_service->log('deposit.handover', 'depositos', 'cash_deposits', $deposit->id, array(
			'admin_receiver_user_id' => $adminUserId,
		));
		$this->json_response(true, 'Entrega al administrador registrada. Pendiente de su visto bueno.');
	}

	/**
	 * Visto bueno final del administrador.
	 */
	public function approve($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$this->require_permission('depositos.aprobar');
		$deposit = $this->Deposit_model->find((int)$id);
		if (!$deposit) {
			$this->json_response(false, 'El depósito no existe.', null, null, 404);
			return;
		}
		$result = $this->Deposit_model->approve($deposit, (int)$this->currentUser->id);
		if (!$result['success']) {
			$this->json_response(false, $result['error']);
			return;
		}
		$this->audit_service->log('deposit.approve', 'depositos', 'cash_deposits', $deposit->id, array());
		$this->json_response(true, 'Depósito aprobado. Visto bueno registrado.');
	}

	/**
	 * Guarda el comprobante del deposito en uploads/deposit_receipts/.
	 *
	 * @param string $field
	 * @return array|null
	 */
	private function _store_receipt($field)
	{
		if (!isset($_FILES[$field]) || !isset($_FILES[$field]['tmp_name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return null;
		}
		$file = $_FILES[$field];
		if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0) {
			return null;
		}

		$allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf');
		$maxBytes = (float)app_setting('evidence_max_size_mb', 8) * 1024 * 1024;
		if ($file['size'] > $maxBytes) {
			return null;
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($file['tmp_name']);
		if (!isset($allowed[$mime])) {
			return null;
		}

		$dir = FCPATH . 'uploads/deposit_receipts/';
		if (!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}
		$name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
		if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
			return null;
		}

		return array(
			'file' => $name,
			'original' => $file['name'],
			'mime' => $mime,
			'size' => $file['size'],
		);
	}

	/**
	 * El usuario actual es mensajero o administrador (vista movil).
	 *
	 * @return bool
	 */
	private function _is_courier()
	{
		return $this->ion_auth->in_group('mensajero') || $this->ion_auth->is_admin();
	}
}
