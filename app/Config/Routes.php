<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/home', 'Home::index');
$routes->get('/about', 'Home::about');
$routes->get('/contact', 'Home::contact');

// 🔹 Authentication Routes
$routes->match(['get','post'], 'register', 'Auth::register');
$routes->match(['get','post'], 'login', 'Auth::login');

$routes->get('dashboard', 'Auth::dashboard');
$routes->get('logout', 'Auth::logout');

$routes->get('/register', 'Register::index');   // show form
$routes->post('/register/store', 'Register::store');  // process form

$routes->get('/login', 'Login::index');       // Show login form
$routes->post('/login/authenticate', 'Login::authenticate'); // Process login
$routes->get('/dashboard', 'Dashboard::index'); // After login redirect
$routes->get('/logout', 'Auth::logout');

