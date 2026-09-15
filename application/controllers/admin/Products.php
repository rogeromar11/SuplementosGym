<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Catalogo de productos (ACME).
 */
class Products extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Catalog_product_model');
		$this->load->helper('form');
	}

	public function index()
	{
		$this->require_permission('productos.ver');

		$this->data['pageTitle'] = 'Productos';
		$this->data['breadcrumbs'] = array(array('label' => 'Productos'));
		$this->data['products'] = $this->db->where('country_id', current_country_id())->order_by('name', 'ASC')->get('products')->result();
		foreach ($this->data['products'] as $product) {
			$product->display_name = product_display_name($product);
		}

		// Alerta de pocos productos: umbral configurable en Configuracion
		$threshold = (int)app_setting('low_stock_threshold', 5);
		$this->data['lowStockThreshold'] = $threshold;
		$this->data['lowStock'] = $this->db->select('id, sku, name, stock_qty')
			->where('country_id', current_country_id())
			->where('is_active', 1)
			->where('stock_enabled', 1)
			->where('stock_qty <=', $threshold)
			->order_by('stock_qty', 'ASC')
			->get('products')->result();

		$this->data['pageScripts'] = array('assets/js/pages/products.js');

		$this->render('products/index', $this->data);
	}

	public function create()
	{
		$this->require_permission('productos.crear');
		$this->_set_create_page_data();
		$this->_save_form(null);
	}

	/**
	 * Descarga una plantilla de Excel con el formato esperado para importar productos.
	 */
	public function import_template()
	{
		$this->require_permission('productos.crear');
		$this->load->library('Excel_service');

		$columns = array(
			array('header' => 'Producto', 'required' => true, 'example' => 'Suplemento alimenticio', 'hint' => 'Tipo o categoría'),
			array('header' => 'Laboratorio', 'required' => false, 'example' => 'Laboratorio Ejemplo', 'hint' => 'Marca o fabricante'),
			array('header' => 'Nombre', 'required' => false, 'example' => 'Multivitamínico 60 cápsulas', 'hint' => 'Si queda vacío se usa "Producto"'),
			array('header' => 'Peso', 'required' => false, 'example' => '500 g', 'hint' => 'Presentación'),
			array('header' => 'Servidas', 'required' => false, 'example' => '60', 'hint' => 'Cantidad de dosis o porciones'),
			array('header' => 'Sabor', 'required' => false, 'example' => 'Vainilla', 'hint' => 'Sabor o variante'),
			array('header' => 'Cantidad disponible', 'required' => true, 'example' => 50, 'hint' => 'Existencia en inventario'),
			array('header' => 'Venta', 'required' => true, 'example' => 12500.50, 'hint' => 'Precio de venta (sin moneda)'),
			array('header' => 'Costo', 'required' => true, 'example' => 9800.00, 'hint' => 'Precio de costo (sin moneda)'),
		);

		$this->excel_service->import_template('plantilla-productos.xlsx', 'Sheet1', $columns);
	}

	/**
	 * Lee y valida un Excel antes de habilitar su importacion.
	 */
	public function import_preview()
	{
		$this->require_permission('productos.crear');
		if ($this->input->method(TRUE) !== 'POST') {
			redirect('products/create');
		}

		$this->_set_create_page_data();
		$this->data['product'] = null;
		$this->data['importPreview'] = null;
		$this->session->unset_userdata('product_import_preview');

		$file = isset($_FILES['inventory_file']) ? $_FILES['inventory_file'] : null;
		$error = $this->_validate_import_file($file);
		if ($error !== null) {
			$this->data['importMessage'] = $error;
			return $this->render('products/form', $this->data);
		}

		$this->load->library('Product_import_service');
		$preview = $this->product_import_service->parse($file['tmp_name']);

		// Todos los productos se crean nuevos; el numero PRO-XXXX se genera
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

		$preview['valid'] = empty($preview['errors']) && $preview['row_error_count'] === 0 && !empty($preview['rows']);
		$preview['filename'] = basename($file['name']);
		$this->data['importPreview'] = $preview;

		if ($preview['valid']) {
			$token = bin2hex(random_bytes(24));
			$this->session->set_userdata('product_import_preview', array(
				'token' => $token,
				'user_id' => (int)$this->currentUser->id,
				'country_id' => (int)current_country_id(),
				'expires_at' => time() + 1800,
				'filename' => $preview['filename'],
				'rows' => $preview['rows'],
			));
			$this->data['importToken'] = $token;
		}

		$this->render('products/form', $this->data);
	}

	/**
	 * Confirma una vista previa valida y aplica todo el lote atomicamente.
	 */
	public function import_commit()
	{
		$this->require_permission('productos.crear');
		if ($this->input->method(TRUE) !== 'POST') {
			redirect('products/create');
		}

		$stored = $this->session->userdata('product_import_preview');
		$token = (string)$this->input->post('import_token');
		if (!$this->_valid_import_session($stored, $token)) {
			$this->session->unset_userdata('product_import_preview');
			$this->session->set_flashdata('message', 'La vista previa expiro o no pertenece a esta sesión. Vuelva a cargar el archivo.');
			redirect('products/create');
		}

		$usedSkus = array();
		$created = 0;

		$this->db->trans_begin();
		foreach ($stored['rows'] as $row) {
			$data = array(
				'sku' => $this->_generate_sku($usedSkus),
				'product_type' => $row['product_type'],
				'laboratory' => $row['laboratory'] !== '' ? $row['laboratory'] : null,
				'name' => $row['name'],
				'weight' => $row['weight'] !== '' ? $row['weight'] : null,
				'servings' => $row['servings'] !== '' ? $row['servings'] : null,
				'flavor' => $row['flavor'] !== '' ? $row['flavor'] : null,
				'cost_price' => round((float)$row['cost_price'], 2),
				'unit_price' => round((float)$row['unit_price'], 2),
				'is_active' => 1,
				'stock_enabled' => 1,
				'stock_qty' => (int)$row['stock_qty'],
				'country_id' => current_country_id(),
				'created_by' => $this->currentUser->id,
			);

			if (!$this->Catalog_product_model->insert($data)) {
				$this->db->trans_rollback();
				$this->_import_failed();
				return;
			}
			$created++;
		}

		$this->audit_service->log('product.import', 'productos', 'products', null, array(
			'filename' => $stored['filename'],
			'created' => $created,
		));

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$this->_import_failed();
			return;
		}
		$this->db->trans_commit();
		$this->session->unset_userdata('product_import_preview');
		$this->session->set_flashdata('success', true);
		$this->session->set_flashdata('message', 'Inventario importado: ' . $created . ' productos nuevos.');
		redirect('products');
	}

	public function edit($id)
	{
		$this->require_permission('productos.editar');

		$product = $this->Catalog_product_model->find_for_country($id);
		if (!$product) {
			show_404();
		}
		$this->data['pageTitle'] = 'Editar producto';
		$this->data['breadcrumbs'] = array(
			array('label' => 'Productos', 'href' => base_url('products')),
			array('label' => 'Editar producto'),
		);
		$this->_save_form($product);
	}

	private function _save_form($product)
	{
		$this->load->library('form_validation');

		if ($this->input->post()) {
			$this->form_validation->set_rules('product_type', 'Producto', 'trim|required|max_length[100]');
			$this->form_validation->set_rules('laboratory', 'Laboratorio', 'trim|max_length[120]');
			$this->form_validation->set_rules('name', 'Nombre', 'trim|max_length[150]');
			$this->form_validation->set_rules('weight', 'Peso', 'trim|max_length[50]');
			$this->form_validation->set_rules('servings', 'Servidas', 'trim|max_length[50]');
			$this->form_validation->set_rules('flavor', 'Sabor', 'trim|max_length[120]');
			$this->form_validation->set_rules('cost_price', 'Costo', 'trim|required|numeric|greater_than_equal_to[0]');
			$this->form_validation->set_rules('unit_price', 'Venta', 'trim|required|numeric|greater_than_equal_to[0]');
			$this->form_validation->set_rules('stock_qty', 'Cantidad disponible', 'trim|required|integer|greater_than_equal_to[0]');

			if ($this->form_validation->run() === TRUE) {
				$name = trim((string)$this->input->post('name'));
				$productType = trim((string)$this->input->post('product_type'));

				// Imagen del producto (opcional): se guarda en assets/img/products/
				$image = $this->_process_image($product ? $product->image : null);
				if ($image['error'] !== null) {
					$this->data['message'] = $image['error'];
					$this->data['product'] = $product;
					return $this->render('products/form', $this->data);
				}

				// El numero de producto (PRO-XXXX) se genera automaticamente al crear;
				// al editar se conserva el asignado.
				$usedSkus = array();
				$data = array(
					'sku' => $product ? $product->sku : $this->_generate_sku($usedSkus),
					'product_type' => $productType,
					'laboratory' => $this->_nullable_post('laboratory'),
					'name' => $name !== '' ? $name : $productType,
					'weight' => $this->_nullable_post('weight'),
					'servings' => $this->_nullable_post('servings'),
					'flavor' => $this->_nullable_post('flavor'),
					'description' => $this->input->post('description'),
					'store_description' => $this->input->post('store_description'),
					'image' => $image['file'],
					'featured' => $this->input->post('featured') ? 1 : 0,
					'cost_price' => round((float)$this->input->post('cost_price'), 2),
					'unit_price' => round((float)$this->input->post('unit_price'), 2),
					'is_active' => $this->input->post('is_active') ? 1 : 0,
					'stock_enabled' => 1,
					'stock_qty' => (int)round((float)$this->input->post('stock_qty')),
				);

				if ($product) {
					$data['updated_by'] = $this->currentUser->id;
					if (!$this->Catalog_product_model->update_for_country($product->id, $data)) {
						$this->data['message'] = 'El SKU ya existe.';
						return $this->render('products/form', $this->data);
					}
					$id = $product->id;
					$this->audit_service->log('product.update', 'productos', 'products', $id);
					$flash = 'Producto actualizado correctamente.';
				} else {
					$data['country_id'] = current_country_id();
					$data['created_by'] = $this->currentUser->id;
					$id = $this->Catalog_product_model->insert($data);
					if (!$id) {
						$this->data['message'] = 'El SKU ya existe.';
						return $this->render('products/form', $this->data);
					}
					$this->audit_service->log('product.create', 'productos', 'products', $id);
					$flash = 'Producto creado correctamente.';
				}

				$this->session->set_flashdata('success', true);
				$this->session->set_flashdata('message', $flash);
				redirect('products');
			}
		}

		$this->data['product'] = $product;
		$this->render('products/form', $this->data);
	}

	private function _set_create_page_data()
	{
		$this->data['pageTitle'] = 'Nuevo producto';
		$this->data['breadcrumbs'] = array(
			array('label' => 'Productos', 'href' => base_url('products')),
			array('label' => 'Nuevo producto'),
		);
	}

	private function _nullable_post($field)
	{
		$value = trim((string)$this->input->post($field));
		return $value === '' || $value === '0' ? null : $value;
	}

	/**
	 * Procesa la imagen opcional del producto.
	 * Guarda el archivo en assets/img/products/ (misma carpeta que usa la tienda)
	 * y devuelve el nombre de archivo a almacenar en products.image.
	 *
	 * @param string|null $existing Imagen actual
	 * @return array ['error' => string|null, 'file' => string|null]
	 */
	private function _process_image($existing)
	{
		$dir = FCPATH . 'assets/img/products/';
		$file = isset($_FILES['image']) ? $_FILES['image'] : null;

		if ($file && isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
			if ($file['error'] !== UPLOAD_ERR_OK) {
				return array('error' => 'No fue posible recibir la imagen.', 'file' => $existing);
			}
			if ((int)$file['size'] <= 0 || (int)$file['size'] > 3 * 1024 * 1024) {
				return array('error' => 'La imagen debe pesar como máximo 3 MB.', 'file' => $existing);
			}

			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime = $finfo->file($file['tmp_name']);
			$allowed = array(
				'image/jpeg' => 'jpg',
				'image/png'  => 'png',
				'image/webp' => 'webp',
			);
			if (!isset($allowed[$mime])) {
				return array('error' => 'Formato de imagen no permitido (use JPG, PNG o WEBP).', 'file' => $existing);
			}

			if (!is_dir($dir)) {
				mkdir($dir, 0775, true);
			}
			$name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
			if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
				return array('error' => 'No fue posible guardar la imagen.', 'file' => $existing);
			}

			if ($existing && $existing !== $name && is_file($dir . $existing)) {
				@unlink($dir . $existing);
			}
			return array('error' => null, 'file' => $name);
		}

		if ($this->input->post('remove_image')) {
			if ($existing && is_file($dir . $existing)) {
				@unlink($dir . $existing);
			}
			return array('error' => null, 'file' => null);
		}

		return array('error' => null, 'file' => $existing);
	}

	/**
	 * Genera un numero de producto unico (PRO-XXXX) evitando los ya ocupados
	 * en el lote de importacion y los existentes en la base de datos.
	 *
	 * @param array $usedSkus Codigos ocupados (por referencia; se actualiza)
	 * @return string
	 */
	private function _generate_sku(&$usedSkus)
	{
		$prefix = 'PRO-';
		$row = $this->db->select('sku')
			->from('products')
			->where('country_id', current_country_id())
			->like('sku', $prefix, 'after')
			->order_by('sku', 'DESC')
			->limit(1)
			->get()->row();
		$seq = $row ? (int)substr($row->sku, -4) + 1 : 1;

		do {
			$sku = $prefix . str_pad($seq++, 4, '0', STR_PAD_LEFT);
		} while (isset($usedSkus[$sku]) || $this->_sku_exists($sku));

		$usedSkus[$sku] = true;
		return $sku;
	}

	/**
	 * Verifica si un SKU ya existe en el pais actual.
	 *
	 * @param string $sku
	 * @return bool
	 */
	private function _sku_exists($sku)
	{
		return $this->db->where('country_id', current_country_id())
			->where('sku', $sku)
			->count_all_results('products') > 0;
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
		$this->session->set_flashdata('message', 'No fue posible importar el inventario. No se guardo ningun cambio.');
		redirect('products/create');
	}

	public function delete($id)
	{
		$this->require_permission('productos.eliminar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$product = $this->Catalog_product_model->find_for_country($id);
		if (!$product) {
			show_404();
		}

		// Producto activo: baja logica (puede eliminarse definitivamente despues)
		if ((int)$product->is_active === 1) {
			if (!$this->Catalog_product_model->update_for_country($id, array('is_active' => 0, 'updated_by' => $this->currentUser->id))) {
				$this->json_response(false, 'No fue posible desactivar el producto.');
				return;
			}
			$this->audit_service->log('product.delete', 'productos', 'products', $id);
			$this->json_response(true, 'Producto desactivado. Vuelva a pulsar Eliminar para borrarlo definitivamente.');
			return;
		}

		// Producto ya inactivo: eliminacion fisica. Los pedidos historicos
		// conservan nombre/codigo/precio copiados en sus lineas.
		if (!$this->Catalog_product_model->delete_for_country($id)) {
			$this->json_response(false, 'No fue posible eliminar el producto.');
			return;
		}
		$this->audit_service->log('product.delete', 'productos', 'products', $id, array(
			'sku' => $product->sku,
			'hard_delete' => true,
		));
		$this->json_response(true, 'Producto eliminado definitivamente.');
	}
}


