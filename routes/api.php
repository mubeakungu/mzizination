<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
# Mzizination Payment Routes
    Route::get('/phone/status', 'UserController@getPhoneStatus');
});

// ===== TRANSACTION HISTORY =====

Route::middleware('auth:api')->prefix('api/transactions')->group(function () {
    // Get all transactions (deposits + withdrawals)
    Route::get('/', 'TransactionController@index');

    // Get transaction details
    Route::get('/{transactionId}', 'TransactionController@show');

    // Export transactions (CSV, PDF)
    Route::get('/export/{format}', 'TransactionController@export');
});

// ===== ADMIN PAYMENT GATEWAY CONFIGURATION =====

Route::middleware(['auth:api', 'admin'])->prefix('api/admin/payment-gateways')->group(function () {
    // List configured gateways
    Route::get('/', 'Admin\PaymentGatewayController@index');

    // Test gateway connection
    Route::post('/{gateway}/test', 'Admin\PaymentGatewayController@test');

    // Update gateway configuration
    Route::put('/{gateway}', 'Admin\PaymentGatewayController@update');

    // Get gateway stats
    Route::get('/{gateway}/stats', 'Admin\PaymentGatewayController@stats');

    // Transaction logs
    Route::get('/logs', 'Admin\PaymentGatewayController@transactionLogs');
});

// ===== ADMIN REPORTS =====

Route::middleware(['auth:api', 'admin'])->prefix('api/admin/reports')->group(function () {
    // Payment statistics
    Route::get('/payments', 'Admin\ReportController@payments');

    // Withdrawal statistics
    Route::get('/withdrawals', 'Admin\ReportController@withdrawals');

    // Revenue report
    Route::get('/revenue', 'Admin\ReportController@revenue');

    // User deposits/withdrawals by date range
    Route::get('/user-transactions', 'Admin\ReportController@userTransactions');
});
