<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankRequest;
use App\Models\Bank;
use App\Models\User; // Make sure User model is imported if not already
use App\Services\DirectDwollaService;
use App\Services\PlaidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception; // Import base Exception class
use App\Models\Transaction;

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
     * Show the form for connecting a new bank account.
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
        try {
            /** @var User $user */
            $user = Auth::user();
            $publicToken = $request->input('public_token');
            $accountId = $request->input('account_id'); // This comes from Plaid Link onSuccess metadata

            if (empty($publicToken) || empty($accountId)) {
                Log::warning('Public token or account_id missing from Plaid Link success callback.', $request->all());
                return redirect()->route('banks.create')->with('error', 'Bank linking failed. Missing information from Plaid.');
            }

            Log::info('Processing Plaid success', [
                'user_id' => $user->id,
                'account_id' => $accountId
            ]);

            // Exchange public token for access token
            $exchangeResponse = $this->plaidService->exchangePublicTokenForAccessToken($publicToken);
            $accessToken = $exchangeResponse['access_token'];
            $itemId = $exchangeResponse['item_id'];

            // Get account details for the selected account
            $accountDetails = $this->plaidService->getAccountDetails($accessToken, $accountId);

            // Check if the bank account already exists for this user
            $existingBank = Bank::where('user_id', $user->id)
                ->where('plaid_account_id', $accountId)
                ->first();

            if ($existingBank) {
                return redirect()->route('banks.index')
                    ->with('error', 'This bank account is already connected.');
            }

            // Create processor token for Dwolla
            $processorToken = $this->plaidService->createProcessorToken($accessToken, $accountId);

            // Check if the user has a Dwolla customer URL
            if (empty($user->dwolla_customer_url)) {
                 // It's often better to redirect to a page explaining the issue or profile completion page
                return redirect()->route('profile.show') // Or your relevant profile page
                                 ->with('error', 'Your profile needs to be verified before connecting a bank account.');
            }

            // Create a Dwolla funding source with the processor token
            $fundingSourceUrl = $this->dwollaService->createFundingSourceWithProcessorToken(
                $user->dwolla_customer_url,
                $processorToken,
                $accountDetails['account_name'] // Make sure this key matches what getAccountDetails returns
            );

            // Save the bank details in the database
            $bank = new Bank([
                'user_id' => $user->id,
                'plaid_item_id' => $itemId,
                'plaid_account_id' => $accountId,
                'plaid_access_token' => $accessToken, // Consider encrypting this
                'institution_name' => $accountDetails['institution_name'],
                'account_name' => $accountDetails['account_name'],
                'account_type' => $accountDetails['account_type'],
                'account_mask' => $accountDetails['account_mask'],
                'dwolla_funding_source_url' => $fundingSourceUrl
            ]);

            $bank->save();

            Log::info('Bank connected successfully', [
                'user_id' => $user->id,
                'bank_id' => $bank->id
            ]);

            return redirect()->route('banks.index')
                ->with('success', 'Bank account connected successfully.');

        } catch (Exception $e) {
            Log::error('Failed to connect bank account: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception_trace' => $e->getTraceAsString() // More detailed logging for dev
            ]);

            return redirect()->route('banks.create') // Redirect back to create page on error
                ->with('error', 'Failed to connect bank account: ' . $e->getMessage());
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
            // if ($bank->dwolla_funding_source_url) {
            //     $this->dwollaService->removeFundingSource($bank->dwolla_funding_source_url);
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
            $accountDetails = $this->plaidService->getAccountDetails($bank->plaid_access_token, $bank->plaid_account_id);

            // Update the bank record
            $bank->account_name = $accountDetails['account_name'];
            $bank->account_mask = $accountDetails['account_mask'];
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
}