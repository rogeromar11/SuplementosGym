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
}

