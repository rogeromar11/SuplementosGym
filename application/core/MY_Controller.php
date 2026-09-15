<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador base de SG Tienda.
 * Carga las dependencias comunes y provee el render de la plantilla publica.
 */
class MY_Controller extends CI_Controller
{
	public $data = array();

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->library(array('session', 'form_validation', 'ion_auth'));
		$this->load->helper(array('url', 'form', 'store'));
		$this->load->model(array('Country_model', 'Product_model', 'Store_order_model', 'Inventory_model'));
		$this->load->library('store_cart');

		$logged_in = $this->ion_auth->logged_in();

		$this->data['countries'] = store_countries();
		$this->data['current_country'] = current_store_country();
		$this->data['store_logged_in'] = $logged_in;
		$this->data['store_user'] = $logged_in ? $this->ion_auth->user()->row() : NULL;
		$this->data['cart_count'] = $this->store_cart->count();
		$this->data['whatsapp_url'] = store_whatsapp_url('Hola, quiero hacer un pedido.');
		$flash = store_flash();
		$this->data['flash_error'] = $flash['error'];
		$this->data['flash_success'] = $flash['success'];

		$this->record_store_visit();
	}

	/**
	 * Registra una visita a la tienda web (una fila por vista de página GET).
	 * Los visitantes únicos se calculan por session_id distinto en una fecha.
	 */
	protected function record_store_visit()
	{
		if ($this->input->method() !== 'get' || $this->input->is_ajax_request())
		{
			return;
		}
		if ( ! $this->db->table_exists('store_visits'))
		{
			return;
		}

		$vid = $this->session->userdata('store_visit_id');
		if ( ! $vid)
		{
			$vid = bin2hex(random_bytes(16));
			$this->session->set_userdata('store_visit_id', $vid);
		}

		$country = current_store_country();
		$user = $this->ion_auth->logged_in() ? $this->ion_auth->user()->row() : NULL;

		$this->db->insert('store_visits', array(
			'country_id' => $country ? (int) $country->id : NULL,
			'user_id'    => $user ? (int) $user->id : NULL,
			'session_id' => $vid,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
			'path'       => substr((string) $this->uri->uri_string(), 0, 255),
			'visit_date' => date('Y-m-d'),
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	protected function render_store($view, $data = array(), $meta = array())
	{
		$data = array_merge($this->data, $data);

		$data['meta'] = array_merge(array(
			'title'       => 'SG Tienda · Suplementos deportivos',
			'description' => 'Suplementos deportivos originales para fuerza, rendimiento y recuperacion.',
			'canonical'   => current_url(),
			'robots'      => 'index,follow',
			'og_image'    => base_url('assets/img/logo.png'),
		), $meta);

		$this->load->view('store/partials/header', $data);
		$this->load->view($view, $data);
		$this->load->view('store/partials/footer', $data);
	}

	protected function json_response($payload, $status = 200)
	{
		$this->output
			->set_status_header($status)
			->set_content_type('application/json')
			->set_output(json_encode($payload));
		return;
	}

	protected function require_login()
	{
		if ( ! $this->ion_auth->logged_in())
		{
			$this->session->set_flashdata('store_error', 'Para completar tu compra necesitas iniciar sesion o crear una cuenta.');
			$return = $this->input->get('return') ?: current_url();
			redirect('ingresar?return=' . rawurlencode($return));
		}
	}
}

// Bases del backoffice: se cargan aquí porque CI3 solo autocarga MY_Controller.
// Las clases base adicionales (SG_Controller, Authenticated_Controller,
// Admin_Controller, Courier_Controller) deben existir antes de instanciar
// los controladores de application/controllers/admin/.
require_once __DIR__ . '/SG_Controller.php';
