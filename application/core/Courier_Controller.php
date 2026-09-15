<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador para mensajeros (grupo mensajero).
 */
class Courier_Controller extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (!$this->ion_auth->in_group('mensajero')) {
			$this->deny_access();
		}
	}
}
