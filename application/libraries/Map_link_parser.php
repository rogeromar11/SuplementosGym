<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Servicio de mapas y enlaces externos.
 * Reconoce coordenadas en enlaces de Google Maps y Waze y genera
 * enlaces de navegacion, WhatsApp y llamadas.
 */
class Map_link_parser
{
	/**
	 * Extrae coordenadas [lat, lng] de un enlace de mapa cuando es posible.
	 *
	 * @param string|null $url
	 * @return array|null
	 */
	public function parse_coordinates($url)
	{
		if (empty($url)) {
			return null;
		}

		$coords = null;

		// Google Maps: .../@9.9281,-84.0907,17z  o  !3d9.9281!4d-84.0907
		if (preg_match('/!3d(-?\d{1,3}(?:\.\d+)?)!4d(-?\d{1,3}(?:\.\d+)?)/', $url, $m)) {
			$coords = array((float)$m[1], (float)$m[2]);
		} elseif (preg_match('/(?:@|\/maps\/search\/[^\/]*\/|maps\/place\/[^\/]*\/)\s*(-?\d{1,3}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)/i', $url, $m)) {
			$coords = array((float)$m[1], (float)$m[2]);
		}

		// Parametros comunes: q, query, ll, destination, location
		if ($coords === null && preg_match('/[?&](?:q|query|ll|destination|location)=([^&]*)/i', $url, $m)) {
			$value = urldecode($m[1]);
			$value = str_replace(array('%2C', ','), ',', $value);
			if (preg_match('/^(-?\d{1,3}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)$/', trim($value), $cm)) {
				$coords = array((float)$cm[1], (float)$cm[2]);
			}
		}

		return $coords;
	}

	/**
	 * Geocodifica una direccion de entrega usando Nominatim (OpenStreetMap).
	 * Devuelve coordenadas aproximadas [lat, lng] o null si no se pudo ubicar.
	 *
	 * @param string|null $address
	 * @param string|null $countryCode Codigo ISO-3166-1 alpha-2 (ej.: 'CR', 'SV') para acotar la busqueda.
	 * @return array|null
	 */
	public function geocode_address($address, $countryCode = null)
	{
		$address = trim((string)$address);
		if ($address === '' || !function_exists('curl_init')) {
			return null;
		}

		$query = array(
			'format' => 'json',
			'q' => $address,
			'limit' => 1,
			'addressdetails' => 0,
		);
		if ($countryCode) {
			$query['countrycodes'] = strtolower($countryCode);
		}

		$url = 'https://nominatim.openstreetmap.org/search?' . http_build_query($query);
		$referer = isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '';

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'User-Agent: SG Tienda/1.0 (geocodificacion de pedidos; soporte: administracion@suplementosgym.local)',
				'Referer: ' . $referer,
				'Accept: application/json',
			),
			CURLOPT_TIMEOUT => 10,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 2,
			CURLOPT_SSL_VERIFYPEER => true,
		));
		$body = curl_exec($ch);
		$errno = curl_errno($ch);
		curl_close($ch);

		if ($errno !== 0 || !$body) {
			return null;
		}

		$results = json_decode($body, true);
		if (!is_array($results) || empty($results[0]) || !isset($results[0]['lat'], $results[0]['lon'])) {
			return null;
		}

		$lat = (float)$results[0]['lat'];
		$lng = (float)$results[0]['lon'];
		if (abs($lat) > 90 || abs($lng) > 180) {
			return null;
		}

		return array($lat, $lng);
	}

	/**
	 * Enlace de Google Maps por coordenadas o direccion.
	 *
	 * @param float|null $lat
	 * @param float|null $lng
	 * @param string|null $address
	 * @return string
	 */
	public function google_maps_link($lat, $lng, $address = null)
	{
		if ($lat !== null && $lng !== null) {
			return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($lat . ',' . $lng);
		}
		return 'https://www.google.com/maps/search/?api=1&query=' . urlencode((string)$address);
	}

	/**
	 * Enlace de Waze por coordenadas o direccion.
	 *
	 * @param float|null $lat
	 * @param float|null $lng
	 * @param string|null $address
	 * @return string
	 */
	public function waze_link($lat, $lng, $address = null)
	{
		if ($lat !== null && $lng !== null) {
			return 'https://waze.com/ul?ll=' . $lat . ',' . $lng . '&navigate=yes';
		}
		return 'https://waze.com/ul?q=' . urlencode((string)$address);
	}

	/**
	 * Enlace de WhatsApp con numero normalizado y mensaje opcional.
	 *
	 * @param string $phone Números solamente (ej.: 50688881111)
	 * @param string|null $message
	 * @return string
	 */
	public function whatsapp_link($phone, $message = null)
	{
		$phone = preg_replace('/\D+/', '', (string)$phone);
		$url = 'https://wa.me/' . $phone;
		if ($message !== null && $message !== '') {
			$url .= '?text=' . rawurlencode($message);
		}
		return $url;
	}

	/**
	 * Enlace tel: para llamada directa.
	 *
	 * @param string $phone
	 * @return string
	 */
	public function tel_link($phone)
	{
		$phone = preg_replace('/\D+/', '', (string)$phone);
		return 'tel:+' . $phone;
	}

	/**
	 * Normaliza un telefono a intl y whatsapp.
	 * Si el número inicia con "+" ya incluye el código de país y se usa tal cual
	 * (permite números extranjeros). En caso contrario, si son 8 digitos y no hay
	 * código de país, se asume el código por defecto.
	 *
	 * @param string $phone
	 * @param string $countryCode
	 * @return array ['intl' => '+506...', 'whatsapp' => '506...']
	 */
	public function normalize_phone($phone, $countryCode = '506')
	{
		$digits = preg_replace('/\D+/', '', (string)$phone);

		if (strlen($digits) === 8 && strpos(ltrim((string)$phone), '+') !== 0) {
			$digits = $countryCode . $digits;
		}

		return array(
			'intl' => '+' . $digits,
			'whatsapp' => $digits,
		);
	}
}
