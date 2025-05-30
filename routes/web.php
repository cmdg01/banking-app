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
use App\Http\Controllers\AiInsightController;

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

// User Bank Linking and Storing Routes (auth protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/banks/link', [BankController::class, 'showLinkForm'])->name('banks.link');
    Route::post('/banks', [BankController::class, 'store'])->name('banks.store');
    
    // Anomaly Detection Routes
    Route::get('/anomalies', function() {
        return view('anomalies.livewire');
    })->name('anomalies.index');
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
// The following route is now handled by the auth-protected group above:
// Route::post('/banks', [BankController::class, 'store'])->name('banks.store'); 
Route::get('/banks/{bank}', [BankController::class, 'show'])->name('banks.show');
Route::delete('/banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');
Route::post('/banks/{bank}/refresh', [BankController::class, 'refresh'])->name('banks.refresh');
Route::post('/banks/{bank}/update-balance', [BankController::class, 'updateBalance'])->name('banks.update-balance');

// Plaid routes
Route::get('/plaid/link-token', [BankController::class, 'getPlaidLinkToken'])->name('plaid.link-token');

// BankLinkController specific routes (if any are still needed apart from the replaced one)
// The following route was replaced by the BankController version in the auth-protected group:
// Route::get('/banks/link', [BankLinkController::class, 'showLinkForm'])->name('banks.link');
Route::post('/banks/link/process', [BankLinkController::class, 'processLink'])->name('banks.link.process');

// Chat Routes
Route::get('/chat', function () {
    return view('chat.livewire');
})
    ->name('chat')
    ->middleware(['auth', 'verified']);
Route::post('/chat/send', [ChatController::class, 'sendMessage'])
    ->name('chat.send')
    ->middleware(['auth', 'verified']);

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

// AI Insights
Route::get('/insights', [AiInsightController::class, 'insightsPage'])
    ->name('insights')
    ->middleware(['auth', 'verified']);

// AI Insights API
Route::get('/ai/insights', [AiInsightController::class, 'getInsights'])
    ->name('ai.insights')
    ->middleware(['auth', 'verified']);

// Test route for AI insights debugging
Route::get('/test/ai-insights', [\App\Http\Controllers\AiInsightController::class, 'testPage'])->name('ai.insights.test');

// API Routes
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Financial Insights
    Route::get('/insights', [\App\Http\Controllers\Api\FinancialInsightController::class, 'getInsights'])
        ->name('api.insights.get');
        
    // Bank Balances
    Route::prefix('banks')->group(function () {
        // Get balance for a specific bank account
        Route::get('/{bank}/balance', [\App\Http\Controllers\BankController::class, 'getBalance'])
            ->name('api.banks.balance');
            
        // Get balances for all connected accounts
        Route::get('/balances', [\App\Http\Controllers\BankController::class, 'getAllBalances'])
            ->name('api.banks.balances');
    });
});

// Test route for Gemini service (temporary - can be removed after debugging)
Route::get('/test/gemini', function (\App\Services\GeminiService $geminiService) {
    try {
        $testData = [
            'summary' => [
                'total_balance' => 10000,
                'total_available' => 8000,
                'total_accounts' => 2,
            ],
            'accounts' => [
                ['institution_name' => 'Test Bank', 'account_name' => 'Checking', 'balance_current' => 5000, 'balance_available' => 4000],
                ['institution_name' => 'Test Bank', 'account_name' => 'Savings', 'balance_current' => 5000, 'balance_available' => 4000],
            ],
            'recent_transactions' => [
                ['date' => now()->format('Y-m-d'), 'amount' => -100, 'name' => 'Grocery Store', 'category' => 'Food & Dining'],
                ['date' => now()->subDay()->format('Y-m-d'), 'amount' => -50, 'name' => 'Gas Station', 'category' => 'Auto & Transport'],
            ],
            'spending_by_category' => [
                'Food & Dining' => 300,
                'Auto & Transport' => 150,
                'Shopping' => 200,
            ]
        ];

        $result = $geminiService->getFinancialInsights($testData);
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Test Gemini Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
})->middleware(['auth', 'verified']);

Route::get('/test-write', function() {
    $testContent = "Testing file write at: " . now()->toDateTimeString();
    $testFile = storage_path('test_write.txt');
    
    $bytesWritten = file_put_contents($testFile, $testContent);
    
    if ($bytesWritten === false) {
        return "Failed to write to: " . $testFile . "<br>" .
               "Check directory permissions for: " . storage_path();
    }
    
    return "Successfully wrote to: " . $testFile . "<br>" .
           "Content: " . file_get_contents($testFile);
});

Route::get('/test-logging', function() {
    // Test error logging
    error_log('This is a test error log message');
    
    // Test Laravel's logger
    \Log::info('This is a test log message from Laravel logger');
    
    // Test writing to a file directly
    $testFile = storage_path('logs/test_direct.log');
    file_put_contents($testFile, date('Y-m-d H:i:s') . " - Test direct file write\n", FILE_APPEND);
    
    return response()->json([
        'message' => 'Logging test completed',
        'files' => [
            'storage/logs/laravel.log exists' => file_exists(storage_path('logs/laravel.log')) ? 'Yes' : 'No',
            'storage/logs/test_direct.log exists' => file_exists($testFile) ? 'Yes' : 'No',
            'storage/logs directory is writable' => is_writable(storage_path('logs')) ? 'Yes' : 'No',
            'storage directory is writable' => is_writable(storage_path()) ? 'Yes' : 'No',
        ]
    ]);
});// Add this to routes/web.php
Route::get('/test/config', function() {
    return [
        'gemini_key_configured' => !empty(config('services.gemini.api_key')),
        'gemini_key_length' => strlen(config('services.gemini.api_key')),
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
    ];
})->middleware('auth');