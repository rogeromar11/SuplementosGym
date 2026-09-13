<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store extends MY_Controller
{
	public function index()
	{
		$country = current_store_country();
		$counts = $this->Product_model->category_counts($country->id);
		$total = array_sum($counts);
		$featured = $total > 0 ? $this->Product_model->featured($country->id, 8) : array();

		$this->render_store('store/home', array(
			'featured'        => $featured,
			'category_counts' => $counts,
			'total_products'  => $total,
		), array(
			'title'       => 'SG Tienda · Suplementos deportivos',
			'description' => 'Proteina, creatina, preentrenos y mas. Suplementos originales con entrega en ' . $country->name . '.',
		));
	}

	public function products()
	{
		$country = current_store_country();
		$filters = array(
			'search'         => $this->input->get('q', TRUE),
			'category'       => $this->input->get('categoria', TRUE),
			'laboratory'     => $this->input->get('laboratorio', TRUE),
			'min_price'      => $this->input->get('min', TRUE),
			'max_price'      => $this->input->get('max', TRUE),
			'available_only' => (bool) $this->input->get('disponible'),
			'sort'           => $this->input->get('orden', TRUE) ?: 'relevance',
		);

		$products = $this->Product_model->catalog($country->id, $filters);
		$counts = $this->Product_model->category_counts($country->id);

		$this->render_store('store/products', array(
			'products'       => $products,
			'filters'        => $filters,
			'laboratories'   => $this->Product_model->laboratories($country->id),
			'price_range'    => $this->Product_model->price_range($country->id),
			'categories'     => store_categories(),
			'category_counts' => $counts,
			'country_total'  => array_sum($counts),
		), array(
			'title'       => 'Productos · SG Tienda',
			'description' => 'Catalogo de suplementos disponibles en ' . $country->name . '.',
		));
	}

	public function product($id = 0)
	{
		$country = current_store_country();
		$product = $this->Product_model->find_active((int) $id, $country->id);

		if ( ! $product)
		{
			show_404();
		}

		$all = $this->Product_model->catalog($country->id, array('category' => store_category($product->product_type)));
		$related = array();
		foreach ($all as $item)
		{
			if ((int) $item->id !== (int) $product->id)
			{
				$related[] = $item;
			}
			if (count($related) >= 4)
			{
				break;
			}
		}

		$this->render_store('store/product', array(
			'product' => $product,
			'related' => $related,
		), array(
			'title'       => $product->name . ' · SG Tienda',
			'description' => store_product_short_description($product, 150) ?: ('Compra ' . $product->name . ' en SG Tienda.'),
			'og_image'    => store_product_image($product),
			'canonical'   => store_product_url($product),
		));
	}

	public function set_country()
	{
		if ($this->input->method() !== 'post')
		{
			redirect('/');
		}

		$id = (int) $this->input->post('country_id');
		$country = $this->Country_model->find($id);

		if ( ! $country || ! (int) $country->is_active)
		{
			return $this->json_response(array(
				'success' => FALSE,
				'message' => 'Pais no valido.',
			), 422);
		}

		$different = current_store_country_id() !== $id;
		$cart_has_items = ! $this->store_cart->is_empty();
		$confirm = (bool) $this->input->post('confirm_cart');

		if ($different && $cart_has_items && ! $confirm)
		{
			return $this->json_response(array(
				'success'              => FALSE,
				'require_confirmation' => TRUE,
				'message'              => 'Al cambiar de pais, los productos actuales del carrito podrian dejar de estar disponibles. ¿Deseas continuar?',
			));
		}

		if ($different)
		{
			$this->store_cart->clear();
		}

		$this->session->set_userdata('store_country_id', $id);

		return $this->json_response(array(
			'success'  => TRUE,
			'message'  => 'Pais actualizado.',
			'redirect' => base_url('productos'),
		));
	}

	public function about()
	{
		$this->render_store('store/about', array(), array(
			'title'       => 'Sobre nosotros · SG Tienda',
			'description' => 'Conoce SG Tienda: productos originales, atencion personalizada y entrega nacional.',
		));
	}

	public function guide()
	{
		$this->render_store('store/guide', array(
			'guides' => store_supplement_guides(),
			'goals'  => store_goals(),
		), array(
			'title'       => 'Guia de suplementos · SG Tienda',
			'description' => 'Aprende que hace cada suplemento, sus beneficios y como elegirlo segun tu objetivo.',
		));
	}

	public function payments()
	{
		$country = current_store_country();
		$methods = $this->db->where('country_id', $country->id)
			->where('is_active', 1)
			->order_by('sort_order', 'ASC')
			->get('payment_methods')->result();

		$this->render_store('store/payments', array(
			'methods' => $methods,
		), array(
			'title'       => 'Formas de pago · SG Tienda',
			'description' => 'Metodos de pago disponibles en ' . $country->name . '.',
		));
	}

	public function contact()
	{
		$country = current_store_country();
		$this->render_store('store/contact', array(), array(
			'title'       => 'Contacto · SG Tienda',
			'description' => 'Escribenos por WhatsApp o correo. Atencion en ' . $country->name . '.',
		));
	}
}
