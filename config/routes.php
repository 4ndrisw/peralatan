<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['peralatan/peralatan/(:num)/(:any)'] = 'peralatan/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['peralatan/list'] = 'myperalatan/list';
$route['peralatan/show/(:num)/(:any)'] = 'myperalatan/show/$1/$2';
$route['peralatan/office/(:num)/(:any)'] = 'myperalatan/office/$1/$2';
$route['peralatan/pdf/(:num)'] = 'myperalatan/pdf/$1';
