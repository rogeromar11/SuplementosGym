<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('store_ci'))
{
	function store_ci()
	{
		return get_instance();
	}
}

if ( ! function_exists('store_countries'))
{
	function store_countries()
	{
		static $countries = NULL;
		if ($countries !== NULL)
		{
			return $countries;
		}
		$ci = store_ci();
		$countries = $ci->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get('countries')->result();
		return $countries;
	}
}

if ( ! function_exists('store_country'))
{
	function store_country($id = NULL)
	{
		$id = ($id === NULL) ? current_store_country_id() : (int) $id;
		foreach (store_countries() as $country)
		{
			if ((int) $country->id === $id)
			{
				return $country;
			}
		}
		$countries = store_countries();
		return $countries ? $countries[0] : NULL;
	}
}

if ( ! function_exists('current_store_country_id'))
{
	function current_store_country_id()
	{
		$ci = store_ci();
		$id = (int) $ci->session->userdata('store_country_id');
		if ($id > 0)
		{
			return $id;
		}
		return 2;
	}
}

if ( ! function_exists('current_store_country'))
{
	function current_store_country()
	{
		return store_country(current_store_country_id());
	}
}

if ( ! function_exists('store_country_currency'))
{
	function store_country_currency($country = NULL)
	{
		$country = $country ?: current_store_country();
		return $country ? $country->currency_symbol : '₡';
	}
}

if ( ! function_exists('store_categories'))
{
	function store_categories()
	{
		return array(
			'proteinas'    => 'Proteinas',
			'creatinas'    => 'Creatinas',
			'quemadores'   => 'Quemadores de Grasa',
			'preentrenos'  => 'Preentrenos',
			'ganadores'    => 'Ganadores de Peso',
			'otros'        => 'Otros',
		);
	}
}

if ( ! function_exists('store_category'))
{
	function store_category($product_type)
	{
		$type = mb_strtolower(trim((string) $product_type), 'UTF-8');
		$type = str_replace(array('á', 'é', 'í', 'ó', 'ú', 'ñ'), array('a', 'e', 'i', 'o', 'u', 'n'), $type);

		$rules = array(
			'ganadores'   => array('mass gainer', 'gainer', 'ganador'),
			'proteinas'   => array('proteina', 'protein', 'whey', 'iso 100', 'caseina'),
			'creatinas'   => array('creatina', 'creatine'),
			'quemadores'  => array('quemador', 'cla', 'lipo', 'fat burn'),
			'preentrenos' => array('pre - entreno', 'pre-entreno', 'preentreno', 'pre workout', 'preworkout'),
		);

		foreach ($rules as $category => $keywords)
		{
			foreach ($keywords as $keyword)
			{
				if (strpos($type, $keyword) !== FALSE)
				{
					return $category;
				}
			}
		}
		return 'otros';
	}
}

if ( ! function_exists('store_category_label'))
{
	function store_category_label($key)
	{
		$categories = store_categories();
		return isset($categories[$key]) ? $categories[$key] : $categories['otros'];
	}
}

if ( ! function_exists('store_price'))
{
	function store_price($amount, $country = NULL)
	{
		$country = $country ?: current_store_country();
		$amount = (float) $amount;
		if ($country && $country->currency === 'USD')
		{
			return $country->currency_symbol . number_format($amount, 2, '.', ',');
		}
		return ($country ? $country->currency_symbol : '₡') . number_format($amount, 0, '.', ',');
	}
}

if ( ! function_exists('store_order_price'))
{
	function store_order_price($order, $amount)
	{
		$country = (object) array(
			'currency'        => isset($order->country_currency) ? $order->country_currency : 'CRC',
			'currency_symbol' => isset($order->currency_symbol) ? $order->currency_symbol : '₡',
		);
		return store_price($amount, $country);
	}
}

if ( ! function_exists('store_product_available'))
{
	function store_product_available($product)
	{
		if ( ! $product)
		{
			return FALSE;
		}
		if ((int) $product->stock_enabled === 0)
		{
			return TRUE;
		}
		return ((int) $product->stock_qty > 0);
	}
}

if ( ! function_exists('store_product_stock'))
{
	function store_product_stock($product)
	{
		if ( ! $product || (int) $product->stock_enabled === 0)
		{
			return NULL;
		}
		return (int) $product->stock_qty;
	}
}

if ( ! function_exists('store_setting'))
{
	function store_setting($key, $default = '', $country_id = NULL)
	{
		static $cache = array();
		$country_id = ($country_id === NULL) ? current_store_country_id() : (int) $country_id;
		if ( ! isset($cache[$country_id]))
		{
			$ci = store_ci();
			$cache[$country_id] = array();
			$rows = $ci->db->where('country_id', $country_id)->get('store_settings')->result();
			foreach ($rows as $row)
			{
				$cache[$country_id][$row->key] = $row->value;
			}
		}
		if (isset($cache[$country_id][$key]) && $cache[$country_id][$key] !== NULL && $cache[$country_id][$key] !== '')
		{
			return $cache[$country_id][$key];
		}
		return $default;
	}
}

if ( ! function_exists('store_whatsapp_number'))
{
	function store_whatsapp_number()
	{
		$number = preg_replace('/[^0-9]/', '', (string) store_setting('whatsapp_number', ''));
		if ($number === '')
		{
			return '';
		}
		return $number;
	}
}

if ( ! function_exists('store_whatsapp_url'))
{
	function store_whatsapp_url($message = '')
	{
		$number = store_whatsapp_number();
		if ($number === '')
		{
			return '';
		}
		$url = 'https://wa.me/' . $number;
		if ($message !== '')
		{
			$url .= '?text=' . rawurlencode($message);
		}
		return $url;
	}
}

if ( ! function_exists('store_product_whatsapp_url'))
{
	function store_product_whatsapp_url($product)
	{
		$message = 'Hola, estoy interesado en el producto: ' . ($product ? $product->name : '') . '.';
		return store_whatsapp_url($message);
	}
}

if ( ! function_exists('store_product_image'))
{
	function store_product_image($product)
	{
		if ($product && ! empty($product->image))
		{
			return base_url('uploads/products/' . rawurlencode($product->image));
		}
		return base_url('assets/img/product-placeholder.svg');
	}
}

if ( ! function_exists('store_product_url'))
{
	function store_product_url($product)
	{
		return base_url('producto/' . (int) $product->id);
	}
}

if ( ! function_exists('store_product_short_description'))
{
	function store_product_short_description($product, $limit = 110)
	{
		$text = trim((string) ($product->store_description ?: $product->description));
		if ($text === '')
		{
			return '';
		}
		return (mb_strlen($text, 'UTF-8') > $limit) ? mb_substr($text, 0, $limit - 1, 'UTF-8') . '…' : $text;
	}
}

if ( ! function_exists('store_flash'))
{
	function store_flash()
	{
		$ci = store_ci();
		return array(
			'error'   => $ci->session->flashdata('store_error'),
			'success' => $ci->session->flashdata('store_success'),
		);
	}
}

if ( ! function_exists('store_supplement_guides'))
{
	function store_supplement_guides()
	{
		return array(
			'proteinas' => array(
				'name'        => 'Proteinas',
				'icon'        => 'bi-cup-hot',
				'tagline'     => 'Whey, aisladas e hidrolizadas',
				'description' => 'Favorecen la sintesis proteica y ayudan a cubrir el requerimiento diario de proteina, clave para la recuperacion y el desarrollo muscular.',
				'benefit'     => 'Recuperacion y construccion muscular.',
				'usage'       => 'Ideal despues del entrenamiento o como complemento entre comidas.',
			),
			'creatinas' => array(
				'name'        => 'Creatina',
				'icon'        => 'bi-lightning-charge',
				'tagline'     => 'El suplemento mas estudiado',
				'description' => 'Mejora la potencia y el rendimiento en esfuerzos cortos e intensos, favoreciendo la fuerza y la recuperacion entre series.',
				'benefit'     => 'Fuerza, potencia y rendimiento.',
				'usage'       => 'Consumo diario constante. No requiere ciclado.',
			),
			'preentrenos' => array(
				'name'        => 'Preentrenos',
				'icon'        => 'bi-activity',
				'tagline'     => 'Energia y enfoque',
				'description' => 'Aportan energia, concentracion y resistencia para afrontar entrenamientos exigentes con mayor intensidad.',
				'benefit'     => 'Energia, enfoque y resistencia.',
				'usage'       => 'Tomar 20-30 minutos antes de entrenar.',
			),
			'quemadores' => array(
				'name'        => 'Quemadores de grasa',
				'icon'        => 'bi-fire',
				'tagline'     => 'Apoyo a la definicion',
				'description' => 'Apoyan el metabolismo y el gasto energetico como complemento de una alimentacion y rutina adecuadas.',
				'benefit'     => 'Apoyo en la etapa de definicion.',
				'usage'       => 'Segun indicacion del producto, junto a dieta y entrenamiento.',
			),
			'ganadores' => array(
				'name'        => 'Ganadores de peso',
				'icon'        => 'bi-bar-chart-fill',
				'tagline'     => 'Mass Gainers',
				'description' => 'Aportan calorias, carbohidratos y proteina para quienes buscan aumentar peso y masa muscular.',
				'benefit'     => 'Aumento de masa y peso.',
				'usage'       => 'Despues del entrenamiento o entre comidas.',
			),
			'otros' => array(
				'name'        => 'Otros',
				'icon'        => 'bi-capsule',
				'tagline'     => 'BCAA, vitaminas y mas',
				'description' => 'Complementos para necesidades especificas: aminoacidos, vitaminas, omega-3 y salud general.',
				'benefit'     => 'Apoyo integral al rendimiento.',
				'usage'       => 'Segun tu objetivo y la indicacion del producto.',
			),
		);
	}
}

if ( ! function_exists('store_goals'))
{
	function store_goals()
	{
		return array(
			array(
				'icon'        => 'bi-graph-up-arrow',
				'title'       => 'Ganar masa muscular',
				'description' => 'Necesitas un aporte suficiente de proteina y calorias para construir tejido muscular.',
				'recommended' => 'Proteinas, ganadores de peso y creatina.',
				'link'        => 'proteinas',
			),
			array(
				'icon'        => 'bi-fire',
				'title'       => 'Definir y reducir grasa',
				'description' => 'Mantener la masa magra mientras se reduce el porcentaje de grasa.',
				'recommended' => 'Quemadores de grasa y proteinas.',
				'link'        => 'quemadores',
			),
			array(
				'icon'        => 'bi-lightning-charge-fill',
				'title'       => 'Energia y rendimiento',
				'description' => 'Rendir mas en cada sesion con mayor fuerza y concentracion.',
				'recommended' => 'Preentrenos y creatina.',
				'link'        => 'preentrenos',
			),
			array(
				'icon'        => 'bi-heart-pulse',
				'title'       => 'Recuperacion',
				'description' => 'Acelerar la recuperacion muscular despues del entrenamiento.',
				'recommended' => 'Proteinas y BCAA.',
				'link'        => 'proteinas',
			),
		);
	}
}

if ( ! function_exists('store_faqs'))
{
	function store_faqs()
	{
		return array(
			array(
				'q' => '¿Los productos son originales?',
				'a' => 'Si. Trabajamos unicamente con marcas reconocidas y verificamos la procedencia de cada producto antes de ofrecerlo.',
			),
			array(
				'q' => '¿Como elijo el suplemento adecuado?',
				'a' => 'Depende de tu objetivo (masa, definicion, energia o recuperacion). Puedes guiarte por nuestra guia de suplementos o escribirnos por WhatsApp para recibir asesoria personalizada.',
			),
			array(
				'q' => '¿Realizan envios a todo el pais?',
				'a' => 'Cubrimos las principales zonas del pais activo. Al confirmar tu pedido te indicamos la informacion de entrega.',
			),
			array(
				'q' => '¿Como puedo pagar?',
				'a' => 'Segun tu pais ofrecemos efectivo, transferencia, SINPE Movil u otras opciones. Consulta la seccion de formas de pago.',
			),
			array(
				'q' => '¿Que pasa si un producto esta agotado?',
				'a' => 'Lo marcamos como "Agotado" y deshabilitamos la compra. Puedes consultarnos por WhatsApp la disponibilidad y fecha de reposicion.',
			),
			array(
				'q' => '¿Debo consultar a un profesional?',
				'a' => 'Recomendamos consultar a un medico o nutricionista antes de iniciar cualquier suplementacion, especialmente si tienes alguna condicion de salud.',
			),
		);
	}
}
