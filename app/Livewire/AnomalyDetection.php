<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\AnomalyDetectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;

class AnomalyDetection extends Component
{
    public $anomalies = [];
    public $loading = true;
    public $feedback = '';
    public $showFeedbackModal = false;
    public $currentTransactionId = null;
    public $isLegitimate = null;
    
    protected $anomalyDetectionService;
    
    protected $listeners = [
        'refreshAnomalies' => 'loadAnomalies'
    ];
    
    public function boot(AnomalyDetectionService $anomalyDetectionService)
    {
        $this->anomalyDetectionService = $anomalyDetectionService;
    }
    
    public function mount()
    {
        $this->loadAnomalies();
    }
    
    public function loadAnomalies()
    {
        $this->loading = true;
        
        try {
            $user = Auth::user();
            $this->anomalies = $this->anomalyDetectionService->detectAnomalies($user);
        } catch (\Exception $e) {
            Log::error('Error loading anomalies', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->anomalies = collect();
        }
        
        $this->loading = false;
    }
    
    public function openFeedbackModal($transactionId, $isLegitimate)
    {
        $this->currentTransactionId = $transactionId;
        $this->isLegitimate = $isLegitimate;
        $this->feedback = '';
        $this->showFeedbackModal = true;
    }
    
    public function closeFeedbackModal()
    {
        $this->showFeedbackModal = false;
        $this->currentTransactionId = null;
        $this->isLegitimate = null;
        $this->feedback = '';
    }
    
    public function submitFeedback()
    {
        if (!$this->currentTransactionId) {
            $this->closeFeedbackModal();
            return;
        }
        
        try {
            $user = Auth::user();
            $transaction = Transaction::where('user_id', $user->id)
                ->where('id', $this->currentTransactionId)
                ->firstOrFail();
            
            // Update transaction with review status
            $transaction->update([
                'is_reviewed' => true,
                'is_legitimate' => $this->isLegitimate,
                'review_feedback' => $this->feedback,
                'reviewed_at' => now(),
                'anomaly_data' => [
                    'reviewed_by' => $user->id,
                    'review_date' => now()->toIso8601String(),
                    'is_legitimate' => $this->isLegitimate,
                ]
            ]);
            
            // Remove the transaction from the anomalies collection
            $this->anomalies = $this->anomalies->reject(function ($anomaly) {
                return $anomaly->id === $this->currentTransactionId;
            });
            
            $this->dispatch('transaction-reviewed', [
                'message' => 'Transaction successfully reviewed',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Error reviewing transaction', [
                'transaction_id' => $this->currentTransactionId,
                'error' => $e->getMessage()
            ]);
            
            $this->dispatch('transaction-reviewed', [
                'message' => 'Failed to update transaction',
                'type' => 'error'
            ]);
        }
        
        $this->closeFeedbackModal();
    }
    
    public function refreshData()
    {
        $this->loadAnomalies();
        
        $this->dispatch('data-refreshed', [
            'message' => 'Anomaly data refreshed',
            'type' => 'success'
        ]);
    }
    
    public function render()
    {
        return view('livewire.anomaly-detection');
    }
}
