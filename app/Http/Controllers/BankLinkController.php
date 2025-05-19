<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use App\Services\PlaidService;
use App\Services\DirectDwollaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BankLinkController extends Controller
{
    protected $plaidService;
    protected $dwollaService;
    
    public function __construct(PlaidService $plaidService, DirectDwollaService $dwollaService)
    {
        $this->plaidService = $plaidService;
        $this->dwollaService = $dwollaService;
    }
    
    /**
     * Show the bank linking form
     */
    public function showLinkForm()
    {
        $linkToken = $this->plaidService->createLinkToken(Auth::user());
        return view('banks.link', compact('linkToken'));
    }
    
    /**
     * Process the bank linking
     */
    public function processLink(Request $request)
    {
        try {
            $user = Auth::user();
            $publicToken = $request->input('public_token');
            $accountId = $request->input('account_id');
            
            Log::info('Processing bank link', [
                'user_id' => $user->id,
                'account_id' => $accountId
            ]);
            
            // Exchange public token for access token
            $exchangeResponse = $this->plaidService->exchangePublicTokenForAccessToken($publicToken);
            $accessToken = $exchangeResponse['access_token'];
            $itemId = $exchangeResponse['item_id'];
            
            // Get account details
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
                throw new \Exception('Your profile needs to be verified before connecting a bank account.');
            }
            
            // Create a Dwolla funding source with the processor token
            $fundingSourceUrl = $this->dwollaService->createFundingSourceWithProcessorToken(
                $user->dwolla_customer_url,
                $processorToken,
                $accountDetails['account_name']
            );
            
            // Save the bank details in the database
            $bank = new Bank([
                'user_id' => $user->id,
                'plaid_item_id' => $itemId,
                'plaid_account_id' => $accountId,
                'plaid_access_token' => $accessToken,
                'institution_name' => $accountDetails['institution_name'],
                'account_name' => $accountDetails['account_name'],
                'account_type' => $accountDetails['account_type'],
                'account_mask' => $accountDetails['account_mask'],
                'dwolla_funding_source_url' => $fundingSourceUrl
            ]);
            
            $bank->save();
            
            Log::info('Bank linked successfully', [
                'user_id' => $user->id,
                'bank_id' => $bank->id
            ]);
            
            return redirect()->route('banks.index')
                ->with('success', 'Bank account linked successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to link bank account: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('banks.index')
                ->with('error', 'Failed to link bank account: ' . $e->getMessage());
        }
    }
}
