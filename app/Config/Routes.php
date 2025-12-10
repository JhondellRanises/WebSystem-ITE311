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
    
    // User Management CRUD Routes
    $routes->get('manage-users', 'ManageUser::index');
    $routes->match(['get', 'post'], 'manage-users/create', 'ManageUser::create');
    $routes->match(['get', 'post'], 'manage-users/store', 'ManageUser::store');
    $routes->match(['get', 'post'], 'manage-users/edit/(:num)', 'ManageUser::edit/$1');
    $routes->match(['get', 'post', 'put'], 'manage-users/update/(:num)', 'ManageUser::update/$1');
    $routes->post('manage-users/delete/(:num)', 'ManageUser::delete/$1');
    $routes->get('manage-users/delete/(:num)', 'ManageUser::delete/$1');
    $routes->get('manage-users/show/(:num)', 'ManageUser::show/$1');

    // Course Management
    $routes->get('manage-courses', 'ManageCourse::index');
    $routes->match(['get', 'post'], 'manage-courses/create', 'ManageCourse::create');
    $routes->match(['get', 'post'], 'manage-courses/edit/(:num)', 'ManageCourse::edit/$1');
    $routes->post('manage-courses/delete/(:num)', 'ManageCourse::delete/$1');
    $routes->get('manage-courses/delete/(:num)', 'ManageCourse::delete/$1');
    $routes->get('manage-courses/show/(:num)', 'ManageCourse::show/$1');
    
    // Soft Delete & Restore Routes
    $routes->post('manage-users/restore/(:num)', 'ManageUser::restore/$1');
    $routes->get('manage-users/restore/(:num)', 'ManageUser::restore/$1');
});

$routes->group('teacher', function($routes) {
    $routes->get('dashboard', 'Teacher::dashboard');
});

$routes->group('student', function($routes) {
    $routes->get('dashboard', 'Student::dashboard');
});

// 📚 Courses
$routes->get('courses', 'Course::index');
$routes->post('course/enroll', 'Course::enroll');

// 🔍 Course Search
$routes->match(['get', 'post'], 'courses/search', 'Course::search');
// 📦 Materials: upload, delete, download, and list
$routes->get('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->post('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->get('/admin/upload', 'Materials::upload');
$routes->get('/teacher/upload', 'Materials::upload');
$routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->get('/materials/download/(:num)', 'Materials::download/$1');
$routes->get('/student/materials', 'Materials::student');
$routes->get('/student/courses', 'Materials::studentCourses');

// 🔔 Notifications API
$routes->get('/notifications', 'Notifications::get');
$routes->post('/notifications/mark_read/(:num)', 'Notifications::mark_as_read/$1');
$routes->post('/notifications/mark_read', 'Notifications::mark_as_read');
// GET fallbacks (for labs without CSRF on AJAX)
$routes->get('/notifications/mark_read/(:num)', 'Notifications::mark_as_read/$1');
$routes->get('/notifications/mark_read', 'Notifications::mark_as_read');
