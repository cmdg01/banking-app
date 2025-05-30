<div>
    <?php

use function Livewire\Volt\{state, mount, computed};
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;

state([
    'messages' => [],
    'newMessage' => '',
    'isLoading' => false,
    'error' => null
]);

mount(function () {
    // Add welcome message
    $this->messages = [
        [
            'content' => "Hello " . Auth::user()->name . "! I'm your financial assistant. How can I help you today?",
            'isUser' => false,
            'timestamp' => now()->timestamp
        ]
    ];
});

$sendMessage = function () {
    if (empty($this->newMessage)) {
        return;
    }

    // Add user message to chat
    $this->messages[] = [
        'content' => $this->newMessage,
        'isUser' => true,
        'timestamp' => now()->timestamp
    ];

    $userMessage = $this->newMessage;
    $this->newMessage = '';
    $this->isLoading = true;
    $this->error = null;

    try {
        $chatService = app(ChatService::class);
        $response = $chatService->generateResponse($userMessage);

        if (is_array($response) && isset($response['error'])) {
            $this->error = $response['error'];
        } else {
            // Add AI response to chat
            $this->messages[] = [
                'content' => $response,
                'isUser' => false,
                'timestamp' => now()->timestamp
            ];
        }
    } catch (\Exception $e) {
        $this->error = "Sorry, I couldn't process your request. Please try again.";
    } finally {
        $this->isLoading = false;
    }
};

$sendQuickAction = function ($action) {
    $actionMessages = [
        'create_budget' => "I want to create a budget",
        'track_expense' => "I need to track my expenses",
        'savings_goal' => "Help me set a savings goal",
        'spending_analysis' => "Analyze my spending patterns"
    ];

    $message = $actionMessages[$action] ?? "I need help with my finances";
    $this->newMessage = $message;
    $this->sendMessage();
};

?>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Quick Action Cards -->
                <div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 px-1">QUICK ACTIONS</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button wire:click="sendQuickAction('create_budget')" class="quick-action group">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center mb-2 group-hover:bg-blue-100 dark:group-hover:bg-blue-800/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Create Budget</span>
                        </button>
                        
                        <button wire:click="sendQuickAction('track_expense')" class="quick-action group">
                            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-2 group-hover:bg-green-100 dark:group-hover:bg-green-800/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">Add Expense</span>
                        </button>

                        <button wire:click="sendQuickAction('savings_goal')" class="quick-action group">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center mb-2 group-hover:bg-purple-100 dark:group-hover:bg-purple-800/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Savings Goal</span>
                        </button>
                        
                        <button wire:click="sendQuickAction('spending_analysis')" class="quick-action group">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center mb-2 group-hover:bg-amber-100 dark:group-hover:bg-amber-800/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Spending Analysis</span>
                        </button>
                    </div>
                </div>
                
                <!-- Chat Messages -->
                <div id="chat-messages" class="h-[500px] overflow-y-auto p-4 space-y-4">
                    @foreach($messages as $message)
                        <div class="flex items-start gap-3 {{ $message['isUser'] ? 'justify-end' : '' }} message animate-fade-in">
                            @if(!$message['isUser'])
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-300" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="bg-blue-50 dark:bg-gray-700 rounded-2xl rounded-tl-none px-4 py-3 max-w-[85%] shadow-sm">
                                    <p class="text-gray-800 dark:text-gray-200 text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                                </div>
                            @else
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl rounded-tr-none px-4 py-3 max-w-[85%] shadow-sm ml-auto">
                                    <p class="text-white text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <span class="text-white text-xs font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    
                    @if($isLoading)
                        <div class="flex items-start gap-3 message animate-fade-in">
                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-300" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 max-w-[80%] shadow-sm">
                                <div class="flex space-x-2">
                                    <div class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse"></div>
                                    <div class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse" style="animation-delay: 0.2s"></div>
                                    <div class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse" style="animation-delay: 0.4s"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($error)
                        <div class="flex items-start gap-3 message animate-fade-in">
                            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg px-4 py-2 max-w-[80%] shadow-sm">
                                <p class="text-sm text-red-800 dark:text-red-200">{{ $error }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Chat Input -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                    <form wire:submit.prevent="sendMessage" class="flex gap-2">
                        <div class="flex-1 relative">
                            <textarea 
                                wire:model="newMessage" 
                                id="user-input" 
                                rows="1"
                                placeholder="Type your message..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-full bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 resize-none"
                                wire:keydown.enter.prevent="$event.shiftKey || sendMessage()"
                            ></textarea>
                        </div>
                        <button type="submit" class="p-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed" {{ $isLoading ? 'disabled' : '' }}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Chat message animations */
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out forwards;
        }
        
        /* Auto-resize textarea */
        textarea {
            min-height: 40px;
            max-height: 120px;
            overflow-y: auto;
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatMessages = document.getElementById('chat-messages');
            
            // Auto-scroll to bottom when new messages arrive
            Livewire.hook('message.processed', (message, component) => {
                if (component.name === 'chat') {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
            
            // Initialize scroll position
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Auto-resize textarea
            const textarea = document.getElementById('user-input');
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    </script>
</div>
