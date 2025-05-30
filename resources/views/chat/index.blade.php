<x-layouts.app :title="__('Chat')">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Financial Assistant') }}
            </h2>
            <div class="flex space-x-2">
                <button id="suggest-budget" class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200">
                    Budget Help
                </button>
                <button id="suggest-expenses" class="px-3 py-1 text-xs bg-green-100 text-green-800 rounded-full hover:bg-green-200 dark:bg-green-900 dark:text-green-200">
                    Track Expenses
                </button>
                <button id="suggest-savings" class="px-3 py-1 text-xs bg-purple-100 text-purple-800 rounded-full hover:bg-purple-200 dark:bg-purple-900 dark:text-purple-200">
                    Savings Tips
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Quick Action Cards -->
                <div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 px-1">QUICK ACTIONS</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button class="quick-action group" data-action="create_budget">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center mb-2 group-hover:bg-blue-100 dark:group-hover:bg-blue-800/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Create Budget</span>
                        </button>
                        
                        <button class="quick-action group" data-action="track_expense">
                            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-2 group-hover:bg-green-100 dark:group-hover:bg-green-800/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">Add Expense</span>
                        </button>

                        <button class="quick-action group" data-action="savings_goal">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center mb-2 group-hover:bg-purple-100 dark:group-hover:bg-purple-800/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Savings Goal</span>
                        </button>

                        <button class="quick-action group" data-action="spending_analysis">
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
                <div class="h-[500px] overflow-y-auto p-4 space-y-4" idvf="chat-messages">
                    <!-- Welcome Message -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="bg-blue-50 dark:bg-gray-700 rounded-2xl rounded-tl-none px-4 py-3 max-w-[85%] shadow-sm">
                            <p class="text-gray-800 dark:text-gray-200 font-medium">Hello! I'm your financial assistant</p>
                            <p class="text-gray-700 dark:text-gray-300 mt-1">I can help you with:</p>
                            <ul class="list-disc pl-5 mt-2 space-y-1 text-gray-700 dark:text-gray-300 text-sm">
                                <li>Creating and managing budgets</li>
                                <li>Tracking expenses and income</li>
                                <li>Setting savings goals</li>
                                <li>Analyzing spending patterns</li>
                                <li>Financial planning and advice</li>
                            </ul>
                            <p class="mt-2 text-blue-600 dark:text-blue-400 font-medium">How can I assist you today?</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Just now</p>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="border-t border-gray-100 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                    <form id="chat-form" class="flex items-center gap-2">
                        <input 
                            type="text" 
                            id="user-input"
                            placeholder="Ask me about budgeting, expenses, or financial advice..." 
                            class="flex-1 rounded-full border-0 bg-gray-100 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-4 py-3 text-sm transition-colors duration-200"
                            autocomplete="off"
                        >
                        <button type="submit" id="send-message" class="p-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatMessages = document.getElementById('chat-messages');
        const userInput = document.getElementById('user-input');
        const sendButton = document.getElementById('send-message');
        const chatForm = document.getElementById('chat-form');
        const quickActions = document.querySelectorAll('.quick-action');
        
        // Function to send a message
        function sendMessage() {
            const message = userInput.value.trim();
            if (message === '') return;
            
            // Add user message to chat
            addMessage(message, true);
            userInput.value = '';
            
            // Show typing indicator
            showTypingIndicator();
            
            // Send message to server
            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Hide typing indicator
                hideTypingIndicator();
                
                if (data.success) {
                    // Show AI response
                    showResponse(data.message);
                } else {
                    // Show error message
                    showErrorMessage(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                // Hide typing indicator
                hideTypingIndicator();
                
                // Show error message
                console.error('Error:', error);
                showErrorMessage('Sorry, there was an error processing your request.');
            });
        }
        
        // Function to show typing indicator
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typing-indicator';
            typingDiv.className = 'flex items-start gap-3 message';
            typingDiv.innerHTML = `
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
            `;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Function to hide typing indicator
        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
        
        // Function to show error message
        function showErrorMessage(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'flex items-start gap-3 message';
            errorDiv.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg px-4 py-2 max-w-[80%] shadow-sm">
                    <p class="text-sm text-red-800 dark:text-red-200">${message}</p>
                </div>
            `;
            chatMessages.appendChild(errorDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Function to show response
        function showResponse(message) {
            addMessage(message);
        }
        
        // Function to add a message to the chat
        function addMessage(message, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex items-start gap-3 ${isUser ? 'justify-end' : ''} message`;
            
            if (!isUser) {
                messageDiv.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-300" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="bg-blue-50 dark:bg-gray-700 rounded-2xl rounded-tl-none px-4 py-3 max-w-[85%] shadow-sm">
                        <p class="text-gray-800 dark:text-gray-200">${message}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Just now</p>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none px-4 py-3 max-w-[85%] shadow-sm">
                        <p>${message}</p>
                        <p class="text-xs text-blue-100 mt-2">Just now</p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-gray-600 dark:text-gray-300 text-sm font-medium">ME</span>
                    </div>
                `;
            }
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return messageDiv;
        }

        // Handle quick action buttons
        quickActions.forEach(button => {
            button.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                console.log('Quick action clicked:', action);
                
                // Add user message based on the action
                let userMessage = '';
                switch(action) {
                    case 'create_budget':
                        userMessage = "I want to create a budget";
                        break;
                    case 'track_expense':
                        userMessage = "I need to track my expenses";
                        break;
                    case 'savings_goal':
                        userMessage = "Help me set a savings goal";
                        break;
                    case 'spending_analysis':
                        userMessage = "Analyze my spending patterns";
                        break;
                    default:
                        userMessage = "I need help with my finances";
                }
                
                // Add user message to chat
                addMessage(userMessage, true);
                
                // Show typing indicator
                showTypingIndicator();
                
                // Send message to server
                fetch('{{ route("chat.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: userMessage })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    // Hide typing indicator
                    hideTypingIndicator();
                    
                    if (data.success) {
                        // Show AI response
                        showResponse(data.message);
                    } else {
                        // Show error message
                        showErrorMessage(data.message || 'An error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Hide typing indicator
                    hideTypingIndicator();
                    
                    // Show error message
                    showErrorMessage('Sorry, there was an error processing your request.');
                });
            });
        });

        // Handle form submission
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            console.log('Form submitted');
            sendMessage();
        });

        // Handle Enter key in input (redundant but kept for accessibility)
        userInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    });
    </script>
    <style>
        /* Chat message animations */
        @keyframes messageIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message {
            animation: messageIn 0.2s ease-out forwards;
            opacity: 0;
        }

        /* Custom scrollbar */
        #chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        #chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        #chat-messages::-webkit-scrollbar-thumb {
            background-color: #cbd5e0;
            border-radius: 3px;
        }

        .dark #chat-messages::-webkit-scrollbar-thumb {
            background-color: #4a5568;
        }

        /* Message bubble styling */
        .message-bubble {
            position: relative;
            border-radius: 1.25rem;
            padding: 0.75rem 1rem;
            max-width: 85%;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            line-height: 1.4;
        }

        .message-bubble.user {
            background-color: #3b82f6;
            color: white;
            border-bottom-right-radius: 0.25rem;
            margin-left: auto;
        }

        .message-bubble.assistant {
            background-color: #f3f4f6;
            color: #1f2937;
            border-top-left-radius: 0.25rem;
            margin-right: auto;
        }

        .dark .message-bubble.assistant {
            background-color: #374151;
            color: #f3f4f6;
        }

        /* Typing indicator */
        .typing-indicator {
            display: flex;
            gap: 0.25rem;
            padding: 0.75rem 1rem;
            background-color: #f3f4f6;
            border-radius: 1.25rem;
            width: fit-content;
            margin-right: auto;
        }

        .dark .typing-indicator {
            background-color: #374151;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background-color: #9ca3af;
            border-radius: 50%;
            display: inline-block;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
    @endpush
</x-layouts.app>
