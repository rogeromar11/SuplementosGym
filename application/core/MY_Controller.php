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
