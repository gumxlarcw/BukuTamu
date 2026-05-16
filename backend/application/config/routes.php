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
|	https://codeigniter.com/user_guide/general/routing.html
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
$route['default_controller'] = 'selamat_datang';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['layanan'] = 'layanan/index';
$route['layanan/go'] = 'layanan/go';
$route['layanan/halaman/(:any)'] = 'layanan/halaman/$1';

$route['selamat_datang/recognize'] = 'selamat_datang/recognize';
$route['selamat_datang/get_face_data'] = 'selamat_datang/get_face_data';
$route['selamat_datang/masuk'] = 'selamat_datang/masuk';
$route['selamat_datang/check'] = 'selamat_datang/check';
$route['layanan/auto'] = 'layanan/go_if_not_set';

$route['admin/daftar_kunjungan'] = 'admin/daftar_kunjungan';
$route['admin/detail_kunjungan/(:num)'] = 'admin/detail_kunjungan/$1';

$route['antrian/cetak/(:any)'] = 'selamat_datang/antrian/cetak/$1';

// API Routes
$route['api/auth/check'] = 'api/auth/check';
$route['api/auth/login'] = 'api/auth/login';
$route['api/auth/logout'] = 'api/auth/logout';
$route['api/guests'] = 'api/guests/index';
$route['api/guests/(:num)'] = 'api/guests/detail/$1';
$route['api/guests/(:num)/visits'] = 'api/guests/visits/$1';
$route['api/guests/(:num)/photo'] = 'api/guests/photo/$1';
$route['api/visits'] = 'api/visits/index';
$route['api/visits/(:num)'] = 'api/visits/detail/$1';
$route['api/visits/(:num)/status'] = 'api/visits/status/$1';
$route['api/visits/(:num)/service'] = 'api/visits/service/$1';
$route['api/visits/(:num)/summary'] = 'api/visits/summary/$1';
$route['api/consultations'] = 'api/consultations/index';
$route['api/consultations/(:num)/call'] = 'api/consultations/call/$1';
$route['api/consultations/(:num)/test-sound'] = 'api/consultations/test_sound/$1';
$route['api/consultations/(:num)/data'] = 'api/consultations/data/$1';
$route['api/consultations/(:num)'] = 'api/consultations/detail/$1';
$route['api/dtsen'] = 'api/dtsen/index';
$route['api/dtsen/(:num)/data'] = 'api/dtsen/data/$1';
$route['api/dtsen/(:num)'] = 'api/dtsen/detail/$1';
$route['api/evaluations/pending'] = 'api/evaluations/pending';
$route['api/evaluations/summary'] = 'api/evaluations/summary';
$route['api/responden'] = 'api/responden/index';
$route['api/users'] = 'api/users/index';
$route['api/users/change-password'] = 'api/users/change_password';
$route['api/users/(:num)'] = 'api/users/detail/$1';
$route['api/audit'] = 'api/audit/index';
$route['api/queue-stats'] = 'api/queue_stats/index';
$route['api/evaluations/(:num)/results'] = 'api/evaluations/results/$1';
$route['api/evaluations/(:num)'] = 'api/evaluations/detail/$1';
$route['api/dashboard/stats'] = 'api/dashboard/stats';
$route['api/dashboard/events'] = 'api/dashboard/events';
$route['api/services'] = 'api/services/index';
$route['api/kiosk/face-data'] = 'api/kiosk/face_data';
$route['api/kiosk/guest-list'] = 'api/kiosk/guest_list';
$route['api/kiosk/register'] = 'api/kiosk/register';
$route['api/kiosk/visit'] = 'api/kiosk/visit';
$route['api/kiosk/ticket/(:num)'] = 'api/kiosk/ticket/$1';
$route['api/kiosk/profile-gaps/(:num)'] = 'api/kiosk/profile_gaps/$1';
$route['api/kiosk/profile-update/(:num)'] = 'api/kiosk/profile_update/$1';
