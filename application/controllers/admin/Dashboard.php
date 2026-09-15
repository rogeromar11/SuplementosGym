<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard principal diferenciado por rol.
 */
class Dashboard extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Order_model');
		$this->load->model('Route_model');

		// El mensajero tiene su propio dashboard movil
		if ($this->ion_auth->in_group('mensajero') && !$this->ion_auth->is_admin()) {
			redirect('courier');
		}
	}

	public function index()
	{
		$this->require_permission('dashboard.ver');

		$this->data['pageTitle'] = 'Dashboard';
		$this->data['breadcrumbs'] = array(array('label' => 'Dashboard'));

		$today = date('Y-m-d');
		$user = $this->currentUser;
		$groups = array_column((array)$this->currentGroups, 'name');

		$stats = array(
			'orders_today' => $this->Order_model->count_by_status(null, $today),
			'pending_prep' => $this->Order_model->count_by_status('pendiente_preparacion') + $this->Order_model->count_by_status('confirmado'),
			'prepared' => $this->Order_model->count_prepared_today($today),
			'preparing' => $this->Order_model->count_by_status('en_preparacion'),
			'in_route' => $this->Order_model->count_by_status('en_ruta'),
			'delivered_today' => $this->Order_model->count_by_status('entregado', $today),
			'not_delivered_today' => $this->Order_model->count_by_status('no_entregado', $today),
		);

		$totals = $this->Order_model->today_totals($today);

		// Visitas a la tienda web de hoy (visitantes únicos y vistas de página)
		$visits = (object) array('visitors' => 0, 'pageviews' => 0);
		if ($this->db->table_exists('store_visits')) {
			$row = $this->db->select('COUNT(*) AS pageviews, COUNT(DISTINCT session_id) AS visitors')
				->where('visit_date', $today)
				->where('country_id', current_country_id())
				->get('store_visits')->row();
			if ($row) {
				$visits = $row;
			}
		}

		// Visitas de los últimos 7 días (visitantes únicos y vistas por día)
		$visitsWeek = array();
		if ($this->db->table_exists('store_visits')) {
			$from = date('Y-m-d', strtotime('-6 days'));
			$rows = $this->db->select('visit_date, COUNT(*) AS pageviews, COUNT(DISTINCT session_id) AS visitors')
				->where('country_id', current_country_id())
				->where('visit_date >=', $from)
				->group_by('visit_date')
				->order_by('visit_date', 'ASC')
				->get('store_visits')->result();

			$byDate = array();
			foreach ($rows as $r) {
				$byDate[$r->visit_date] = $r;
			}
			for ($i = 6; $i >= 0; $i--) {
				$day = date('Y-m-d', strtotime('-' . $i . ' days'));
				$visitsWeek[] = array(
					'label'     => date('d/m', strtotime($day)),
					'visitors'  => isset($byDate[$day]) ? (int) $byDate[$day]->visitors : 0,
					'pageviews' => isset($byDate[$day]) ? (int) $byDate[$day]->pageviews : 0,
				);
			}
		}

		$this->data['stats'] = $stats;
		$this->data['totals'] = $totals;
		$this->data['visits'] = $visits;
		$this->data['visitsWeek'] = $visitsWeek;
		$this->data['today'] = $today;

		// Rutas de hoy
		$this->data['todayRoutes'] = $this->Route_model->search(array('route_date' => $today));

		// Ultimos pedidos visibles segun permiso
		$f = array();
		if (in_array('vendedor', $groups, true) && !$this->has_permission('pedidos.ver_todos')) {
			$f['seller_user_id'] = $user->id;
		}
		$this->data['recentOrders'] = $this->Order_model->search($f, 8);

		$this->data['isCourierUser'] = in_array('mensajero', $groups, true);
		$this->data['pageScripts'] = array('assets/js/pages/dashboard.js');

		$this->render('dashboard/index', $this->data);
	}
}
