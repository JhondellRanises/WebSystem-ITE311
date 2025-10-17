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

// Public Pages
$routes->get('/', 'Home::index');
$routes->get('/home', 'Home::index');
$routes->get('/about', 'Home::about');
$routes->get('/contact', 'Home::contact');

// Authentication
$routes->match(['get', 'post'], 'register', 'Auth::register');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// Dashboard
$routes->get('dashboard', 'Auth::dashboard');

// Course Enroll
$routes->post('course/enroll', 'Course::enroll');  // ✅ single route only
$routes->get('teacher/dashboard', 'Teacher::dashboard');
$routes->get('admin/dashboard', 'Admin::dashboard');
$routes->get('announcements', 'Announcement::index');

