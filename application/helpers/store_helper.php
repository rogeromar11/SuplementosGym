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
			'proteinas'        => 'Proteinas',
			'creatinas'        => 'Creatinas',
			'preentrenos'      => 'Preentrenos',
			'aminos'           => 'Aminos',
			'quemadores'       => 'Quemadores de Grasa',
			'ganadores'        => 'Ganadores de Peso',
			'multivitaminicos' => 'Multivitaminicos',
			'otros'            => 'Otros',
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
			'ganadores'        => array('mass gainer', 'gainer', 'ganador'),
			'proteinas'        => array('proteina', 'protein', 'whey', 'iso 100', 'caseina'),
			'creatinas'        => array('creatina', 'creatine'),
			'aminos'           => array('bcaa', 'eaa', 'amino'),
			'multivitaminicos' => array('multivit', 'vitamina', 'vitamin'),
			'quemadores'       => array('quemador', 'cla', 'lipo', 'fat burn'),
			'preentrenos'      => array('pre - entreno', 'pre-entreno', 'preentreno', 'pre workout', 'preworkout'),
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
	function store_whatsapp_number($country_id = NULL)
	{
		$number = preg_replace('/[^0-9]/', '', (string) store_setting('whatsapp_number', '', $country_id));
		if ($number === '')
		{
			return '';
		}
		return $number;
	}
}

if ( ! function_exists('store_whatsapp_url'))
{
	function store_whatsapp_url($message = '', $country_id = NULL)
	{
		$number = store_whatsapp_number($country_id);
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

if ( ! function_exists('store_cart_whatsapp_message'))
{
	function store_cart_whatsapp_message($lines, $subtotal, $shipping, $total, $country = NULL, $customer = array())
	{
		$country = $country ?: current_store_country();
		$msg = 'Hola, quiero finalizar esta compra' . ($country ? ' (' . $country->name . ')' : '') . ':' . "\n\n";
		foreach ($lines as $line)
		{
			$msg .= '- ' . (int) $line['quantity'] . 'x ' . $line['name'];
			if ( ! empty($line['laboratory']))
			{
				$msg .= ' (' . $line['laboratory'] . ')';
			}
			if ( ! empty($line['flavor']))
			{
				$msg .= ' - Sabor: ' . $line['flavor'];
			}
			$msg .= ' - ' . store_price($line['line_total'], $country) . "\n";
		}
		$msg .= "\nSubtotal: " . store_price($subtotal, $country);
		$msg .= "\nEnvio: " . store_price($shipping, $country);
		$msg .= "\nTotal: " . store_price($total, $country);
		if ( ! empty($customer['nombre']))
		{
			$msg .= "\n\nNombre: " . $customer['nombre'];
		}
		if ( ! empty($customer['telefono']))
		{
			$msg .= "\nTelefono: " . $customer['telefono'];
		}
		if ( ! empty($customer['zona']))
		{
			$msg .= "\nZona: " . $customer['zona'];
		}
		if ( ! empty($customer['direccion']))
		{
			$msg .= "\nDireccion: " . $customer['direccion'];
		}
		return $msg;
	}
}

if ( ! function_exists('store_cart_whatsapp_url'))
{
	function store_cart_whatsapp_url($lines, $subtotal, $shipping, $total, $country = NULL, $customer = array())
	{
		$message = store_cart_whatsapp_message($lines, $subtotal, $shipping, $total, $country, $customer);
		$number = store_whatsapp_number($country ? $country->id : NULL);
		if ($number === '')
		{
			return '';
		}
		return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
	}
}

if ( ! function_exists('store_social_links'))
{
	function store_social_links($country_id = NULL)
	{
		$defs = array(
			'instagram' => array('label' => 'Instagram', 'icon' => 'bi-instagram'),
			'facebook'  => array('label' => 'Facebook', 'icon' => 'bi-facebook'),
			'tiktok'    => array('label' => 'TikTok', 'icon' => 'bi-tiktok'),
		);
		$links = array();
		foreach ($defs as $key => $def)
		{
			$url = store_setting($key . '_url', '', $country_id);
			if (trim((string) $url) !== '')
			{
				$links[] = array('key' => $key, 'label' => $def['label'], 'icon' => $def['icon'], 'url' => $url);
			}
		}
		return $links;
	}
}

if ( ! function_exists('store_product_image'))
{
	function store_product_image($product)
	{
		if ($product && ! empty($product->image))
		{
			return base_url('assets/img/products/' . rawurlencode($product->image));
		}
		return base_url('assets/img/product-placeholder.svg');
	}
}

if ( ! function_exists('store_product_webp'))
{
	function store_product_webp($product)
	{
		if ( ! $product || empty($product->image))
		{
			return '';
		}
		$webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $product->image);
		if ($webp === $product->image)
		{
			return '';
		}
		$path = FCPATH . 'assets/img/products/' . $webp;
		if ( ! file_exists($path))
		{
			return '';
		}
		return base_url('assets/img/products/' . rawurlencode($webp));
	}
}

if ( ! function_exists('store_products_url'))
{
	function store_products_url($filters, $extra = array())
	{
		$params = array();
		if ( ! empty($filters['search'])) { $params['q'] = $filters['search']; }
		if ( ! empty($filters['category'])) { $params['categoria'] = $filters['category']; }
		if ( ! empty($filters['laboratory'])) { $params['laboratorio'] = $filters['laboratory']; }
		if (isset($filters['min_price']) && $filters['min_price'] !== '') { $params['min'] = $filters['min_price']; }
		if (isset($filters['max_price']) && $filters['max_price'] !== '') { $params['max'] = $filters['max_price']; }
		if ( ! empty($filters['available_only'])) { $params['disponible'] = 1; }
		if ( ! empty($filters['sort']) && $filters['sort'] !== 'relevance') { $params['orden'] = $filters['sort']; }
		if ( ! empty($filters['per_page']) && (int) $filters['per_page'] !== 25) { $params['por_pagina'] = (int) $filters['per_page']; }
		$params = array_merge($params, $extra);
		$qs = http_build_query($params);
		return base_url('productos' . ($qs ? '?' . $qs : ''));
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
			'aminos' => array(
				'name'        => 'Aminos (BCAA / EAA)',
				'icon'        => 'bi-droplet',
				'tagline'     => 'Aminoacidos esenciales',
				'description' => 'Los aminoacidos ayudan a la recuperacion muscular y pueden apoyar la resistencia durante el entrenamiento.',
				'benefit'     => 'Recuperacion y resistencia.',
				'usage'       => 'Durante o despues del entrenamiento, segun el producto.',
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
			'multivitaminicos' => array(
				'name'        => 'Multivitaminicos',
				'icon'        => 'bi-capsule-pill',
				'tagline'     => 'Salud y bienestar diario',
				'description' => 'Aportan vitaminas y minerales para cubrir tus necesidades diarias y apoyar tu rendimiento y salud general.',
				'benefit'     => 'Cobertura de micronutrientes.',
				'usage'       => 'Una dosis diaria, preferiblemente con una comida.',
			),
			'otros' => array(
				'name'        => 'Otros',
				'icon'        => 'bi-capsule',
				'tagline'     => 'Omega-3, magnesio y mas',
				'description' => 'Complementos para necesidades especificas como omega-3, magnesio, ashwagandha y otros.',
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
