<?php

namespace App\Console\Commands;

use App\Models\Bank;
use App\Services\PlaidService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plaid:sync-transactions 
                            {--days=30 : Number of days of transactions to sync} 
                            {--bank= : Specific bank ID to sync} 
                            {--all-users : Sync for all users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync transactions from Plaid for connected bank accounts';

    /**
     * The Plaid service instance.
     *
     * @var PlaidService
     */
    protected $plaidService;

    /**
     * Create a new command instance.
     *
     * @param PlaidService $plaidService
     * @return void
     */
    public function __construct(PlaidService $plaidService)
    {
        parent::__construct();
        $this->plaidService = $plaidService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $bankId = $this->option('bank');
        $allUsers = $this->option('all-users');

        $this->info("Starting Plaid transaction sync for " . ($allUsers ? 'all users' : 'current user') . ".");
        $this->info("Syncing transactions for the last {$days} days.");

        // Set date range
        $endDate = Carbon::today()->format('Y-m-d');
        $startDate = Carbon::today()->subDays($days)->format('Y-m-d');

        // Get banks to process
        $query = Bank::with('user')
            ->whereNotNull('plaid_access_token')
            ->whereNotNull('plaid_account_id');

        if ($bankId) {
            $query->where('id', $bankId);
        }

        if (!$allUsers && !$bankId) {
            $query->where('user_id', auth()->id() ?? 0);
        }

        $banks = $query->get();

        if ($banks->isEmpty()) {
            $this->warn('No connected bank accounts found.');
            return 0;
        }

        $this->info("Found {$banks->count()} bank accounts to process.");

        $totalSynced = 0;
        $errors = [];

        foreach ($banks as $bank) {
            try {
                $this->line("Syncing transactions for {$bank->institution_name} - {$bank->account_name}...");
                
                // Get transactions from Plaid
                $plaidTransactions = $this->plaidService->getTransactions(
                    $bank->plaid_access_token,
                    $startDate,
                    $endDate,
                    $bank->plaid_account_id
                );

                $synced = 0;
                $skipped = 0;

                foreach ($plaidTransactions as $transaction) {
                    // Check if transaction already exists
                    $existing = $bank->transactions()
                        ->where('plaid_transaction_id', $transaction['transaction_id'])
                        ->first();
                    
                    if (!$existing) {
                        $bank->transactions()->create([
                            'user_id' => $bank->user_id,
                            'plaid_transaction_id' => $transaction['transaction_id'],
                            'plaid_account_id' => $transaction['account_id'],
                            'name' => $transaction['name'],
                            'amount' => abs($transaction['amount']),
                            'date' => Carbon::parse($transaction['date']),
                            'category' => $transaction['category'][0] ?? null,
                            'type' => $transaction['payment_channel'] ?? null,
                            'payment_meta' => $transaction['payment_meta'] ?? null,
                        ]);
                        $synced++;
                    } else {
                        $skipped++;
                    }
                }

                $this->info("  ✓ Synced: {$synced}, Skipped: {$skipped}");
                $totalSynced += $synced;

            } catch (\Exception $e) {
                $errorMsg = "Error syncing bank ID {$bank->id}: " . $e->getMessage();
                $this->error($errorMsg);
                $errors[] = $errorMsg;
                Log::error($errorMsg, ['exception' => $e]);
            }
        }

        // Output summary
        $this->newLine();
        $this->info("✅ Sync completed!");
        $this->info("Total transactions synced: {$totalSynced}");
        
        if (!empty($errors)) {
            $this->warn("Encountered " . count($errors) . " errors during sync.");
            foreach ($errors as $error) {
                $this->line("  - " . $error);
            }
        }

        return 0;
    }
}
