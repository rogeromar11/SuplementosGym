<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo base con utilidades comunes para tablas del sistema.
 */
class MY_Model extends CI_Model
{
	/**
	 * @var string Nombre de la tabla.
	 */
	protected $table = '';

	/**
	 * @var string Clave primaria.
	 */
	protected $primaryKey = 'id';

	/**
	 * @var bool Indica si la tabla usa baja logica con columna is_active.
	 */
	protected $softDelete = true;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string Nombre de la tabla.
	 */
	public function table()
	{
		return $this->table;
	}

	/**
	 * Obtiene un registro por su clave primaria.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function find($id)
	{
		return $this->db->where($this->primaryKey, $id)->get($this->table)->row();
	}

	/**
	 * Aplica el alcance por pais a las consultas cuando la tabla lo soporta.
	 */
	protected function apply_country_scope()
	{
		if ($this->db->field_exists('country_id', $this->table)) {
			$this->db->where($this->table . '.country_id', current_country_id());
		}
	}

	/**
	 * Lista registros activos (respetando baja logica).
	 *
	 * @param array $where
	 * @param string $orderBy
	 * @param string $orderDir
	 * @return array
	 */
	public function all($where = array(), $orderBy = null, $orderDir = 'ASC')
	{
		if ($this->softDelete && isset($this->db->dbdriver)) {
			$this->db->where('is_active', 1);
		}
		$this->apply_country_scope();
		if (!empty($where)) {
			$this->db->where($where);
		}
		if ($orderBy !== null) {
			$this->db->order_by($orderBy, $orderDir);
		}
		return $this->db->get($this->table)->result();
	}

	/**
	 * Inserta un registro y devuelve su id.
	 *
	 * @param array $data
	 * @return int
	 */
	public function insert($data)
	{
		// Las tablas con alcance por país heredan el país activo de la sesión.
		if (!isset($data['country_id']) && $this->db->field_exists('country_id', $this->table)) {
			$country_id = current_country_id();
			if ($country_id) {
				$data['country_id'] = $country_id;
			}
		}
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
	 * Actualiza registros.
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function update($id, $data)
	{
		return $this->db->where($this->primaryKey, $id)->update($this->table, $data);
	}

	/**
	 * Actualiza registros por condicion personalizada.
	 *
	 * @param array $where
	 * @param array $data
	 * @return bool
	 */
	public function update_where($where, $data)
	{
		return $this->db->where($where)->update($this->table, $data);
	}

	/**
	 * Eliminacion logica (is_active = 0). Devuelve false si la tabla no lo soporta.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete($id)
	{
		if ($this->softDelete && $this->db->field_exists('is_active', $this->table)) {
			return $this->db->where($this->primaryKey, $id)->update($this->table, array('is_active' => 0));
		}
		return $this->db->where($this->primaryKey, $id)->delete($this->table);
	}

	/**
	 * Conteo de registros con condicion opcional.
	 *
	 * @param array $where
	 * @return int
	 */
	public function count($where = array())
	{
		$this->apply_country_scope();
		if (!empty($where)) {
			$this->db->where($where);
		}
		return $this->db->count_all_results($this->table);
	}
}
