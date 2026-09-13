<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Country_model extends CI_Model
{
	protected $table = 'countries';

	public function all_active()
	{
		return $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get($this->table)->result();
	}

	public function find($id)
	{
		return $this->db->where('id', (int) $id)->get($this->table)->row();
	}

	public function find_by_code($code)
	{
		return $this->db->where('code', strtoupper((string) $code))->get($this->table)->row();
	}
}
