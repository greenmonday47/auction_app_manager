<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Main route
$routes->get('/', 'Home::index');

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // Auth routes
    $routes->post('register', 'AuthController::register');
    $routes->post('login', 'AuthController::login');
    $routes->post('logout', 'AuthController::logout');
    $routes->post('verify-pin', 'AuthController::verifyPin');
    
    // Auction routes
    $routes->get('auctions', 'AuctionController::getAll');
    $routes->get('auctions/upcoming', 'AuctionController::getUpcoming');
    $routes->get('auctions/live', 'AuctionController::getLive');
    $routes->get('auctions/completed', 'AuctionController::getCompleted');
    $routes->get('auctions/(:num)', 'AuctionController::getAuction/$1');
    $routes->post('auctions/(:num)/bid', 'AuctionController::placeBid/$1');
    $routes->get('auctions/(:num)/bids', 'AuctionController::getBids/$1');
    
    // Wallet routes
    $routes->get('wallet', 'WalletController::getWallet');
    $routes->post('wallet/topup', 'WalletController::topUp');
    $routes->post('wallet/withdraw', 'WalletController::withdraw');
    $routes->get('wallet/transactions', 'WalletController::getTransactions');
    $routes->get('wallet/balance', 'WalletController::getBalance');
    
    // Payment routes
    $routes->post('payment/initializeTopUp', 'PaymentController::initializeTopUp');
    $routes->get('payment/verify/(:any)', 'PaymentController::verify/$1');
    $routes->post('payment/updateStatus', 'PaymentController::updateStatus');
    $routes->post('payment/gmpayWebhook', 'PaymentController::gmpayWebhook');
    $routes->get('payment/getUserPayments', 'PaymentController::getUserPayments');
    $routes->get('payment/getPaymentHistory', 'PaymentController::getPaymentHistory');
    $routes->get('payment/getWalletStats', 'PaymentController::getWalletStats');
    
    // Cron routes (for payment status checking)
    $routes->get('cron/check-payments', 'CronController::checkPendingPayments');
    $routes->get('cron/health', 'CronController::healthCheck');
    
    // Test routes
    $routes->get('test/database', 'TestController::testDatabase');
    
    // User routes
    $routes->get('user', 'UserController::getProfile');
    $routes->post('user/update', 'UserController::updateProfile');
    $routes->post('user/change-pin', 'UserController::changePin');
    $routes->get('user/bids', 'UserController::getBidHistory');
    $routes->get('user/auctions', 'UserController::getUserAuctions');
    
    // Rules route
    $routes->get('rules', 'RuleController::getRules');
});

// Admin routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Auth routes
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::doLogin');
    $routes->post('setup', 'AuthController::setup');
    $routes->get('logout', 'AuthController::logout');
    
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');
    
    // Users management
    $routes->get('users', 'UserController::index');
    $routes->post('users/add', 'UserController::add');
    $routes->post('users/edit', 'UserController::edit');
    $routes->post('users/add-tokens', 'UserController::addTokens');
    $routes->get('users/delete/(:num)', 'UserController::delete/$1');
    $routes->get('users/view/(:num)', 'UserController::view/$1');
    $routes->get('users/get/(:num)', 'UserController::get/$1');
    
    // Auctions management
    $routes->get('auctions', 'AuctionController::index');
    $routes->post('auctions/add', 'AuctionController::add');
    $routes->post('auctions/edit', 'AuctionController::edit');
    $routes->get('auctions/start/(:num)', 'AuctionController::start/$1');
    $routes->get('auctions/end/(:num)', 'AuctionController::end/$1');
    $routes->get('auctions/delete/(:num)', 'AuctionController::delete/$1');
    $routes->get('auctions/view/(:num)', 'AuctionController::view/$1');
    $routes->get('auctions/get/(:num)', 'AuctionController::get/$1');
    
    // Transactions management
    $routes->get('transactions', 'TransactionController::index');
    $routes->post('transactions/add', 'TransactionController::add');
    $routes->post('transactions/edit', 'TransactionController::edit');
    $routes->get('transactions/approve/(:num)', 'TransactionController::approve/$1');
    $routes->get('transactions/reject/(:num)', 'TransactionController::reject/$1');
    $routes->get('transactions/view/(:num)', 'TransactionController::view/$1');
    $routes->get('transactions/get/(:num)', 'TransactionController::get/$1');
    
    // Rules management
    $routes->get('rules', 'RuleController::index');
    $routes->post('rules/add', 'RuleController::add');
    $routes->post('rules/edit', 'RuleController::edit');
    $routes->get('rules/toggle/(:num)/(:any)', 'RuleController::toggle/$1/$2');
    $routes->get('rules/delete/(:num)', 'RuleController::delete/$1');
    $routes->post('rules/update-auction-settings', 'RuleController::updateAuctionSettings');
    $routes->post('rules/update-user-settings', 'RuleController::updateUserSettings');
});

// Catch-all route for 404
$routes->set404Override(function() {
    return view('errors/html/error_404');
});
