<x-layouts.app :title="__('Dashboard')">
    <!-- Gradient Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-6 mb-6 text-white">
        <h1 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-blue-100 opacity-90">Here's what's happening with your accounts</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Total Balance -->
        <div class="bg-white dark:bg-zinc-800 p-5 rounded-xl border border-neutral-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Balance</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($totalBalance, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Connected Banks -->
        <div class="bg-white dark:bg-zinc-800 p-5 rounded-xl border border-neutral-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Connected Banks</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $linkedAccounts }}</p>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white dark:bg-zinc-800 p-5 rounded-xl border border-neutral-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Recent Transactions</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recentTransactions->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-zinc-800 p-5 rounded-xl border border-neutral-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Quick Actions</p>
                    <div class="flex space-x-2">
                        <a href="{{ route('banks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-2 px-3 rounded-lg transition-colors">
                            Connect Bank
                        </a>
                        <a href="{{ route('transfers.create') }}" class="bg-green-600 hover:bg-green-700 text-white text-xs font-medium py-2 px-3 rounded-lg transition-colors">
                            New Transfer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Bank Accounts -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-neutral-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="p-6 pb-4 border-b border-neutral-200 dark:border-zinc-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Bank Accounts</h2>
                        <a href="{{ route('banks.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">View All</a>
                    </div>
                </div>
                
                @if($banks->isEmpty())
                    <div class="p-8 text-center">
                        <div class="mx-auto flex h-12 w-12 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No bank accounts</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by connecting your first bank account.</p>
                        <div class="mt-6">
                            <a href="{{ route('banks.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Connect Bank
                            </a>
                        </div>
                    </div>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($banks->take(3) as $bank)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                        {{ strtoupper(substr($bank->institution_name, 0, 1)) }}
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $bank->account_name }}</p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($bank->balance_current, 2) }}</p>
                                        </div>
                                        <div class="flex items-center justify-between mt-1">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $bank->institution_name }} ••••{{ $bank->account_mask }}</p>
                                            <p class="text-xs text-green-600 dark:text-green-400">
                                                Available: ${{ number_format($bank->balance_available ?? 0, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($banks->count() > 3)
                        <div class="px-6 py-3 text-center border-t border-gray-200 dark:border-zinc-700">
                            <a href="{{ route('banks.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                                View all {{ $banks->count() }} accounts
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-neutral-200 dark:border-zinc-700 overflow-hidden shadow-sm h-full flex flex-col">
                <div class="p-6 pb-4 border-b border-neutral-200 dark:border-zinc-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Transactions</h2>
                        <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">View All</a>
                    </div>
                </div>
                
                @if($recentTransactions->isEmpty())
                    <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
                        <div class="mx-auto flex h-12 w-12 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No transactions</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your transactions will appear here.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-zinc-700 flex-1 overflow-y-auto" style="max-height: 400px;">
                        @foreach($recentTransactions as $transaction)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full {{ $transaction->amount < 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' }} flex items-center justify-center">
                                        @if($transaction->amount < 0)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-4 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $transaction->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $transaction->date->format('M d, Y') }} • {{ $transaction->bank->institution_name }}
                                        </p>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-sm font-medium {{ $transaction->amount < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            {{ $transaction->amount < 0 ? '-' : '' }}${{ number_format(abs($transaction->amount), 2) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $transaction->category ?? 'Uncategorized' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const insightsContainer = document.getElementById('aiInsightsContainer');
            const insightsContent = document.getElementById('insightsContent');
            const loadingElement = document.getElementById('insightsLoading');
            const errorElement = document.getElementById('insightsError');
            const errorDetailElement = document.getElementById('insightsErrorDetail');
            const refreshButton = document.getElementById('refreshInsights');
            const retryButton = document.getElementById('retryInsights');
            let isLoading = false;

            async function loadInsights() {
                if (isLoading) return;
                
                // Show loading, hide error and previous content
                loadingElement.classList.remove('hidden');
                errorElement.classList.add('hidden');
                insightsContent.innerHTML = '';
                isLoading = true;
                
                // Disable refresh button while loading
                if (refreshButton) {
                    refreshButton.disabled = true;
                    refreshButton.innerHTML = `
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    `;
                }
                
                try {
                    const response = await fetch('{{ route("ai.insights") }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        method: 'GET'
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.error || `HTTP error! status: ${response.status}`);
                    }
                    
                    // Hide loading
                    loadingElement.classList.add('hidden');
                    
                    if (data.insights && data.insights.length > 0) {
                        insightsContent.innerHTML = data.insights.map(insight => `
                            <div class="p-4 bg-gray-50 dark:bg-zinc-700/30 rounded-lg border-l-4 border-blue-500 mb-3">
                                <p class="text-gray-800 dark:text-gray-200">${insight}</p>
                            </div>
                        `).join('');
                    } else if (data.raw) {
                        insightsContent.innerHTML = `
                            <div class="p-4 bg-gray-50 dark:bg-zinc-700/30 rounded-lg">
                                <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line">${data.raw}</p>
                            </div>
                        `;
                    } else {
                        throw new Error('No insights available');
                    }
                    
                } catch (error) {
                    console.error('Error loading insights:', error);
                    
                    // Show error message
                    errorDetailElement.textContent = error.message || 'An unknown error occurred';
                    errorElement.classList.remove('hidden');
                    loadingElement.classList.add('hidden');
                } finally {
                    isLoading = false;
                    
                    // Re-enable refresh button
                    if (refreshButton) {
                        refreshButton.disabled = false;
                        refreshButton.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                        `;
                    }
                }
            }

            // Initial load
            loadInsights();

            // Set up refresh button
            if (refreshButton) {
                refreshButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadInsights();
                });
            }

            // Set up retry button
            if (retryButton) {
                retryButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    errorElement.classList.add('hidden');
                    loadInsights();
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>
