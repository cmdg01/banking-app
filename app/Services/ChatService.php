<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Bank;

class ChatService
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Generate a response to a user message using Gemini
     *
     * @param \App\Models\User $user The user making the request
     * @param string $message The user's message
     * @return string The AI-generated response
     */
    public function generateResponse($user, string $message)
    {
        try {
            $context = $this->buildContext($user);
            
            $prompt = $this->buildPrompt($message, $context);
            
            $response = $this->geminiService->generateChatResponse($prompt);
            
            if (isset($response['error'])) {
                Log::error('Error from Gemini API in chat', ['error' => $response['error']]);
                return "I'm sorry, I encountered an error while processing your request. Please try again later.";
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Error in ChatService', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return "I apologize, but I'm having trouble processing your request right now. Please try again later.";
        }
    }

    /**
     * Build context information about the user for more personalized responses
     *
     * @param \App\Models\User $user
     * @return array
     */
    protected function buildContext($user)
    {
        $context = [
            'user_name' => $user->name,
            'account_summary' => [],
            'recent_transactions' => []
        ];

        // Add bank account information if available
        $banks = Bank::where('user_id', $user->id)->get();
        foreach ($banks as $bank) {
            $context['account_summary'][] = [
                'bank_name' => $bank->name,
                'account_type' => $bank->account_type,
                'balance' => $bank->balance,
                'currency' => $bank->currency ?? 'USD'
            ];
        }

        // Add recent transactions if available
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($recentTransactions as $transaction) {
            $context['recent_transactions'][] = [
                'date' => $transaction->date,
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'category' => $transaction->category
            ];
        }

        return $context;
    }

    /**
     * Build the prompt for Gemini with user context and message
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function buildPrompt(string $message, array $context)
    {
        $accountInfo = '';
        if (!empty($context['account_summary'])) {
            $accountInfo .= "User has the following accounts:\n";
            foreach ($context['account_summary'] as $account) {
                $accountInfo .= "- {$account['bank_name']} ({$account['account_type']}): {$account['balance']} {$account['currency']}\n";
            }
        }

        $transactionInfo = '';
        if (!empty($context['recent_transactions'])) {
            $transactionInfo .= "Recent transactions:\n";
            foreach ($context['recent_transactions'] as $transaction) {
                $transactionInfo .= "- {$transaction['date']}: {$transaction['description']} - {$transaction['amount']} ({$transaction['category']})\n";
            }
        }

        return <<<PROMPT
You are a helpful financial assistant for a banking app. Your name is FinAssist.
You help users with budgeting, expense tracking, savings goals, and financial advice.
Be concise, friendly, and helpful. Provide specific, actionable advice when possible.

USER INFORMATION:
Name: {$context['user_name']}
{$accountInfo}
{$transactionInfo}

USER QUERY:
{$message}

Please respond in a helpful, concise way. If you don't have enough information to provide specific advice, ask clarifying questions.
PROMPT;
    }
}
