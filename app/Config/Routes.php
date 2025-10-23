<?php

namespace Config;

use Config\Services;

$routes = Services::routes();

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

// 🏠 Public Pages
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');
$routes->get('dashboard', 'Auth::dashboard');

//Auth
$routes->match(['get', 'post'], 'register', 'Auth::register');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

//Announcements — public for logged-in users
$routes->get('announcements', 'Announcement::index');

//Role-based dashboards (protected by RoleAuth)
$routes->group('admin', function($routes) {
    $routes->get('dashboard', 'Admin::dashboard');
});

$routes->group('teacher', function($routes) {
    $routes->get('dashboard', 'Teacher::dashboard');
});

$routes->group('student', function($routes) {
    $routes->get('dashboard', 'Student::dashboard');
});

// 📚 Course enrollment
$routes->post('course/enroll', 'Course::enroll');

// 📦 Materials: upload, delete, download, and list
$routes->get('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->post('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->get('/materials/download/(:num)', 'Materials::download/$1');
$routes->get('/course/(:num)/materials', 'Materials::list/$1');
$routes->get('/materials', 'Materials::all');
$routes->get('/materials/upload', 'Materials::uploadIndex');
$routes->get('/materials/student', 'Materials::student');
