<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
	protected $table = 'products';

	public function active_by_country($country_id)
	{
		return $this->db->where('country_id', (int) $country_id)
			->where('is_active', 1)
			->order_by('name', 'ASC')
			->get($this->table)->result();
	}

	public function find($id, $country_id = NULL)
	{
		$this->db->where('id', (int) $id);
		if ($country_id !== NULL)
		{
			$this->db->where('country_id', (int) $country_id);
		}
		return $this->db->get($this->table)->row();
	}

	public function find_active($id, $country_id)
	{
		return $this->db->where('id', (int) $id)
			->where('country_id', (int) $country_id)
			->where('is_active', 1)
			->get($this->table)->row();
	}

	public function featured($country_id, $limit = 4)
	{
		return $this->db->where('country_id', (int) $country_id)
			->where('is_active', 1)
			->order_by('featured', 'DESC')
			->order_by('sort_order', 'ASC')
			->order_by('name', 'ASC')
			->limit((int) $limit)
			->get($this->table)->result();
	}

	public function laboratories($country_id)
	{
		$rows = $this->db->select('laboratory')
			->distinct()
			->where('country_id', (int) $country_id)
			->where('is_active', 1)
			->where('laboratory IS NOT NULL', NULL, FALSE)
			->where('laboratory !=', '')
			->order_by('laboratory', 'ASC')
			->get($this->table)->result();
		$labs = array();
		foreach ($rows as $row)
		{
			$labs[] = $row->laboratory;
		}
		return $labs;
	}

	public function price_range($country_id)
	{
		$row = $this->db->select('MIN(unit_price) AS min_price, MAX(unit_price) AS max_price')
			->where('country_id', (int) $country_id)
			->where('is_active', 1)
			->get($this->table)->row();
		return array(
			'min' => $row ? (float) $row->min_price : 0,
			'max' => $row ? (float) $row->max_price : 0,
		);
	}

	public function catalog($country_id, $filters = array())
	{
		$products = $this->active_by_country($country_id);

		$search = isset($filters['search']) ? mb_strtolower(trim((string) $filters['search']), 'UTF-8') : '';
		$category = isset($filters['category']) ? (string) $filters['category'] : '';
		$laboratory = isset($filters['laboratory']) ? (string) $filters['laboratory'] : '';
		$min = isset($filters['min_price']) && $filters['min_price'] !== '' ? (float) $filters['min_price'] : NULL;
		$max = isset($filters['max_price']) && $filters['max_price'] !== '' ? (float) $filters['max_price'] : NULL;
		$available_only = ! empty($filters['available_only']);

		$result = array();
		foreach ($products as $product)
		{
			if ($category !== '' && store_category($product->product_type) !== $category)
			{
				continue;
			}
			if ($laboratory !== '' && strcasecmp((string) $product->laboratory, $laboratory) !== 0)
			{
				continue;
			}
			$price = (float) $product->unit_price;
			if ($min !== NULL && $price < $min)
			{
				continue;
			}
			if ($max !== NULL && $price > $max)
			{
				continue;
			}
			if ($available_only && ! store_product_available($product))
			{
				continue;
			}
			if ($search !== '')
			{
				$haystack = mb_strtolower(implode(' ', array(
					$product->name,
					$product->laboratory,
					$product->product_type,
					$product->flavor,
					$product->sku,
				)), 'UTF-8');
				if (strpos($haystack, $search) === FALSE)
				{
					continue;
				}
			}
			$result[] = $product;
		}

		$sort = isset($filters['sort']) ? $filters['sort'] : 'relevance';
		usort($result, function ($a, $b) use ($sort) {
			switch ($sort)
			{
				case 'name':
					return strcasecmp($a->name, $b->name);
				case 'price_asc':
					return ((float) $a->unit_price <=> (float) $b->unit_price) ?: strcasecmp($a->name, $b->name);
				case 'price_desc':
					return ((float) $b->unit_price <=> (float) $a->unit_price) ?: strcasecmp($a->name, $b->name);
				default:
					$cmp = strcasecmp($a->product_type, $b->product_type);
					return $cmp !== 0 ? $cmp : strcasecmp($a->name, $b->name);
			}
		});

		return $result;
	}

	public function category_counts($country_id)
	{
		$counts = array_fill_keys(array_keys(store_categories()), 0);
		foreach ($this->active_by_country($country_id) as $product)
		{
			$counts[store_category($product->product_type)]++;
		}
		return $counts;
	}
}
