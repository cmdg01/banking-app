<?php

namespace App\Console\Commands;

use App\Models\Bank;
use App\Models\Transaction;
use App\Services\PlaidService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPlaidTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plaid:sync-transactions {--bank=} {--all} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync transactions from Plaid to the database';

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
        $bankId = $this->option('bank');
        $syncAll = $this->option('all');
        $force = $this->option('force');

        $query = Bank::query();

        // Filter by bank ID if provided
        if ($bankId) {
            $query->where('id', $bankId);
        }

        // Only sync banks that haven't been synced recently, unless forced
        if (!$syncAll && !$force) {
            $query->where(function ($q) {
                $q->where('last_synced_at', '<', now()->subHours(1))
                  ->orWhereNull('last_synced_at');
            });
        }

        $banks = $query->get();

        if ($banks->isEmpty()) {
            $this->info('No banks to sync.');
            return 0;
        }

        $this->info('Syncing transactions for ' . $banks->count() . ' banks...');
        $bar = $this->output->createProgressBar($banks->count());
        $bar->start();

        $totalAdded = 0;
        $totalModified = 0;
        $totalRemoved = 0;

        foreach ($banks as $bank) {
            try {
                $this->syncBankTransactions($bank, $totalAdded, $totalModified, $totalRemoved);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Error syncing bank {$bank->id}: " . $e->getMessage());
                Log::error("Error syncing bank transactions", [
                    'bank_id' => $bank->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Sync completed: Added {$totalAdded}, Modified {$totalModified}, Removed {$totalRemoved} transactions.");

        return 0;
    }

    /**
     * Sync transactions for a specific bank.
     *
     * @param Bank $bank
     * @param int &$totalAdded
     * @param int &$totalModified
     * @param int &$totalRemoved
     * @return void
     */
    protected function syncBankTransactions(Bank $bank, &$totalAdded, &$totalModified, &$totalRemoved)
    {
        $cursor = $bank->plaid_cursor;
        $hasMore = true;

        while ($hasMore) {
            $response = $this->plaidService->syncTransactions(
                $bank->plaid_access_token,
                $cursor
            );

            // Process added transactions
            foreach ($response['added'] as $txData) {
                $this->processTransaction($bank, $txData);
                $totalAdded++;
            }

            // Process modified transactions
            foreach ($response['modified'] as $txData) {
                $this->processTransaction($bank, $txData);
                $totalModified++;
            }

            // Process removed transactions
            foreach ($response['removed'] as $removed) {
                Transaction::where('plaid_transaction_id', $removed['transaction_id'])
                    ->where('bank_id', $bank->id)
                    ->delete();
                $totalRemoved++;
            }

            // Update cursor and check if there are more transactions
            $cursor = $response['next_cursor'];
            $hasMore = $response['has_more'];

            // Update the bank's cursor
            $bank->plaid_cursor = $cursor;
            $bank->save();

            // If there are more transactions, but we're not in verbose mode, just log it
            if ($hasMore && !$this->option('verbose')) {
                $this->info("More transactions available for bank {$bank->id}, continuing sync...");
            }
        }

        // Update last synced timestamp
        $bank->last_synced_at = now();
        $bank->save();
    }

    /**
     * Process a transaction and save it to the database.
     *
     * @param Bank $bank
     * @param array $txData
     * @return void
     */
    protected function processTransaction(Bank $bank, array $txData)
    {
        // Format the transaction data
        $transactionData = [
            'user_id' => $bank->user_id,
            'bank_id' => $bank->id,
            'plaid_transaction_id' => $txData['transaction_id'],
            'plaid_account_id' => $txData['account_id'],
            'name' => $txData['name'],
            'amount' => $txData['amount'],
            'date' => Carbon::parse($txData['date']),
            'category' => $txData['category'][0] ?? 'Uncategorized',
            'type' => $txData['payment_channel'] ?? 'other',
            'pending' => $txData['pending'] ?? false,
            'payment_meta' => json_encode($txData['payment_meta'] ?? []),
            'channel' => $txData['payment_channel'] ?? null,
        ];

        // Update or create the transaction
        Transaction::updateOrCreate(
            [
                'plaid_transaction_id' => $txData['transaction_id'],
                'bank_id' => $bank->id
            ],
            $transactionData
        );
    }
}
