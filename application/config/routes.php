<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes with
| underscores in the controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'store';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/* SG Tienda */
$route['productos'] = 'store/products';
$route['producto/(:num)'] = 'store/product/$1';
$route['nosotros'] = 'store/about';
$route['guia'] = 'store/guide';
$route['calculadora'] = 'store/macros';
$route['formas-de-pago'] = 'store/payments';
$route['contacto'] = 'store/contact';
$route['pais'] = 'store/set_country';

$route['carrito'] = 'cart/index';
$route['carrito/agregar'] = 'cart/add';
$route['carrito/actualizar'] = 'cart/update';
$route['carrito/eliminar'] = 'cart/remove';
$route['carrito/vaciar'] = 'cart/clear';
$route['carrito/mini'] = 'cart/mini';

$route['checkout'] = 'checkout/index';
$route['checkout/confirmar'] = 'checkout/place';

$route['cuenta'] = 'account/index';
$route['cuenta/actualizar'] = 'account/update';
$route['cuenta/password'] = 'account/password';
$route['cuenta/pedidos'] = 'account/orders';
$route['cuenta/pedido/(:num)'] = 'account/order/$1';

$route['ingresar'] = 'store_auth/login';
$route['registro'] = 'store_auth/register';
$route['salir'] = 'store_auth/logout';
$route['recuperar'] = 'store_auth/forgot_password';
$route['restablecer/(:any)'] = 'store_auth/reset_password/$1';

/* SEO: sitemap.xml dinamico (robots.txt es un archivo estatico en la raiz) */
$route['sitemap.xml'] = 'sitemap/index';

/* -----------------------------------------------------------------
 * Backoffice (/admin): funcionalidad portada de SGMensajeria.
 * Los controladores viven en application/controllers/admin/ y las
 * vistas en application/third_party/sgadmin/views.
 * ----------------------------------------------------------------- */
$route['menu'] = 'admin/dashboard/menu';

$admin_modules = array(
	'auth', 'dashboard', 'users', 'roles', 'clients', 'products',
	'warehouses', 'deposits', 'catalogs', 'orders', 'preparation',
	'routes', 'reports', 'audit', 'settings', 'courier',
);
foreach ($admin_modules as $admin_module)
{
	$route[$admin_module] = 'admin/' . $admin_module;
	// (.*) captura rutas de varios segmentos (ej. orders/detail/5, auth/login/CR).
	$route[$admin_module . '/(.*)'] = 'admin/' . $admin_module . '/$1';
}

