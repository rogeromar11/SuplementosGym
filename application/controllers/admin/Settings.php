<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Configuracion del sistema.
 */
class Settings extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_permission('configuracion.editar');
	}

	public function index()
	{
		$this->data['pageTitle'] = 'Configuracion';
		$this->data['breadcrumbs'] = array(array('label' => 'Configuracion'));
		$this->data['settings'] = $this->settings;
		$this->data['storeSettings'] = $this->Settings_model->store_all_key_value();
		$this->data['pageScripts'] = array('assets/js/pages/settings.js');
		$this->render('settings/index', $this->data);
	}

	public function save()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		// --- Configuracion de la tienda web (store_settings) ---
		$storeFields = array(
			'whatsapp_number', 'contact_email', 'business_hours',
			'shipping_cost', 'free_shipping_from',
			'instagram_url', 'facebook_url', 'tiktok_url',
			'availability_low_stock', 'availability_min_stock', 'availability_show_qty',
		);
		$storeValues = array();
		foreach ($storeFields as $field) {
			$value = $this->input->post($field);
			if ($value === null) {
				continue;
			}
			$value = trim((string) $value);

			if ($field === 'contact_email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
				$this->json_response(false, 'El correo de la empresa no es válido.');
				return;
			}
			if (in_array($field, array('instagram_url', 'facebook_url', 'tiktok_url'), true) && $value !== '') {
				if (!preg_match('#^https?://#i', $value)) {
					$value = 'https://' . ltrim($value, '/');
				}
				if (!filter_var($value, FILTER_VALIDATE_URL)) {
					$this->json_response(false, 'Una de las direcciones de redes sociales no es válida.');
					return;
				}
			}
			if (in_array($field, array('shipping_cost', 'free_shipping_from'), true)) {
				$value = str_replace(',', '.', $value);
				if ($value === '') {
					$value = '0';
				}
				if (!is_numeric($value) || (float) $value < 0) {
					$this->json_response(false, 'El costo de envio debe ser un numero mayor o igual a 0.');
					return;
				}
				$value = number_format((float) $value, 2, '.', '');
			}
			$storeValues[$field] = $value;
		}
		foreach ($storeValues as $key => $value) {
			$this->Settings_model->set_store($key, $value);
		}

		// --- Configuracion interna del sistema (system_settings) ---
		$fields = array(
			'company_name', 'company_phone', 'company_email', 'company_address',
			'country_code', 'timezone', 'currency', 'currency_symbol',
			'evidence_max_size_mb', 'orders_display_time', 'low_stock_threshold',
		);
		foreach ($fields as $field) {
			$value = $this->input->post($field);
			if ($value !== null) {
				$this->Settings_model->set($field, $value);
			}
		}
		$this->audit_service->log('settings.update', 'configuracion', 'system_settings');
		$this->json_response(true, 'Configuracion guardada.');
	}
}
