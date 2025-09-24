<?php

namespace Config;

use Config\Services;

// Create a new instance of our RouteCollection
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

// 🔹 Authentication Routes
$routes->match(['get', 'post'], 'register', 'Auth::register');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// 🔹 Unified Dashboard
$routes->get('dashboard', 'Auth::dashboard');
