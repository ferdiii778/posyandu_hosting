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
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Api Chat
$route['api/chat']['GET'] = 'api/chat/index';
$route['api/chat/(:num)']['GET'] = 'api/chat/detail/$1';

// Api Balita
$route['api/balita']['GET'] = 'api/balita/index';
$route['api/balita']['POST'] = 'api/balita/create';
$route['api/balita/(:any)']['GET'] = 'api/balita/detail/$1';
$route['api/balita/(:any)']['PUT'] = 'api/balita/update/$1';
$route['api/balita/(:any)']['DELETE'] = 'api/balita/delete/$1';

// Api Informasi
$route['api/informasi']['GET'] = 'api/informasi/index';
$route['api/informasi/(:num)']['GET'] = 'api/informasi/detail/$1';

// Api Jadwal CRUD
$route['api/jadwal']['GET'] = 'api/jadwal/index';
$route['api/jadwal/(:num)']['GET'] = 'api/jadwal/detail/$1';

// Api imunisasi CRUD
$route['api/imunisasi']['GET'] = 'api/imunisasi/index';
$route['api/imunisasi/(:num)']['GET'] = 'api/imunisasi/detail/$1';
$route['api/imunisasi']['POST'] = 'api/imunisasi/store';
$route['api/imunisasi/(:num)']['PUT'] = 'api/imunisasi/update/$1';
$route['api/imunisasi/(:num)']['DELETE'] = 'api/imunisasi/delete/$1';

// Api Vitamin CRUD
$route['api/vitamin']['GET'] = 'api/vitamin/index';
$route['api/vitamin/(:num)']['GET'] = 'api/vitamin/detail/$1';
$route['api/vitamin']['POST'] = 'api/vitamin/store';
$route['api/vitamin/(:num)']['PUT'] = 'api/vitamin/update/$1';
$route['api/vitamin/(:num)']['DELETE'] = 'api/vitamin/delete/$1';

// Api Kematian CRUD
$route['api/kematian']['GET'] = 'api/kematian/index';
$route['api/kematian/(:num)']['GET'] = 'api/kematian/detail/$1';
$route['api/kematian']['POST'] = 'api/kematian/store';
$route['api/kematian/(:num)']['PUT'] = 'api/kematian/update/$1';
$route['api/kematian/(:num)']['DELETE'] = 'api/kematian/delete/$1';

// Api konsultasi
$route['api/konsultasi']['GET'] = 'api/konsultasi/index';
$route['api/konsultasi/(:num)']['GET'] = 'api/konsultasi/detail/$1';

// Api Orang Tua
$route['api/orangtua']['GET'] = 'api/orangtua/index';
$route['api/orangtua/(:num)']['GET'] = 'api/orangtua/detail/$1';

// Api Ortu Bayi
$route['api/ortu-bayi']['GET'] = 'api/ortubayi/index';
$route['api/ortu-bayi/(:num)']['GET'] = 'api/ortubayi/detail/$1';

// Api Pemeriksaan
$route['api/pemeriksaan']['GET'] = 'api/pemeriksaan/index';
$route['api/pemeriksaan/(:any)']['GET'] = 'api/pemeriksaan/detail/$1';

// Api Berat Badan Menurut Umur Laki
$route['api/ref-bb-u-laki']['GET'] = 'api/bbulaki/index';
$route['api/ref-bb-u-laki/(:num)']['GET'] = 'api/bbulaki/detail/$1';

// Api Berat Badan Menurut Umur Perempuan
$route['api/ref-bb-u-perempuan']['GET'] = 'api/bbuperempuan/index';
$route['api/ref-bb-u-perempuan/(:num)']['GET'] = 'api/bbuperempuan/detail/$1';

// Api Panjang Badan Menurut Umur laki
$route['api/ref-pb-u-laki']['GET'] = 'api/pbulaki/index';
$route['api/ref-pb-u-laki/(:num)']['GET'] = 'api/pbulaki/detail/$1';

// Api Panjang Badan Menurut Umur perempuan
$route['api/ref-pb-u-perempuan']['GET'] = 'api/pbuperempuan/index';
$route['api/ref-pb-u-perempuan/(:num)']['GET'] = 'api/pbuperempuan/detail/$1';

// Api Standarbbp Laki
$route['api/ref-standar-bb-pb-laki']['GET'] = 'api/standarbbpblaki/index';
$route['api/ref-standar-bb-pb-laki/(:num)']['GET'] = 'api/standarbbpblaki/detail/$1';

// Api Standarbbp Perempuan
$route['api/ref-standar-bb-pb-perempuan']['GET'] = 'api/standarbbpbperempuan/index';
$route['api/ref-standar-bb-pb-perempuan/(:num)']['GET'] = 'api/standarbbpbperempuan/detail/$1';

// Api Standarbbp2460 Laki
$route['api/ref-standar-bb-pb-24-60-laki']['GET'] = 'api/standarbbpb2460laki/index';
$route['api/ref-standar-bb-pb-24-60-laki/(:num)']['GET'] = 'api/standarbbpb2460laki/detail/$1';

// Api Standarbbp2460 Perempuan
$route['api/ref-standar-bb-pb-24-60-perempuan']['GET'] = 'api/standarbbpb2460perempuan/index';
$route['api/ref-standar-bb-pb-24-60-perempuan/(:num)']['GET'] = 'api/standarbbpb2460perempuan/detail/$1';

// Api Mstandarimtu024laki
$route['api/ref-standar-imt-u-0-24-laki']['GET'] = 'api/standarimtu024laki/index';
$route['api/ref-standar-imt-u-0-24-laki/(:num)']['GET'] = 'api/standarimtu024laki/detail/$1';

// Api standarimtu024perempuan
$route['api/ref-standar-imt-u-0-24-perempuan']['GET'] = 'api/standarimtu024perempuan/index';
$route['api/ref-standar-imt-u-0-24-perempuan/(:num)']['GET'] = 'api/standarimtu024perempuan/detail/$1';

// Api standarimtu518laki
$route['api/ref-standar-imt-u-5-18-laki']['GET'] = 'api/standarimtu518laki/index';
$route['api/ref-standar-imt-u-5-18-laki/(:num)']['GET'] = 'api/standarimtu518laki/detail/$1';

// Api standarimtu518perempuan
$route['api/ref-standar-imt-u-5-18-perempuan']['GET'] = 'api/standarimtu518perempuan/index';
$route['api/ref-standar-imt-u-5-18-perempuan/(:num)']['GET'] = 'api/standarimtu518perempuan/detail/$1';

// Api Posyandu
$route['api/posyandu'] = 'api/posyandu/index';
$route['api/posyandu/(:num)'] = 'api/posyandu/detail/$1';

// Api User Auth
$route['api/user']['GET'] = 'api/user/index';
$route['api/user/(:num)']['GET'] = 'api/user/detail/$1';
$route['api/user']['POST'] = 'api/user/create';
$route['api/user/(:num)']['PUT'] = 'api/user/update/$1';
$route['api/user/(:num)']['DELETE'] = 'api/user/delete/$1';
