<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Transaction;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AiInsightController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function getInsights()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::error('No authenticated user');
                return redirect()->back()->with('error', 'Not authenticated');
            }

            $cacheKey = "user_{$user->id}_ai_insights";
            
            // For debugging, you can temporarily disable cache
            // Cache::forget($cacheKey);
            
            $response = Cache::remember($cacheKey, now()->addHour(), function () use ($user) {
                $financialData = $this->prepareFinancialData($user);
                
                if (empty($financialData['accounts']) && empty($financialData['recent_transactions'])) {
                    Log::warning('No financial data available for user', ['user_id' => $user->id]);
                    return [
                        'insights' => [
                            'No financial data available. Please connect a bank account and import transactions to get insights.'
                        ]
                    ];
                }
                
                try {
                    $insights = $this->geminiService->getFinancialInsights($financialData);
                    
                    if (is_string($insights)) {
                        // If we got a raw string response, wrap it in the expected format
                        return ['raw' => $insights];
                    }
                    
                    if (isset($insights['error'])) {
                        Log::error('Error from Gemini API', ['error' => $insights['error']]);
                        throw new \Exception($insights['error']);
                    }
                    
                    // Ensure we always return an array of insights
                    if (isset($insights['insights']) && is_array($insights['insights'])) {
                        return $insights;
                    } else if (is_array($insights) && !empty($insights)) {
                        return ['insights' => $insights];
                    } else {
                        throw new \Exception('Unexpected response format from AI service');
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error generating insights', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            });

            // Instead of returning JSON, flash the data to the session and redirect back
            return redirect()->back()
                ->with('insights', $response['insights'] ?? null)
                ->with('raw', $response['raw'] ?? null);

        } catch (\Exception $e) {
            Log::error('Error in getInsights', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate insights: ' . $e->getMessage());
        }
    }

    protected function prepareFinancialData($user): array
    {
        try {
            $banks = Bank::where('user_id', $user->id)
                ->select(['institution_name', 'account_name', 'account_type', 'balance_current', 'balance_available'])
                ->get()
                ->toArray();

            $recentTransactions = Transaction::where('user_id', $user->id)
                ->with('bank')
                ->orderBy('date', 'desc')
                ->take(20)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'date' => $transaction->date->format('Y-m-d'),
                        'amount' => $transaction->amount,
                        'name' => $transaction->name,
                        'category' => $transaction->category,
                        'bank' => $transaction->bank->institution_name ?? 'Unknown',
                    ];
                });

            $totalBalance = array_sum(array_column($banks, 'balance_current'));
            $totalAvailable = array_sum(array_column($banks, 'balance_available'));

            return [
                'summary' => [
                    'total_balance' => $totalBalance,
                    'total_available' => $totalAvailable,
                    'total_accounts' => count($banks),
                ],
                'accounts' => $banks,
                'recent_transactions' => $recentTransactions,
                'spending_by_category' => $this->getSpendingByCategory($user->id),
            ];
            
        } catch (\Exception $e) {
            Log::error('Error preparing financial data', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'error' => 'Failed to prepare financial data',
                'message' => $e->getMessage()
            ];
        }
    }

    protected function getSpendingByCategory($userId): array
    {
        try {
            return Transaction::where('user_id', $userId)
                ->where('amount', '<', 0)
                ->selectRaw('category, SUM(ABS(amount)) as total')
                ->groupBy('category')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->pluck('total', 'category')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting spending by category', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Test page for AI insights debugging
     */
    public function testPage()
    {
        $user = Auth::user();
        
        // Get test data
        $testData = [
            'summary' => [
                'total_balance' => 10000,
                'total_available' => 8000,
                'total_accounts' => 2,
            ],
            'accounts' => [
                [
                    'institution_name' => 'Test Bank',
                    'account_name' => 'Checking',
                    'balance_current' => 5000,
                    'balance_available' => 4000,
                ],
                [
                    'institution_name' => 'Test Bank',
                    'account_name' => 'Savings',
                    'balance_current' => 5000,
                    'balance_available' => 4000,
                ]
            ],
            'recent_transactions' => [
                [
                    'date' => now()->format('Y-m-d'),
                    'amount' => -100,
                    'name' => 'Grocery Store',
                    'category' => 'Food & Dining'
                ],
                [
                    'date' => now()->subDay()->format('Y-m-d'),
                    'amount' => -50,
                    'name' => 'Gas Station',
                    'category' => 'Auto & Transport'
                ]
            ],
            'spending_by_category' => [
                'Food & Dining' => 300,
                'Auto & Transport' => 150,
                'Shopping' => 200,
            ]
        ];

        // Get real user data if available
        try {
            $realData = $this->prepareFinancialData($user);
            $hasRealData = !empty($realData['accounts']) || !empty($realData['recent_transactions']);
        } catch (\Exception $e) {
            $realData = [];
            $hasRealData = false;
            Log::error('Error preparing real financial data', ['error' => $e->getMessage()]);
        }

        return view('test.ai-insights', [
            'testData' => $testData,
            'realData' => $realData ?? [],
            'hasRealData' => $hasRealData,
            'apiKeyConfigured' => !empty(config('services.gemini.api_key')),
            'apiKeyLength' => strlen(config('services.gemini.api_key') ?? '')
        ]);
    }

    /**
     * Display the dedicated insights page
     */
    public function insightsPage()
    {
        $user = Auth::user();
        
        Log::info('Loading insights page', [
            'user_id' => $user->id,
            'has_api_key' => !empty(config('services.gemini.api_key'))
        ]);
        
        // Get financial data
        $financialData = [];
        $hasData = false;
        
        try {
            Log::debug('Preparing financial data');
            $financialData = $this->prepareFinancialData($user);
            $hasData = !empty($financialData['accounts']) || !empty($financialData['recent_transactions']);
            
            Log::debug('Financial data prepared', [
                'has_accounts' => !empty($financialData['accounts']),
                'accounts_count' => count($financialData['accounts'] ?? []),
                'has_transactions' => !empty($financialData['recent_transactions']),
                'transactions_count' => count($financialData['recent_transactions'] ?? [])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error preparing financial data for insights page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id
            ]);
            
            return view('insights.index', [
                'user' => $user,
                'financialData' => [],
                'hasData' => false,
                'apiKeyConfigured' => !empty(config('services.gemini.api_key')),
                'apiKeyLength' => strlen(config('services.gemini.api_key') ?? ''),
                'error' => 'Failed to load financial data. Please try again later.'
            ]);
        }
        
        Log::info('Rendering insights page', [
            'user_id' => $user->id,
            'has_data' => $hasData,
            'api_key_configured' => !empty(config('services.gemini.api_key'))
        ]);

        return view('insights.index', [
            'user' => $user,
            'financialData' => $financialData,
            'hasData' => $hasData,
            'apiKeyConfigured' => !empty(config('services.gemini.api_key')),
            'apiKeyLength' => strlen(config('services.gemini.api_key') ?? '')
        ]);
    }
}
