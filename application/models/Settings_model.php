<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model
{
	/**
	 * Devuelve la configuracion del pais actual como arreglo clave => valor.
	 *
	 * @return array
	 */
	public function all_key_value()
	{
		$countryId = current_country_id();
		if (!$countryId) {
			return array();
		}
		$rows = $this->db->where('country_id', $countryId)->get('system_settings')->result();
		$out = array();
		foreach ($rows as $row) {
			$out[$row->key] = $row->value;
		}
		return $out;
	}

	/**
	 * Guarda o actualiza una clave de configuracion del pais actual.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function set($key, $value)
	{
		$countryId = current_country_id();
		if (!$countryId) {
			return false;
		}
		$exists = $this->db->where('country_id', $countryId)->where('key', $key)->count_all_results('system_settings');
		if ($exists) {
			return $this->db->where('country_id', $countryId)->where('key', $key)->update('system_settings', array('value' => $value));
		}
		return $this->db->insert('system_settings', array('country_id' => $countryId, 'key' => $key, 'value' => $value));
	}

	/**
	 * Devuelve la configuracion de la tienda web del pais actual (clave => valor).
	 * Estas claves viven en store_settings y las consume el storefront
	 * (WhatsApp, correo de contacto, redes sociales, envio...).
	 *
	 * @return array
	 */
	public function store_all_key_value()
	{
		$countryId = current_country_id();
		if (!$countryId) {
			return array();
		}
		$rows = $this->db->where('country_id', $countryId)->get('store_settings')->result();
		$out = array();
		foreach ($rows as $row) {
			$out[$row->key] = $row->value;
		}
		return $out;
	}

	/**
	 * Guarda o actualiza una clave de configuracion de la tienda del pais actual.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function set_store($key, $value)
	{
		$countryId = current_country_id();
		if (!$countryId) {
			return false;
		}
		$exists = $this->db->where('country_id', $countryId)->where('key', $key)->count_all_results('store_settings');
		if ($exists) {
			return $this->db->where('country_id', $countryId)->where('key', $key)->update('store_settings', array('value' => $value));
		}
		return $this->db->insert('store_settings', array('country_id' => $countryId, 'key' => $key, 'value' => $value));
	}
}
