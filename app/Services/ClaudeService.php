<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClaudeService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.anthropic.com/v1';
    protected string $model = 'claude-3-opus-20240229';
    protected ?User $user = null;

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * Set the user for which to fetch data
     */
    public function forUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get financial insights for the authenticated user
     */
    public function getUserFinancialInsights(int $months = 3): array
    {
        if (!$this->user) {
            throw new \RuntimeException('User not set. Call forUser() first.');
        }

        $transactions = $this->getUserTransactions($months);
        $accounts = $this->getUserAccounts();

        return $this->getFinancialInsights($transactions, $accounts);
    }

    /**
     * Get user transactions from the database
     */
    protected function getUserTransactions(int $months = 3): array
    {
        $date = Carbon::now()->subMonths($months);
        
        return Transaction::where('user_id', $this->user->id)
            ->where('date', '>=', $date)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => (float) $transaction->amount,
                    'description' => $transaction->name,
                    'date' => $transaction->date->format('Y-m-d'),
                    'category' => $transaction->category,
                    'type' => $transaction->amount >= 0 ? 'credit' : 'debit',
                    'account_id' => $transaction->bank_id
                ];
            })
            ->toArray();
    }

    /**
     * Get user accounts with balances
     */
    protected function getUserAccounts(): array
    {
        return Bank::where('user_id', $this->user->id)
            ->get()
            ->map(function ($account) {
                $balance = Transaction::where('bank_id', $account->id)
                    ->orderBy('date', 'desc')
                    ->value('running_balance') ?? 0;

                return [
                    'account_id' => $account->id,
                    'account_name' => $account->account_name,
                    'balance' => (float) $balance,
                    'currency' => 'USD',
                    'type' => $account->account_type,
                    'institution' => $account->institution_name,
                    'mask' => $account->account_mask
                ];
            })
            ->toArray();
    }

    /**
     * Get financial insights from Claude API
     */
    public function getFinancialInsights(array $transactions, array $accounts): array
    {
        $prompt = $this->buildFinancialPrompt($transactions, $accounts);
        
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post("$this->baseUrl/messages", [
                'model' => $this->model,
                'max_tokens' => 1000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'insights' => $response->json()['content'][0]['text'] ?? 'No insights available.'
                ];
            }

            Log::error('Claude API error', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to get financial insights.'
            ];

        } catch (\Exception $e) {
            Log::error('Claude API exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Service unavailable. Please try again later.'
            ];
        }
    }

    /**
     * Build the prompt for financial insights
     */
    protected function buildFinancialPrompt(array $transactions, array $accounts): string
    {
        $transactionSummary = json_encode($transactions, JSON_PRETTY_PRINT);
        $balanceSummary = json_encode($accounts, JSON_PRETTY_PRINT);
        
        return "You are a financial advisor analyzing a user's transactions and account balances. "
             . "Provide clear, actionable insights and recommendations based on the following data. "
             . "Focus on spending patterns, potential savings opportunities, and financial health.\n\n"
             . "Transaction History (last 3 months):\n$transactionSummary\n\n"
             . "Account Balances:\n$balanceSummary\n\n"
             . "Provide your analysis in a clear, structured format with specific recommendations. "
             . "Be concise but thorough. If you notice any concerning patterns or opportunities, highlight them. "
             . "Format your response in Markdown for better readability.";
    }
}
