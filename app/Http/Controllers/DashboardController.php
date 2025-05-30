<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Bank;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with recent transactions.
     */
    public function index()
    {
        $user = Auth::user();
        $banks = $user->banks()->with('transactions')->get();
        
        // Calculate total balance from all connected bank accounts
        $totalBalance = $banks->sum(function($bank) {
            return $bank->balance_current ?? 0;
        });
        
        // Get recent transactions across all accounts
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('bank')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();
            
        // Count linked accounts (banks)
        $linkedAccounts = $banks->count();
        
        // Count recent transactions
        $recentTransactionCount = $recentTransactions->count();
            
        return view('dashboard', compact('banks', 'totalBalance', 'recentTransactions', 'linkedAccounts', 'recentTransactionCount'));
    }
}
