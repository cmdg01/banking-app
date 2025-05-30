<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        if (empty($this->apiKey)) {
            Log::error('Gemini API key is not configured');
        } else {
            Log::info('GeminiService initialized with API key');
        }
    }

    public function getFinancialInsights(array $financialData)
    {
        try {
            if (empty($this->apiKey)) {
                throw new \Exception('Gemini API key is not configured');
            }

            Log::info('Generating financial insights', ['data_summary' => $this->summarizeData($financialData)]);
            
            $prompt = $this->buildPrompt($financialData);
            
            $httpClient = Http::withOptions([
                'verify' => false, // Disable SSL verification (for development only)
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $response = $httpClient->post(
                "{$this->baseUrl}?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ]
                ]
            );

            Log::debug('Gemini API response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            if ($response->failed()) {
                throw new \Exception('API request failed: ' . $response->body());
            }

            $result = $response->json();
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $insightText = $result['candidates'][0]['content']['parts'][0]['text'];
                $formatted = [
                    'insights' => $this->formatInsights($insightText),
                    'raw' => $insightText
                ];
                Log::info('Successfully generated insights', ['insights_count' => count($formatted['insights'] ?? [])]);
                return $formatted;
            }

            throw new \Exception('Unexpected API response format');

        } catch (\Exception $e) {
            Log::error('Gemini service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a chat response using Gemini API
     *
     * @param string $prompt The prompt to send to Gemini
     * @return string|array The response text or error array
     */
    public function generateChatResponse(string $prompt)
    {
        try {
            if (empty($this->apiKey)) {
                throw new \Exception('Gemini API key is not configured');
            }

            Log::info('Generating chat response', ['prompt_length' => strlen($prompt)]);
            
            $httpClient = Http::withOptions([
                'verify' => false, // Disable SSL verification (for development only)
                'timeout' => 15,
                'connect_timeout' => 5,
            ]);

            $response = $httpClient->post(
                "{$this->baseUrl}?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.8,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 512,
                    ]
                ]
            );

            Log::debug('Gemini API chat response', [
                'status' => $response->status(),
                'body_length' => strlen($response->body())
            ]);

            if ($response->failed()) {
                throw new \Exception('API request failed: ' . $response->body());
            }

            $result = $response->json();
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $responseText = $result['candidates'][0]['content']['parts'][0]['text'];
                Log::info('Successfully generated chat response', ['response_length' => strlen($responseText)]);
                return $responseText;
            }

            throw new \Exception('Unexpected API response format');

        } catch (\Exception $e) {
            Log::error('Gemini chat service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function summarizeData(array $data): array
    {
        return [
            'accounts_count' => count($data['accounts'] ?? []),
            'transactions_count' => count($data['recent_transactions'] ?? []),
            'total_balance' => $data['summary']['total_balance'] ?? 0,
            'categories' => array_keys($data['spending_by_category'] ?? []),
        ];
    }

    protected function buildPrompt(array $data): string
    {
        return "Act as a financial advisor. Analyze the following financial data and provide 3-5 key insights and recommendations. " .
               "Be concise, specific, and actionable. Format the response with markdown. " .
               "Here's the data: " . json_encode($data, JSON_PRETTY_PRINT);
    }

    protected function formatInsights(string $insightText): array
    {
        // Parse markdown into an array of insights
        $insights = array_filter(
            array_map('trim', 
                explode('\n', preg_replace('/^[-*]\s*/m', '', $insightText))
            )
        );

        return array_values($insights);
    }
}
