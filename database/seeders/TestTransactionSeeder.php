<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Bank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a user to associate transactions with
        $user = User::first();
        
        if (!$user) {
            $this->command->error('No users found. Please run the UserSeeder first.');
            return;
        }
        
        // Get or create a bank for the transactions
        $bank = Bank::where('user_id', $user->id)->first();
        
        if (!$bank) {
            $bank = Bank::create([
                'user_id' => $user->id,
                'name' => 'Test Bank',
                'plaid_access_token' => 'test_access_token',
                'plaid_item_id' => 'test_item_id',
                'plaid_institution_id' => 'test_institution_id',
            ]);
        }
        
        // Clear existing test transactions if any
        Transaction::where('user_id', $user->id)
            ->where('name', 'like', 'Test:%')
            ->delete();
        
        // Create normal transaction patterns
        $this->createNormalTransactions($user, $bank);
        
        // Create anomalous transactions
        $this->createAnomalousTransactions($user, $bank);
        
        $this->command->info('Test transactions created successfully.');
    }
    
    /**
     * Create normal transaction patterns
     */
    private function createNormalTransactions($user, $bank)
    {
        // Regular grocery transactions (weekly, ~$50-100)
        $startDate = Carbon::now()->subDays(90);
        $groceryStores = ['Test: Whole Foods', 'Test: Trader Joe\'s', 'Test: Safeway', 'Test: Kroger'];
        
        for ($i = 0; $i < 12; $i++) {
            $date = $startDate->copy()->addDays($i * 7 + rand(-1, 1));
            
            Transaction::create([
                'user_id' => $user->id,
                'bank_id' => $bank->id,
                'plaid_transaction_id' => 'test_grocery_' . $i,
                'plaid_account_id' => 'test_account',
                'name' => $groceryStores[array_rand($groceryStores)],
                'amount' => rand(5000, 10000) / 100,
                'date' => $date,
                'category' => 'Groceries',
                'type' => 'debit',
                'payment_meta' => json_encode(['reference_number' => 'REF' . rand(1000, 9999)]),
                'channel' => 'in store',
            ]);
        }
        
        // Regular dining transactions (2-3 times per week, ~$20-50)
        $restaurants = ['Test: Chipotle', 'Test: Olive Garden', 'Test: Cheesecake Factory', 'Test: Local Diner', 'Test: Pizza Place'];
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays(rand(1, 90));
            
            Transaction::create([
                'user_id' => $user->id,
                'bank_id' => $bank->id,
                'plaid_transaction_id' => 'test_dining_' . $i,
                'plaid_account_id' => 'test_account',
                'name' => $restaurants[array_rand($restaurants)],
                'amount' => rand(2000, 5000) / 100,
                'date' => $date,
                'category' => 'Restaurants',
                'type' => 'debit',
                'payment_meta' => json_encode(['reference_number' => 'REF' . rand(1000, 9999)]),
                'channel' => 'in store',
            ]);
        }
        
        // Regular utility bills (monthly, consistent amounts)
        $utilities = [
            'Test: Electric Company' => [8000, 12000],
            'Test: Water Utility' => [4000, 6000],
            'Test: Internet Provider' => [7000, 7500],
            'Test: Cell Phone Provider' => [8500, 9500],
        ];
        
        foreach ($utilities as $name => $range) {
            for ($i = 0; $i < 3; $i++) {
                $date = $startDate->copy()->addDays($i * 30 + rand(-2, 2));
                
                Transaction::create([
                    'user_id' => $user->id,
                    'bank_id' => $bank->id,
                    'plaid_transaction_id' => 'test_utility_' . $name . '_' . $i,
                    'plaid_account_id' => 'test_account',
                    'name' => $name,
                    'amount' => rand($range[0], $range[1]) / 100,
                    'date' => $date,
                    'category' => 'Utilities',
                    'type' => 'debit',
                    'payment_meta' => json_encode(['reference_number' => 'REF' . rand(1000, 9999)]),
                    'channel' => 'online',
                ]);
            }
        }
        
        // Regular income (bi-weekly, consistent amounts)
        for ($i = 0; $i < 6; $i++) {
            $date = $startDate->copy()->addDays($i * 14 + rand(-1, 1));
            
            Transaction::create([
                'user_id' => $user->id,
                'bank_id' => $bank->id,
                'plaid_transaction_id' => 'test_income_' . $i,
                'plaid_account_id' => 'test_account',
                'name' => 'Test: Employer Payroll',
                'amount' => -1 * rand(300000, 320000) / 100, // Negative amount for deposits
                'date' => $date,
                'category' => 'Income',
                'type' => 'credit',
                'payment_meta' => json_encode(['reference_number' => 'PAYROLL' . rand(1000, 9999)]),
                'channel' => 'online',
            ]);
        }
        
        // Regular subscription services (monthly, consistent amounts)
        $subscriptions = [
            'Test: Netflix' => [1499, 1499],
            'Test: Spotify' => [999, 999],
            'Test: Gym Membership' => [4999, 4999],
            'Test: Cloud Storage' => [999, 999],
        ];
        
        foreach ($subscriptions as $name => $range) {
            for ($i = 0; $i < 3; $i++) {
                $date = $startDate->copy()->addDays($i * 30 + rand(-1, 1));
                
                Transaction::create([
                    'user_id' => $user->id,
                    'bank_id' => $bank->id,
                    'plaid_transaction_id' => 'test_subscription_' . $name . '_' . $i,
                    'plaid_account_id' => 'test_account',
                    'name' => $name,
                    'amount' => rand($range[0], $range[1]) / 100,
                    'date' => $date,
                    'category' => 'Subscription',
                    'type' => 'debit',
                    'payment_meta' => json_encode(['reference_number' => 'REF' . rand(1000, 9999)]),
                    'channel' => 'online',
                ]);
            }
        }
    }
    
    /**
     * Create anomalous transactions for testing
     */
    private function createAnomalousTransactions($user, $bank)
    {
        // 1. Unusually large grocery purchase
        Transaction::create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'plaid_transaction_id' => 'test_anomaly_grocery',
            'plaid_account_id' => 'test_account',
            'name' => 'Test: Whole Foods',
            'amount' => 389.99, // Much higher than normal
            'date' => Carbon::now()->subDays(3),
            'category' => 'Groceries',
            'type' => 'debit',
            'payment_meta' => json_encode(['reference_number' => 'REF' . rand(1000, 9999)]),
            'channel' => 'in store',
        ]);
        
        // 2. Unusual merchant category
        Transaction::create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'plaid_transaction_id' => 'test_anomaly_category',
            'plaid_account_id' => 'test_account',
            'name' => 'Test: Luxury Jewelry Store',
            'amount' => 1299.99,
            'date' => Carbon::now()->subDays(5),
            'category' => 'Jewelry',
            'type' => 'debit',
            'payment_meta' => json_encode(['reference_number' => 'REF' . rand(1000, 9999)]),
            'channel' => 'in store',
        ]);
        
        // 3. Unusual frequency (multiple restaurant charges in one day)
        $today = Carbon::now()->subDays(2);
        $restaurants = ['Test: Expensive Restaurant 1', 'Test: Expensive Restaurant 2', 'Test: Expensive Restaurant 3'];
        
        foreach ($restaurants as $index => $restaurant) {
            Transaction::create([
                'user_id' => $user->id,
                'bank_id' => $bank->id,
                'plaid_transaction_id' => 'test_anomaly_frequency_' . $index,
                'plaid_account_id' => 'test_account',
                'name' => $restaurant,
                'amount' => rand(8000, 15000) / 100,
                'date' => $today,
                'category' => 'Restaurants',
                'type' => 'debit',
                'payment_meta' => json_encode(['reference_number' => 'REF' . rand(1000, 9999)]),
                'channel' => 'in store',
            ]);
        }
        
        // 4. Unusual location
        Transaction::create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'plaid_transaction_id' => 'test_anomaly_location',
            'plaid_account_id' => 'test_account',
            'name' => 'Test: Foreign Merchant',
            'amount' => 156.78,
            'date' => Carbon::now()->subDays(1),
            'category' => 'Shopping',
            'type' => 'debit',
            'payment_meta' => json_encode([
                'reference_number' => 'REF' . rand(1000, 9999),
                'location' => 'International'
            ]),
            'channel' => 'online',
        ]);
        
        // 5. Unusual high-risk merchant
        Transaction::create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'plaid_transaction_id' => 'test_anomaly_high_risk',
            'plaid_account_id' => 'test_account',
            'name' => 'Test: Cryptocurrency Exchange',
            'amount' => 2000.00,
            'date' => Carbon::now()->subDays(4),
            'category' => 'Financial',
            'type' => 'debit',
            'payment_meta' => json_encode(['reference_number' => 'REF' . rand(1000, 9999)]),
            'channel' => 'online',
        ]);
    }
}
