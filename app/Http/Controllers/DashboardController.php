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
        $banks = $user->banks;
        
        // Get total balance (assuming we store balance in bank model, or calculate from transactions)
        $totalBalance = 0;
        
        // Get recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();
            
        // Count linked accounts (banks)
        $linkedAccounts = $banks->count();
        
        // Count recent transactions
        $recentTransactionCount = $recentTransactions->count();
            
        return view('dashboard.index', compact('banks', 'totalBalance', 'recentTransactions', 'linkedAccounts', 'recentTransactionCount'));
    }
}
