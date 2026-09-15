<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Gestion de pedidos.
 */
class Orders extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Order_model');
		$this->load->model('Warehouse_model');
		$this->load->model('Catalog_product_model');
		$this->load->model('Client_model');
		$this->load->library(array('Order_service', 'Map_link_parser'));
		$this->load->helper('form');
	}

	public function index()
	{
		$this->require_permission('pedidos.ver');

		$f = array(
			'status' => $this->input->get('status'),
			'date_from' => $this->input->get('date_from'),
			'date_to' => $this->input->get('date_to'),
			'search' => $this->input->get('search'),
			'seller_user_id' => $this->input->get('seller_user_id'),
			'warehouse_id' => $this->input->get('warehouse_id'),
			'origin' => $this->input->get('origin'),
		);

		// El vendedor ve solo sus pedidos (salvo permiso para ver todos)
		if ($this->ion_auth->in_group('vendedor') && !$this->ion_auth->is_admin() && !$this->has_permission('pedidos.ver_todos')) {
			$f['seller_user_id'] = $this->currentUser->id;
		}

		$this->data['pageTitle'] = 'Pedidos';
		$this->data['breadcrumbs'] = array(array('label' => 'Pedidos'));
		$this->data['orders'] = $this->Order_model->search($f);
		$this->data['filters'] = $f;
		$this->data['statuses'] = array(
			'registrado' => 'Registrado',
			'confirmado' => 'Confirmado',
			'pendiente_preparacion' => 'Pendiente de preparación',
			'en_preparacion' => 'En preparación',
			'preparado' => 'Preparado',
			'asignado_ruta' => 'Asignado a ruta',
			'en_ruta' => 'En ruta',
			'entregado' => 'Entregado',
			'no_entregado' => 'No entregado',
			'reprogramado' => 'Reprogramado',
			'cancelado' => 'Cancelado',
		);
		$this->data['warehouses'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('name')->get('warehouses')->result();
		$this->data['sellers'] = $this->db->select('u.id, u.first_name, u.last_name')->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')->join('groups g', 'g.id = ug.group_id')
			->where('u.country_id', current_country_id())->where('g.name', 'vendedor')->order_by('u.first_name')->get()->result();
		$this->data['pageScripts'] = array('assets/js/pages/orders.js');

		$this->render('orders/index', $this->data);
	}

	public function create()
	{
		$this->require_permission('pedidos.crear');
		$this->_form(null);
	}

	public function edit($id)
	{
		$this->require_permission('pedidos.editar');

		$order = $this->Order_model->find_with_items($id);
		if (!$order) {
			show_404();
		}
		if (in_array($order->status, array('entregado', 'cancelado'), true)) {
			$this->session->set_flashdata('message', 'Un pedido ' . $order->status . ' no puede editarse.');
			redirect('orders/detail/' . $id . back_query_string());
		}
		$this->_form($order);
	}

	private function _form($order)
	{
		$this->load->library('form_validation');

		$isEdit = (bool)$order;
		$canChangePrice = $this->has_permission('pedidos.modificar_precio');
		$canDiscount = $this->has_permission('pedidos.aplicar_descuento');

		$this->data['pageTitle'] = $isEdit ? 'Editar pedido ' . $order->order_number : 'Nuevo pedido';
		$this->data['breadcrumbs'] = array(
			array('label' => 'Pedidos', 'href' => base_url('orders')),
			array('label' => $isEdit ? 'Editar pedido' : 'Nuevo pedido'),
		);
		$this->data['order'] = $order;
		$this->data['warehouses'] = $this->Warehouse_model->options();
		$this->data['defaultWarehouse'] = $isEdit ? null : $this->Warehouse_model->default_warehouse();
		$this->data['paymentMethods'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('payment_methods')->result();
		$this->data['transports'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('transports')->result();
		$this->data['canChangePrice'] = $canChangePrice;
		$this->data['canDiscount'] = $canDiscount;
		$this->data['sellers'] = $this->db->select('u.id, u.first_name, u.last_name')->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')->join('groups g', 'g.id = ug.group_id')
			->where('u.country_id', current_country_id())->where('g.name', 'vendedor')->order_by('u.first_name')->get()->result();
		$this->data['pageScripts'] = array('assets/js/pages/order_form.js');

		if ($this->input->post()) {
			$this->form_validation->set_rules('customer_name', 'Nombre del cliente', 'trim|required|max_length[150]');
			$this->form_validation->set_rules('customer_phone', 'Teléfono', 'trim|required');
			$this->form_validation->set_rules('delivery_address', 'Dirección de entrega', 'trim|required');
			$this->form_validation->set_rules('delivery_zone', 'Zona de entrega', 'trim|required|max_length[100]');
			$this->form_validation->set_rules('warehouse_id', 'Bodega', 'required|integer');

			$rawItems = (array)$this->input->post('item_name');
			$items = array();
			$missingProduct = false;
			foreach ($rawItems as $i => $name) {
				if (trim((string)$name) === '') {
					continue;
				}
				$isManual = !empty($_POST['is_manual'][$i]) ? 1 : 0;
				$productId = isset($_POST['product_id'][$i]) && $_POST['product_id'][$i] !== '' ? (int)$_POST['product_id'][$i] : null;
				if (!$isManual && !$productId) {
					$missingProduct = true;
				}
				$items[] = array(
					'product_id' => $productId,
					'item_sku' => isset($_POST['item_sku'][$i]) ? $_POST['item_sku'][$i] : null,
					'item_name' => $name,
					'quantity' => isset($_POST['quantity'][$i]) ? (int)round((float)$_POST['quantity'][$i]) : 1,
					'unit_price' => isset($_POST['unit_price'][$i]) ? $_POST['unit_price'][$i] : 0,
					'is_manual' => $isManual,
				);
			}

			if ($missingProduct) {
				$this->data['message'] = 'Seleccione el producto de cada línea de catálogo antes de guardar.';
			} elseif ($this->_requested_date_in_past($isEdit, $order)) {
				$this->data['message'] = 'La fecha solicitada de entrega no puede ser anterior a hoy.';
			} elseif ($this->_paid_receipt_missing($isEdit, $order)) {
				$this->data['message'] = 'Adjunte el comprobante del pago realizado para continuar.';
			} elseif ($this->form_validation->run() === TRUE && !empty($items)) {
				// Normalizacion del telefono
				$countryCode = app_setting('country_code', '506');
				$phone = $this->map_link_parser->normalize_phone($this->input->post('customer_phone'), $countryCode);
				$phone2 = $this->input->post('customer_phone2');
				$phone2Raw = $phone2 !== null ? trim((string)$phone2) : '';
				$phone2Data = $phone2Raw !== ''
					? $this->map_link_parser->normalize_phone($phone2Raw, $countryCode)
					: array('intl' => null, 'whatsapp' => null);

				// Coordenadas desde enlace de mapa
				$coords = $this->map_link_parser->parse_coordinates($this->input->post('map_url'));
				$lat = $this->input->post('latitude');
				$lng = $this->input->post('longitude');
				if ($coords && ($lat === '' || $lng === '')) {
					$lat = $coords[0];
					$lng = $coords[1];
				}

				$data = array(
					'order_number' => $isEdit ? $order->order_number : $this->order_service->next_order_number(),
					'customer_name' => $this->input->post('customer_name'),
					'customer_phone' => $this->input->post('customer_phone'),
					'customer_phone_intl' => $phone['intl'],
					'customer_phone_whatsapp' => $phone['whatsapp'],
					'customer_phone2' => $phone2Data['intl'] !== null ? $phone2Raw : null,
					'customer_phone2_intl' => $phone2Data['intl'],
					'customer_phone2_whatsapp' => $phone2Data['whatsapp'],
					'customer_email' => $this->input->post('customer_email'),
					'delivery_address' => $this->input->post('delivery_address'),
					'delivery_reference' => $this->input->post('delivery_reference'),
				'delivery_zone' => trim((string)$this->input->post('delivery_zone')) !== '' ? trim((string)$this->input->post('delivery_zone')) : null,
					'map_url' => $this->input->post('map_url'),
					'latitude' => $lat !== '' ? $lat : null,
					'longitude' => $lng !== '' ? $lng : null,
					'warehouse_id' => $this->input->post('warehouse_id'),
					'seller_user_id' => $isEdit ? $order->seller_user_id : ($this->currentUser->id ?: null),
					'payment_method_id' => $this->input->post('payment_method_id'),
					'discount' => $canDiscount ? (float)$this->input->post('discount') : 0,
					'includes_gifts' => $this->input->post('includes_gifts') ? 1 : 0,
					'gift_description' => $this->input->post('gift_description'),
					'is_parcel' => $this->input->post('is_parcel') ? 1 : 0,
					'transport_id' => $this->input->post('transport_id') ? (int)$this->input->post('transport_id') : null,
					'notes' => $this->input->post('notes'),
					'requested_delivery_date' => $this->input->post('requested_delivery_date') ?: null,
					'paid_amount' => $isEdit ? $order->paid_amount : 0,
					'balance_amount' => $isEdit ? $order->balance_amount : 0,
					'status' => $isEdit ? $order->status : 'registrado',
				);

				// Homologación con el catálogo de clientes: se vincula (o crea)
				// el cliente para poder ubicar quién realizó el pedido.
				$manualClient = $this->Client_model->find_or_create_manual(
					current_country_id(),
					array(
						'name' => $this->input->post('customer_name'),
						'email' => $this->input->post('customer_email'),
						'phone' => $phone['intl'] ?: $this->input->post('customer_phone'),
						'phone2' => $phone2Data['intl'],
						'zone' => trim((string)$this->input->post('delivery_zone')) ?: null,
						'address' => $this->input->post('delivery_address'),
						'delivery_type' => 'domicilio',
					),
					$this->currentUser->id
				);
				$data['client_id'] = $manualClient ?: null;
				$data['origin'] = $isEdit ? $order->origin : 'admin';
				if ($manualClient) {
					$clientRow = $this->db->select('user_id')->where('id', $manualClient)->get('clients')->row();
					$data['user_id'] = $clientRow ? $clientRow->user_id : null;
				}

				if (!$canChangePrice) {
					// Fijar precios del catálogo para productos existentes
					foreach ($items as $k => $item) {
						if (empty($item['product_id'])) {
							continue;
						}
						$prod = $this->db->where('id', $item['product_id'])->where('country_id', current_country_id())->get('products')->row();
						if ($prod) {
							$items[$k]['unit_price'] = $prod->unit_price;
						}
					}
				}

				$result = $isEdit
					? $this->order_service->update($order->id, $data, $items, $this->currentUser->id, array('status'))
					: $this->order_service->create($data, $items, $this->currentUser->id);

				if ($result['success']) {
					// El pago anticipado se registra como un pago real, para que el saldo
					// quede en cero y el mensajero no tenga que cobrarlo al entregar.
					if ($this->input->post('paid_before_delivery')) {
						$paymentOrder = $this->db->select('balance_amount')
							->where('id', $result['id'])
							->where('country_id', current_country_id())
							->get('store_orders')->row();
						if ($paymentOrder && (float)$paymentOrder->balance_amount > 0.01) {
							$this->load->library('Payment_service');
							$paymentResult = $this->payment_service->register_payment(
								$result['id'],
								(int)$data['payment_method_id'],
								(float)$paymentOrder->balance_amount,
								$this->currentUser->id,
								array(
									'reference' => 'PAGO-ANTICIPADO-' . $result['id'],
									'notes' => 'Pago marcado como recibido al registrar el pedido.',
								)
							);
							if (!$paymentResult['success']) {
								$this->session->set_flashdata('success', false);
								$this->session->set_flashdata('message', 'El pedido fue guardado, pero no se pudo registrar el pago anticipado: ' . $paymentResult['error']);
								redirect('orders/detail/' . $result['id'] . back_query_string());
							}
						}
					}

					// Adjunto opcional: comprobante de pago
					$receiptMsg = '';
					if (!empty($_FILES['payment_receipt']['name'])) {
						$attId = $this->order_service->store_payment_receipt($result['id'], $this->currentUser->id, $_FILES['payment_receipt']);
						if (!$attId) {
							$receiptMsg = ' El comprobante no pudo adjuntarse (formato no permitido o tamaño excesivo).';
						}
					}
					$this->session->set_flashdata('success', true);
					$this->session->set_flashdata('message', ($isEdit ? 'Pedido actualizado.' : 'Pedido ' . $data['order_number'] . ' registrado.') . $receiptMsg);
					redirect('orders/detail/' . $result['id'] . back_query_string());
				}
				$this->data['message'] = $result['error'];
			}
		}

		$this->render('orders/form', $this->data);
	}

	public function detail($id)
	{
		$this->require_permission('pedidos.ver');

		$order = $this->Order_model->find_with_items($id);
		if (!$order) {
			show_404();
		}

		$this->data['pageTitle'] = 'Pedido ' . $order->order_number;
		$this->data['breadcrumbs'] = array(
			array('label' => 'Pedidos', 'href' => base_url('orders')),
			array('label' => $order->order_number),
		);
		$this->data['order'] = $order;
		$this->data['paymentMethods'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('payment_methods')->result();
		$this->data['pageScripts'] = array('assets/js/pages/order_detail.js');

		$this->render('orders/detail', $this->data);
	}

	public function print_view($id)
	{
		$this->require_permission('pedidos.ver');

		$order = $this->Order_model->find_with_items($id);
		if (!$order) {
			show_404();
		}

		$this->data['pageTitle'] = 'Pedido ' . $order->order_number;
		$this->data['order'] = $order;

		$this->render_print('orders/print', $this->data);
	}

	/**
	 * Valida que, si se marca "El cliente ya realizó el pago completo", se
	 * adjunte el comprobante. En edicion basta con que el pedido ya tenga un
	 * comprobante previo (no exige subirlo de nuevo).
	 *
	 * @param bool $isEdit
	 * @param object|null $order Pedido en edicion
	 * @return bool
	 */
	private function _paid_receipt_missing($isEdit, $order = null)
	{
		if (!$this->input->post('paid_before_delivery')) {
			return false;
		}
		$hasFile = isset($_FILES['payment_receipt'])
			&& isset($_FILES['payment_receipt']['error'])
			&& $_FILES['payment_receipt']['error'] !== UPLOAD_ERR_NO_FILE;
		if ($hasFile) {
			return false;
		}
		if ($isEdit && $order) {
			$hasExisting = $this->db->where('order_id', $order->id)
				->where('type', 'comprobante_pago')
				->count_all_results('attachments') > 0;
			if ($hasExisting) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Valida que la fecha solicitada de entrega no sea anterior a hoy.
	 * En edicion se permite conservar la fecha original del pedido (solo se
	 * rechaza si se cambia a una fecha pasada).
	 *
	 * @param bool $isEdit
	 * @param object|null $order Pedido en edicion
	 * @return bool
	 */
	private function _requested_date_in_past($isEdit, $order = null)
	{
		$date = trim((string)$this->input->post('requested_delivery_date'));
		if ($date === '') {
			return false;
		}
		$parsed = DateTime::createFromFormat('Y-m-d', $date);
		if (!$parsed || $parsed->format('Y-m-d') !== $date) {
			return false;
		}
		if ($parsed->format('Y-m-d') >= date('Y-m-d')) {
			return false;
		}
		// Edicion: la fecha original se conserva sin rechazo
		if ($isEdit && $order && trim((string)$order->requested_delivery_date) === $date) {
			return false;
		}
		return true;
	}

	/**
	 * Busqueda AJAX de productos.
	 */
	public function product_search()
	{
		$term = $this->input->get('term');
		$products = $this->Catalog_product_model->options($term);
		$results = array();
		foreach ($products as $p) {
			$displayName = product_display_name($p);
			$results[] = array(
				'id' => $p->id,
				'text' => $displayName . ' [' . $p->sku . ']',
				'sku' => $p->sku,
				'name' => $displayName,
				'unit_price' => $p->unit_price,
				'stock_qty' => (int)$p->stock_qty,
			);
		}
		$this->json_response(true, '', array('results' => $results));
	}

	/**
	 * Busqueda AJAX de clientes del catálogo.
	 */
	public function client_search()
	{
		$term = $this->input->get('term');
		$this->db->select('id, code, name, phone, phone2, zone, address, delivery_type')
			->from('clients')
			->where('country_id', current_country_id())
			->where('is_active', 1)
			->group_start()
			->like('name', $term)
			->or_like('code', $term)
			->or_like('phone', $term)
			->group_end()
			->order_by('name', 'ASC')
			->limit(20);
		$clients = $this->db->get()->result();
		$results = array();
		foreach ($clients as $c) {
			$results[] = array(
				'id' => $c->id,
				'text' => $c->name . ($c->code ? ' [' . $c->code . ']' : ''),
				'name' => $c->name,
				'code' => $c->code,
				'phone' => $c->phone,
				'phone2' => $c->phone2,
				'zone' => $c->zone,
				'address' => $c->address,
				'delivery_type' => $c->delivery_type,
			);
		}
		$this->json_response(true, '', array('results' => $results));
	}

	/**
	 * AJAX: devuelve el stock disponible actual de un lote de productos.
	 * Se usa para validar en el formulario que la cantidad no supere el inventario.
	 */
	public function product_stock()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$ids = $this->input->post('product_ids');
		if (!is_array($ids)) {
			$ids = $ids ? array($ids) : array();
		}
		$ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
		$stock = array();
		if (!empty($ids)) {
			$rows = $this->db->select('id, stock_qty')
				->where_in('id', $ids)
				->where('country_id', current_country_id())
				->get('products')->result();
			foreach ($rows as $row) {
				$stock[(int)$row->id] = (int)$row->stock_qty;
			}
		}
		$this->json_response(true, '', array('stock' => $stock));
	}

	/**
	 * AJAX: parsea un enlace de mapa y devuelve coordenadas.
	 */
	public function parse_map()
	{
		$url = $this->input->post('map_url');
		$coords = $this->map_link_parser->parse_coordinates($url);
		$this->json_response(true, '', array('coords' => $coords));
	}

	/**
	 * AJAX: geocodifica una direccion de entrega y devuelve coordenadas aproximadas.
	 */
	public function geocode_address()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$address = $this->input->post('address');
		$country = current_country();
		$code = $country && !empty($country->code) ? $country->code : null;
		$coords = $this->map_link_parser->geocode_address($address, $code);
		if (!$coords) {
			$this->json_response(false, 'No se pudo ubicar la dirección automáticamente. Puede fijar el pin manualmente en el mapa.');
			return;
		}
		$this->json_response(true, '', array('coords' => $coords));
	}

	/**
	 * AJAX: actualiza la fecha de un pedido reprogramado.
	 */
	public function update_rescheduled_date($id)
	{
		$this->require_permission('pedidos.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$order = $this->db->where('id', $id)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order) {
			$this->json_response(false, 'El pedido no existe.', null, null, 404);
			return;
		}
		if ($order->status !== 'reprogramado') {
			$this->json_response(false, 'Solo se puede cambiar la fecha de un pedido reprogramado.');
			return;
		}

		$date = trim((string)$this->input->post('rescheduled_delivery_date'));
		$parsed = DateTime::createFromFormat('Y-m-d', $date);
		if (!$parsed || $parsed->format('Y-m-d') !== $date) {
			$this->json_response(false, 'Indique una fecha válida.');
			return;
		}

		$previousDate = $order->rescheduled_delivery_date;
		$this->db->trans_start();
		$this->db->where('id', $order->id)->update('store_orders', array(
			'rescheduled_delivery_date' => $date,
			'updated_by' => $this->currentUser->id,
			'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->order_service->record_status_change($order->id, 'reprogramado', 'reprogramado', $this->currentUser->id, 'Fecha reprogramada: ' . ($previousDate ?: 'sin fecha') . ' → ' . $date);
		$this->db->trans_complete();

		if (!$this->db->trans_status()) {
			$this->json_response(false, 'No fue posible actualizar la fecha.');
			return;
		}
		$this->audit_service->log('order.reschedule_date_update', 'pedidos', 'orders', $order->id, array('from' => $previousDate, 'to' => $date));
		$this->json_response(true, 'Fecha de entrega actualizada.');
	}

	/**
	 * AJAX: anula (cancela) un pedido.
	 */
	public function cancel($id)
	{
		$this->require_permission('pedidos.anular');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$order = $this->db->where('id', $id)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order) {
			$this->json_response(false, 'El pedido no existe.', null, null, 404);
			return;
		}
		if (in_array($order->status, array('entregado', 'cancelado', 'en_ruta'), true)) {
			$this->json_response(false, 'El pedido no puede anularse en su estado actual.');
			return;
		}
		$notes = $this->input->post('reason');
		$ok = $this->order_service->change_status($id, 'cancelado', $this->currentUser->id, $notes ?: 'Anulacion manual');
		$this->json_response($ok, $ok ? 'Pedido anulado.' : 'No fue posible anular el pedido.');
	}

	/**
	 * AJAX: elimina fisicamente un pedido.
	 */
	public function delete($id)
	{
		$this->require_permission('pedidos.eliminar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$result = $this->order_service->delete((int)$id, $this->currentUser->id);
		$this->json_response($result['success'], $result['error'] ?: 'Pedido eliminado.');
	}

	/**
	 * AJAX: agrega un comprobante de pago a un pedido ya entregado.
	 * Solo el administrador puede hacerlo; el comprobante es el unico dato
	 * editable en ese estado y el resto del pedido se mantiene intacto.
	 */
	public function add_receipt($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		if (!$this->ion_auth->is_admin()) {
			$this->json_response(false, 'Solo el administrador puede agregar comprobantes de pago.', null, null, 403);
			return;
		}

		$order = $this->db->where('id', $id)->where('country_id', current_country_id())->get('store_orders')->row();
		if (!$order) {
			$this->json_response(false, 'El pedido no existe.', null, null, 404);
			return;
		}
		if ($order->status !== 'entregado') {
			$this->json_response(false, 'El comprobante de pago solo puede agregarse cuando el pedido ya fue entregado.');
			return;
		}

		if (empty($_FILES['payment_receipt']['name'])) {
			$this->json_response(false, 'Seleccione un archivo para adjuntar.');
			return;
		}

		$attId = $this->order_service->store_payment_receipt($order->id, $this->currentUser->id, $_FILES['payment_receipt']);
		if (!$attId) {
			$this->json_response(false, 'No fue posible adjuntar el comprobante. Use una imagen (JPG, PNG, WEBP) o PDF de máximo ' . (int)app_setting('evidence_max_size_mb', 8) . ' MB.');
			return;
		}

		$this->audit_service->log('order.receipt_added', 'pedidos', 'orders', $order->id, array(
			'order_number' => $order->order_number,
			'attachment_id' => $attId,
			'status' => $order->status,
		));

		$this->json_response(true, 'Comprobante de pago agregado correctamente.', array('attachment_id' => $attId));
	}
}

