<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClaudeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialInsightController extends Controller
{
    protected ClaudeService $claudeService;

    public function __construct(ClaudeService $claudeService)
    {
        $this->middleware('auth:sanctum');
        $this->claudeService = $claudeService->forUser(Auth::user());
    }

    /**
     * Get financial insights for the authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getInsights(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'months' => 'sometimes|integer|min:1|max:24',
        ]);

        $months = $validated['months'] ?? 3;

        try {
            $result = $this->claudeService->getUserFinancialInsights($months);
            
            if (!$result['success']) {
                return response()->json([
                    'message' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'insights' => $result['insights'],
                    'time_period' => "last {$months} months"
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting financial insights: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate financial insights. Please try again later.'
            ], 500);
        }
    }
}
