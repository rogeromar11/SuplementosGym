<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador para administradores (grupo admin).
 */
class Admin_Controller extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (!$this->ion_auth->is_admin()) {
			$this->deny_access();
		}
	}
}
