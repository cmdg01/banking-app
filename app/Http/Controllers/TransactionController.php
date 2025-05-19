<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Bank;
use App\Services\PlaidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected $plaidService;

    public function __construct(PlaidService $plaidService)
    {
        $this->plaidService = $plaidService;
    }

    /**
     * Display a listing of the user's transactions.
     */
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->paginate(20);
            
        return view('transactions.index', compact('transactions'));
    }

    /**
     * Sync transactions for a specific bank account.
     */
    public function sync(Bank $bank)
    {
        // Make sure the bank belongs to the authenticated user
        if ($bank->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            // Default to last 30 days of transactions
            $endDate = Carbon::today()->format('Y-m-d');
            $startDate = Carbon::today()->subDays(30)->format('Y-m-d');

            // Get transactions from Plaid
            $plaidTransactions = $this->plaidService->getTransactions(
                $bank->plaid_access_token,
                $startDate,
                $endDate,
                $bank->plaid_account_id
            );

            $syncCount = 0;

            // Process and store each transaction
            foreach ($plaidTransactions as $plaidTransaction) {
                // Check if transaction already exists
                $existing = Transaction::where('user_id', Auth::id())
                    ->where('plaid_transaction_id', $plaidTransaction['transaction_id'])
                    ->first();
                
                if (!$existing) {
                    Transaction::create([
                        'user_id' => Auth::id(),
                        'bank_id' => $bank->id,
                        'plaid_transaction_id' => $plaidTransaction['transaction_id'],
                        'plaid_account_id' => $plaidTransaction['account_id'],
                        'name' => $plaidTransaction['name'],
                        'amount' => abs($plaidTransaction['amount']), // Store as positive number
                        'date' => Carbon::parse($plaidTransaction['date']),
                        'category' => isset($plaidTransaction['category']) ? $plaidTransaction['category'][0] : null,
                        'type' => $plaidTransaction['payment_channel'] ?? null,
                        'payment_meta' => $plaidTransaction['payment_meta'] ?? null,
                    ]);
                    
                    $syncCount++;
                }
            }

            return redirect()->route('banks.show', $bank)
                ->with('success', "Successfully synced $syncCount new transactions.");
                
        } catch (\Exception $e) {
            return redirect()->route('banks.show', $bank)
                ->with('error', 'Failed to sync transactions: ' . $e->getMessage());
        }
    }
}
