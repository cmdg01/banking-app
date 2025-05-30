<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankRequest;
use App\Models\Bank;
use App\Models\User;
use App\Services\DirectDwollaService;
use App\Services\PlaidService;
use Illuminate\Http\Request; // Retained for potential other uses
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class BankController extends Controller
{
    protected $plaidService;
    protected $dwollaService;

    public function __construct(PlaidService $plaidService, DirectDwollaService $dwollaService)
    {
        $this->plaidService = $plaidService;
        $this->dwollaService = $dwollaService;
    }

    /**
     * Display a listing of the user's connected banks.
     */
    public function index()
    {
        $banks = Auth::user()->banks;
        return view('banks.index', compact('banks'));
    }

    /**
     * Show the form for connecting a new bank account (legacy or alternative create view).
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in to link a bank account.');
        }
        
        try {
            $linkToken = $this->plaidService->createLinkToken($user);
            return view('banks.create', compact('linkToken'));
        } catch (Exception $e) {
            Log::error('Error in BankController create method: ' . $e->getMessage());
            // Redirect back with an error message, or to an error page
            return redirect()->route('banks.index')
                             ->with('error', 'Could not initialize bank linking. Please try again later. ' . $e->getMessage());
        }
    }

    /**
     * Show the form for linking a new bank account using Plaid Link.
     */
    public function showLinkForm()
    {
        try {
            $linkToken = $this->plaidService->createLinkToken(auth()->user());
            return view('banks.link', ['linkToken' => $linkToken]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to initialize bank linking: ' . $e->getMessage());
        }
    }

    /**
     * Get a Plaid Link token for the current user. (For AJAX calls if any)
     */
    public function getPlaidLinkToken()
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $linkToken = $this->plaidService->createLinkToken($user);
            return response()->json(['link_token' => $linkToken]);
        } catch (Exception $e) {
            Log::error('Failed to get Plaid Link token: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to initialize bank linking: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process the Plaid Link success and create a new bank connection.
     */
    public function store(StoreBankRequest $request)
    {
        $user = Auth::user();
        
        // Check for existing bank with same account
        $existingBank = Bank::where('user_id', $user->id)
            ->where('institution_name', $request->input('institution_name', 'Unknown Bank'))
            ->where('account_name', $request->input('account_name', 'Checking Account'))
            ->where('account_mask', $request->input('mask', ''))
            ->first();

        if ($existingBank) {
            if ($existingBank->status === 'inactive') {
                // Reactivate the existing bank
                $existingBank->status = 'active';
                $existingBank->save();
                
                return redirect()->route('dashboard')
                    ->with('success', 'Bank account was reactivated successfully!');
            }
            
            return redirect()->back()
                ->with('error', 'This bank account is already linked to your profile.');
        }

        try {
            // Exchange public token for access token
            $tokenResponse = $this->plaidService->exchangePublicToken($request->input('public_token'));
            $accessToken = $tokenResponse['access_token'];
            $itemId = $tokenResponse['item_id'];
            
            // Check if this Plaid item is already linked to another account
            $existingPlaidItem = Bank::where('plaid_item_id', $itemId)
                ->where('user_id', '!=', $user->id)
                ->exists();
                
            if ($existingPlaidItem) {
                throw new Exception('This bank account is already linked to another user.');
            }
            
            // Create bank record
            $bank = new Bank([
                'user_id' => $user->id,
                'plaid_item_id' => $itemId,
                'plaid_account_id' => $request->input('account_id'),
                'plaid_access_token' => $accessToken,
                'institution_name' => $request->input('institution_name', 'Unknown Bank'),
                'account_name' => $request->input('account_name', 'Checking Account'),
                'account_type' => $request->input('account_type', 'depository'),
                'account_subtype' => $request->input('account_subtype', 'checking'),
                'account_mask' => $request->input('mask', ''),
                'status' => 'pending',
            ]);

            if (!$bank->save()) {
                throw new Exception('Failed to save bank account');
            }

            // Get account details
            $accountDetails = $this->plaidService->getAccountDetails($accessToken, $bank->plaid_account_id);
            
            // Update bank with account details
            if (!empty($accountDetails)) {
                $bank->institution_name = $accountDetails['institution_name'] ?? $bank->institution_name;
                $bank->account_name = $accountDetails['account_name'] ?? $bank->account_name;
                $bank->account_type = $accountDetails['account_type'] ?? $bank->account_type;
                $bank->account_mask = $accountDetails['account_mask'] ?? $bank->account_mask;
                $bank->save();
            }

            // Create processor token for Dwolla
            $processorTokenResponse = $this->plaidService->createProcessorToken(
                $accessToken,
                $bank->plaid_account_id,
                'dwolla'
            );
            
            try {
                // Create funding source in Dwolla
                $accountSubtype = $bank->account_subtype ?: 'checking';
                $fundingSource = $this->dwollaService->createFundingSource(
                    $user->dwolla_customer_url,
                    $processorTokenResponse['processor_token'],
                    $bank->institution_name . ' ' . $bank->account_name,
                    $accountSubtype
                );
                
                // Update bank with Dwolla funding source details
                $bank->dwolla_funding_source_id = $fundingSource['id'];
                $bank->dwolla_funding_source_url = $fundingSource['_links']['self']->href;
                $bank->status = 'active';
                $bank->save();
                
            } catch (\Exception $dwollaException) {
                // Handle Dwolla-specific errors
                if (str_contains($dwollaException->getMessage(), 'DuplicateResource')) {
                    // Extract the existing funding source ID from the error message
                    preg_match('/id=([a-f0-9-]+)/', $dwollaException->getMessage(), $matches);
                    $existingId = $matches[1] ?? null;
                    
                    if ($existingId) {
                        // Update with existing funding source details
                        $bank->dwolla_funding_source_id = $existingId;
                        $bank->dwolla_funding_source_url = "https://api-sandbox.dwolla.com/funding-sources/" . $existingId;
                        $bank->status = 'active';
                        $bank->save();
                        
                        return redirect()->route('dashboard')
                            ->with('success', 'Bank account reconnected successfully!');
                    }
                }
                
                // If we can't handle the error, rethrow it
                throw $dwollaException;
            }

            return redirect()->route('dashboard')
                ->with('success', 'Bank account connected successfully!');

        } catch (\Exception $e) {
            if (isset($bank) && $bank instanceof Bank && $bank->exists) {
                $bank->status = 'failed';
                $bank->save();
            }
            
            Log::error('Error linking bank account: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'request_data' => $request->except(['public_token', 'account_number', 'routing_number']),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Failed to connect bank: ' . $e->getMessage();
            
            // Provide more user-friendly error messages
            if (str_contains($e->getMessage(), 'DuplicateResource')) {
                $errorMessage = 'This bank account is already linked to your profile.';
            } elseif (str_contains($e->getMessage(), 'processor token')) {
                $errorMessage = 'Error processing bank account. Please try again or contact support.';
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified bank with its transactions.
     */
    public function show(Bank $bank)
    {
        // Make sure the bank belongs to the authenticated user
        if ($bank->user_id !== Auth::id()) {
            abort(403);
        }

        // If balance has never been synced or it's been more than 1 hour since last sync
        if (!$bank->last_synced_at || $bank->last_synced_at->diffInHours(now()) >= 1) {
            try {
                \Log::info('Fetching balance for bank', [
                    'bank_id' => $bank->id,
                    'plaid_account_id' => $bank->plaid_account_id,
                    'has_access_token' => !empty($bank->plaid_access_token)
                ]);

                $balanceData = $this->plaidService->getAccountBalance(
                    $bank->plaid_access_token,
                    $bank->plaid_account_id
                );

                \Log::info('Received balance data', [
                    'bank_id' => $bank->id,
                    'balance_data' => $balanceData
                ]);

                $bank->balance_available = $balanceData['available'] ?? null;
                $bank->balance_current = $balanceData['current'] ?? null;
                $bank->balance_limit = $balanceData['limit'] ?? null; // Assuming Bank model has balance_limit
                $bank->balance_currency = $balanceData['iso_currency_code'] ?? 'USD';
                $bank->last_synced_at = now();
                $bank->save();

                \Log::info('Updated bank balance', [
                    'bank_id' => $bank->id,
                    'available' => $bank->balance_available,
                    'current' => $bank->balance_current,
                    'limit' => $bank->balance_limit,
                    'currency' => $bank->balance_currency
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to update balance: ' . $e->getMessage(), [
                    'bank_id' => $bank->id,
                    'exception' => $e->getTraceAsString()
                ]);
            }
        } else {
            \Log::info('Using cached balance', [
                'bank_id' => $bank->id,
                'last_synced' => $bank->last_synced_at,
                'available' => $bank->balance_available,
                'current' => $bank->balance_current
            ]);
        }

        $transactions = Transaction::where('bank_id', $bank->id)
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('banks.show', compact('bank', 'transactions'));
    }

    /**
     * Remove the specified bank from storage.
     */
    public function destroy(Bank $bank)
    {
        // Make sure the bank belongs to the authenticated user
        if ($bank->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            // TODO: Consider removing the funding source from Dwolla as well
            // if ($bank->dwolla_funding_source_id) { // Check if dwolla_funding_source_id exists
            //     // You'll need a method in dwollaService like:
            //     // $this->dwollaService->removeFundingSourceById($bank->dwolla_funding_source_id);
            //     // Or if you store the full URL:
            //     // $this->dwollaService->removeFundingSource($bank->dwolla_funding_source_url); 
            // }

            $bank->delete();

            return redirect()->route('banks.index')
                ->with('success', 'Bank account removed successfully.');
        } catch (Exception $e) {
            Log::error('Failed to remove bank account: ' . $e->getMessage());

            return redirect()->route('banks.index')
                ->with('error', 'Failed to remove bank account: ' . $e->getMessage());
        }
    }

    /**
     * Refresh the bank account information.
     */
    public function refresh(Bank $bank)
    {
        // Make sure the bank belongs to the authenticated user
        if ($bank->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            // Get updated account information using Plaid API
            // Note: Plaid may not provide new details unless there's a reason (e.g., balance update)
            // For things like account name or mask, they typically don't change.
            // This refresh might be more for balance or transaction updates if those products are used.
            // Assuming getAccountDetails returns an array with expected keys
            $accountDetails = $this->plaidService->getAccountDetails($bank->plaid_access_token, $bank->plaid_account_id);

            // Update the bank record
            $bank->account_name = $accountDetails['account_name'] ?? $bank->account_name;
            $bank->account_mask = $accountDetails['account_mask'] ?? $bank->account_mask;
            // You might update institution_name or account_type if they can change,
            // but usually, they are static for a given account_id.
            $bank->save();

            return redirect()->route('banks.show', $bank)
                ->with('success', 'Bank account information refreshed successfully.');
        } catch (Exception $e) {
            Log::error('Failed to refresh bank account: ' . $e->getMessage(), [
                'bank_id' => $bank->id
            ]);

            return redirect()->route('banks.show', $bank)
                ->with('error', 'Failed to refresh bank account: ' . $e->getMessage());
        }
    }

    /**
     * Get the account balance for a specific bank account
     * @param Bank $bank
     * @return JsonResponse
     */
    public function getBalance(Bank $bank): JsonResponse
    {
        try {
            // Verify the bank account belongs to the authenticated user
            if ($bank->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this bank account.'
                ], 403);
            }

            // Get balance from Plaid
            $balanceData = $this->plaidService->getAccountBalance(
                $bank->plaid_access_token,
                $bank->plaid_account_id
            );

            // Get recent transactions
            $transactions = Transaction::where('bank_id', $bank->id)
                ->orderBy('date', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'account' => [
                        'id' => $bank->id,
                        'name' => $bank->account_name,
                        'institution' => $bank->institution_name,
                        'type' => $bank->account_type,
                        'mask' => $bank->account_mask,
                    ],
                    'balance' => [
                        'available' => $balanceData['available'] ?? 0,
                        'current' => $balanceData['current'] ?? 0,
                        'limit' => $balanceData['limit'] ?? null,
                        'currency' => $balanceData['iso_currency_code'] ?? 'USD',
                        'last_updated' => now()->toDateTimeString(), // This is when this API call was made
                    ],
                    'recent_transactions' => $transactions->map(function($transaction) {
                        return [
                            'id' => $transaction->id,
                            'date' => $transaction->date->format('Y-m-d'), // Assuming date is a Carbon instance
                            'description' => $transaction->name, // Assuming Transaction model has 'name'
                            'amount' => (float) $transaction->amount,
                            'category' => $transaction->category, // Assuming Transaction model has 'category'
                            'pending' => (bool) $transaction->pending, // Assuming Transaction model has 'pending'
                        ];
                    })
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch account balance: ' . $e->getMessage(), [
                'bank_id' => $bank->id,
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch account balance. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get balances for all accounts
     * @return JsonResponse
     */
    public function getAllBalances(): JsonResponse
    {
        try {
            $user = Auth::user();
            $banks = $user->banks;
            
            $balances = [];
            
            foreach ($banks as $bank) {
                try { // Added inner try-catch for individual bank balance fetch
                    $balanceData = $this->plaidService->getAccountBalance(
                        $bank->plaid_access_token,
                        $bank->plaid_account_id
                    );
                    
                    $balances[] = [
                        'id' => $bank->id,
                        'name' => $bank->account_name,
                        'institution' => $bank->institution_name,
                        'type' => $bank->account_type,
                        'mask' => $bank->account_mask,
                        'balance' => [
                            'available' => $balanceData['available'] ?? 0,
                            'current' => $balanceData['current'] ?? 0,
                            'limit' => $balanceData['limit'] ?? null,
                            'currency' => $balanceData['iso_currency_code'] ?? 'USD',
                        ]
                    ];
                } catch (Exception $e) {
                    Log::error('Failed to fetch balance for one account in getAllBalances', [
                        'bank_id' => $bank->id,
                        'user_id' => Auth::id(),
                        'error' => $e->getMessage()
                    ]);
                    // Optionally add a placeholder or skip this bank
                    $balances[] = [
                        'id' => $bank->id,
                        'name' => $bank->account_name,
                        'institution' => $bank->institution_name,
                        'type' => $bank->account_type,
                        'mask' => $bank->account_mask,
                        'balance' => null, // Indicate error or missing data
                        'error' => 'Failed to retrieve balance for this account.'
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $balances
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to fetch all balances: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch account balances. Please try again later.'
            ], 500);
        }
    }

    /**
     * Update the account balance from Plaid
     * @param Bank $bank
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBalance(Bank $bank)
    {
        // Make sure the bank belongs to the authenticated user
        if ($bank->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            // Get balance from Plaid
            $balanceData = $this->plaidService->getAccountBalance(
                $bank->plaid_access_token,
                $bank->plaid_account_id
            );

            // Update the bank model with the balance data
            $bank->balance_available = $balanceData['available'] ?? null;
            $bank->balance_current = $balanceData['current'] ?? null;
            $bank->balance_limit = $balanceData['limit'] ?? null; // Assuming Bank model has balance_limit
            $bank->balance_currency = $balanceData['iso_currency_code'] ?? 'USD';
            $bank->last_synced_at = now(); // Also update last_synced_at
            $bank->save();

            return redirect()->route('banks.show', $bank)
                ->with('success', 'Account balance updated successfully.');
        } catch (Exception $e) {
            Log::error('Failed to update account balance: ' . $e->getMessage(), [
                'bank_id' => $bank->id,
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return redirect()->route('banks.show', $bank)
                ->with('error', 'Failed to update account balance: ' . $e->getMessage());
        }
    }
}