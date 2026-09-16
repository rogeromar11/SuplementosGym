<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog_product_model extends MY_Model
{
	protected $table = 'products';
	protected $primaryKey = 'id';
	protected $softDelete = true;

	/**
	 * Productos activos para selectores y busqueda de pedidos.
	 *
	 * @param string|null $term
	 * @return array
	 */
	public function options($term = null)
	{
		$this->db->select('id, sku, product_type, laboratory, name, weight, servings, flavor, unit_price, is_active, stock_enabled, stock_qty')
			->where('country_id', current_country_id())
			->where('is_active', 1);
		if ($term) {
			$this->db->group_start()
				->like('sku', $term)
				->or_like('name', $term)
				->or_like('product_type', $term)
				->or_like('laboratory', $term)
				->or_like('flavor', $term)
				->group_end();
		}
		$this->db->order_by('name', 'ASC');
		return $this->db->get($this->table)->result();
	}

	public function find_for_country($id)
	{
		return $this->db->where('id', $id)
			->where('country_id', current_country_id())
			->get($this->table)->row();
	}

	public function existing_by_skus($skus)
	{
		if (empty($skus)) {
			return array();
		}

		$rows = $this->db->select('id, sku')
			->where('country_id', current_country_id())
			->where_in('sku', $skus)
			->get($this->table)->result();
		$result = array();
		foreach ($rows as $row) {
			$result[strtoupper($row->sku)] = $row;
		}
		return $result;
	}

	public function update_for_country($id, $data)
	{
		return $this->db->where('id', $id)
			->where('country_id', current_country_id())
			->update($this->table, $data);
	}

	/**
	 * Eliminacion fisica de un producto (solo para productos ya inactivos).
	 * Las lineas de pedidos histÃ³ricas conservan nombre/cÃ³digo/precio copiados
	 * (store_order_items.product_id queda en NULL por ON DELETE SET NULL).
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete_for_country($id)
	{
		return $this->db->where('id', $id)
			->where('country_id', current_country_id())
			->delete($this->table);
	}

	/**
	 * Galeria de imagenes de un producto (la principal primero).
	 *
	 * @param int $product_id
	 * @return array
	 */
	public function images($product_id)
	{
		return $this->db->where('product_id', (int) $product_id)
			->order_by('is_main', 'DESC')
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get('product_images')->result();
	}

	/**
	 * Agrega una imagen a la galeria del producto.
	 *
	 * @param int $product_id
	 * @param string $filename
	 * @param bool $is_main
	 * @return int
	 */
	public function add_image($product_id, $filename, $is_main = false)
	{
		$this->db->insert('product_images', array(
			'product_id' => (int) $product_id,
			'filename'   => $filename,
			'is_main'    => $is_main ? 1 : 0,
			'sort_order' => 0,
			'created_at' => date('Y-m-d H:i:s'),
		));
		return (int) $this->db->insert_id();
	}

	/**
	 * Marca una imagen como principal (y quita la marca a las demas).
	 *
	 * @param int $product_id
	 * @param int $image_id
	 * @return bool
	 */
	public function set_main_image($product_id, $image_id)
	{
		$this->db->trans_start();
		$this->db->where('product_id', (int) $product_id)->update('product_images', array('is_main' => 0));
		$this->db->where('id', (int) $image_id)->where('product_id', (int) $product_id)->update('product_images', array('is_main' => 1));
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/**
	 * Elimina el registro de una imagen (no borra el archivo).
	 *
	 * @param int $image_id
	 * @param int $product_id
	 * @return bool
	 */
	public function delete_image($image_id, $product_id)
	{
		return $this->db->where('id', (int) $image_id)
			->where('product_id', (int) $product_id)
			->delete('product_images');
	}

	/**
	 * Asegura que exista una imagen principal (si no, toma la primera) y
	 * sincroniza products.image con la imagen principal de la galeria.
	 *
	 * @param int $product_id
	 * @return string|null Nombre de archivo de la imagen principal
	 */
	public function sync_main_image($product_id)
	{
		$main = $this->db->where('product_id', (int) $product_id)
			->order_by('is_main', 'DESC')
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get('product_images')->row();

		$filename = null;
		if ($main) {
			$filename = $main->filename;
			if (!(int) $main->is_main) {
				$this->db->where('product_id', (int) $product_id)->update('product_images', array('is_main' => 0));
				$this->db->where('id', $main->id)->update('product_images', array('is_main' => 1));
			}
		}

		$this->db->where('id', (int) $product_id)->update($this->table, array('image' => $filename));
		return $filename;
	}
}

