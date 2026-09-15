<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	/**
	 * Todos los usuarios con sus grupos concatenados.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->db->select('u.*, GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ",") AS group_names')
			->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id', 'left')
			->join('groups g', 'g.id = ug.group_id', 'left')
			->where('u.country_id', current_country_id())
			->group_by('u.id')
			->order_by('u.created_on', 'DESC')
			->get()->result();
	}

	/**
	 * Usuario con grupos.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function find_with_groups($id)
	{
		$user = $this->db->where('id', $id)->where('country_id', current_country_id())->get('users')->row();
		if (!$user) {
			return null;
		}
		$user->groups = $this->db->select('g.*')->from('users_groups ug')
			->join('groups g', 'g.id = ug.group_id')
			->where('ug.user_id', $id)->get()->result();
		return $user;
	}

	/**
	 * Todos los grupos.
	 *
	 * @return array
	 */
	public function all_groups()
	{
		return $this->db->order_by('id', 'ASC')->get('groups')->result();
	}

	/**
	 * Usuarios que pertenecen a un grupo (por nombre).
	 *
	 * @param string $groupName
	 * @return array
	 */
	public function by_group($groupName)
	{
		return $this->db->select('u.*')->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')
			->join('groups g', 'g.id = ug.group_id')
			->where('g.name', $groupName)
			->where('u.country_id', current_country_id())
			->where('u.active', 1)
			->order_by('u.first_name', 'ASC')
			->get()->result();
	}
}
