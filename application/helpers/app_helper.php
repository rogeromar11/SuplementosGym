<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('app_setting')) {
	/**
	 * Valor de una configuracion del sistema del pais actual.
	 *
	 * @param string $key
	 * @param string|null $default
	 * @return string|null
	 */
	function app_setting($key, $default = null)
	{
		$ci =& get_instance();
		if (isset($ci->settings) && array_key_exists($key, $ci->settings)) {
			return $ci->settings[$key];
		}
		return $default;
	}
}

if (!function_exists('current_country')) {
	/**
	 * País activo de la sesión (objeto) o null.
	 *
	 * @return object|null
	 */
	function current_country()
	{
		$ci =& get_instance();
		if (isset($ci->currentCountry)) {
			return $ci->currentCountry;
		}
		$data = $ci->session->userdata('sgms_country');
		return $data ? (object)$data : null;
	}
}

if (!function_exists('current_country_id')) {
	/**
	 * Id del pais activo de la sesion.
	 *
	 * @return int|null
	 */
	function current_country_id()
	{
		$country = current_country();
		return $country ? (int)$country->id : null;
	}
}

if (!function_exists('current_path_query')) {
	/**
	 * Ruta relativa actual (con query string) respecto a la base de la app.
	 * Ej.: 'orders?status=entregado&date_from=2026-08-13'
	 *
	 * @return string
	 */
	function current_path_query()
	{
		$ci =& get_instance();
		$uri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
		$base = $ci->config->item('base_url');
		$basePath = $base ? (string)parse_url($base, PHP_URL_PATH) : '';
		if ($basePath !== '' && strpos($uri, $basePath) === 0) {
			$uri = substr($uri, strlen($basePath));
		}
		return ltrim($uri, '/');
	}
}

if (!function_exists('back_query_string')) {
	/**
	 * Cadena '?back=...' para propagar el destino de regreso actual en un enlace,
	 * o cadena vacia si no hay destino de regreso.
	 * Tambien considera el campo POST 'back' (formularios envian POST sin query string).
	 *
	 * @return string
	 */
	function back_query_string()
	{
		$back = isset($_GET['back']) ? trim((string)$_GET['back']) : '';
		if ($back === '' && isset($_POST['back'])) {
			$back = trim((string)$_POST['back']);
		}
		return $back !== '' ? '?back=' . urlencode($back) : '';
	}
}

if (!function_exists('back_url')) {
	/**
	 * URL de regreso contextual: usa el parametro GET 'back' si apunta a una
	 * ruta interna valida; si no, devuelve la URL por defecto indicada.
	 *
	 * @param string $default Ruta por defecto (ej.: 'orders')
	 * @return string URL absoluta
	 */
	function back_url($default)
	{
		$back = isset($_GET['back']) ? trim((string)$_GET['back']) : '';
		$valid = $back !== ''
			&& mb_strlen($back) <= 300
			&& preg_match('#^[A-Za-z0-9_\-][A-Za-z0-9/_\-?=&%.]*$#', $back) === 1
			&& strpos($back, '//') === false
			&& strpos($back, '..') === false;
		return base_url($valid ? $back : $default);
	}
}

if (!function_exists('asset_url')) {
	/**
	 * URL de un asset con versionado por fecha de modificacion
	 * para evitar cache del navegador al cambiar archivos.
	 *
	 * @param string $path Ej.: 'assets/js/app.js'
	 * @return string
	 */
	function asset_url($path)
	{
		$file = FCPATH . $path;
		$v = file_exists($file) ? (int)filemtime($file) : 0;
		return base_url($path) . '?v=' . $v;
	}
}

if (!function_exists('money')) {
	/**
	 * Formatea un monto con el simbolo de la moneda configurada.
	 *
	 * @param float $amount
	 * @return string
	 */
	function money($amount)
	{
		$symbol = app_setting('currency_symbol', '₡');
		return $symbol . ' ' . number_format((float)$amount, 2, '.', ',');
	}
}

if (!function_exists('product_display_name')) {
	/**
	 * Nombre legible de una variante de producto.
	 *
	 * @param object|array $product
	 * @return string
	 */
	function product_display_name($product)
	{
		$get = function ($key) use ($product) {
			if (is_array($product)) {
				return isset($product[$key]) ? trim((string)$product[$key]) : '';
			}
			return isset($product->{$key}) ? trim((string)$product->{$key}) : '';
		};

		$name = $get('name');
		if ($name === '') {
			$name = $get('product_type');
		}
		$details = array_filter(array($get('laboratory'), $get('weight'), $get('flavor')), 'strlen');
		return $name . (!empty($details) ? ' — ' . implode(' · ', $details) : '');
	}
}

if (!function_exists('fmt_date')) {
	/**
	 * Formatea fecha interna (yyyy-mm-dd) a dd/mm/yyyy.
	 *
	 * @param string|null $date
	 * @param string $empty
	 * @return string
	 */
	function fmt_date($date, $empty = '—')
	{
		if (empty($date) || $date === '0000-00-00') {
			return $empty;
		}
		$ts = strtotime($date);
		return $ts ? date('d/m/Y', $ts) : $empty;
	}
}

if (!function_exists('fmt_datetime')) {
	/**
	 * Formatea datetime a dd/mm/yyyy HH:MM.
	 *
	 * @param string|null $datetime
	 * @return string
	 */
	function fmt_datetime($datetime)
	{
		if (empty($datetime)) {
			return '—';
		}
		$ts = strtotime($datetime);
		return $ts ? date('d/m/Y H:i', $ts) : '—';
	}
}

if (!function_exists('order_status_label')) {
	/**
	 * Etiqueta en espanol del estado de un pedido.
	 *
	 * @param string $status
	 * @return string
	 */
	function order_status_label($status)
	{
		$map = array(
			'registrado' => 'Registrado',
			'pendiente_preparacion' => 'Pendiente de preparación',
			'en_preparacion' => 'En preparación',
			'preparado' => 'Preparado',
			'asignado_ruta' => 'Asignado a ruta',
			'en_ruta' => 'En ruta',
			'entregado' => 'Entregado',
			'no_entregado' => 'No entregado',
			'reprogramado' => 'Reprogramado',
			'cancelado' => 'Cancelado',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}
}

if (!function_exists('order_status_badge')) {
	/**
	 * HTML de badge Bootstrap para el estado de un pedido.
	 *
	 * @param string $status
	 * @return string
	 */
function order_status_badge($status)
{
	// Preparado usa morado para no confundirse con el verde de Entregado
	if ($status === 'preparado') {
		return '<span class="badge" style="background:#7C3AED;color:#FFFFFF;">' . html_escape(order_status_label($status)) . '</span>';
	}
	$colors = array(
		'registrado' => 'secondary',
		'confirmado' => 'info',
		'pendiente_preparacion' => 'warning',
		'en_preparacion' => 'info',
		'preparado' => 'success',
		'asignado_ruta' => 'primary',
		'en_ruta' => 'primary',
		'entregado' => 'success',
		'no_entregado' => 'danger',
		'reprogramado' => 'warning',
		'cancelado' => 'dark',
	);
	$color = isset($colors[$status]) ? $colors[$status] : 'secondary';
	return '<span class="badge text-bg-' . $color . '">' . html_escape(order_status_label($status)) . '</span>';
}
}

if (!function_exists('route_status_label')) {
	/**
	 * Etiqueta en espanol del estado de una ruta.
	 *
	 * @param string $status
	 * @return string
	 */
	function route_status_label($status)
	{
		$map = array(
			'borrador' => 'Borrador',
			'planificada' => 'Planificada',
			'en_progreso' => 'En progreso',
			'finalizada' => 'Finalizada',
			'cancelada' => 'Cancelada',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}
}

if (!function_exists('route_status_badge')) {
	/**
	 * HTML de badge Bootstrap para el estado de una ruta.
	 *
	 * @param string $status
	 * @return string
	 */
	function route_status_badge($status)
	{
		$colors = array(
			'borrador' => 'secondary',
			'planificada' => 'info',
			'en_progreso' => 'primary',
			'finalizada' => 'success',
			'cancelada' => 'danger',
		);
		$color = isset($colors[$status]) ? $colors[$status] : 'secondary';
		return '<span class="badge text-bg-' . $color . '">' . html_escape(route_status_label($status)) . '</span>';
	}
}

if (!function_exists('delivery_status_label')) {
	/**
	 * Etiqueta en espanol del estado de una entrega dentro de ruta.
	 *
	 * @param string $status
	 * @return string
	 */
	function delivery_status_label($status)
	{
		$map = array(
			'pendiente' => 'Pendiente',
			'en_proceso' => 'En proceso',
			'entregado' => 'Entregado',
			'no_entregado' => 'No entregado',
			'reprogramado' => 'Reprogramado',
		);
		return isset($map[$status]) ? $map[$status] : $status;
	}
}

if (!function_exists('delivery_status_badge')) {
	/**
	 * HTML de badge Bootstrap para el estado de una entrega.
	 *
	 * @param string $status
	 * @return string
	 */
	function delivery_status_badge($status)
	{
		$colors = array(
			'pendiente' => 'secondary',
			'en_proceso' => 'info',
			'entregado' => 'success',
			'no_entregado' => 'danger',
			'reprogramado' => 'warning',
		);
		$color = isset($colors[$status]) ? $colors[$status] : 'secondary';
		return '<span class="badge text-bg-' . $color . '">' . html_escape(delivery_status_label($status)) . '</span>';
	}
}
