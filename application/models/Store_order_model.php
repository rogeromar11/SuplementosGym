<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_order_model extends CI_Model
{
	protected $table = 'store_orders';
	protected $items = 'store_order_items';

	public function next_number($country_id)
	{
		$country = $this->db->where('id', (int) $country_id)->get('countries')->row();
		$code = $country ? $country->code : 'XX';
		$prefix = 'SG-' . $code . '-' . date('Ym') . '-';
		$row = $this->db->select('order_number')
			->like('order_number', $prefix, 'after')
			->order_by('order_number', 'DESC')
			->limit(1)
			->get($this->table)->row();
		$sequence = 1;
		if ($row)
		{
			$parts = explode('-', $row->order_number);
			$sequence = ((int) end($parts)) + 1;
		}
		return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
	}

	public function create($data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function add_items($order_id, $lines)
	{
		foreach ($lines as $line)
		{
			$this->db->insert($this->items, array(
				'order_id'   => (int) $order_id,
				'product_id' => (int) $line['product_id'],
				'item_sku'   => $line['sku'],
				'item_name'  => $line['name'],
				'unit_price' => $line['unit_price'],
				'quantity'   => (int) $line['quantity'],
				'line_total' => $line['line_total'],
			));
		}
	}

	public function for_user($user_id)
	{
		return $this->db->select('o.*, c.code AS country_code, c.currency AS country_currency, c.currency_symbol, pm.name AS payment_method_name, pm.code AS payment_method_code')
			->from($this->table . ' o')
			->join('countries c', 'c.id = o.country_id', 'left')
			->join('payment_methods pm', 'pm.id = o.payment_method_id', 'left')
			->where('o.user_id', (int) $user_id)
			->order_by('o.created_at', 'DESC')
			->get()->result();
	}

	public function find_for_user($order_id, $user_id)
	{
		$order = $this->db->select('o.*, c.code AS country_code, c.currency AS country_currency, c.currency_symbol, pm.name AS payment_method_name, pm.code AS payment_method_code')
			->from($this->table . ' o')
			->join('countries c', 'c.id = o.country_id', 'left')
			->join('payment_methods pm', 'pm.id = o.payment_method_id', 'left')
			->where('o.id', (int) $order_id)
			->where('o.user_id', (int) $user_id)
			->get()->row();
		if ( ! $order)
		{
			return NULL;
		}
		$order->items = $this->db->where('order_id', $order->id)->order_by('id', 'ASC')->get($this->items)->result();
		return $order;
	}

	public function mark_inventory_applied($order_id)
	{
		return $this->db->where('id', (int) $order_id)->update($this->table, array('inventory_applied' => 1));
	}
}
