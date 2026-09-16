<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SEO: sitemap.xml generado dinamicamente.
 *
 * Se sirve via ruta (no es un archivo fisico) para no hardcodear el dominio:
 *   /sitemap.xml -> Sitemap::index
 *
 * robots.txt es un archivo estatico en la raiz del proyecto (ver DEPLOYMENT.md),
 * porque la plantilla nginx de Hestia resuelve /robots.txt como archivo fisico.
 */
class Sitemap extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Product_model');
	}

	public function index()
	{
		$pages = array(
			''               => '1.0',
			'productos'      => '0.9',
			'nosotros'       => '0.6',
			'guia'           => '0.6',
			'calculadora'    => '0.5',
			'formas-de-pago' => '0.5',
			'contacto'       => '0.5',
		);

		$xml = $this->load->view('store/sitemap', array(
			'pages'    => $pages,
			'products' => $this->Product_model->for_sitemap(),
		), TRUE);

		$this->output
			->set_content_type('application/xml', 'utf-8')
			->set_output($xml);
	}
}
