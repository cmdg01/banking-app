<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Chat extends Component
{
    public $messages = [];
    public $newMessage = '';
    public $isLoading = false;
    public $error = null;
    
    protected $chatService;
    
    public function boot(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }
    
    public function mount()
    {
        // Initialize with a welcome message
        $this->messages = [
            [
                'content' => "Hello " . Auth::user()->name . "! I'm your AI financial assistant. How can I help you today?",
                'isUser' => false,
                'timestamp' => now()->timestamp,
            ]
        ];
    }
    
    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) {
            return;
        }
        
        // Add user message to chat
        $this->messages[] = [
            'content' => $this->newMessage,
            'isUser' => true,
            'timestamp' => now()->timestamp,
        ];
        
        $userMessage = $this->newMessage;
        $this->newMessage = ''; // Clear input field
        $this->error = null; // Clear any previous errors
        
        // Show loading indicator
        $this->isLoading = true;
        
        try {
            // Get response from ChatService
            $response = $this->chatService->generateResponse(Auth::user(), $userMessage);
            
            // Add AI response to chat
            $this->messages[] = [
                'content' => $response,
                'isUser' => false,
                'timestamp' => now()->timestamp,
            ];
        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            $this->error = "Sorry, I couldn't generate a response. Please try again later.";
        } finally {
            $this->isLoading = false;
        }
    }
    
    public function sendQuickAction($action)
    {
        $actionMessages = [
            'create_budget' => "I want to create a new budget.",
            'track_expense' => "I need to add a new expense.",
            'savings_goal' => "Help me set up a savings goal.",
            'spending_analysis' => "Can you analyze my spending patterns?"
        ];
        
        if (isset($actionMessages[$action])) {
            $this->newMessage = $actionMessages[$action];
            $this->sendMessage();
        }
    }
    
    public function render()
    {
        return view('livewire.chat');
    }
}
