<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\DwollaController;
use App\Http\Controllers\DwollaCustomerController;
use App\Http\Controllers\DwollaDirectController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\ChatController;

// Add this at the top of your routes/web.php file, after any middleware definitions
// but before any route groups
Route::get('/dwolla-simple-test', function () {
    return 'Hello from Dwolla test route';
});

// Guest Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes (if not fully handled by Breeze UI)
// These are optional — only needed if you're customizing login/register views
Route::get('login', fn() => view('auth.login'))->name('login');
Route::get('register', fn() => view('auth.register'))->name('register');

// Dashboard Route (protected)
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Settings Routes (Volt components)
Route::middleware(['auth'])->group(function () {
    Route::redirect('/settings', '/settings/profile')->name('settings');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// Only accessible when logged in as admin
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::post('/dwolla/create-customer', [DwollaController::class, 'createCustomer'])
         ->name('admin.dwolla.create-customer');
    Route::post('/dwolla/create-customer-for-user/{userId}', [DwollaController::class, 'createCustomerForExistingUser'])
         ->name('admin.dwolla.create-customer-for-user');
    Route::get('/dwolla/customer-form', function () {
        return view('admin.create-dwolla-customer');
    })->name('admin.dwolla.customer-form');
});

// Include Laravel Breeze auth routes (this includes password reset, verification, etc.)
require __DIR__.'/auth.php';

Route::get('/admin/dwolla-test', function () {
    return view('admin.dwolla-test');
})->name('admin.dwolla.test');

// Add these routes after your working test route
Route::get('/dwolla-customer-form', [DwollaCustomerController::class, 'showForm']);
Route::post('/create-dwolla-customer', [DwollaCustomerController::class, 'createCustomer']);

// Add this to your routes/web.php
Route::get('/test-dwolla-connection', function() {
    try {
        $environment = config('dwolla.environment');
        $key = config('dwolla.key');
        $secret = config('dwolla.secret');
        
        if (empty($key) || empty($secret)) {
            return 'Error: Dwolla credentials are missing in configuration.';
        }
        
        $apiUrl = $environment === 'production' 
            ? 'https://api.dwolla.com/token' 
            : 'https://api-sandbox.dwolla.com/token';
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic " . base64_encode("$key:$secret"),
                "Content-Type: application/x-www-form-urlencoded"
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);
        
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            return 'cURL Error: ' . $err;
        }
        
        $result = json_decode($response);
        
        if (isset($result->error)) {
            return 'API Error: ' . $result->error . ' - ' . ($result->error_description ?? 'No description');
        }
        
        if (!isset($result->access_token)) {
            return 'No access token in response: ' . $response;
        }
        
        return 'Connection successful! Token: ' . substr($result->access_token, 0, 10) . '...';
    } catch (Exception $e) {
        return 'Exception: ' . $e->getMessage();
    }
});

Route::get('/dwolla-direct-form', [DwollaDirectController::class, 'showForm']);
Route::post('/create-dwolla-customer-direct', [DwollaDirectController::class, 'createCustomer']);

// Bank routes
Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
Route::get('/banks/create', [BankController::class, 'create'])->name('banks.create');
Route::post('/banks', [BankController::class, 'store'])->name('banks.store');
Route::get('/banks/{bank}', [BankController::class, 'show'])->name('banks.show');
Route::delete('/banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');
Route::post('/banks/{bank}/refresh', [BankController::class, 'refresh'])->name('banks.refresh');

// Plaid routes
Route::get('/plaid/link-token', [BankController::class, 'getPlaidLinkToken'])->name('plaid.link-token');

// Add these routes with the existing bank routes
Route::get('/banks/link', [BankLinkController::class, 'showLinkForm'])->name('banks.link');
Route::post('/banks/link/process', [BankLinkController::class, 'processLink'])->name('banks.link.process');

// Chat Routes
Route::get('/chat', [ChatController::class, 'index'])
    ->middleware(['auth'])
    ->name('chat.index');

// Transaction routes
Route::middleware(['auth'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/banks/{bank}/transactions/sync', [TransactionController::class, 'sync'])->name('transactions.sync');
});

// Transfer Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
});