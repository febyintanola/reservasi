<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// === ROUTES UNTUK USER YANG SUDAH LOGIN DAN ROLE-NYA 'user' ===
$routes->group('', ['filter' => 'auth'], function($routes) {

    // Semua ini hanya untuk role 'user'
    $routes->group('', ['filter' => 'role:user'], function($routes) {
        $routes->get('/', 'Home::index');
        $routes->get('/home', 'Home::index');

        // RUANG
        $routes->get('/ruang', 'RoomController::index');
        $routes->post('/ruang/check', 'RoomController::checkAvailability');
        $routes->get('/ruang/ajax-check', 'RoomController::ajaxCheckAvailability');
        $routes->get('/ruang/booking-form', 'RoomController::bookingForm');
        $routes->post('/ruang/save-booking', 'RoomController::saveBooking');
        $routes->post('/ruang/simpan-booking', 'RoomController::saveBooking');

        // RUANG RAPAT (jika memang untuk user)
        $routes->get('/ruang-rapat', 'RuangRapatController::index');
        $routes->post('/ruang-rapat/simpan', 'RuangRapatController::simpan');

        // MOBIL
        $routes->get('/car', 'CarController::index');
        $routes->get('/car/form', 'CarController::form');
        $routes->post('/car/save', 'CarController::save');
        $routes->get('/car/konfirmasi', 'CarController::konfirmasi');

        // PROFIL DAN HISTORI
        $routes->get('/user/profile', 'UserController::profile');
        $routes->post('/profile/update', 'UserController::update');
        $routes->get('/history', 'HistoryController::index');
        $routes->get('/ruang/detail/(:num)', 'HistoryController::detailRuang/$1');
        $routes->get('/car/detail/(:num)', 'HistoryController::detailMobil/$1');


    });

});

$routes->group('admin', ['filter' => 'auth'], function($routes) {

    // Semua ini hanya untuk role 'admin'
    $routes->group('', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'AdminDashboardController::index');
        $routes->get('/dashboard', 'AdminDashboardController::index');
        
        // Tambahkan route admin lainnya di sini jika ada
        $routes->get('/mobil', 'Admin\MobilController::index');
        $routes->get('/ruang', 'Admin\RuangController::index');
    });

});

// === ROUTES PUBLIC ===
$routes->get('/register', 'AuthController::register');
$routes->post('/register', 'AuthController::storeRegister');
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::loginProcess');
$routes->get('/logout', 'AuthController::logout');
