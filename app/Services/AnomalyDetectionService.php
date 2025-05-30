<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AnomalyDetectionService
{
    protected $geminiService;
    
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }
    
    /**
     * Detect anomalies in user transactions
     *
     * @param User $user
     * @param int $lookbackDays
     * @return Collection
     */
    public function detectAnomalies(User $user, int $lookbackDays = 90): Collection
    {
        try {
            // Get user transactions
            $recentTransactions = $this->getUserTransactions($user, $lookbackDays);
            
            if ($recentTransactions->isEmpty()) {
                return collect();
            }
            
            // Calculate statistics for normal behavior
            $stats = $this->calculateTransactionStatistics($recentTransactions);
            
            // Identify potential anomalies
            $potentialAnomalies = $this->identifyPotentialAnomalies($recentTransactions, $stats);
            
            // Generate explanations for anomalies
            $anomalies = $this->generateAnomalyExplanations($potentialAnomalies);
            
            return $anomalies;
        } catch (\Exception $e) {
            Log::error('Error detecting anomalies', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return collect();
        }
    }
    
    /**
     * Get user transactions for analysis
     *
     * @param User $user
     * @param int $lookbackDays
     * @return Collection
     */
    protected function getUserTransactions(User $user, int $lookbackDays): Collection
    {
        $startDate = Carbon::now()->subDays($lookbackDays);
        
        return Transaction::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date', 'desc')
            ->get();
    }
    
    /**
     * Calculate statistics for normal transaction behavior
     *
     * @param Collection $transactions
     * @return array
     */
    protected function calculateTransactionStatistics(Collection $transactions): array
    {
        // Group transactions by category
        $transactionsByCategory = $transactions->groupBy('category');
        
        $stats = [
            'amount' => [
                'mean' => $transactions->avg('amount'),
                'stddev' => $this->calculateStdDev($transactions->pluck('amount')->toArray()),
                'max' => $transactions->max('amount'),
                'min' => $transactions->min('amount'),
            ],
            'categories' => [],
            'frequency' => [
                'daily' => $this->calculateDailyFrequency($transactions),
            ],
            'time_patterns' => $this->analyzeTimingPatterns($transactions),
        ];
        
        // Calculate statistics for each category
        foreach ($transactionsByCategory as $category => $categoryTransactions) {
            $stats['categories'][$category] = [
                'count' => $categoryTransactions->count(),
                'percentage' => $categoryTransactions->count() / $transactions->count() * 100,
                'amount' => [
                    'mean' => $categoryTransactions->avg('amount'),
                    'stddev' => $this->calculateStdDev($categoryTransactions->pluck('amount')->toArray()),
                    'max' => $categoryTransactions->max('amount'),
                    'min' => $categoryTransactions->min('amount'),
                ],
            ];
        }
        
        return $stats;
    }
    
    /**
     * Calculate standard deviation
     *
     * @param array $values
     * @return float
     */
    protected function calculateStdDev(array $values): float
    {
        $count = count($values);
        
        if ($count <= 1) {
            return 0;
        }
        
        $mean = array_sum($values) / $count;
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return sqrt($variance / ($count - 1));
    }
    
    /**
     * Calculate daily transaction frequency
     *
     * @param Collection $transactions
     * @return array
     */
    protected function calculateDailyFrequency(Collection $transactions): array
    {
        $transactionDates = $transactions->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('Y-m-d');
        });
        
        $uniqueDates = $transactionDates->unique();
        $totalDays = $uniqueDates->count();
        
        if ($totalDays === 0) {
            return [
                'mean' => 0,
                'stddev' => 0,
                'max' => 0,
            ];
        }
        
        $dailyCounts = $transactionDates->countBy();
        $maxDaily = $dailyCounts->max();
        $meanDaily = $transactions->count() / $totalDays;
        
        $variance = 0;
        foreach ($dailyCounts as $count) {
            $variance += pow($count - $meanDaily, 2);
        }
        
        $stddev = sqrt($variance / $totalDays);
        
        return [
            'mean' => $meanDaily,
            'stddev' => $stddev,
            'max' => $maxDaily,
        ];
    }
    
    /**
     * Analyze timing patterns in transactions
     *
     * @param Collection $transactions
     * @return array
     */
    protected function analyzeTimingPatterns(Collection $transactions): array
    {
        $dayOfWeekCounts = [0, 0, 0, 0, 0, 0, 0]; // Sun-Sat
        
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->date);
            $dayOfWeekCounts[$date->dayOfWeek]++;
        }
        
        return [
            'day_of_week' => $dayOfWeekCounts,
        ];
    }
    
    /**
     * Identify potential anomalies in transactions
     *
     * @param Collection $transactions
     * @param array $stats
     * @return Collection
     */
    protected function identifyPotentialAnomalies(Collection $transactions, array $stats): Collection
    {
        $anomalies = collect();
        
        foreach ($transactions as $transaction) {
            $anomalyReasons = [];
            $anomalyScore = 0;
            
            // Skip already reviewed transactions
            if ($transaction->is_reviewed) {
                continue;
            }
            
            // Check for amount anomalies (transactions significantly larger than average)
            $amountZScore = ($transaction->amount - $stats['amount']['mean']) / ($stats['amount']['stddev'] ?: 1);
            if (abs($amountZScore) > 2.5) {
                $anomalyReasons[] = 'amount';
                $anomalyScore += abs($amountZScore) / 2;
            }
            
            // Check for category anomalies (unusual categories or unusual amounts within a category)
            $category = $transaction->category;
            if (isset($stats['categories'][$category])) {
                $categoryStats = $stats['categories'][$category];
                
                // Unusual amount for this category
                $categoryAmountZScore = ($transaction->amount - $categoryStats['amount']['mean']) / ($categoryStats['amount']['stddev'] ?: 1);
                if (abs($categoryAmountZScore) > 2.5) {
                    $anomalyReasons[] = 'category_amount';
                    $anomalyScore += abs($categoryAmountZScore) / 2;
                }
                
                // Rare category (less than 5% of transactions)
                if ($categoryStats['percentage'] < 5) {
                    $anomalyReasons[] = 'rare_category';
                    $anomalyScore += (5 - $categoryStats['percentage']) / 5;
                }
            } else {
                // New category (not seen before)
                $anomalyReasons[] = 'new_category';
                $anomalyScore += 1;
            }
            
            // If anomaly score is high enough, add to anomalies
            if ($anomalyScore >= 1 || !empty($anomalyReasons)) {
                $transaction->anomaly_reasons = $anomalyReasons;
                $transaction->anomaly_score = $anomalyScore;
                $anomalies->push($transaction);
            }
        }
        
        // Sort by anomaly score (highest first)
        return $anomalies->sortByDesc('anomaly_score');
    }
    
    /**
     * Generate explanations for anomalies using Gemini API
     *
     * @param Collection $anomalies
     * @return Collection
     */
    protected function generateAnomalyExplanations(Collection $anomalies): Collection
    {
        foreach ($anomalies as $transaction) {
            try {
                $explanation = $this->explainAnomaly($transaction);
                
                // Update transaction with anomaly data
                $transaction->is_anomaly = true;
                $transaction->anomaly_explanation = $explanation;
                $transaction->anomaly_data = [
                    'reasons' => $transaction->anomaly_reasons,
                    'score' => $transaction->anomaly_score,
                    'detected_at' => Carbon::now()->toIso8601String(),
                ];
                
                $transaction->save();
            } catch (\Exception $e) {
                Log::error('Error generating anomaly explanation', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
                
                $transaction->anomaly_explanation = 'This transaction appears unusual based on your spending patterns.';
            }
        }
        
        return $anomalies;
    }
    
    /**
     * Generate an explanation for why a transaction is anomalous
     *
     * @param Transaction $transaction
     * @return string
     */
    protected function explainAnomaly(Transaction $transaction): string
    {
        $reasons = $transaction->anomaly_reasons;
        $prompt = $this->buildAnomalyPrompt($transaction, $reasons);
        
        try {
            $response = $this->geminiService->generateChatResponse($prompt);
            return $response;
        } catch (\Exception $e) {
            Log::error('Error calling Gemini API for anomaly explanation', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback explanations if API call fails
            if (in_array('amount', $reasons)) {
                return "This transaction amount of $" . abs($transaction->amount) . " is unusually large compared to your typical spending.";
            } elseif (in_array('category_amount', $reasons)) {
                return "This " . $transaction->category . " transaction is much larger than your typical spending in this category.";
            } elseif (in_array('new_category', $reasons) || in_array('rare_category', $reasons)) {
                return "This transaction is in a category (" . $transaction->category . ") that you rarely or never use.";
            } else {
                return "This transaction appears unusual based on your spending patterns.";
            }
        }
    }
    
    /**
     * Build a prompt for the Gemini API to explain an anomaly
     *
     * @param Transaction $transaction
     * @param array $reasons
     * @return string
     */
    protected function buildAnomalyPrompt(Transaction $transaction, array $reasons): string
    {
        $reasonsText = implode(', ', $reasons);
        
        return <<<PROMPT
You are an AI assistant for a banking app that detects unusual transactions. 
Please explain why the following transaction might be considered unusual in a brief, helpful way (2-3 sentences max).
Be conversational but professional. Focus on the anomaly reasons provided.

Transaction details:
- Merchant: {$transaction->name}
- Amount: \${$transaction->amount}
- Category: {$transaction->category}
- Date: {$transaction->date->format('Y-m-d')}

Anomaly reasons: {$reasonsText}

Your explanation:
PROMPT;
    }
}
