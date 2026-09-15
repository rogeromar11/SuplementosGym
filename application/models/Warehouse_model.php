<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse_model extends MY_Model
{
	protected $table = 'warehouses';
	protected $primaryKey = 'id';
	protected $softDelete = true;

	/**
	 * Lista de bodegas activas para selectores.
	 *
	 * @return array
	 */
	public function options()
	{
		$this->db->select('id, code, name')->where('country_id', current_country_id())->where('is_active', 1)->order_by('name', 'ASC');
		return $this->db->get($this->table)->result();
	}

	/**
	 * Bodega predeterminada (o la primera activa) del pais actual.
	 *
	 * @return object|null
	 */
	public function default_warehouse()
	{
		$row = $this->db->where('country_id', current_country_id())->where('is_active', 1)->where('is_default', 1)->get($this->table)->row();
		if (!$row) {
			$row = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('id', 'ASC')->get($this->table)->row();
		}
		return $row;
	}

	/**
	 * Establece una bodega como predeterminada (transaccional).
	 *
	 * @param int $id
	 * @return bool
	 */
	public function set_default($id)
	{
		$this->db->trans_start();
		$this->db->where('is_default', 1)->update($this->table, array('is_default' => 0));
		$this->db->where('id', $id)->update($this->table, array('is_default' => 1));
		$this->db->trans_complete();
		return $this->db->trans_status();
	}
}
