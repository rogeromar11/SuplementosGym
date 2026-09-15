<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Servicio de generacion de PDF usando Dompdf.
 */
class Pdf_service
{
	/**
	 * Renderiza un PDF y lo envia al navegador.
	 *
	 * @param string $html
	 * @param string $filename
	 * @param array $options ['orientation' => 'portrait'|'landscape', 'paper' => 'A4']
	 */
	public function render($html, $filename, $options = array())
	{
		if (function_exists('ini_set')) {
			@ini_set('memory_limit', '256M');
		}
		if (function_exists('set_time_limit')) {
			@set_time_limit(120);
		}

		$opts = new Options();
		$opts->set('isRemoteEnabled', true);
		$opts->set('isHtml5ParserEnabled', true);
		$opts->set('defaultFont', 'DejaVu Sans');

		$dompdf = new Dompdf($opts);
		$dompdf->loadHtml($html);
		$dompdf->setPaper(isset($options['paper']) ? $options['paper'] : 'A4', isset($options['orientation']) ? $options['orientation'] : 'portrait');
		$dompdf->render();
		$dompdf->stream($filename, array('Attachment' => isset($options['attachment']) ? $options['attachment'] : true));
		exit;
	}

	/**
	 * Devuelve el contenido binario del PDF (sin descargar).
	 *
	 * @param string $html
	 * @param array $options
	 * @return string
	 */
	public function output($html, $options = array())
	{
		if (function_exists('ini_set')) {
			@ini_set('memory_limit', '256M');
		}
		if (function_exists('set_time_limit')) {
			@set_time_limit(120);
		}

		$opts = new Options();
		$opts->set('isRemoteEnabled', true);
		$opts->set('isHtml5ParserEnabled', true);
		$opts->set('defaultFont', 'DejaVu Sans');

		$dompdf = new Dompdf($opts);
		$dompdf->loadHtml($html);
		$dompdf->setPaper(isset($options['paper']) ? $options['paper'] : 'A4', isset($options['orientation']) ? $options['orientation'] : 'portrait');
		$dompdf->render();
		return $dompdf->output();
	}
}
