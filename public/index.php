// FILE: /public/index.php
<?php

/**
 * Application Entry Point
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */

// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Set timezone
date_default_timezone_set('UTC');

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/core/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/helpers/' . $class . '.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Initialize router
$router = new Router();

// ===============================================
// ROUTES
// ===============================================

// Authentication
$router->get('/', 'AuthController@login');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@processLogin');
$router->get('/logout', 'AuthController@logout');

// Dashboard
$router->get('/dashboard', 'DashboardController@index');

// Units
$router->get('/units', 'UnitController@index');
$router->get('/units/create', 'UnitController@create');
$router->post('/units/create', 'UnitController@store');
$router->get('/units/{id}', 'UnitController@view');
$router->get('/units/{id}/edit', 'UnitController@edit');
$router->post('/units/{id}/edit', 'UnitController@update');
$router->post('/units/{id}/delete', 'UnitController@delete');

// Customers
$router->get('/customers', 'CustomerController@index');
$router->get('/customers/create', 'CustomerController@create');
$router->post('/customers/create', 'CustomerController@store');
$router->get('/customers/{id}', 'CustomerController@view');
$router->get('/customers/{id}/edit', 'CustomerController@edit');
$router->post('/customers/{id}/edit', 'CustomerController@update');

// Contracts
$router->get('/contracts', 'ContractController@index');
$router->get('/contracts/create', 'ContractController@create');
$router->post('/contracts/create', 'ContractController@store');
$router->get('/contracts/{id}', 'ContractController@view');

// API Routes
$router->post('/api/customers/create', 'ApiController@createCustomer');
$router->get('/api/units/available', 'ApiController@listAvailableUnits');
$router->post('/api/bookings/create', 'ApiController@createBooking');
$router->post('/api/payments/add', 'ApiController@addPayment');

// Dispatch router
try {
    $router->dispatch();
} catch (Exception $e) {
    // Log error
    error_log($e->getMessage());

    // Show error page
    http_response_code(500);
    echo "An error occurred. Please try again later.";
}
