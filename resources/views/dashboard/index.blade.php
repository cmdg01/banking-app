<x-layouts.app :title="__('Dashboard')">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 dark:text-white leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg overflow-hidden mb-8 text-white">
                <div class="p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                            <h2 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h2>
                            <p class="text-blue-100">Here's what's happening with your accounts</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <span class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm font-medium">
                                <span class="h-2 w-2 rounded-full bg-green-400 mr-2"></span>
                                Online Banking
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Balance -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Balance</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($totalBalance, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Linked Accounts -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Linked Accounts</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $linkedAccounts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Recent Transactions</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recentTransactionCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Financial Insights Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">AI Financial Assistant</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Budgeting Tips -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-3 rounded-full bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h4 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Budgeting Tips</h4>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Dining Out</p>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="bg-red-500 h-2.5 rounded-full" style="width: 75%"></div>
                                    </div>
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">You've spent $225 of $300 budget (75%)</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">AI Tip: Consider reducing dining out by 2 times this week to stay on budget.</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Entertainment</p>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="bg-yellow-500 h-2.5 rounded-full" style="width: 45%"></div>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">$90 of $200 budget remaining</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Spending Analysis -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-3 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h4 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Spending Analysis</h4>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">Top Category This Month</p>
                                    <p class="font-medium dark:text-white">🛍 Shopping <span class="text-gray-500 dark:text-gray-400">(35% of expenses)</span></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">Monthly Change</p>
                                    <p class="font-medium text-green-600 dark:text-green-400">↓ 12% decrease from last month</p>
                                </div>
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">You're on track to save $300 this month! Keep it up!</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Recommendations -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-3 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <h4 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Smart Recommendations</h4>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-600 dark:text-gray-300">Increase your savings by $50/month to reach your goal 3 months earlier</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-600 dark:text-gray-300">Consider opening a high-yield savings account (3.5% APY)</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="flex items-center justify-center h-5 w-5 rounded-full bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-400">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="ml-3 text-sm text-gray-600 dark:text-gray-300">Review recurring subscriptions - potential $29.99/month savings</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('banks.create') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors duration-200 text-center">
                                <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-full text-blue-600 dark:text-blue-400 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Link Account</span>
                            </a>
                            <a href="{{ route('transfers.create') }}" class="flex flex-col items-center justify-center p-4 bg-green-50 dark:bg-green-900/30 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors duration-200 text-center">
                                <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full text-green-600 dark:text-green-400 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Transfer Money</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Dwolla Status -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Status</h3>
                        @if(auth()->user()->hasDwollaAccount())
                            <div class="flex items-center p-4 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-full text-green-600 dark:text-green-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800 dark:text-green-400">Dwolla Connected</p>
                                    <p class="text-xs text-green-600 dark:text-green-300">Your account is ready for transfers</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center p-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg">
                                <div class="p-2 bg-yellow-100 dark:bg-yellow-900/50 rounded-full text-yellow-600 dark:text-yellow-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-400">Action Required</p>
                                    <p class="text-xs text-yellow-600 dark:text-yellow-300">Connect your Dwolla account to enable transfers</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
                        <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200">
                            View All
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    @if($recentTransactions->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No transactions yet</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your transactions will appear here</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recentTransactions as $transaction)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $transaction->date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $transaction->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $transaction->category->name ?? 'Uncategorized' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $transaction->account->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $transaction->amount < 0 ? 'text-red-600' : 'text-green-600' }} dark:{{ $transaction->amount < 0 ? 'text-red-400' : 'text-green-400' }}">
                                                {{ $transaction->amount < 0 ? '-' : '+' }}${{ number_format(abs($transaction->amount), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
