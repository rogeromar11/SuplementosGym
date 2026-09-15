<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador para usuarios autenticados del backoffice.
 */
class Authenticated_Controller extends SG_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_login();
	}
}
