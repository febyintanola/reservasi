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
        $routes->get('room/findNextAvailableSlot', 'RoomController::findNextAvailableSlot');
        $routes->get('room/findNextAvailableSlotAnyRoom', 'RoomController::findNextAvailableSlotAnyRoom');

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
        $routes->get('user/car/detail/(:num)', 'CarController::userDetail/$1');


    });

});

$routes->group('admin', ['filter' => 'auth'], function($routes) {

    // Semua ini hanya untuk role 'admin'
    $routes->group('', ['filter' => 'role:admin'], static function($routes) {
        $routes->get('/', 'AdminDashboardController::index');
        $routes->get('dashboard', 'AdminDashboardController::index');
        $routes->get('booking/detail/(:num)', 'AdminDashboardController::detail/$1');
        $routes->post('booking/approve/(:num)', 'AdminDashboardController::approve/$1');
        $routes->post('booking/reject/(:num)', 'AdminDashboardController::reject/$1');
    $routes->get('car/detailMobil/(:num)', 'AdminDashboardController::detailMobil/$1');
    $routes->post('car/approve/(:num)','AdminDashboardController::approveCar/$1');
    $routes->post('car/reject/(:num)','AdminDashboardController::rejectCar/$1');

        // Tambahkan route admin lainnya di sini jika ada

        // Assign driver & mobil
        $routes->get('car/assign/(:num)', 'CarController::assignForm/$1');
        $routes->post('car/assign_save/(:num)', 'CarController::assignSave/$1');
        $routes->get('mobil', 'Admin\MobilController::index');
        // Admin Car bookings list & CRUD
        $routes->get('car', 'CarController::adminIndex');
        $routes->get('car/detail/(:num)', 'CarController::detail/$1');
        // Driver management
        $routes->get('driver', 'CarController::driverList');
        $routes->get('driver/tambah', 'CarController::driverCreate');
        $routes->post('driver/store', 'CarController::driverStore');
        $routes->get('driver/edit/(:num)', 'CarController::driverEdit/$1');
        $routes->post('driver/update/(:num)', 'CarController::driverUpdate/$1');
        $routes->get('ruang', 'RoomController::adminIndex');
        $routes->get('ruang/create', 'RoomController::create');
        $routes->get('ruang/tambah', 'RoomController::create');
        $routes->post('ruang/store', 'RoomController::store');
        $routes->get('ruang/edit/(:num)', 'RoomController::edit/$1');
        $routes->post('ruang/update/(:num)', 'RoomController::update/$1');
        $routes->get('car/create','CarController::create');
        $routes->get('car/tambah','CarController::create');
        $routes->post('car/store','CarController::store');
        $routes->get('car/edit/(:num)','CarController::edit/$1');
        $routes->post('car/update/(:num)','CarController::update/$1');

        // Profile (admin)
        $routes->get('profile', 'UserController::profile');
        $routes->post('profile/update', 'UserController::update');
        $routes->get('reports', 'ReportsController::index');
        $routes->get('reports/export/rooms/excel', 'ReportsController::exportRoomsExcel');
        $routes->get('reports/export/rooms/pdf', 'ReportsController::exportRoomsPdf');
        $routes->get('reports/export/cars/excel', 'ReportsController::exportCarsExcel');
        $routes->get('reports/export/cars/pdf', 'ReportsController::exportCarsPdf');
    });

});

// === ROUTES PUBLIC ===
$routes->get('/register', 'AuthController::register');
$routes->post('/register', 'AuthController::storeRegister');
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::loginProcess');
$routes->get('/logout', 'AuthController::logout');

// Forgot / Reset Password
$routes->get('/forgot-password', 'AuthController::forgotPasswordForm');
$routes->post('/forgot-password', 'AuthController::sendResetLink');
$routes->get('/reset-password/(:segment)', 'AuthController::resetPasswordForm/$1');
$routes->post('/reset-password', 'AuthController::resetPasswordProcess');

// Unauthorized
$routes->get('/unauthorized', 'AuthController::unauthorized');

// === ROUTES UNTUK DRIVER ===
$routes->group('driver', ['filter' => 'auth'], function($routes) {
    // Khusus role driver
    $routes->group('', ['filter' => 'role:driver'], function($routes) {
        $routes->get('dashboard', 'DriverDashboardController::index');
        $routes->get('jobs/(:num)', 'DriverDashboardController::show/$1');
        $routes->post('jobs/(:num)/status', 'DriverDashboardController::updateStatus/$1');
        $routes->get('driver/profile', 'DriverProfileController::index');
        $routes->post('driver/profile/photo', 'DriverProfileController::updatePhoto');
    });
});
