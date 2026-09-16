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

		$this->data['images'] = $product ? $this->Catalog_product_model->images($product->id) : array();
		$this->data['pageScripts'] = array('assets/js/pages/product_gallery.js');

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

				// Valida las imagenes nuevas antes de guardar el producto.
				$uploadError = $this->_validate_gallery_uploads();
				if ($uploadError !== null) {
					$this->data['message'] = $uploadError;
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
					$redirectTo = 'products';
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
					$redirectTo = 'products/edit/' . $id;
					$this->audit_service->log('product.create', 'productos', 'products', $id);
					$flash = 'Producto creado. Ahora puedes agregar sus imágenes.';
				}

				// Galeria: elimina las seleccionadas, sube las nuevas y define la principal.
				$galleryError = $this->_process_gallery($id);
				if ($galleryError !== null) {
					$this->data['message'] = $galleryError;
					$this->data['product'] = $this->Catalog_product_model->find_for_country($id);
					$this->data['images'] = $this->Catalog_product_model->images($id);
					return $this->render('products/form', $this->data);
				}

				$this->session->set_flashdata('success', true);
				$this->session->set_flashdata('message', $flash);
				redirect($redirectTo);
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
	 * Valida las imagenes nuevas subidas en el formulario (campo images[]).
	 * No guarda nada; se llama antes de crear/actualizar el producto.
	 *
	 * @return string|null Mensaje de error o null si todo es valido
	 */
	private function _validate_gallery_uploads()
	{
		$files = isset($_FILES['images']) ? $_FILES['images'] : null;
		if (!$files || !isset($files['name']) || !is_array($files['name'])) {
			return null;
		}
		$allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
		$count = count($files['name']);
		for ($i = 0; $i < $count; $i++) {
			if (!isset($files['error'][$i]) || $files['error'][$i] === UPLOAD_ERR_NO_FILE) {
				continue;
			}
			if ($files['error'][$i] !== UPLOAD_ERR_OK) {
				return 'No fue posible recibir una de las imágenes.';
			}
			if ((int)$files['size'][$i] <= 0 || (int)$files['size'][$i] > 3 * 1024 * 1024) {
				return 'Cada imagen debe pesar como máximo 3 MB.';
			}
			$mime = (new finfo(FILEINFO_MIME_TYPE))->file($files['tmp_name'][$i]);
			if (!isset($allowed[$mime])) {
				return 'Formato de imagen no permitido (use JPG, PNG o WEBP).';
			}
		}
		return null;
	}

	/**
	 * Aplica los cambios de la galeria de un producto: elimina las imagenes
	 * marcadas, sube las nuevas, define la principal y sincroniza products.image.
	 *
	 * @param int $productId
	 * @return string|null Mensaje de error o null si todo salio bien
	 */
	private function _process_gallery($productId)
	{
		$dir = FCPATH . 'assets/img/products/';
		if (!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		// 1) Eliminar imagenes marcadas
		$deleteIds = (array) $this->input->post('delete_images');
		$deleteIds = array_values(array_filter(array_map('intval', $deleteIds)));
		if (!empty($deleteIds)) {
			$rows = $this->db->where_in('id', $deleteIds)
				->where('product_id', $productId)
				->get('product_images')->result();
			foreach ($rows as $row) {
				if (is_file($dir . $row->filename)) {
					@unlink($dir . $row->filename);
				}
				$this->Catalog_product_model->delete_image($row->id, $productId);
			}
		}

		// 2) Subir imagenes nuevas
		$uploadError = $this->_move_uploads($productId);
		if ($uploadError !== null) {
			return $uploadError;
		}

		// 3) Definir imagen principal si se eligio una existente
		$mainId = (int) $this->input->post('main_image_id');
		if ($mainId) {
			$belongs = $this->db->where('id', $mainId)->where('product_id', $productId)->count_all_results('product_images');
			if ($belongs) {
				$this->Catalog_product_model->set_main_image($productId, $mainId);
			}
		}

		// 4) Asegurar una principal y sincronizar products.image
		$this->Catalog_product_model->sync_main_image($productId);
		return null;
	}

	/**
	 * Mueve a assets/img/products/ las imagenes nuevas subidas (campo images[])
	 * y las registra en la galeria del producto.
	 *
	 * @param int $productId
	 * @return string|null Mensaje de error o null
	 */
	private function _move_uploads($productId)
	{
		$dir = FCPATH . 'assets/img/products/';
		if (!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}
		$files = isset($_FILES['images']) ? $_FILES['images'] : null;
		$allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
		if (!$files || !isset($files['name']) || !is_array($files['name'])) {
			return null;
		}
		$count = count($files['name']);
		for ($i = 0; $i < $count; $i++) {
			if (!isset($files['error'][$i]) || $files['error'][$i] === UPLOAD_ERR_NO_FILE) {
				continue;
			}
			$mime = (new finfo(FILEINFO_MIME_TYPE))->file($files['tmp_name'][$i]);
			if (!isset($allowed[$mime])) {
				continue;
			}
			$name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
			if (!move_uploaded_file($files['tmp_name'][$i], $dir . $name)) {
				return 'No fue posible guardar una de las imágenes.';
			}
			$this->Catalog_product_model->add_image($productId, $name, false);
		}
		return null;
	}

	/**
	 * Formatea la galeria para las respuestas AJAX.
	 *
	 * @param array $images
	 * @return array
	 */
	private function _gallery_payload($images)
	{
		$out = array();
		foreach ($images as $img) {
			$out[] = array(
				'id'      => (int) $img->id,
				'url'     => base_url('assets/img/products/' . rawurlencode($img->filename)),
				'is_main' => (int) $img->is_main === 1,
			);
		}
		return $out;
	}

	/**
	 * AJAX: agrega imagenes a la galeria de un producto existente.
	 *
	 * @param int $id
	 */
	public function upload_images($id)
	{
		$this->require_permission('productos.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$product = $this->Catalog_product_model->find_for_country($id);
		if (!$product) {
			$this->json_response(false, 'El producto no existe.', null, null, 404);
			return;
		}
		$error = $this->_validate_gallery_uploads();
		if ($error !== null) {
			$this->json_response(false, $error);
			return;
		}
		$error = $this->_move_uploads((int) $product->id);
		if ($error !== null) {
			$this->json_response(false, $error);
			return;
		}
		$this->Catalog_product_model->sync_main_image((int) $product->id);
		$this->audit_service->log('product.images_add', 'productos', 'product_images', $product->id);
		$this->json_response(true, 'Imágenes agregadas.', array(
			'images' => $this->_gallery_payload($this->Catalog_product_model->images($product->id)),
		));
	}

	/**
	 * AJAX: elimina una imagen de la galeria del producto.
	 *
	 * @param int $id
	 * @param int $imageId
	 */
	public function delete_image($id, $imageId)
	{
		$this->require_permission('productos.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$row = $this->db->where('id', (int) $imageId)->where('product_id', (int) $id)->get('product_images')->row();
		if (!$row) {
			$this->json_response(false, 'La imagen no existe.', null, null, 404);
			return;
		}
		$path = FCPATH . 'assets/img/products/' . $row->filename;
		if (is_file($path)) {
			@unlink($path);
		}
		$this->Catalog_product_model->delete_image($row->id, (int) $id);
		$this->Catalog_product_model->sync_main_image((int) $id);
		$this->audit_service->log('product.image_delete', 'productos', 'product_images', $row->id);
		$this->json_response(true, 'Imagen eliminada.', array(
			'images' => $this->_gallery_payload($this->Catalog_product_model->images($id)),
		));
	}

	/**
	 * AJAX: marca una imagen como principal.
	 *
	 * @param int $id
	 * @param int $imageId
	 */
	public function set_main_image($id, $imageId)
	{
		$this->require_permission('productos.editar');
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$belongs = $this->db->where('id', (int) $imageId)->where('product_id', (int) $id)->count_all_results('product_images');
		if (!$belongs) {
			$this->json_response(false, 'La imagen no existe.', null, null, 404);
			return;
		}
		$this->Catalog_product_model->set_main_image((int) $id, (int) $imageId);
		$this->Catalog_product_model->sync_main_image((int) $id);
		$this->audit_service->log('product.image_main', 'productos', 'product_images', (int) $imageId);
		$this->json_response(true, 'Imagen principal actualizada.', array(
			'images' => $this->_gallery_payload($this->Catalog_product_model->images($id)),
		));
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


