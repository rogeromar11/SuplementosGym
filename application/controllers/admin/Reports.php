<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reportes operativos y financieros con exportacion a PDF y Excel.
 */
class Reports extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Report_model');
		$this->load->model('User_model');
	}

	private function filters()
	{
		return array(
			'date_from' => $this->input->get('date_from') ?: null,
			'date_to' => $this->input->get('date_to') ?: null,
			'courier_user_id' => $this->input->get('courier_user_id') ?: null,
			'warehouse_id' => $this->input->get('warehouse_id') ?: null,
			'shift_id' => $this->input->get('shift_id') ?: null,
			'route_status' => $this->input->get('route_status') ?: null,
		);
	}

	private function default_filters()
	{
		return array(
			'date_from' => date('Y-m-d', strtotime('-7 days')),
			'date_to' => date('Y-m-d'),
			'courier_user_id' => null,
			'warehouse_id' => null,
			'shift_id' => null,
			'route_status' => null,
		);
	}

	private function filters_from_input()
	{
		$f = $this->filters();
		if (!$f['date_from']) {
			$f['date_from'] = date('Y-m-d', strtotime('-7 days'));
		}
		if (!$f['date_to']) {
			$f['date_to'] = date('Y-m-d');
		}
		return $f;
	}

	/**
	 * Estados canonicos de pedido para el filtro.
	 *
	 * @return array
	 */
	private function order_statuses()
	{
		return array(
			'registrado' => 'Registrado',
			'confirmado' => 'Confirmado',
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
	}

	/**
	 * Filtros del reporte de pedidos (incluye rango de fechas por defecto).
	 *
	 * @return array
	 */
	private function orders_filters_from_input()
	{
		$f = $this->filters_from_input();
		$f['status'] = $this->input->get('status') ?: null;
		$f['origin'] = $this->input->get('origin') ?: null;
		$f['seller_user_id'] = $this->input->get('seller_user_id') ?: null;
		$f['payment_method_id'] = $this->input->get('payment_method_id') ?: null;
		$f['search'] = $this->input->get('search') ?: null;
		return $f;
	}

	public function index()
	{
		$this->require_permission('reportes.ver');

		$f = $this->filters_from_input();
		$this->data['pageTitle'] = 'Reporte general';
		$this->data['breadcrumbs'] = array(array('label' => 'Reportes'));
		$this->data['filters'] = $f;
		$this->data['rows'] = $this->Report_model->general($f);
		$this->data['paymentBreakdown'] = $this->Report_model->payment_breakdown($f);
		$this->data['couriers'] = $this->User_model->by_group('mensajero');
		$this->data['warehouses'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('name')->get('warehouses')->result();
		$this->data['shifts'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('route_shifts')->result();
		$this->data['activeTab'] = 'general';

		$this->render('reports/index', $this->data);
	}

	public function couriers()
	{
		$this->require_permission('reportes.ver');

		$f = $this->filters_from_input();
		$this->data['pageTitle'] = 'Reporte de mensajeros';
		$this->data['breadcrumbs'] = array(array('label' => 'Reportes'), array('label' => 'Mensajeros'));
		$this->data['filters'] = $f;
		$this->data['rows'] = $this->Report_model->courier_summary($f);
		$this->data['couriers'] = $this->User_model->by_group('mensajero');
		$this->data['warehouses'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('name')->get('warehouses')->result();
		$this->data['shifts'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('route_shifts')->result();
		$this->data['activeTab'] = 'couriers';

		$this->render('reports/couriers', $this->data);
	}

	public function products()
	{
		$this->require_permission('reportes.ver');

		$f = $this->filters_from_input();
		$this->data['pageTitle'] = 'Reporte de productos';
		$this->data['breadcrumbs'] = array(array('label' => 'Reportes'), array('label' => 'Productos'));
		$this->data['filters'] = $f;
		$this->data['rows'] = $this->Report_model->product_sales($f);
		$this->data['warehouses'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('name')->get('warehouses')->result();
		$this->data['activeTab'] = 'products';

		$this->render('reports/products', $this->data);
	}

	public function deposits()
	{
		$this->require_permission('reportes.ver');

		$f = $this->filters_from_input();
		if ($this->input->get('status')) {
			$f['status'] = $this->input->get('status');
		}
		$this->data['pageTitle'] = 'Reporte de depósitos';
		$this->data['breadcrumbs'] = array(array('label' => 'Reportes'), array('label' => 'Depósitos'));
		$this->data['filters'] = $f;
		$this->data['rows'] = $this->Report_model->deposits($f);
		$this->data['couriers'] = $this->User_model->by_group('mensajero');
		$this->data['activeTab'] = 'deposits';

		$this->render('reports/deposits', $this->data);
	}

	public function sellers()
	{
		$this->require_permission('reportes.ver');

		$f = $this->filters_from_input();
		$this->data['pageTitle'] = 'Reporte de vendedores';
		$this->data['breadcrumbs'] = array(array('label' => 'Reportes'), array('label' => 'Vendedores'));
		$this->data['filters'] = $f;
		$this->data['daily'] = $this->Report_model->sellers_daily($f);
		$summary = $this->Report_model->sellers_summary($f);

		// Participacion de cada vendedor sobre el total del periodo
		// (por monto vendido y por cantidad de ventas)
		$grandTotal = 0.0;
		$grandVentas = 0;
		foreach ($summary as $row) {
			$grandTotal += (float)$row->total_vendido;
			$grandVentas += (int)$row->ventas;
		}
		foreach ($summary as $row) {
			$row->share_pct = $grandTotal > 0 ? round(((float)$row->total_vendido / $grandTotal) * 100, 1) : 0;
			$row->share_ventas_pct = $grandVentas > 0 ? round(((int)$row->ventas / $grandVentas) * 100, 1) : 0;
		}
		$this->data['summary'] = $summary;
		$this->data['grandTotalVendido'] = $grandTotal;
		$this->data['grandVentas'] = $grandVentas;
		$this->data['sellers'] = $this->User_model->by_group('vendedor');
		$this->data['activeTab'] = 'sellers';

		$this->render('reports/sellers', $this->data);
	}

	public function export_sellers_excel()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		$daily = $this->Report_model->sellers_daily($f);
		$summary = $this->Report_model->sellers_summary($f);

		// Totales del periodo para los porcentajes por vendedor
		$grandTotal = 0.0;
		$grandVentas = 0;
		foreach ($summary as $row) {
			$grandTotal += (float)$row->total_vendido;
			$grandVentas += (int)$row->ventas;
		}

		$summaryRows = array();
		foreach ($summary as $row) {
			$avg = (int)$row->ventas > 0 ? round((float)$row->total_vendido / (int)$row->ventas, 2) : 0;
			$summaryRows[] = array(
				'seller_name' => $row->seller_name,
				'ventas' => (int)$row->ventas,
				'share_ventas_pct' => $grandVentas > 0 ? round(((int)$row->ventas / $grandVentas) * 100, 1) . '%' : '0%',
				'total_vendido' => round((float)$row->total_vendido, 2),
				'share_pct' => $grandTotal > 0 ? round(((float)$row->total_vendido / $grandTotal) * 100, 1) . '%' : '0%',
				'ticket_promedio' => $avg,
				'total_cobrado' => round((float)$row->total_cobrado, 2),
				'pendiente_cobro' => round((float)$row->pendiente_cobro, 2),
				'entregados' => (int)$row->entregados,
				'cancelados' => (int)$row->cancelados,
			);
		}

		// Porcentajes de cada vendedor dentro de su propio dia
		$dayTotals = array();
		foreach ($daily as $d) {
			$key = $d->sale_date;
			if (!isset($dayTotals[$key])) {
				$dayTotals[$key] = array('ventas' => 0, 'monto' => 0.0);
			}
			$dayTotals[$key]['ventas'] += (int)$d->ventas;
			$dayTotals[$key]['monto'] += (float)$d->total_vendido;
		}

		$dailyRows = array();
		foreach ($daily as $d) {
			$day = $dayTotals[$d->sale_date];
			$dailyRows[] = array(
				'sale_date' => fmt_date($d->sale_date),
				'seller_name' => $d->seller_name,
				'ventas' => (int)$d->ventas,
				'share_ventas_pct' => $day['ventas'] > 0 ? round(((int)$d->ventas / $day['ventas']) * 100, 1) . '%' : '0%',
				'total_vendido' => round((float)$d->total_vendido, 2),
				'share_pct' => $day['monto'] > 0 ? round(((float)$d->total_vendido / $day['monto']) * 100, 1) . '%' : '0%',
				'ticket_promedio' => (int)$d->ventas > 0 ? round((float)$d->total_vendido / (int)$d->ventas, 2) : 0,
				'total_cobrado' => round((float)$d->total_cobrado, 2),
				'pendiente_cobro' => round((float)$d->pendiente_cobro, 2),
				'entregados' => (int)$d->entregados,
				'cancelados' => (int)$d->cancelados,
			);
		}

		$headers = array(
			'seller_name' => 'Vendedor',
			'ventas' => 'Ventas',
			'share_ventas_pct' => '% de Ventas',
			'total_vendido' => 'Total vendido',
			'share_pct' => '% del Monto',
			'ticket_promedio' => 'Ticket promedio',
			'total_cobrado' => 'Cobrado',
			'pendiente_cobro' => 'Pendiente de cobro',
			'entregados' => 'Entregados',
			'cancelados' => 'Cancelados',
		);
		$dailyHeaders = array(
			'sale_date' => 'Fecha',
			'seller_name' => 'Vendedor',
			'ventas' => 'Ventas',
			'share_ventas_pct' => '% de Ventas (dia)',
			'total_vendido' => 'Total vendido',
			'share_pct' => '% del Monto (dia)',
			'ticket_promedio' => 'Ticket promedio',
			'total_cobrado' => 'Cobrado',
			'pendiente_cobro' => 'Pendiente de cobro',
			'entregados' => 'Entregados',
			'cancelados' => 'Cancelados',
		);

		$this->load->library('Excel_service');
		$this->excel_service->export('reporte-vendedores-' . date('Ymd') . '.xlsx', $headers, $summaryRows, array(
			'title' => 'Reporte de ventas por vendedor',
			'sheet_name' => 'Resumen por Vendedor',
			'summary' => array(
				'Desde' => fmt_date($f['date_from']),
				'Hasta' => fmt_date($f['date_to']),
				'Generado' => date('d/m/Y H:i'),
			),
			'extra_sheets' => array(
				array(
					'name' => 'Detalle por Dia',
					'headers' => $dailyHeaders,
					'rows' => $dailyRows,
				),
			),
		));
	}

	public function export_sellers_pdf()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		$daily = $this->Report_model->sellers_daily($f);
		$summary = $this->Report_model->sellers_summary($f);

		$this->load->library('Pdf_service');
		$html = $this->load->view('reports/pdf_sellers', array(
			'daily' => $daily,
			'summary' => $summary,
			'filters' => $f,
			'settings' => $this->settings,
		), true);

		$this->pdf_service->render($html, 'reporte-vendedores-' . date('Ymd') . '.pdf', array('orientation' => 'landscape'));
	}

	public function export_deposits_excel()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		if ($this->input->get('status')) {
			$f['status'] = $this->input->get('status');
		}
		$rows = $this->Report_model->deposits($f);
		$statusLabels = array(
			'pendiente' => 'Pendiente de confirmar',
			'recibido' => 'Recibido',
			'entregado' => 'Entregado al administrador',
			'aprobado' => 'Aprobado',
		);
		$headers = array(
			'id' => 'Depósito',
			'created_at' => 'Fecha',
			'courier_name' => 'Mensajero',
			'receiver_name' => 'Recibió',
			'admin_receiver_name' => 'Entregado a',
			'route_count' => 'Rutas',
			'amount' => 'Monto',
			'status_label' => 'Estado',
			'confirmed_by_name' => 'Visto bueno',
		);

		$data = array();
		foreach ($rows as $row) {
			$row->status_label = isset($statusLabels[$row->status]) ? $statusLabels[$row->status] : $row->status;
			$item = array();
			foreach ($headers as $key => $label) {
				if ($key === 'created_at' || $key === 'courier_receipt_at' || $key === 'confirmed_at') {
					$item[$key] = fmt_datetime($row->{$key});
				} elseif ($key === 'confirmed_by_name') {
					$item[$key] = $row->confirmed_at ? $row->confirmed_by_name . ' (' . fmt_datetime($row->confirmed_at) . ')' : 'Pendiente';
				} else {
					$item[$key] = $row->{$key};
				}
			}
			$data[] = $item;
		}

		$this->load->library('Excel_service');
		$this->excel_service->export('reporte-depositos-' . date('Ymd') . '.xlsx', $headers, $data, array(
			'title' => 'Reporte de depósitos de efectivo',
			'summary' => array(
				'Desde' => fmt_date($f['date_from']),
				'Hasta' => fmt_date($f['date_to']),
				'Generado' => date('d/m/Y H:i'),
			),
		));
	}

	public function export_deposits_pdf()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		if ($this->input->get('status')) {
			$f['status'] = $this->input->get('status');
		}
		$rows = $this->Report_model->deposits($f);

		$this->load->library('Pdf_service');
		$html = $this->load->view('reports/pdf_deposits', array(
			'rows' => $rows,
			'filters' => $f,
			'settings' => $this->settings,
		), true);

		$this->pdf_service->render($html, 'reporte-depositos-' . date('Ymd') . '.pdf', array('orientation' => 'landscape'));
	}

	public function export_general_pdf()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		$rows = $this->Report_model->general($f);
		$breakdown = $this->Report_model->payment_breakdown($f);

		$this->load->library('Pdf_service');
		$html = $this->load->view('reports/pdf_general', array(
			'rows' => $rows,
			'breakdown' => $breakdown,
			'filters' => $f,
			'settings' => $this->settings,
		), true);

		$this->pdf_service->render($html, 'reporte-general-' . date('Ymd') . '.pdf', array('orientation' => 'landscape'));
	}

	public function export_general_excel()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		$rows = $this->Report_model->general($f);

		$this->load->library('Excel_service');
		$headers = array(
			'route_date' => 'Fecha',
			'shift_name' => 'Turno',
			'warehouse_name' => 'Bodega',
			'courier_name' => 'Mensajero',
			'total_orders' => 'Pedidos',
			'delivered' => 'Entregados',
			'not_delivered' => 'No entregados',
			'pending' => 'Pendientes',
			'expected_amount' => 'Esperado',
			'collected_amount' => 'Cobrado',
			'pending_amount' => 'Pendiente',
		);

		$data = array();
		foreach ($rows as $row) {
			$item = array();
			foreach ($headers as $key => $label) {
				$item[$key] = $row->{$key};
			}
			$data[] = $item;
		}

		$this->excel_service->export('reporte-general-' . date('Ymd') . '.xlsx', $headers, $data, array(
			'title' => 'Reporte general de rutas',
			'summary' => array(
				'Desde' => fmt_date($f['date_from']),
				'Hasta' => fmt_date($f['date_to']),
				'Generado' => date('d/m/Y H:i'),
			),
		));
	}

	public function export_couriers_pdf()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		$rows = $this->Report_model->courier_summary($f);

		$this->load->library('Pdf_service');
		$html = $this->load->view('reports/pdf_couriers', array(
			'rows' => $rows,
			'filters' => $f,
			'settings' => $this->settings,
		), true);

		$this->pdf_service->render($html, 'reporte-mensajeros-' . date('Ymd') . '.pdf', array('orientation' => 'landscape'));
	}

	public function export_couriers_excel()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		$rows = $this->Report_model->courier_summary($f);

		$this->load->library('Excel_service');
		$headers = array(
			'courier_name' => 'Mensajero',
			'routes' => 'Rutas',
			'total_orders' => 'Pedidos',
			'delivered' => 'Entregados',
			'not_delivered' => 'No entregados',
			'success_rate' => '% Exito',
			'expected_amount' => 'Esperado',
			'collected_amount' => 'Cobrado',
			'pending_amount' => 'Pendiente',
		);

		$data = array();
		foreach ($rows as $row) {
			$item = array();
			foreach ($headers as $key => $label) {
				$item[$key] = ($key === 'success_rate') ? round((float)$row->{$key}, 1) : $row->{$key};
			}
			$data[] = $item;
		}

		$this->excel_service->export('reporte-mensajeros-' . date('Ymd') . '.xlsx', $headers, $data, array(
			'title' => 'Reporte de mensajeros',
			'summary' => array(
				'Desde' => fmt_date($f['date_from']),
				'Hasta' => fmt_date($f['date_to']),
				'Generado' => date('d/m/Y H:i'),
			),
		));
	}

	public function export_products_excel()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->filters_from_input();
		$rows = $this->Report_model->product_sales($f);
		$headers = array(
			'sale_date' => 'Fecha',
			'sku' => 'SKU',
			'item_name' => 'Producto',
			'quantity_sold' => 'Cantidad vendida',
			'total_sold' => 'Total vendido',
			'stock_qty' => 'Existencias actuales',
		);

		$data = array();
		foreach ($rows as $row) {
			$item = array();
			foreach ($headers as $key => $label) {
				$item[$key] = $row->{$key};
			}
			$data[] = $item;
		}

		$this->load->library('Excel_service');
		$this->excel_service->export('reporte-productos-' . date('Ymd') . '.xlsx', $headers, $data, array(
			'title' => 'Reporte de productos vendidos',
			'summary' => array(
				'Desde' => fmt_date($f['date_from']),
				'Hasta' => fmt_date($f['date_to']),
				'Generado' => date('d/m/Y H:i'),
			),
		));
	}

	public function orders()
	{
		$this->require_permission('reportes.ver');

		$f = $this->orders_filters_from_input();

		$per_page = (int) $this->input->get('per_page');
		if (!in_array($per_page, array(50, 200, 500), true)) {
			$per_page = 50;
		}
		$page = max(1, (int) $this->input->get('page'));
		$total = $this->Report_model->orders_count($f);
		$pages = max(1, (int) ceil($total / $per_page));
		if ($page > $pages) {
			$page = $pages;
		}

		$this->data['pageTitle'] = 'Reporte de pedidos';
		$this->data['breadcrumbs'] = array(array('label' => 'Reportes'), array('label' => 'Pedidos'));
		$this->data['filters'] = $f;
		$this->data['rows'] = $this->Report_model->orders($f, $per_page, ($page - 1) * $per_page);
		$this->data['totals'] = $this->Report_model->orders_totals($f);
		$this->data['per_page'] = $per_page;
		$this->data['page'] = $page;
		$this->data['pages'] = $pages;
		$this->data['total'] = $total;
		$this->data['statuses'] = $this->order_statuses();
		$this->data['paymentMethods'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('sort_order')->get('payment_methods')->result();
		$this->data['warehouses'] = $this->db->where('country_id', current_country_id())->where('is_active', 1)->order_by('name')->get('warehouses')->result();
		$this->data['sellers'] = $this->User_model->by_group('vendedor');
		$this->data['activeTab'] = 'orders';

		$this->render('reports/orders', $this->data);
	}

	public function export_orders_pdf()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->orders_filters_from_input();
		$rows = $this->Report_model->orders($f);

		$this->load->library('Pdf_service');
		$html = $this->load->view('reports/pdf_orders', array(
			'rows' => $rows,
			'filters' => $f,
			'statuses' => $this->order_statuses(),
			'settings' => $this->settings,
		), true);

		$this->pdf_service->render($html, 'reporte-pedidos-' . date('Ymd') . '.pdf', array('orientation' => 'landscape'));
	}

	public function export_orders_excel()
	{
		$this->require_permission('reportes.exportar');
		$f = $this->orders_filters_from_input();
		$rows = $this->Report_model->orders($f);

		$headers = array(
			'order_number' => 'Pedido',
			'created_at' => 'Fecha',
			'origin' => 'Origen',
			'customer_name' => 'Cliente',
			'customer_phone' => 'Teléfono',
			'delivery_zone' => 'Zona',
			'warehouse_name' => 'Bodega',
			'seller_name' => 'Vendedor',
			'payment_name' => 'Forma de pago',
			'status' => 'Estado',
			'total' => 'Total',
			'paid_amount' => 'Pagado',
			'balance_amount' => 'Saldo',
		);

		$data = array();
		foreach ($rows as $row) {
			$data[] = array(
				'order_number' => $row->order_number,
				'created_at' => fmt_datetime($row->created_at),
				'origin' => $row->origin === 'web' ? 'Web' : 'Manual',
				'customer_name' => $row->customer_name,
				'customer_phone' => $row->customer_phone,
				'delivery_zone' => $row->delivery_zone,
				'warehouse_name' => $row->warehouse_name,
				'seller_name' => $row->seller_name,
				'payment_name' => $row->payment_name,
				'status' => order_status_label($row->status),
				'total' => round((float)$row->total, 2),
				'paid_amount' => round((float)$row->paid_amount, 2),
				'balance_amount' => round((float)$row->balance_amount, 2),
			);
		}

		$this->load->library('Excel_service');
		$this->excel_service->export('reporte-pedidos-' . date('Ymd') . '.xlsx', $headers, $data, array(
			'title' => 'Reporte de pedidos',
			'summary' => array(
				'Desde' => fmt_date($f['date_from']),
				'Hasta' => fmt_date($f['date_to']),
				'Generado' => date('d/m/Y H:i'),
			),
		));
	}
}
