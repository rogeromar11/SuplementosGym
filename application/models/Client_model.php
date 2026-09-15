<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends MY_Model
{
	protected $table = 'clients';
	protected $primaryKey = 'id';
	protected $softDelete = true;

	public function find_for_country($id)
	{
		return $this->db->where('id', $id)
			->where('country_id', current_country_id())
			->get($this->table)->row();
	}

	public function existing_by_codes($codes)
	{
		if (empty($codes)) {
			return array();
		}

		$rows = $this->db->select('id, code, notes')
			->where('country_id', current_country_id())
			->where_in('code', array_values(array_unique($codes)))
			->get($this->table)->result();
		$result = array();
		foreach ($rows as $row) {
			$result[mb_strtoupper($row->code, 'UTF-8')] = $row;
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
	 * Elimina físicamente un cliente del país actual.
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

	public function code_exists($code, $ignoreId = null)
	{
		$this->db->where('country_id', current_country_id())->where('code', $code);
		if ($ignoreId !== null) {
			$this->db->where('id !=', (int)$ignoreId);
		}
		return $this->db->count_all_results($this->table) > 0;
	}

	/**
	 * Genera el siguiente código de cliente para un país (CLI-0001).
	 *
	 * @param int|null $countryId
	 * @return string
	 */
	public function next_code($countryId = null)
	{
		$countryId = $countryId ?: current_country_id();
		$prefix = 'CLI-';
		$row = $this->db->select('code')
			->where('country_id', $countryId)
			->like('code', $prefix, 'after')
			->order_by('code', 'DESC')
			->limit(1)
			->get($this->table)->row();
		$seq = 1;
		if ($row) {
			$seq = (int) substr($row->code, strlen($prefix)) + 1;
		}
		return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
	}

	/**
	 * Cliente vinculado a un usuario registrado (o null).
	 *
	 * @param int $userId
	 * @return object|null
	 */
	public function find_by_user($userId)
	{
		return $this->db->where('user_id', (int) $userId)->get($this->table)->row();
	}

	/**
	 * Homologa un usuario registrado con su registro de cliente:
	 * lo crea si no existe o actualiza sus datos de contacto/entrega.
	 *
	 * @param int $userId
	 * @param int $countryId
	 * @param array $data name, email, phone, phone2, zone, address, delivery_type
	 * @param int|null $actorId
	 * @return int id del cliente
	 */
	public function find_or_create_for_user($userId, $countryId, $data, $actorId = null)
	{
		$client = $this->find_by_user($userId);
		$data['country_id'] = $countryId;
		$data['user_id'] = (int) $userId;
		$data['updated_by'] = $actorId;
		$data['updated_at'] = date('Y-m-d H:i:s');

		if ($client) {
			$this->db->where('id', $client->id)->update($this->table, $data);
			return (int) $client->id;
		}

		if (empty($data['code'])) {
			$data['code'] = $this->next_code($countryId);
		}
		$data['created_by'] = $actorId;
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $data);
		return (int) $this->db->insert_id();
	}

	/**
	 * Busca (por teléfono o nombre) o crea un cliente manual sin cuenta.
	 *
	 * @param int $countryId
	 * @param array $data name, email, phone, phone2, zone, address, delivery_type
	 * @param int|null $actorId
	 * @return int id del cliente
	 */
	public function find_or_create_manual($countryId, $data, $actorId = null)
	{
		if ( ! empty($data['phone'])) {
			$client = $this->db->where('country_id', $countryId)
				->where('phone', $data['phone'])
				->get($this->table)->row();
			if ($client) {
				return (int) $client->id;
			}
		}
		if ( ! empty($data['name'])) {
			$client = $this->db->where('country_id', $countryId)
				->where('name', $data['name'])
				->get($this->table)->row();
			if ($client) {
				return (int) $client->id;
			}
		}

		if (empty($data['client_type'])) {
			$data['client_type'] = 'normal';
		}
		$data['country_id'] = $countryId;
		if (empty($data['code'])) {
			$data['code'] = $this->next_code($countryId);
		}
		$data['created_by'] = $actorId;
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $data);
		return (int) $this->db->insert_id();
	}
}
